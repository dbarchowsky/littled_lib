<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\ForeignKeyInput;
use Littled\Request\StringTextField;

class SerializedLinkedTestHarness extends SerializedContent
{
    public StringTextField $name;
    public ForeignKeyInput $parent2;

    protected static string $table_name = 'test_parent1';

    public const LINK_KEY = 'p2Key';

    /**
     * @throws InvalidTypeException
     */
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->id->setColumnName('parent1_id');
        $this->name = new StringTextField('Name', 'p1Name', true, '', 50);
        $this->parent2 = (new ForeignKeyInput('Parent 2', self::LINK_KEY))
            ->setContentClass(LinkedContentTestHarness::class)
            ->setColumnName('parent2_id')
            ->setAllowMultiple();
    }

    /**
     * Public interface for testing.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws NotInitializedException
     */
    public function commitForeignKeys_public()
    {
        parent::commitForeignKeys();
    }

    /**
     * Public interface for testing.
     * @return array
     */
    public function getForeignKeyPropertyList_public(): array
    {
        return parent::getForeignKeyPropertyList();
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
        return (is_int($this->id->hasData()) || $this->parent2->hasData());
    }
}