<?php
namespace Littled\Request;


class BooleanCheckbox extends BooleanInput
{
    /** @var string Form element template filename */
    public static string $template_filename = 'boolean-checkbox-field.php';
	/** @var string Form input element template filename */
	public static string $input_template_filename = 'boolean-checkbox-input.php';
}