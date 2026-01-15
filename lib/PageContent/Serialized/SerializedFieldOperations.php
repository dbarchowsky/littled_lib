<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Albums\Gallery;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Request\ForeignKeyInput;
use Littled\Request\PrimaryKeyInput;
use Littled\Request\RenderedInput;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;


trait SerializedFieldOperations
{
    use InputOperations;

    protected bool              $has_foreign_key            = true;

    /**
     * Returns the form data members of the objects as a series of nested associative arrays.
     * @param array|null $exclude_keys An optional array of parameter names to exclude from the returned array.
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
                        $ar[$key] = $item->arrayEncode(array('tn', 'site_section'));
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

    public function clearValues(): void
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
     * Uses request data to assign values to properties representing primary and foreign keys.
     * @param array|null $src
     * @param array|null $exclude
     * @return $this
     */
    public function collectKeysRequestData(?array $src=null, ?array $exclude=[]): static
    {
        $properties = $this->getKeyPropertiesList();
        foreach ($properties as $property) {
            if (!in_array($property, $exclude)) {
                $this->$property->collectRequestData($src);
            }
        }
        return $this;
    }

    /**
     * Assigns a value collected from request data to an individual input property. Any property can be passed as an
     * argument to the method. The method tests the property to determine if its type and state allow for the
     * assigment to be made.
     * @param mixed $property
     * @param array $src
     * @return void
     */
    protected static function collectPropertyValueFromRequestData(mixed $property, array $src): void
    {
        if (!is_object($property)) {
            return;
        }
        if (property_exists($property, 'bypassCollectFromInput') && $property->bypassCollectFromInput === true) {
            return;
        }
        if (method_exists($property, 'isDatabaseField') && !$property->isDatabaseField()) {
            return;
        }
        if (method_exists($property, 'collectRequestData')) {
            $property->collectRequestData($src);
        } elseif (method_exists($property, 'collectFormInput')) {
            $property->collectFormInput(null, $src);
        }
    }

    /**
     * Set property values using input variable values, e.g., GET, POST, cookies
     * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @return $this
     */
    public function collectRequestData(?array $src = null): static
    {
        $src = $src ?? Validation::getDefaultInputSource();
        foreach ($this as $item) {
            static::collectPropertyValueFromRequestData($item, $src);
        }
        return $this;
    }

    /**
     * Copies the property values from one object into this instance.
     * @param mixed $src Object to use to copy values over to this object.
     * @throws InvalidTypeException Source is not a valid object.
     */
    public function copy(mixed $src): void
    {
        if (!is_object($src)) {
            throw new InvalidTypeException('Source for copy is not an object.');
        }
        if (get_class($this) != get_class($src)) {
            throw new InvalidTypeException('Invalid object for copy.');
        }
        foreach (get_object_vars($src) as $key => $value) {
            if (isset($this->{$key}) && is_object($this->$key) && method_exists($this->$key, 'copy')) {
                $this->$key->copy($value);
            } elseif (!is_object($value)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Returns a list of column names to use to format SQL queries that will be used to read and update records.
     * @param array $used_keys (Optional) Properties that have already been added to the stack.
     * @return QueryField[] Key/value pairs for each RequestInput property of the class.
     */
    protected function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $fields = [];

        foreach ($this as $key => $item) {

            // return any RenderedInput properties that map to fields in the database record
            if ($this->isDatabaseProperty($item, $used_keys)) {
                /** @var RenderedInput $item */
                /* format column name and value for SQL statement */
                $fields[] = (new QueryField())
                    ->setisPrimaryKey(Validation::isSubclass($item, PrimaryKeyInput::class))
                    ->setIsForeignKey(Validation::isSubclass($item, ForeignKeyInput::class))
                    ->setKey($item->getColumnName($this->getRecordsetPrefix(0) . $key))
                    ->setType($item::getPreparedStatementTypeIdentifier())
                    ->setValue($item->getInputValue());
            }

            // return any PK properties for linked records
            elseif($this->testForLinkedProperty($key)) {
                if ($item->isDatabaseProperty($item->id, $used_keys)) {
                    $fields[] = (new QueryField())
                        ->setisPrimaryKey(false) /* << not PK because it's a FK column in the parent table */
                        ->setIsForeignKey(true)
                        ->setKey($item->id->getColumnName($item->getRecordsetPrefix(0) . 'id'))
                        ->setType($item->id::getPreparedStatementTypeIdentifier())
                        ->setValue($item->id->getInputValue());
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
     * @param object|array $src Source object containing values to assign to this instance.
     */
    public function fill(object|array $src): void
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
     * Returns a list of all properties of the object that represent linked child records in the database.
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
     * "Has foreign key" setting getter. Determines if this object is linked to another parent object in the database.
     * @return bool
     */
    public function getHasForeignKey(): bool
    {
        return $this->has_foreign_key;
    }

    /**
     * Returns a list of all properties that represent either primary or foreign keys.
     * @param $exclude string[]
     * @return string[]
     */
    protected function getKeyPropertiesList(array $exclude=[]): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubclass($property, PrimaryKeyInput::class) ||
                Validation::isSubclass($property, ForeignKeyInput::class)) {
                if (method_exists($property, 'isDatabaseField') &&
                    $property->isDatabaseField() &&
                    !in_array($property->getKey(), $exclude)) {
                    $properties[] = $key;
                }
            }
        }
        return $properties;
    }

    /**
     * Returns a list of all the names of properties associated with records linked to this object.
     * @return string[]
     */
    protected function getLinkedContentPropertiesList(): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubclass($property, SerializedContentIO::class) &&
                !Validation::isSubclass($property, ContentProperties::class)) {
                $properties[] = $key;
            }
        }
        return $properties;
    }

