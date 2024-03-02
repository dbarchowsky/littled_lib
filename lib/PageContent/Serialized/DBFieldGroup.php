<?php

namespace Littled\PageContent\Serialized;


use Littled\Request\RequestInput;
use Littled\Validation\Validation;

class DBFieldGroup
{
    public function getFieldList(array &$used_keys): array
    {
        $fields = [];
        foreach ($this as $key => $property) {
            if (static::isInput($key, $property, $used_keys)) {
                $fields[] = new QueryField(
                    $property->column_name ?: $key,
                    $property::getPreparedStatementTypeIdentifier(),
                    $property->value);
            }
        }
        return $fields;
    }

    /**
     * Tests if the object property represents data that should be included in a sql query.
     * @param string $key
     * @param mixed $item
     * @param array $used_keys
     * @return bool
     */
    protected static function isInput(string $key, $item, array &$used_keys): bool
    {
        if (!Validation::isSubclass($item, RequestInput::class)) {
            return false;
        }
        if (!$item->isDatabaseField()) {
            return false;
        }
        if (in_array($key, $used_keys)) {
            return false;
        }
        $used_keys[] = $item->key;
        return true;
    }
}