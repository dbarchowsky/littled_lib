<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Albums\Gallery;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

trait SerializedFieldOperations
{
    protected RecordsetPrefix $recordset_prefix;

    /**
     * Returns the form data members of the objects as series of nested associative arrays.
     * @param array|null $exclude_keys (Optional) array of parameter names to exclude from the returned array.
     * @return array Associative array containing the object's form data members as name/value pairs.
     */
    public function arrayEncode(?array $exclude_keys = null): array
    {
        $ar = array();
        foreach ($this as $key => $item) {
            if (is_object($item)) {
                if (!is_array($exclude_keys) || !in_array($key, $exclude_keys)) {
                    if ($item instanceof RequestInput) {
                        $ar[$key] = $item->value;
                    } elseif ($item instanceof SerializedContent || $item instanceof DBFieldGroup) {
                        /** @var SerializedContent $item */
                        $ar[$key] = $item->arrayEncode($exclude_keys);
                    } elseif ($item instanceof Gallery) {
                        /** @var Gallery $item */
                        $ar[$key] = $item->arrayEncode(array("tn", "site_section"));
                    }
                }
            } elseif (is_array($item)) {
                $temp = [];
                foreach ($item as $element) {
                    if ($element instanceof SerializedContent) {
                        $temp[] = $element->arrayEncode($exclude_keys);
                    }
                }
                $ar[$key] = $temp;
            }
        }
        return ($ar);
    }

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

    public function clearValues()
    {
        foreach ($this as $item) {
            /** @var object $item */
            if (is_object($item) && method_exists($item, 'clearValue')) {
                $item->clearValue();
            } elseif (is_object($item) && method_exists($item, 'clearValues')) {
                $item->clearValues();
            }
        }
    }

    /**
     * Set property values using input variable values, e.g. GET, POST, cookies
     * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     */
    public function collectRequestData(?array $src = null)
    {
        foreach ($this as $item) {
            if (is_object($item) &&
                (!property_exists($item, 'bypassCollectFromInput') || $item->bypassCollectFromInput === false)) {
                if (method_exists($item, 'collectRequestData')) {
                    $item->collectRequestData($src);
                } elseif (method_exists($item, 'collectFormInput')) {
                    $item->collectFormInput(null, $src);
                }
            }
        }
    }

    /**
     * Copies the property values from one object into this instance.
     * @param mixed $src Object to use to copy values over to this object.
     * @throws InvalidTypeException Source is not a valid object.
     */
    public function copy($src)
    {
        if (!is_object($src)) {
            throw new InvalidTypeException("Source for copy is not an object.");
        }
        if (get_class($this) != get_class($src)) {
            throw new InvalidTypeException("Invalid object for copy.");
        }
        foreach (get_object_vars($src) as $key => $value) {
            if ($value instanceof RequestInput) {
                $this->$key->value = $value->value;
            } elseif ((is_object($this->$key)) && method_exists($this->$key, 'copy')) {
                $this->$key->copy($value);
            } elseif (!is_object($value)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Returns a list of column names to use to format SQL queries that will be used to read and update
     * records.
     * @param array $used_keys (Optional) Properties that have already been added to the stack.
     * @return array Key/value pairs for each RequestInput property of the class.
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    protected function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $this->connectToDatabase();
        $fields = array();
        foreach ($this as $key => $item) {
            if ($this->isInput($key, $item, $used_keys)) {
                /** @var RequestInput $item */
                if ($item->is_database_field === false) {
                    continue;
                }
                /* format column name and value for SQL statement */
                $fields[] = new QueryField(
                    $item->column_name ?: $key,
                    $item::getPreparedStatementTypeIdentifier(),
                    $item->escapeSQL($this->mysqli));
            }
            elseif(Validation::isSubclass($item, DBFieldGroup::class)) {
                /** @var DBFieldGroup $item */
                $fields = array_merge($fields, $item->extractPreparedStmtArgs($used_keys));
            }
        }
        return ($fields);
    }

    /**
     * Fills object properties using property values found in $src argument.
     * @param array|object $src Source object containing values to assign to this instance.
     */
    public function fill($src)
    {
        foreach ($src as $key => $val) {
            if (property_exists(get_class($this), $key)) {
                if (!isset($this->$key)) {
                    $this->$key = $val;
                }
                else {
                    if ($this->$key instanceof RequestInput) {
                        $this->$key->setInputValue($val);
                    }
                    elseif (is_object($this->$key) === false) {
                        $this->$key = $val;
                    }
                }
                if (is_object($src)) {
                    unset($src->$key);
                }
                elseif (is_array($src)) {
                    unset($src[$key]);
                }
            }
        }
        foreach($this as $item) {
            if (Validation::isSubclass($item, DBFieldGroup::class)) {
                $item->fill($src);
            }
        }
    }

    /**
     * Returns a list of all RequestInput properties of an object.
     * @param bool $db_only If true, only properties marked as database fields will be returned.
     * @param array $ignore_keys Keys to ignore. By default, it ignores keys named to indicate they are id or index
     * properties.
     * @return array
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
     * Returns a list of all properties associated with records linked to this object.
     * @return array
     */
    protected function getLinkedContentPropertiesList(): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubclass($property, SerializedContentIO::class)) {
                $properties[] = $key;
            }
        }
        return $properties;
    }

    /**
     * Recordset prefix getter.
     * @return string|string[]
     */
    public function getRecordsetPrefix()
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
     * Assign values contained in array to object input properties.
     * @param object $row Recordset row containing values to copy into the object's properties.
     */
    protected function hydrateFromRecordsetRow(object $row)
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
    protected function isInput(string $key, $item, array &$used_keys): bool
    {
        if (!Validation::isSubclass($item, RequestInput::class)) {
            return false;
        }
        if ($key === 'id' && !$this->hasRecordsetPrefix()) {
            return false;
        }
        if ($key === 'index') {
            return false;
        }
        if (!$item->isDatabaseField()) {
            return false;
        }

        /**
         * Check if this item has already been used as in input property.
         * This prevents references used as aliases of existing properties
         * from being included in database queries.
         */
        if (in_array($item->key, $used_keys)) {
            return false;
        }

        /**
         * Once an input property is marked as such track it, so it won't be included again.
         */
        $used_keys[] = $item->key;
        return true;
    }

    /**
     * Save RequestInput property values in form markup.
     * @param array $excluded_keys Optional list of keys that will be excluded from the form markup.
     */
    public function preserveInForm(array $excluded_keys = [])
    {
        foreach ($this as $item) {
            if ($item instanceof RequestInput && !in_array($item->key, $excluded_keys)) {
                // make sure to use template path for base object, which is a hidden input element
                $item->saveInForm(RequestInput::getTemplatePath());
            }
            elseif(is_object($item) && method_exists($item, 'preserveInForm')) {
                $item->preserveInForm($excluded_keys);
            }
        }
    }

    /**
     * Recordset prefix setter.
     * @param $prefix
     */
    public function setRecordsetPrefix($prefix)
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