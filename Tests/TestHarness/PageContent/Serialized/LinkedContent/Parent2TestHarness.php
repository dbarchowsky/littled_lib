<?php

namespace LittledTests\TestHarness\PageContent\Serialized\LinkedContent;


use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\StringTextField;

class Parent2TestHarness extends SerializedContent
{
    public StringTextField $name;

    protected static string $table_name = 'test_parent2';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->name = new StringTextField('Name', 'p2Name', true, '', 50);
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = 'INS'.'ERT INTO `` (`id`, `name`) VALUES (??) '.
            'ON DUPLICATE KEY UPDATE id = ?, name = ?';
        return [$query, 'isis', &$this->id->value, &$this->name->value, &$this->id->value, &$this->name->value];
    }
}