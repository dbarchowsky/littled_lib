<?php

namespace Littled\Request\Inline;

use Littled\Request\StringInput;

class InlineNameInput extends InlineInput
{
    public StringInput $name;

    /**
     * InlineNameInput constructor.
     * @param array $column_names List of possible column names representing the column in the table that stores the "name" value.
     */
    function __construct(array $column_names=[])
    {
        parent::__construct();
        $this->name = new StringInput("Name", "n", true, "", 100);
        $this->columnNameOptions = array_merge(array("name", "title", "catno", "code"), $column_names);
    }

    /**
     * @inheritDoc
     */
    protected function formatSelectQuery(): array
    {
        $query = "SEL"."ECT `$this->column_name` as `name` ".
            "FROM `{$this->table->value}` ".
            "WHERE id = ?";
        return array($query, 'i', &$this->parent_id->value);
    }

    /**
     * @inheritDoc
     */
    protected function formatUpdateQuery(): array
    {
        return $this->generateUpdateQuery();
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = "UPD"."ATE `{$this->table->value}` ".
            "SET `$this->column_name` = ? ".
            "WHERE id = ?";
        return array($query, 'si', &$this->name->value, &$this->parent_id->value);
    }

    public function read()
    {
        $data = parent::read();
        $this->name->value = $data[0]->name;
    }
}