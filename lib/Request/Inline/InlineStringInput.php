<?php

namespace Littled\Request\Inline;


class InlineStringInput extends InlineInput
{
    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $property = static::$input_property;
        $query = 'UPDATE `' . $this->getTableName() . "` SET `$property` = ? WHERE `id` = ?";
        return [$query, 'si', $this->{$property}->value, $this->id->value];
    }
}