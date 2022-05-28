<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\Request\BooleanInput;
use Littled\Request\DateInput;
use Littled\Request\StringInput;

class SerializedContentNameTestHarness extends SerializedContentTestHarness
{
    public StringInput $name;
    public StringInput $vc_col;
    public BooleanInput $bool_col;
    public DateInput $date_col;
    protected static string $table_name = 'sc_name_temp_unit_test';

    public function __construct()
    {
        parent::__construct();
        $this->name = new StringInput('Name field', 'pname', true, '', 50);
        $this->vc_col = new StringInput('String field', 'pstr', false, '', 255);
        $this->bool_col = new BooleanInput('Boolean field', 'pbool');
        $this->date_col = new DateInput('Date field', 'pdate');
    }

    public function hasData(): bool
    {
        return ($this->id->value > 0 || strlen("".$this->name->value) > 0);
    }
}