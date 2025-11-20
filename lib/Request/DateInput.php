<?php

namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use DateTime;


/**
 * Base class of date inputs.
 */
class DateInput extends StringInput
{
    public const            DEFAULT_SIZE_LIMIT = 20;
    protected static string $input_template_filename = 'date-text-input.php';
    protected static string $template_filename = 'date-text-field.php';
    public string           $format = 'Y-m-d H:i:s';
    public string           $default = '';

    /**
     * @inheritDoc
     */
    function __construct(
        string      $label          = '',
        string      $key            = '',
        bool        $required       = false,
        mixed       $value          = null,
        int         $size_limit     = self::DEFAULT_SIZE_LIMIT,
        ?int        $index          = null
    )
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * @inheritDoc
     * @throws ContentValidationException
     */
    public function collectAjaxRequestData(object $data): void
    {
        parent::collectAjaxRequestData($data);
        if (strlen('' . $this->value) > 0) {
            $this->setDateValue();
        }
    }

    /**
     * Returns the current value of the object as a formatted string value.
     * @param string $date_format
     * @param string $date
     * @return ?string Formatted date string.
     * @throws ContentValidationException Current value not a valid date value.
     */
    public function formatDateValue(string $date_format = '', string $date = ''): ?string
    {
        $date_format = $date_format ?: $this->format;
        $date = $date ?: $this->value;
        if ('' === $date || null === $date) {
            return null;
        }
        $valid = (
            (false !== strtotime($date)) ||
            (DateTime::createFromFormat('d/m/Y', $date) !== false) ||
            (DateTime::createFromFormat('Y-m-d', $date) !== false));
        if (!$valid) {
            throw new ContentValidationException("$this->label is not in a recognized date format.");
        }
        if (null !== $date_format && '' !== $date_format) {
            return (date($date_format, strtotime($date)));
        }
        return $date;
    }

    /**
     * Date format string getter.
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return bool
     */
    public function hasData(): bool
    {
        return parent::hasData();
    }

    /**
     * Converts the current value of the object to a standard date format.
     * @param string $date_format
     * @throws ContentValidationException Current value not a valid date value.
     */
    protected function setDateValue(string $date_format = ''): void
    {
        $date_format = $date_format ?? $this->format;
        $this->value = $this->formatDateValue($date_format);
    }

    /**
     * Sets a default value if none is entered.
     * @param string $date
     * @return $this
     */
    public function setDefault(string $date): DateInput
    {
        $this->default = $date;
        return $this;
    }

    /**
     * Date format string setter.
     * @param string $format
     * @return $this
     */
    public function setFormat(string $format): DateInput
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Assigns a value to the object after parsing the value to be in a workable format.
     * @param ?mixed $value Value to assign to the object.
     * @param string $date_format
     * @return $this
     */
    public function setInputValue(mixed $value, string $date_format = ''): static
    {
        $date_format = $date_format ?: $this->format;
        parent::setInputValue($value);
        try {
            $this->setDateValue($date_format ?: $this->format ?: 'Y-m-d');
        } catch (ContentValidationException) {
            $this->value = null;
        }
        return $this;
    }

    /**
     * Validates the date value.
     * @throws ContentValidationException Date value is missing when required or is in an unrecognized format.
     */
    public function validate(): void
    {
        if (true === $this->required && (null === $this->value || strlen($this->value) < 1)) {
            throw new ContentValidationException("$this->label is required.");
        }
        if (false === $this->required && (null === $this->value || strlen($this->value) < 1)) {
            return;
        }
        if (strlen($this->value) > $this->size_limit) {
            throw new ContentValidationException("$this->label is limited to $this->size_limit character" . (($this->size_limit != 1) ? ('s') : ('')) . '.');
        }
        $this->setDateValue();
    }
}
