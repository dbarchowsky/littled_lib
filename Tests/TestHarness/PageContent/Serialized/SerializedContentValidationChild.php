<?php

namespace Littled\Tests\TestHarness\PageContent\Serialized;


use Littled\PageContent\Serialized\SerializedContentValidation;
use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

/**
 * Class SerializedContentUtilsChild
 */
class SerializedContentValidationChild extends SerializedContentValidation
{
	public StringInput $vc_col1;
	public StringInput $vc_col2;
	public IntegerInput $int_col;
	public BooleanInput $bool_col;

	/**
	 * SerializedContentUtilsChild constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->vc_col1 = new StringInput('Test varchar value 1', 'p_vc1', true, '', 50);
		$this->vc_col2 = new StringInput('Test varchar value 1', 'p_vc2', false, '', 255);
		$this->int_col = new IntegerInput('Test int value', 'p_int');
		$this->bool_col = new BooleanInput('Test bool value', 'p_bool');
	}
}
