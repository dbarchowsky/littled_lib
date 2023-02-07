<?php

namespace Littled\Tests\TestHarness\PageContent\Serialized;

use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class SerializedContentTitleTestHarness extends SerializedContentTestHarness
{
    /** @var StringInput Title property */
    public StringInput $title;
    /** @var StringInput Test string property */
    public StringInput $vc_col;
    /** @var IntegerInput Test integer property */
    public IntegerInput $int_col;
    /** @var string */
    protected static string $table_name = 'sc_title_temp_unit_test';

    public function __construct()
    {
        parent::__construct();
        $this->title = new StringInput('Title field', 'ptit', true, '', 50);
        $this->vc_col = new StringInput('String field', 'pstr', false, '', 255);
        $this->int_col = new IntegerInput('Integer field', 'pint');
    }

    public function hasData(): bool
    {
        if ($this->title->value !== null && strlen($this->title->value) > 0) { return(true); }
        if ($this->vc_col->value !== null && strlen($this->vc_col->value) > 0) { return(true); }
        if ($this->int_col->value !== null) { return(true); }
        return parent::hasData();
    }
}