<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\DuplicateRecordException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\OutOfBoundsException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\ForeignKeyInput;
use Littled\Validation\Validation;
use Error;
use TypeError;


abstract class SerializedRecordList extends SerializedContentIO
{
    public bool                 $allow_duplicates = false;
    public ForeignKeyInput      $parent_id;
    protected array             $records = [];
    protected static string     $content_class;

    public function __construct()
    {
        parent::__construct();
        $this->parent_id = (new ForeignKeyInput())
            ->setLabel('Primary id')
            ->setKey(LittledGlobals::ID_KEY)
            ->setAsNotRequired();
    }

    /**
     * Push a link object onto the list, at the end of the list.
     * @param LinkedContent $link
     * @return $this
     * @throws DuplicateRecordException
     */
    public function addLink(LinkedContent $link): static
    {
        $this->checkForPreexistingLink($link);
        $this->pushLink($link->setParentId($this->getParentId()));
        return $this;
    }

    /**
     * Adds record id to the existing list of record ids.
     * @param int|int[] $value
     * @return $this
     * @throws NotInitializedException
     */
    public function addLinkByRecordId(array|int $value): static
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach($value as $record_id) {
            if ($this->allow_duplicates) {
                $this->pushLink($this->instantiateChild($record_id));
            }
            elseif ($this->lookupRecordById($record_id) === false) {
                $this->pushLink($this->instantiateChild($record_id));
            }
        }
        return $this;
    }

    /**
     * Tests if a record already exists in the stack matching the specified $link record.
     * @param LinkedContent $link
     * @return void
     * @throws DuplicateRecordException
     */
    protected function checkForPreexistingLink(LinkedContent $link): void
    {
        if (!$this->allow_duplicates &&
            $link->getLinkedId() > 0 &&
            ($this->lookupRecordById($link->getLinkedId()) !== false)) {
            throw new DuplicateRecordException(
                'A '. strtolower($this->getContentLabel()) . ' record with id ' . $link->getLinkedId() . ' already exists.');
        }
    }

    /**
     * Clears the list of records stored by the object (without making any changes to data in the database).
     * @return $this
     */
    public function clearLinks(): static
    {
        for($i=count($this->records)-1; $i >= 0; $i--) {
            unset($this->records[$i]);
        }
        $this->records = array_values($this->records);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NotInitializedException|ConfigurationUndefinedException
     * @return $this
     */
    public function collectRequestData(?array $src = null): static
    {
        if (isset(static::$content_class)) {
            $key = $this->getLinkedKey();
            $src = $src ?? Validation::getDefaultInputSource();
            if (array_key_exists($key, $src)) {
                if (is_array($src[$key])) {
                    // processing an array of record ids, one for each linked record
                    for($i = 0; $i < count($src[$key]); $i++) {
                        $this->records[$i] = new static::$content_class();
                        $this->records[$i]
                            ->shareConnection($this)
                            ->setIndex($i)
                            ->collectRequestData($src)
                            ->collectKeysRequestData($src);
                    }
                }
                else {
                    // single link value
                    $o = new static::$content_class();
                    $o
                        ->shareConnection($this)
                        ->collectRequestData($src);
                    $this->records[] = $o;
                }
            }
        }
        return parent::collectRequestData($src);
    }

    /**
     * Returns TRUE if $link_id value is found in the instance's current link id values.
     * @param int $record_id Link record id to look up.
     * @return bool TRUE if $link_id is found in the instance's current link id values.
     */
    protected function containsRecordId(int $record_id): bool
    {
        foreach($this->records as $record) {
            if ($this->getChildRecordId($record)  === $record_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws FailedQueryException
     * @throws InvalidStateException
     * @throws RecordNotFoundException
     */
    public function delete(): string
    {
        foreach($this->records as $record) {
            $record->delete();
        }
        $this->records = [];
        /** @var SerializedContent $class */
        $class = static::$content_class;
        return 'The '.strtolower((new $class())->getContentLabel()).' records were deleted. ';
    }

    /**
     * Deletes any stale links between the two tables.
     * @param int[] $link_ids
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws InvalidStateException
     * @throws NotInitializedException
     */
    public function deleteStaleLinks(array $link_ids): void
    {
        $stale_link_ids = $this->getStaleLinkIds($link_ids);
        $args = $this->formatDeleteStaleLinksStmt($stale_link_ids);
        $this->query(...$args);

        for ($i = count($this->records) - 1; $i >= 0; $i--) {
            if (in_array($this->records[$i]->getLinkedId(), $stale_link_ids)) {
                unset($this->records[$i]);
            }
        }
        $this->records = array_values($this->records);
    }

    /**
     * @inheritDoc
     * Doesn't return anything. Committing its data means saving data stored in its $records property.
     */
    protected function formatCommitQuery(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws NotInitializedException
     * @throws ConnectionException
     */
    protected function formatRecordSelectQuery(): array
    {
        $c = static::getContentClass();
        try {
            $fields = (new $c())->extractPreparedStmtArgs();
        } catch (Error|TypeError) {
            throw new ConfigurationUndefinedException('A content class has not been defined for ' . basename(str_replace('\\', '/', static::class)));
        }
        $query = 'SELECT `' .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) .
            '` FROM `' . static::getTableName(). '` '.
            'WHERE `' .$this->parent_id->getColumnName('parent_id') . '` = ? ';
        return [$query, 'i', &$this->parent_id->value];
    }

    /**
     * Return SQL statement to use to delete stale linked records.
     * @param array $stale_link_ids
     * @return array
     * @throws ConfigurationUndefinedException
     */
    abstract protected function formatDeleteStaleLinksStmt(array $stale_link_ids): array;

    /**
     * @inheritDoc
     * @throws NotImplementedException
     */
    protected function executeCommitQuery(): void
    {
        throw new NotImplementedException('executeCommitQuery() is not implemented for JunctionRecordList.');
    }

    /**
     * Allow duplicates flag value getter.
     * @return bool
     */
    public function getAllowDuplicates(): bool
    {
        return $this->allow_duplicates;
    }

    /**
     * Get the record id of a single record linked to the parent record.
     * @param LinkedContent $e
     * @return int|null
     */
    protected function getChildRecordId(LinkedContent $e): int|null
    {
        return $e->getRecordId();
    }

    /**
     * Content class property value getter.
     * @return string
     */
    public static function getContentClass(): string
    {
        return static::$content_class ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        $label = '';
        try {
            $links = $this->items();
            if (count($links) > 0) {
                $label = $links[0]->getContentLabel();
            }
        }
        catch (InvalidValueException) {
        }
        if (!$label) {
            $class = static::$content_class;
            $o = new $class();
            $label = $o->getContentLabel();
            unset ($o);
        }
        return $label;
    }

    /**
     * Get the name of the database table holding this object's data.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function _getTableName(): string
    {
        $content_class = static::getContentClass();
        if ($content_class === '') {
            throw new ConfigurationUndefinedException('A content class has not been defined for ' . basename(str_replace('\\', '/', static::class)));
        }
        return call_user_func([$content_class, 'getTableName']);
    }

    /**
     * Returns all currently stored link record id values in an array
     * @return int[]
     */
    public function getLinkIds(): array
    {
        return array_map(fn($e): int => (int)$e->getLinkedId(), $this->records);
    }

    /**
     * Return the key representing the value that links the list of records to a linked table.
     * @return string
     * @throws NotInitializedException
     */
    protected function getLinkedKey(): string
    {
        if (isset($this->records) && count($this->records) > 0) {
            return $this->records[0]->link_id->key;
        }
        else {
            if (!isset(static::$content_class)) {
                throw new NotInitializedException('Content class property has not been assigned a value.');
            }
            $o = new static::$content_class();
            return $o->link_id->key;
        }
    }

    /**
     * Parent id getter
     * @return int|null
     */
    public function getParentId(): int|null
    {
        return $this->parent_id->value;
    }

    /**
     * Parent id property getter.
     * @return ForeignKeyInput
     */
    protected function getParentIdObj(): ForeignKeyInput
    {
        return $this->parent_id;
    }

    /**
     * Returns the key of the parent id property.
     * @return string
     */
    protected function getParentKey(): string
    {
        return $this->parent_id->getKey();
    }

    /**
     * Returns the id values of any records that aren't linked to the parent anymore.
     * @param array $link_ids List of not stale child record ids.
     * @return array
     */
    protected function getStaleLinkIds(array $link_ids): array
    {
        if (count($link_ids) < 1 || count($this->records) < 1) {
            return [];
        }

        $stale_link_ids = array_map(fn($e): int => $e->getLinkedId(), $this->records);
        $filtered = array_filter($stale_link_ids, function($e) use ($link_ids) { return !in_array($e, $link_ids); });
        return array_values($filtered);
    }

    /**
     * @inheritDoc
     * @param bool $check_children Flag to indicate whether to check the child records for data.
     */
    public function hasData(bool $check_children=true): bool
    {
        if (parent::hasData()) {
            return true;
        }
        if ($check_children) {
            foreach ($this->records as $record) {
                if ($record->hasData()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Tests if the record has a primary key that has been assigned a value.
     * @return bool
     */
    protected function hasPrimaryKeyValue(): bool
    {
        return isset($this->id) && $this->id->isDatabaseField() && $this->id->hasData();
    }

    /**
     * Tests if any of the records currently loaded in the object has data to be committed to the database.
     * @return bool
     */
    public function hasRecordData(): bool
    {
        foreach($this->records as $record) {
            if ($record->hasData()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Insert link object onto the list, at the beginning of the list.
     * @param LinkedContent $link
     * @return $this
     * @throws DuplicateRecordException
     */
    public function insertLink(LinkedContent $link): static
    {
        $this->checkForPreexistingLink($link);
        $this->unshiftLink($link);
        return $this;
    }

    /**
     * Create a new linked record and assign its linked record id value.
     * @param int|null $linked_id
     * @return LinkedContent
     * @throws NotInitializedException
     */
    protected function instantiateChild(int|null $linked_id = null): LinkedContent
    {
        if (!isset(static::$content_class)) {
            throw new NotInitializedException('Content class property has not been assigned a value.');
        }
        return (new static::$content_class())
            ->setParentId($this->getParentId())
            ->setLinkedId($linked_id);
    }

    /**
     * @inheritdoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->parent_id->hasData();
    }

    /**
     * @inheritDoc
     */
    public function isRequired(): bool
    {
        if (parent::isRequired()) {
            return true;
        }
        foreach($this->records as $record) {
            if ($record->isRequired()) {
                return true;
            }
        }
        return $this->parent_id->isRequired();
    }

    /**
     * With no argument, returns all records currently attached to the object. With an argument, returns a specific
     * record in the list, at the $index position.
     * @param int|null $index
     * @return LinkedContent|LinkedContent[]
     * @throws OutOfBoundsException
     */
    public function items(?int $index = null): LinkedContent|array
    {
        if ($index === null) {
            return $this->records;
        }
        if (count($this->records) < $index+1) {
            throw new OutOfBoundsException('Requested index is out of bounds.');
        }
        return $this->records[$index];
    }

    /**
     * Returns the index of the record whose link id value matches the supplied value.
     * @param int $record_id
     * @return false|int
     */
    public function lookupRecordById(int $record_id): bool|int
    {
        return array_search($record_id, $this->getLinkIds());
    }

    /**
     * Push link on stack and make the necessary updates to the state of the list of linked records.
     * @param LinkedContent $link
     * @return void
     */
    protected function pushLink(LinkedContent $link): void
    {
        $index = count($this->records);
        $this->records[] = $link;
        $this->records[$index]->setIndex($index);
    }

    /**
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws FailedQueryException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws NotInitializedException
     */
    public function read(): static
    {
        $this->clearLinks();
        $data = $this->fetchRecords(...$this->formatRecordSelectQuery());
        foreach($data as $row) {
            $o = (new static::$content_class())->shareConnection($this);
            $o->hydrateFromRecordsetRow($row);
            if (!$o->getParentId()) {
                /*
                 * assign parent id to a child object if the assignment wasn't made in the hydrate routine
                 */
                $o->setParentId($this->getParentId());
            }
            $this->records[] = $o;
        }
        return $this;
    }

    /**
     * Remove records with link id values that match values in $link_ids from the current list of linked records.
     * @param int|int[] $record_ids
     * @return $this
     */
    public function removeLink(array|int $record_ids): static
    {
        if (!is_array($record_ids)) {
            $record_ids = [$record_ids];
        }
        for($i = count($this->records)-1; $i >= 0; $i--) {
            if (in_array($this->records[$i]->getRecordId(), $record_ids)) {
                unset($this->records[$i]);
            }
        }
        // re-index
        $this->records = array_values($this->records);
        return $this;
    }

    /**
     * @inheritDoc
     * @return void
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function save(): static
    {
        foreach($this->records as $record) {
            $record->save();
        }
        return $this;
    }

    /**
     * Allow duplicates flag value setter.
     * @param bool $flag
     * @return $this
     */
    public function setAllowDuplicates(bool $flag=true): static
    {
        $this->allow_duplicates = $flag;
        return $this;
    }

    /**
     * @return $this
     */
    public function setAsNotRequired(): static
    {
        parent::setAsNotRequired();
        foreach($this->records as $record) {
            $record->setAsNotRequired();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setAsRequired(): static
    {
        parent::setAsRequired();
        foreach($this->records as $record) {
            $record->setAsRequired();
        }
        return $this;
    }

    /**
     * Linked record id value setter.
     * @param int|null $link_id
     * @return $this
     */
    public function setLinkedId(?int $link_id): static
    {
        foreach($this->records as $record) {
            $record->setLinkedId($link_id);
        }
        return $this;
    }

    /**
     * Linked property key value setter.
     * @param string $key
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function setLinkedKey(string $key): static
    {
        foreach($this->records as $record) {
            $record->setLinkedKey($key);
        }
        return $this;
    }

    /**
     * Parent id setter.
     * @param int|null $record_id
     * @return $this
     */
    public function setParentId(int|null $record_id): static
    {
        $this->parent_id->setInputValue($record_id);
        foreach($this->records as $record) {
            $record->setParentId($record_id);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRecordId(?int $record_id): static
    {
       $this->setParentId($record_id);
       return $this;
    }

    /**
     * Sets the object as required in form data depending on $flag value.
     * @param bool $flag
     * @return $this
     */
    public function setRequiredFlag(bool $flag): static
    {
        $method = $flag ? 'setAsRequired' : 'setAsNotRequired';
        return $this->$method($flag);
    }

    /**
     * Unshift the link onto stack (at the beginning of the stack) and make the necessary updates to the state of
     * the list of linked records.
     * @param LinkedContent $link
     * @return void
     */
    protected function unshiftLink(LinkedContent $link): void
    {
        array_unshift($this->records, $link);
        for($i=0; $i< count($this->records); $i++) {
            $this->records[$i]->setIndex($i);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateInput(array $exclude_properties = []): void
    {
        try {
            parent::validateInput($exclude_properties);
        } catch (ContentValidationException) {
            /* continue */
        }

        foreach($this->records as $record) {
            try {
                $record->validateInput($exclude_properties);
            } catch (ContentValidationException) {
                $this->addValidationError($record->validationErrors());
            }
        }
        if ($this->isRequired() && count($this->records) < 1) {
            $this->addValidationError( ucfirst(strtolower(static::getContentLabel())) . ' is required.');
        }

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException($this->validation_message);
        }
    }
}