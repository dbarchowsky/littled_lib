<?php

namespace Littled\Request;

use DateTime;
use mysqli;
use Littled\Exception\ContentValidationException;


/**
 * Date inputs base class.
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
        string $label,
        string $key,
        bool   $required = false,
               $value = null,
        int    $size_limit = self::DEFAULT_SIZE_LIMIT,
        ?int   $index = null
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
     * Returns a string to use to save the object's value to a database record.
     * @param mysqli $mysqli Database connection to use for its escape_string() routine.
     * @param bool $include_quotes Optional. If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
     * @return ?string Escaped value.
     */
    public function escapeSQL(mysqli $mysqli, bool $include_quotes = false): ?string
    {
        $src = ($this->value === null) ? ($this->default) : ($this->value);
        if ($src === '') {
            return null;
        }
        $ts = strtotime($src);
        if ($ts !== false) {
            $date_string = date('Y-m-d H:i:s', $ts);
        } else {
            $date = DateTime::createFromFormat('d/m/Y', $src);
            if ($date !== false) {
                $date_string = $date->format('Y-m-d');
            } else {
                /* maybe it's in YYYY-MM-DD format, just send it back whatever it is */
                $date_string = $src;
            }
        }
        return ((($include_quotes) ? ("'") : ('')) . $mysqli->real_escape_string($date_string) . (($include_quotes) ? ("'") : ('')));
    }

    /**
     * Returns the current value of the object as formatted string value.
     * @param string $date_format
     * @return ?string Formatted date string.
     * @throws ContentValidationException Current value not a valid date value.
     */
    public function formatDateValue(string $date_format = ''): ?string
    {
        $date_format = $date_format ?: $this->format;
        if ('' === $this->value || null === $this->value) {
            return null;
        }
        $valid = (
            (false !== strtotime($this->value)) ||
            (DateTime::createFromFormat('d/m/Y', $this->value) !== false) ||
            (DateTime::createFromFormat('Y-m-d', $this->value) !== false));
        if (!$valid) {
            throw new ContentValidationException("$this->label is not in a recognized date format.");
        }
        if (null !== $date_format && '' !== $date_format) {
            return (date($date_format, strtotime($this->value)));
        }
        return $this->value;
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
     * Assigns a value to the object after parsing the value in order to be in a workable format.
     * @param ?mixed $value Value to assign to the object.
     * @param string $date_format
     * @return $this
     */
    public function setInputValue(mixed $value, string $date_format = ''): DateInput
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
