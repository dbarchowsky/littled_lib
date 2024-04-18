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
     * Assign values contained in array to object input properties.
     * @param object $row Recordset row containing values to copy into the object's properties.
     */
    public function hydrateFromRecordsetRow(object $row)
    {
        $used_keys = array();
        foreach ($this as $key => $property) {
            // copy over property values that correspond to html form data
            if ($this->isInput($key, $property, $used_keys)) {
                /** @var RequestInput $property */
                /* store value retrieved from database */
                $this->assignRowValue($key, $property, $row);
            }
            elseif(Validation::isSubclass($property, SerializedContent::class) &&
                $property->hasRecordsetPrefix()) {
                $property->hydrateFromRecordsetRow($row);
            }
            elseif(Validation::isSubclass($property, DBFieldGroup::class)) {
                $property->hydrateFromRecordsetRow($row);
            }
            elseif (!is_object($property)) {
                // copy over properties read from the database but not collected in html form data
                if (property_exists($row, $key)) {
                    $this->$key = $row->$key;
                }
            }
        }
    }
}