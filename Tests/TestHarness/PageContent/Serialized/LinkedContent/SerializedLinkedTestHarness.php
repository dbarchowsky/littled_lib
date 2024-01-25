<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\StringTextField;

class SerializedLinkedTestHarness extends SerializedContent
{
    public StringTextField              $name;
    public LinkedContentTestHarness     $linked;

    protected static string             $table_name = 'test_parent1';

    public const PRIMARY_KEY = 'pId';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->id->setKey('pId')->setColumnName('parent1_id');
        $this->name = new StringTextField('Name', 'p1Name', true, '', 50);
        $this->linked = new LinkedContentTestHarness();
    }

    /**
     * @throws NotInitializedException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    public function deleteLinked()
    {
        $this->linked->setPrimaryId($this->id->value)->delete();
    }

    /**
     * Public interface for testing.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException|InvalidValueException
     */
    public function commitLinkedRecords_public()
    {
        parent::commitLinkedRecords();
    }

    /**
     * Public interface for testing.
     * @return array
     */
    public function getForeignKeyPropertyList_public(): array
    {
        return parent::getLinkedContentPropertyList();
    }

    /**
     * @inheritDoc
     * @throws NotImplementedException
     */
    public function generateUpdateQuery(): ?array
    {
        $query = 'INS'.'ERT INTO `'.static::getTableName().'` '.
            '(`id`, `name`) VALUES (?,?) '.
            'ON DUPLICATE KEY UPDATE `name` = ?';
        return [$query, 'iss', $this->id->value, $this->name->value, $this->name->value];
    }

    public function hasData(): bool
    {
        return (is_int($this->id->hasData()) || $this->linked->hasData());
    }

    function getContentLabel(): string
    {
        return 'Serialized link test harness';
    }

    function save()
    {
        parent::save();
    }
}