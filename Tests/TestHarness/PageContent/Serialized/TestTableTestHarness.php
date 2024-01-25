<?php

namespace LittledTests\TestHarness\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanSelect;
use Littled\Request\DateTextField;
use Littled\Request\IntegerTextField;
use Littled\Request\StringTextField;

class TestTableTestHarness extends SerializedContent
{
    public const                EXISTING_RECORD_ID = 2216;
    public const                NONEXISTENT_RECORD_ID = 56784;

    protected static string     $table_name = 'test_table';

    public StringTextField      $name;
    public IntegerTextField     $int_col;
    public BooleanSelect        $bool_col;
    public DateTextField        $date;
    public IntegerTextField     $slot;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->name = new StringTextField('Name', 'ttName', true, '', 50);
        $this->int_col = new IntegerTextField('Integer column', 'ttInt');
        $this->bool_col = new BooleanSelect('Boolean column', 'ttBool');
        $this->date = new DateTextField('Date', 'ttDate');
        $this->slot = new IntegerTextField('Slot', 'ttSlot');
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = 'CALL testTableUpdate(@insert_id,?,?,?,?,?)';
        return [$query, 'sii'.'si',
            &$this->name->value,
            &$this->int_col->value,
            &$this->bool_col->value,
            &$this->date->value,
            &$this->slot->value];
    }

    /**
     * @inheritDoc
     */
    function getContentLabel(): string
    {
        /* stub */
        return 'Test table';
    }

    /**
     * Public interface for testing purposes.
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException
     */
    public function prepareInsertIdSession_public()
    {
        parent::prepareInsertIdSession();
    }

    /**
     * Public interface for testing purposes.
     * @param string $query
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    public function updateIdAfterCommit_public(string $query)
    {
        parent::updateIdAfterCommit($query);
    }
}