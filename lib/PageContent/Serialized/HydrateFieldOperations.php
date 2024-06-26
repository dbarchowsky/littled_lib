<?php

namespace Littled\PageContent\Serialized;

use Littled\Request\RequestInput;
use Littled\Validation\Validation;


trait HydrateFieldOperations
{
    /**
     * Copies values from a recordset row to the properties of the object based on a one-to-one match between
     * the name of the field in the recordset and the name of the object property, or its "column name" value,
     * or its name plus a prefix defined by its parent object
     * @param string $prop_key
     * @param RequestInput $property
     * @param object $row
     * @return void
     */
    protected function assignRowValue(string $prop_key, RequestInput $property, object $row): void
    {
        $field = $property->getColumnName($prop_key);
        // check if this object is a child of a parent and fields exist in the recordset that should be
        // assigned to this object's properties based on the fields' name prefix

        if ($this->hasRecordsetPrefix()) {
            $pfx_field = $this->recordset_prefix->lookupPrefixProperty($row, $field);
            if (!$pfx_field) {
                // no matching field found within the recordset, move on
                return;
            }
            $property->setInputValue($row->$pfx_field);
            return;
        }
        if(property_exists($row, $field)) {
            // assign value to top-level/parent object property, assuming a one-to-one match between the name
            // of the property of the object and the name of the field within the recordset
            $property->setInputValue($row->$field);
        }
    }

    /**
     * Returns list of all properties of the object that are candidates to have values copied from a recordset row
     * @return string[]
     */
    protected function getHydrateProperties(object $row): array
    {
        $properties = array_keys(get_class_vars(get_class($this)));
        foreach ($properties as $property) {
            if (!$this->isHydrateProperty($row, $property)) {
                $key = array_search($property, $properties);
                unset($properties[$key]);
            }
        }
        return array_values($properties);
    }

    /**
     * Assign values contained in array to object input properties.
     * @param object $row Recordset row containing values to copy into the object's properties.
     */
    public function hydrateFromRecordsetRow(object $row): void
    {
        $used_keys = array();
        foreach ($this->getHydrateProperties($row) as $key) {

            // copy over property values that correspond to html form data
            if (isset($this->{$key}) && $this->isInput($key, $this->$key, $used_keys)) {
                /** @var RequestInput $property */
                /* store value retrieved from database */
                $property = $this->$key;
                $this->assignRowValue($property->getColumnName($key), $property, $row);
            }
            elseif(isset($this->{$key}) &&
                Validation::isSubclass($this->$key, SerializedContent::class) &&
                $this->$key->hasRecordsetPrefix()) {
                $this->$key->hydrateFromRecordsetRow($row);
            }
            elseif(isset($this->{$key}) && Validation::isSubclass($this->$key, DBFieldGroup::class)) {
                $this->$key->hydrateFromRecordsetRow($row);
            }
            elseif (!isset($this->{$key}) || isset($this->{$key}) && !is_object($this->$key)) {
                // copy over properties read from the database but not collected in html form data
                $dst_key = (method_exists($this, 'getRecordsetPrefix') ? $this->getRecordsetPrefix() : '') . $key;
                if (property_exists($this, $dst_key)) {
                    $this->$dst_key = $row->$key;
                }
            }
        }
    }

    /**
     * Tests if a property of an object should be considered a candidate for copying from a recordset row
     * @param object $row
     * @param string $key
     * @return bool
     */
    protected function isHydrateProperty(object $row, string $key): bool
    {
        if (isset($this->{$key}) && Validation::isSubclass($this->{$key}, RequestInput::class)) {
            /** @var RequestInput $p */
            $p = $this->$key;
            $key = $this->lookupHydratePrefix($row, $key) . $p->getColumnName($key);
        }

        if (property_exists($row, $key)) {
            return true;
        }

        if (isset($this->{$key}) &&
            (Validation::isSubclass($this->$key, SerializedContent::class) ||
            Validation::isSubclass($this->$key, DBFieldGroup::class))) {
            return true;
        }
        return false;
    }

    /**
     * Returns the recordset prefix that matches properties of the recordset row.
     * @param object $row
     * @param string $key
     * @return string
     */
    protected function lookupHydratePrefix(object $row, string $key): string
    {
        if (!$this->hasRecordsetPrefix()) {
            return '';
        }
        $pfx = $this->getRecordsetPrefix();
        if (is_array($pfx)) {
            foreach ($pfx as $prefix) {
                if (array_key_exists($prefix.$key, (array)$row)) {
                    return $prefix;
                }
            }
            return '';
        }
        else {
            return $pfx;
        }
    }
}