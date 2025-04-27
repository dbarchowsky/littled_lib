<?php
namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\DuplicateRecordException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\Validation\Validation;


abstract class SerializedRecordList extends SerializedContentIO
{
    public bool                 $allow_duplicates = false;
    protected array             $records = [];
    protected static string     $content_class;

    /**
     * Adds record id to existing list of record ids.
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
     * Push link object onto the list, at the end of the list.
     * @param LinkedContent $link
     * @return $this
     * @throws DuplicateRecordException
     */
    public function addLink(LinkedContent $link): static
    {
        $this->checkForPreexistingLink($link);
        $this->pushLink($link);
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
        $link_id = $this->getChildRecordId($link);
        if (!$this->allow_duplicates &&
            $link_id > 0 &&
            ($this->lookupRecordById($link_id) !== false)) {
            throw new DuplicateRecordException(
                'A '. strtolower($this->getContentLabel()) . " record with id $link_id already exists.");
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
                            ->setIndex($i)
                            ->collectRequestData($src)
                            ->collectKeysRequestData($src);
                    }
                }
                else {
                    // single link value
                    $o = new static::$content_class();
                    $o->collectRequestData($src);
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
        return 'The '.strtolower($class::getContentLabel()).' records were deleted. ';
    }

    /**
     * Deletes any stale links between the two tables.
     * @param int[] $link_ids
     * @return void
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
            if (in_array($this->records[$i]->getLinkId(), $stale_link_ids)) {
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
     * @throws NotInitializedException
     */
    protected function getContentTableName(): string
    {
        $table = call_user_func([static::$content_class, 'getTableName']);
        if (!trim($table)) {
            $err_msg = 'A table has not been assigned within ' . Log::getClassBaseName(static::$content_class) . '.';
            throw new NotInitializedException($err_msg);
        }
        return $table;
    }

    /**
     * Returns the record id value of the linked record from the external table.
     * @return int|null
     */
    abstract public function getLinkedId(): int|null;

    /**
     * Returns all currently stored link record id values in an array
     * @return int[]
     */
    public function getLinkIds(): array
    {
        return array_map(fn($e): int => (int)$e->id->safeValue(), $this->records);
    }

    /**
     * Return the key representing the value that links the list of records to a linked table.
     * @return string
     */
    abstract protected function getLinkedKey(): string;

    /**
     * Get the name of the property representing link to foreign table.
     * @return string
     */
    abstract protected static function getLinkedPropertyName(): string;

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

        $stale_link_ids = array_map(fn($e): int => $this->getChildRecordId($e), $this->records);
        $filtered = array_filter($stale_link_ids, function($e) use ($link_ids) { return !in_array($e, $link_ids); });
        return array_values($filtered);
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
     * Create a new linked record and assign it's record id value.
     * @param int|null $record_id
     * @return LinkedContent
     * @throws NotInitializedException
     */
    protected function instantiateChild(int $record_id=null): LinkedContent
    {
        if (!isset(static::$content_class)) {
            throw new NotInitializedException('Content class property has not been assigned a value.');
        }
        $c = (new static::$content_class());
        if ($this->getParentId() > 0) {
            $c->setLinkedId($this->getParentId());
        }
        return $c->setRecordId($record_id);
    }

    /**
     * With no argument, returns all records currently attached to the object. With an argument, returns a specific
     * record in the list, at the $index position.
     * @param int|null $index
     * @return LinkedContent|LinkedContent[]
     * @throws InvalidValueException
     */
    public function items(?int $index = null): LinkedContent|array
    {
        if ($index === null) {
            return $this->records;
        }
        if (count($this->records) < $index+1) {
            throw new InvalidValueException('Requested index is out of bounds.');
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
        for($i = 0; $i < count($this->records); $i++) {
            if ($this->records[$i]->getRecordId() === $record_id) {
                return $i;
            }
        }
        return false;
    }

    /**
     * Push link on stack and make necessary updates to the state of the list of linked records.
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
     * @inheritDoc
     * @throws FailedQueryException
     */
    public function read(): static
    {
        $this->clearLinks();
        $data = $this->fetchRecords(...$this->formatRecordSelectPreparedStmt());
        foreach($data as $row) {
            $o = new static::$content_class();
            $o->hydrateFromRecordsetRow($row);
            $this->records[] = $o;
        }
        return $this;
    }

    /**
     * Remove records with link id values matching values in $link_ids from the current list of linked records.
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
    public function save(): void
    {
        foreach($this->records as $record) {
            $record->save();
        }
    }

    /**
     * Linked record id value setter.
     * @param int|null $record_id
     * @return $this
     */
    public function setLinkedId(?int $record_id): static
    {
        foreach($this->records as $record) {
            $record->setLinkedId($record_id);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRecordId(?int $record_id): static
    {
        return $this->setLinkedId($record_id);
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
     * Unshift link onto stack (at the beginning of the stack) and make necessary updates to the state of the list of
     * linked records.
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

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException($this->validation_message);
        }
    }
}