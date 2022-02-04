<?php
namespace Littled\Request;


/**
 * Class BooleanCheckbox
 * @package Littled\Request
 */
class BooleanCheckbox extends BooleanInput
{
    /** @var string Form element template filename */
    public static $template_filename = 'string-checkbox-field.php';
	/** @var string Form input element template filename */
	public static $input_template_filename = 'string-checkbox-input.php';
}