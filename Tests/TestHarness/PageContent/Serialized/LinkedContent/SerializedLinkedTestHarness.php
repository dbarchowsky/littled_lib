<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\ForeignKeyInput;
use Littled\Request\StringTextField;

class SerializedLinkedTestHarness extends SerializedContent
{
    public StringTextField $name;
    public ForeignKeyInput $parent2;

    protected static string $table_name = 'test_parent1';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->name = new StringTextField('Name', 'p1Name', true, '', 50);
        $this->parent2 = new ForeignKeyInput('Parent 2', 'p2Key');
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
     */
    public function generateUpdateQuery(): ?array
    {
        return [];
    }
}