<?php
namespace Littled\Forms;


class Input
{
	/** @var string Parameter name within form. */
	public $key;
	/** @var mixed Value stored in database and entered into form. */
	public $value;
	/** @var string Data type label. */
	public $label;
	/** @var boolean Flag indicating that the input value is required. */
	public $is_required;
	/** @var integer Size limit of the input data. */
	public $size_limit;
	/** @var string CSS class name to apply to the input element. */
	public $css_class;
	/** @var string Extra attributes to apply to the input element. */
	public $attributes;

	function __construct( $key, $label, $value=null, $is_required=false, $size_limit=null)
	{
		$this->key = $key;
		$this->label = $label;
		$this->value = $value;
		$this->is_required = $is_required;
		$this->size_limit = $size_limit;
	}
}