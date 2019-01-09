<?php
namespace Littled\Request;


/**
 * Class BooleanCheckbox
 * @package Littled\Request
 */
class BooleanCheckbox extends BooleanInput
{
	/** @var string Form input element template filename */
	public static $input_template_filename = 'boolean-checkbox-input.php';
	/** @var string Form element template filename */
	public static $template_filename = 'boolean-checkbox-field.php';
}