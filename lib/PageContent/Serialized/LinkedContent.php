<?php

namespace Littled\PageContent\Serialized;

use Exception;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

class LinkedContent extends SerializedContentIO
{
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
}