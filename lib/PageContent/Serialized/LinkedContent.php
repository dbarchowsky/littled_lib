<?php

namespace Littled\PageContent\Serialized;

use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\ForeignKeyInput;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

abstract class LinkedContent extends SerializedContentIO
{
    public IntegerInput         $primary_id;
    public ForeignKeyInput      $foreign_id;
    public array                $listings_data;

    /**
     * Saves single link record to the database, as opposed to ::save() which saves all links currently in the object.
     * @param int $link_id Link record id of the link to save.
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     */
    public function commitSingleLink(int $link_id)
    {
        if (!$this->containsLinkId($link_id)) {
            throw new InvalidValueException('The requested link id not found in the current link id set.');
        }
        $args = $this->generateLinkUpdatePreparedStmt($link_id);
        $this->query(...$args);
    }

    /**
     * Deletes all links to the parent record.
     * @return string
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     * @throws InvalidQueryException
     */
    public function delete(): string
    {
        $query = 'DEL'.'ETE FROM `'.static::getTableName().'`'.
            ' WHERE `'.$this->primary_id->getColumnName('primary_id').'` = ?';
        $this->query($query, 'i', $this->primary_id->value);
        return ('The requested '.static::getTableName().' records were deleted.');
    }

    /**
     * Deletes a single link record leaving any other links to the parent record untouched.
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     * @throws InvalidQueryException|InvalidValueException
     */
    public function deleteLink(int $link_id): string
    {
        if (!$this->containsLinkId($link_id)) {
            throw new InvalidValueException(
                'The requested link id was not found in the current set of link id values.');
        }
        $query = 'DEL'.'ETE FROM `'.static::getTableName().'`'.
            ' WHERE `'.$this->primary_id->getColumnName('primary_id').'` = ?'.
            ' AND `'.$this->foreign_id->getColumnName('link_id').'` = ?';
        $this->query($query, 'ii', $this->primary_id->value, $link_id);
        return ('The requested '.static::getTableName().' record was deleted.');
    }

    /**
     * Returns TRUE if $link_id value is found in the instance's current link id values.
     * @param int $link_id Link record id to look up.
     * @return bool TRUE if $link_id is found in the instance's current link id values.
     */
    protected function containsLinkId(int $link_id): bool
    {
        if (is_array($this->foreign_id->value)) {
            return in_array($link_id, $this->foreign_id->value);
        }
        else {
            return ($this->foreign_id->value === $link_id);
        }
    }

