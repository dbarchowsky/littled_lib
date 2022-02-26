<?php
namespace Littled\Request;


class StringTextField extends StringInput
{
	/** @var string Form input element template filename */
	protected static string $input_template_filename = 'string-text-input.php';
	/** @var string Form element template filename */
	protected static string $template_filename = 'string-text-field.php';
}