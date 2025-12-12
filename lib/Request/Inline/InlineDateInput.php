<?php

namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\DateTextField;


abstract class InlineDateInput extends InlineInput
{
    public DateTextField $date;
    protected static string $input_property = 'date';

    function __construct(array $column_names = [])
    {
        parent::__construct();
        $this->date = (new DateTextField())
            ->setLabel('Date')
            ->setKey('d')
            ->setAsRequired();
    }

    /**
     * @inheritDoc
     */
    protected function formatRecordSelectQuery(): array
    {
        $query = 'SELECT `' . static::$input_property . '` ' .
            'FROM `' . static::getTableName(). '` ' .
            'WHERE `id` = ?';
        return [$query, 'i', $this->id->value];
    }

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $property = static::$input_property;
        $query = 'UPDATE `' . static::getTableName() . '` ' .
            "SET `$property` = ? " .
            'WHERE `id` = ?';
        return [$query, 'si', $this->{$property}->formatDateValue(), $this->id->value];
    }

    /**
     * @inheritDoc
     * @param mixed $value
     * @param string $format
     */
    public function setValue(mixed $value, string $format = ''): static
    {
        $this->{$this::$input_property}->setInputValue($value, $format);
        return $this;
    }
}