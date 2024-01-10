<?php

namespace LittledTests\TestHarness\PageContent\Serialized;

use Littled\Request\StringTextField;

class SerializedContentNonDefaultColumn extends SerializedContentNameTestHarness
{
    /** @var StringTextField Column to use to test non-default column names */
    public StringTextField $nonDefaultCol;
    protected static string $table_name = 'sc_column_temp_unit_test';

    public function __construct()
    {
        parent::__construct();
        $this->nonDefaultCol = new StringTextField('Non-default column', 'pnfc', true, null, 50);
        $this->nonDefaultCol->column_name = 'non_default';
    }

    public function hasData(): bool
    {
        $result = parent::hasData();
        if ($this->nonDefaultCol->value) {
            $result = true;
        }
        return ($result);
    }
}