<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class SerializedContentChild extends SerializedContentTestHarness
{
    /** @var StringInput Test string input property */
    public StringInput $vc_col1;
    /** @var StringInput Test string input property */
    public StringInput $vc_col2;
    /** @var IntegerInput Test integer input property */
    public IntegerInput $int_col;
    /** @var BooleanInput Test boolean input property */
    public BooleanInput $bool_col;
    /** @var mixed Test plain mixed variable value property */
    public $prop1;
    /** @var mixed Another test plain mixed variable value property */
    public $prop2;
    /** @var array Test array container */
    public array $array_container=[];
    /** @var SerializedContentChild */
    public SerializedContentChild $child;
    /** @var string */
    protected static string $table_name = 'sc_child_temp_unit_test';

    public function __construct()
    {
        parent::__construct();
        $this->vc_col1 = new StringInput('Test varchar value 1', 'p_vc1', true, '', 50);
        $this->vc_col2 = new StringInput('Test varchar value 1', 'p_vc2', false, '', 255);
        $this->int_col = new IntegerInput('Test int value', 'p_int');
        $this->bool_col = new BooleanInput('Test bool value', 'p_bool');
    }

    public function hasData(): bool
    {
        if ($this->vc_col2->value !== null && strlen($this->vc_col2->value) > 0) { return(true); }
        if ($this->vc_col1->value !== null && strlen($this->vc_col1->value) > 0) { return(true); }
        if ($this->int_col->value !== null) { return(true); }
        if ($this->bool_col->value !== null) { return(true); }
        return (parent::hasData());
    }
}