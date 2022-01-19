<?php
namespace Littled\Request;


class StringTextField extends StringInput
{
	/** @var string Form input element template filename */
	protected static $input_template_filename = 'string-text-input.php';
	/** @var string Form element template filename */
	protected static $template_filename = 'string-text-field.php';
}