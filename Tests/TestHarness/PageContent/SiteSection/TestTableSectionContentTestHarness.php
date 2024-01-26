<?php

namespace LittledTests\TestHarness\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\SiteSection\SectionContent as parentAlias;
use Littled\Request\BooleanInput;
use Littled\Request\DateInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;


class TestTableSectionContentTestHarness extends parentAlias
{
    public const                ID_KEY = 'testId';
    protected static int $content_type_id = 6037;
    protected static string $table_name = 'test_table';
    public StringInput $name;
    public IntegerInput $int_col;
    public BooleanInput $bool_col;
    public DateInput $date;
    public IntegerInput $slot;

    /**
     * Class constructor.
     * @param int|null $id Record id.
     * @param string $name Test string field.
     * @param int|null $int_col Test integer value field.
     * @param bool|null $bool_col Test boolean value field.
     * @param ?string $date Test date value field.
     * @param int|null $slot Place of the record within listings of similar records.
     * @throws ConfigurationUndefinedException
     */
    public function __construct(
        ?int    $id = null,
        string  $name = '',
        ?int    $int_col = null,
        ?bool   $bool_col = null,
        ?string $date = null,
        ?int    $slot = null)
    {
        parent::__construct($id);
        $this->name = new StringInput('Name', 'name', false, $name, 50);
        $this->bool_col = new BooleanInput('Boolean column', 'boolCol', false, $bool_col);
        $this->int_col = new IntegerInput('Integer column', 'intCol', false, $int_col);
        $this->date = new DateInput('Date', 'Date column', false, $date);
        $this->slot = new IntegerInput('Slot', 'slot', false, $slot);
    }

    public function formatDatabaseColumnList(array $used_keys = []): array
    {
        return parent::formatDatabaseColumnList($used_keys);
    }

    public function executeUpdateQuery()
    {
        parent::executeUpdateQuery();
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        return array('CALL testTableUpdate(?,?,?,?,?,?)',
            'isiisi',
            &$this->id->value,
            &$this->name->value,
            &$this->int_col->value,
            &$this->bool_col->value,
            &$this->date->value,
            &$this->slot->value);
    }

    /**
     * @inheritDoc
     * Override parent method to provide public interface for testing.
     */
    public function executeInsertQuery()
    {
        parent::executeInsertQuery();
    }
}