    /**
     * Save RequestInput property values in form markup.
     * @param array $excluded_keys Optional list of keys that will be excluded from the form markup.
     */
    public function preserveInForm(array $excluded_keys = []): void
    {
        foreach ($this as $item) {
            if ($item instanceof RenderedInput && !in_array($item->key, $excluded_keys)) {
                // make sure to use the template path for the base object, which is a hidden input element
                $item->saveInForm(RenderedInput::getHiddenTemplatePath());
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
    public function setColumnPrefix(string $prefix): void
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setColumnName($prefix . $property);
        }
    }

    /**
     * "Has foreign key" setter. Determines if this object is linked to a parent object in the database.
     * @param bool $flag
     * @return $this
     */
    public function setHasForeignKey(bool $flag): static
    {
        $this->has_foreign_key = $flag;
        return $this;
    }

    /**
     * Sets the index for all input properties of the object.
     * @param int $index
     * @param string[]|null $exclude
     * @return $this
     */
    public function setIndex(int $index, array|null $exclude = null): static
    {
        $properties = $this->getInputPropertiesList(true, $exclude);

        // Add primary keys and foreign keys for child objects, but the top-level PK value is unique and not
        // passed in the request as an array of values
        $properties = array_merge($properties, $this->getKeyPropertiesList([LittledGlobals::ID_KEY]));
        foreach ($properties as $property) {
            if ($this->$property->getKey() !== LittledGlobals::ID_KEY) {
                $this->$property->index = $index;
            }
        }
        $properties = $this->getLinkedContentPropertiesList();
        foreach ($properties as $property) {
            if (method_exists($this->$property, 'setIndex')) {
                $this->$property->setIndex($index);
            }
        }
        return $this;
    }

    /**
     * Adds a prefix to any RequestInput property of the object.
     * @param string $prefix
     * @return void
     */
    public function setInputPrefix(string $prefix): void
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setKey($prefix . $this->$property->key);
        }
    }

    /**
     * Tests a property to determine if it represents a record linked to the main record represented by this object.
     * @param string $key
     * @return bool
     */
    protected function testForLinkedProperty(string $key): bool
    {
        $property = $this->$key;
        return (
            Validation::isSubclass($property, SerializedContent::class) &&
            !Validation::isSubclass($property, ContentProperties::class) &&
            $property->getHasForeignKey());
    }
}