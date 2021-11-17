<?php
namespace Littled\Request;


use Littled\Validation\Validation;

/**
 * Class StringTextFieldInput
 * @package Littled\Request
 */
class EmailTextField extends StringTextField
{
	/** @var string Path to form input templates. */
	protected static $template_base_path = '';
	/** @var string Form input element template filename */
	protected static $input_template_filename = 'email-input.php';
	/** @var string Form element template filename */
	protected static $template_filename = 'email-field.php';

	/**
	 * {@inheritDoc}
	 */
	public function __construct($label, $param, $required = false, $value = null, $size_limit = 255, $index = null)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
	}

	public static function getTemplateFilename(): string
	{
		return static::$template_filename;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		parent::validate();
		if (strlen(trim($this->value)) > 0)
		{
			if (Validation::validateEmailAddress($this->value)===false)
			{
				$this->throwValidationError($this->formatErrorLabel()." is not in a recognized email format.");
			}
		}
	}
}