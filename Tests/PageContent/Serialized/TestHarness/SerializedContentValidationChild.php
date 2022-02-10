<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;


use Littled\PageContent\Serialized\SerializedContentValidation;
use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

/**
 * Class SerializedContentUtilsChild
 */
class SerializedContentValidationChild extends SerializedContentValidation
{
	public $vc_col1;
	public $vc_col2;
	public $int_col;
	public $bool_col;

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
