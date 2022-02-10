<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\Request\StringTextField;

class SerializedContentNonDefaultColumn extends SerializedContentNameTestHarness
{
    /** @var StringTextField Column to use to test non-default column names */
    public $nonDefaultCol;
    protected static $table_name = 'sc_column_temp_unit_test';

    public function __construct()
    {
        parent::__construct();
        $this->nonDefaultCol = new StringTextField('Non-default column', 'pnfc', true, null, 50);
        $this->nonDefaultCol->columnName = 'non_default';
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