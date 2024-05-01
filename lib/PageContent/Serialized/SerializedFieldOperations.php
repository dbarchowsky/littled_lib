<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Albums\Gallery;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Request\PrimaryKeyInput;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

trait SerializedFieldOperations
{
    use InputOperations;

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
     * @return QueryField[] Key/value pairs for each RequestInput property of the class.
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    protected function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $this->connectToDatabase();
        $fields = [];
        foreach ($this as $key => $item) {

            // return any RequestInput properties that map to fields in the database record
            if ($this->isDatabaseProperty($item, $used_keys)) {
                /** @var RequestInput $item */
                /* format column name and value for SQL statement */
                $fields[] = (new QueryField())
                    ->setisPrimaryKey(Validation::isSubclass($item, PrimaryKeyInput::class))
                    ->setKey($item->getColumnName($this->getRecordsetPrefix() . $key))
                    ->setType($item::getPreparedStatementTypeIdentifier())
                    ->setValue($item->escapeSQL($this->mysqli));
            }

            // return any PK properties for linked records
            elseif(Validation::isSubclass($item, SerializedContent::class) &&
                !Validation::isSubclass($item, ContentProperties::class)) {
                if ($item->isDatabaseProperty($item->id, $used_keys)) {
                    $fields[] = (new QueryField())
                        ->setisPrimaryKey(false) /* << not PK because it's a FK column in the parent table */
                        ->setKey($item->id->getColumnName($item->getRecordsetPrefix() . 'id'))
                        ->setType($item->id::getPreparedStatementTypeIdentifier())
                        ->setValue($item->id->escapeSQL($this->mysqli));
                }
            }

            // other RequestInput properties that have been grouped together
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
     * Returns list of all properties of the object that represent linked child records in the database.
     * @param array $exclude
     * @return string[]
     */
    protected function getContentPropertiesList(array $exclude = []): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubclass($property, SerializedContentIO::class) && !in_array($key, $exclude)) {
                $properties[] = $key;
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
     * Adds a prefix to any RequestInput property of the object.
     * @param string $prefix
     * @return void
     */
    public function setColumnPrefix(string $prefix)
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setColumnName($prefix . $property);
        }
    }

    /**
     * Adds a prefix to any RequestInput property of the object.
     * @param string $prefix
     * @return void
     */
    public function setInputPrefix(string $prefix)
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setKey($prefix . $this->$property->key);
        }
    }
}