    /**
     * Deletes any stale links between the two tables.
     * @throws NotImplementedException
     * @throws InvalidQueryException
     */
    public function deleteStaleLinks(array $valid_foreign_ids)
    {
        if (count($valid_foreign_ids) < 1) {
            return;
        }

        $query = 'DEL'.'ETE FROM `'.static::getTableName().'` '.
            'WHERE `'.$this->primary_id->getColumnName('primary_id').'` = ? '.
            'AND `'.$this->foreign_id->getColumnName('foreign_id').'` NOT IN (';
        $first = true;
        $arg_types = 'i';
        $ids = [];
        foreach($valid_foreign_ids as $id) {
            $ids[] = $id;
            $query .= ($first ? '' : ',').'?';
            $arg_types .= 'i';
            $first = false;
        }
        $query .= ')';
        $args = array_merge([$this->primary_id->value], $ids);
        try {
            $this->query($query, $arg_types, ...$args);
        }
        catch (Exception $e) {
            throw new InvalidQueryException("Error deleting stale links. \n".$e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @throws NotImplementedException
     * @throws Exception
     */
    protected function executeInsertQuery()
    {
        $this->executeUpdateQuery();
    }

    /**
     * @inheritDoc
     * @throws NotImplementedException
     * @throws Exception
     */
    protected function executeUpdateQuery()
    {
        list($query, $arg_types, $args) = $this->generateUpdateQuery();
        $this->query($query, $arg_types, ...$args);
    }

    /**
     * @throws InvalidValueException
     * @throws InvalidQueryException
     */
    public function fetchLinkedListings()
    {
        if (!$this->primary_id->hasData()) {
            $err_msg = 'The '.strtolower($this->primary_id->label).' was not provided '.
                'for retrieving linked '.$this->getInlineLabel(true).'.';
            throw new InvalidValueException($err_msg);
        }
        try {
            $ps = $this->generateListingsPreparedStmt();
            if ($ps) {
                $this->listings_data = $this->fetchRecords(...$ps);
            }
        }
        catch (Exception $e) {
            $err_msg = 'Error retrieving linked '.$this->getInlineLabel(true).'.';
            $err_msg .= (LittledGlobals::showVerboseErrors() ? " \n".$e->getMessage() : '');
            throw new InvalidQueryException($err_msg);
        }
    }

    /**
     * @param string $query
     * @return array
     */
    protected function formatRecordLookupQuery(string $query): array
    {
        $properties = $this->getLinkedProperties();
        $first = true;
        $arg_types = '';
        $args = [];
        foreach($properties as $property) {
            $query .= ($first ? ' WHERE `' : ' AND `').$this->$property->getColumnName($property).'` = ?';
            $first = false;
            $arg_types .= $this->$property->getPreparedStatementTypeIdentifier();
            $args[] = $this->$property->value;
        }
        return [$query, $arg_types, $args];
    }

    /**
     * Returns mysql prepared statement, type string, and arguments that can be used to retrieve linked record listings.
     * @return array
     */
    abstract public function generateListingsPreparedStmt(): array;

    /**
     * @inheritDoc
     * @throws NotImplementedException
     * @throws InvalidValueException
     */
    public function generateUpdateQuery(): ?array
    {
        if (is_array($this->foreign_id->value)) {
            if (count($this->foreign_id->value)===1) {
                return $this->generateLinkUpdatePreparedStmt($this->foreign_id->value[0]);
            }
            else {
                $err_msg =
                    'Call to generateUpdateQuery() with multiple links. Use generateLinkUpdatePreparedStmt() instead.';
                throw new InvalidValueException($err_msg);
            }
        }
        else {
            return $this->generateLinkUpdatePreparedStmt($this->foreign_id->value);
        }
    }

    /**
     * Generates the prepared statement that will be used to insert or update a single link record in the database.
     * @param int $link_id The id of the link that is to be saved to the database.
     * @throws NotImplementedException
     */
    protected function generateLinkUpdatePreparedStmt(int $link_id): array
    {
        $col_str = '`'.$this->primary_id->getColumnName('primary_id').'`,'.
            '`'.$this->foreign_id->getColumnName('link_id').'`';
        $val_str = '?,?';
        $arg_types = 'ii';
        $args = [$this->primary_id->value, $link_id];
        $update_str = '`'.$this->primary_id->getColumnName('primary_id').'` = '.
            '`'.$this->primary_id->getColumnName('primary_id').'`, '.
            '`'.$this->foreign_id->getColumnName('link_id').'` = '.
            '`'.$this->foreign_id->getColumnName('link_id').'`';
        foreach($this as $key => $property) {
            /** @var RequestInput $property */
            if ($this->isExtraField($property)) {
                $col_str .= ',`'.$property->getColumnName($key) . '`';
                $val_str .= ',?';
                $arg_types .= $property->getPreparedStatementTypeIdentifier();
                $args[] = $property->value;
                $update_str .= ', `' . $property->getColumnName($key) . '` = ?';
            }
        }
        $query = 'INS'.'ERT INTO `'.static::getTableName().
            "` ($col_str) VALUES ($val_str) ON DUPLICATE KEY UPDATE $update_str;";
        // double up arg_types and args for insert and update portions of the query
        $args = array_merge($args, array_slice($args, 2));
        $arg_types = $arg_types.substr($arg_types,2);
        array_unshift($args, $query, $arg_types);
        return $args;
    }

    /**
     * Tests if the object property represents a field in the database that is not either the primary or link id.
     * @param mixed $property
     * @return bool
     */
    protected function isExtraField($property): bool
    {
        return (
            Validation::isSubclass($property, RequestInput::class) &&
            $property !== $this->primary_id &&
            $property !== $this->foreign_id &&
            $property->isDatabaseField());
    }

    /**
     * Looks up and returns list of all properties in the object that could be used to link the tables together
     * @return array[string]
     */
    protected function getLinkedProperties(): array
    {
        $linked = [];
        foreach ($this as $key => $property) {
            if (Validation::isSubclass($property, IntegerInput::class)) {
                /** @var IntegerInput $property */
                if ($property->isRequired()) {
                    $linked[] = $key;
                }
            }
        }
        return $linked;
    }

    /**
     * Listings data getter.
     * @return array
     * @throws NotInitializedException
     */
    public function listingsData(): array
    {
        if (!isset($this->listings_data)) {
            throw new NotInitializedException(
                'Attempt to access '.$this->getContentLabel().' listings data before it has been retrieved.');
        }
        return $this->listings_data;
    }

    /**
     * @inheritDoc
     * @throws RecordNotFoundException|NotImplementedException
     * @throws Exception
     */
    public function read()
    {
        list($query, $arg_types, $args) = $this->formatRecordLookupQuery(
            'SEL'.'ECT * FROM `'.static::getTableName().'` ');
        $result = $this->fetchRecords($query, $arg_types, ...$args);
        if (count($result) < 1) {
            throw new RecordNotFoundException(static::getTableName()." record not found.");
        }
        $this->fill($result[0]);
    }

    /**
     * @inheritDoc
     */
    public function recordExists(): bool
    {
        list($query, $arg_types, $args) = $this->formatRecordLookupQuery(
            'SEL'.'ECT COUNT(1) as `count` FROM `'.static::getTableName().'` ');
        $result = $this->fetchRecords($query, $arg_types, ...$args);
        return Validation::parseBoolean($result[0]->count);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws InvalidQueryException
     * @throws ConnectionException|InvalidValueException
     */
    public function save()
    {
        if (!$this->primary_id->hasData()) {
            throw new ContentValidationException(get_class($this)." primary content not specified.");
        }
        if (!$this->foreign_id->hasData()) {
            throw new ContentValidationException("Record has no data to save.");
        }
        $fk_ids = (is_array($this->foreign_id->value) ? $this->foreign_id->value : [$this->foreign_id->value]);
        foreach($fk_ids as $id) {
            $this->commitSingleLink($id);
        }
        $this->deleteStaleLinks($fk_ids);
    }

    /**
     * Foreign id setter.
     * @param int|int[] $value
     * @return LinkedContent
     * @throws NotInitializedException
     */
    public function setLinkId($value): LinkedContent
    {
        if (!isset($this->foreign_id)) {
            throw new NotInitializedException("Link id object is not initialized.");
        }
        $this->foreign_id->setInputValue($value);
        return $this;
    }

    /**
     * Primary id setter.
     * @throws NotInitializedException
     */
    public function setPrimaryId(int $value): LinkedContent
    {
        if (!isset($this->primary_id)) {
            throw new NotInitializedException("Primary id object is not initialized.");
        }
        $this->primary_id->setInputValue($value);
        return $this;
    }
}