<?php
namespace Littled\Request;


use Littled\Validation\Validation;

/**
 * Class StringTextFieldInput
 * @package Littled\Request
 */
class EmailTextField extends StringTextField
{
	/** @var string Form input element template filename */
	protected static string $input_template_filename = 'email-input.php';
	/** @var string Form element template filename */
	protected static string $template_filename = 'email-field.php';

	/**
	 * @inheritDoc
	 */
	public function __construct(
        string $label,
        string $key,
        bool $required = false,
        $value = null,
        int $size_limit = 255,
        int $index = null)
	{
		parent::__construct($label, $key, $required, $value, $size_limit, $index);
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