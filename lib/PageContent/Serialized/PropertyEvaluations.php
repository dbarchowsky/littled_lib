<?php

namespace Littled\PageContent\Serialized;

use Littled\Request\RequestInput;
use Littled\Validation\Validation;


trait PropertyEvaluations
{
    protected RecordsetPrefix $recordset_prefix;

    /**
     * Returns a list of all RequestInput properties of an object.
     * @param bool $db_only If true, only properties marked as database fields will be returned.
     * @param array $ignore_keys Keys to ignore. By default, it ignores keys named to indicate they are id or index
     * properties.
     * @return string[]
     */
    protected function getInputPropertiesList(bool $db_only=true, array $ignore_keys = ['id', 'index']): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubclass($property, RequestInput::class)) {
                /** @var RequestInput $property */
                if ((!$db_only || $property->isDatabaseField()) &&
                    (!in_array($key, $ignore_keys))) {
                    $properties[] = $key;
                }
            }
        }
        return $properties;
    }

    /**
     * Recordset prefix getter.
     * @return string|string[]
     */
    public function getRecordsetPrefix(): array|string
    {
        if (!isset($this->recordset_prefix)) {
            return '';
        }
        return $this->recordset_prefix->getPrefix();
    }

    /**
     * Test for recordset prefix value.
     * @return bool
     */
    public function hasRecordsetPrefix(): bool
    {
        if (!isset($this->recordset_prefix)) {
            return false;
        }
        return $this->recordset_prefix->hasValue();
    }

    /**
     * Checks if a class property corresponds to a column of the database record.
     * @param mixed $property Any property of the object.
     * @param array $used_keys Array containing a list of keys that have already been returned, to avoid duplicates.
     * @return bool
     */
    protected function isDatabaseProperty(mixed $property, array &$used_keys): bool
    {
        if (!Validation::isSubclass($property, RequestInput::class)) {
            return false;
        }
        if (!$property->isDatabaseField()) {
            return false;
        }
        if (in_array($property->key, $used_keys)) {
            return false;
        }
        /**
         * Once an input property is marked as such track it, so it won't be included again.
         */
        $used_keys[] = $property->key;
        return true;
    }

    /**
     * Checks if the class property is an input object and should be used for
     * various operations such as updating or retrieving data from the database,
     * or retrieving data from forms.
     * @param string $key Name of the class property.
     * @param mixed $item Value of the class property.
     * @param array $used_keys Array containing a list of the objects that
     * have already been listed as input properties.
     * @return boolean True if the object is an input class and should be used to update the database. False otherwise.
     */
    protected function isInput(string $key, mixed $item, array &$used_keys): bool
    {
        // ignore keys that have already been included in order to avoid using the same key multiple times
        if (!$this->isDatabaseProperty($item, $used_keys)) {
            return false;
        }
        // don't include primary key properties by default, unless it's not a top-level object as indicated by...xd
        // (a) The object has a recordset prefix, something like "child_" for a structure like parent.child_id
        // (b) The object has overridden its $id->key default value, e.g. with something like "child_id"
        if ($key === 'id' && !$this->hasRecordsetPrefix() && $item->getColumnName('id') === 'id') {
            return false;
        }
        // ignore "index" which is used on arrays
        if ($key === 'index') {
            return false;
        }

        /**
         * Once an input property is marked as such track it, so it won't be included again.
         */
        $used_keys[] = $item->key;
        return true;
    }

    /**
     * Removes existing recordset prefix.
     * @return void
     */
    public function removeRecordsetPrefix(): void
    {
        if (!$this->hasRecordsetPrefix()) {
            return;
        }
        $this->recordset_prefix->setPrefix('');
        foreach ($this as $property) {
            if (Validation::isSubclass($property, DBFieldGroup::class)) {
                $property->removeRecordsetPrefix();
            }
        }
    }


    /**
     * Recordset prefix setter.
     * @param $prefix
     */
    public function setRecordsetPrefix($prefix): void
    {
        $this->recordset_prefix ??= new RecordsetPrefix();
        $this->recordset_prefix->setPrefix($prefix);
        foreach($this as $property) {
            if (Validation::isSubclass($property, DBFieldGroup::class)) {
                $property->setRecordsetPrefix($prefix);
            }
        }
    }
}