<?php

namespace Littled\PageContent\Serialized;

use Exception;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\ForeignKeyInput;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

class LinkedContent extends SerializedContentIO
{
    public IntegerInput $primary_id;
    public ForeignKeyInput $foreign_id;

    /**
     * @inheritDoc
     * @throws NotImplementedException
     * @throws Exception
     */
    public function delete(): string
    {
        list($query, $arg_types, $args) = $this->formatRecordLookupQuery('DEL'.'ETE FROM `'.static::getTableName().'` ');
        $this->query($query, $arg_types, ...$args);
        return ('The requested '.static::getTableName().' record was deleted.');
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
     * @inheritDoc
     * @throws NotImplementedException
     */
    public function generateUpdateQuery(): ?array
    {
        $first = true;
        $col_str = $val_str = $update_str = $arg_types = '';
        $args = [];
        foreach($this as $key => $property) {
            /** @var RequestInput $property */
            if (Validation::isSubclass($property, RequestInput::class) && $property->isDatabaseField()) {
                $col_str .= ($first ? '' : ',') . '`' . $property->getColumnName($key) . '`';
                $val_str .= ($first ? '' : ',') . '?';
                $update_str .= ($first ? '' : ',') . '`' . $property->getColumnName($key) . '` = ?';
                $arg_types .= $property->getPreparedStatementTypeIdentifier();
                $args[] = $property->value;
                $first = false;
            }
        }
        $query = 'INS'.'ERT INTO `'.static::getTableName().
            "` ($col_str) VALUES($val_str) ON DUPLICATE KEY UPDATE $update_str";
        // double up arg_types and args for insert and update portions of the query
        $args = array_merge($args, $args);
        array_unshift($args, $query, $arg_types.$arg_types);
        return $args;
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
     * @inheritDoc
     * @throws RecordNotFoundException|NotImplementedException
     * @throws Exception
     */
    public function read()
    {
        list($query, $arg_types, $args) = $this->formatRecordLookupQuery('SEL'.'ECT * FROM `'.static::getTableName().'` ');
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
        list($query, $arg_types, $args) = $this->formatRecordLookupQuery('SEL'.'ECT COUNT(1) as `count` FROM `'.static::getTableName().'` ');
        $result = $this->fetchRecords($query, $arg_types, ...$args);
        return Validation::parseBoolean($result[0]->count);
    }

    /**
     * @inheritDoc
     * @throws ContentValidationException
     * @throws NotImplementedException
     */
    public function save()
    {
        if (!$this->hasData()) {
            throw new ContentValidationException("Record has no data to save.");
        }
        $vars = $this->generateUpdateQuery();
        if ($vars) {
            call_user_func_array([$this, 'commitSaveQuery'], $vars);
        }
        else {
            $this->executeUpdateQuery();
        }
    }

    /**
     * Foreign id setter.
     * @throws NotInitializedException
     */
    public function setForeignID(int $value): LinkedContent
    {
        if (!isset($this->foreign_id)) {
            throw new NotInitializedException("Foreign id object is not initialized.");
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

    /**
     * @throws ContentValidationException
     * @throws NotInitializedException
     * @throws NotImplementedException
     */
    public function setValuesAndCommit(int $primary_id, int $foreign_id)
    {
        $this
            ->setPrimaryId($primary_id)
            ->setForeignID($foreign_id)
            ->save();
    }
}