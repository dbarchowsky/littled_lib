<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;

/**
 * Class RequestInput
 * Base class for all varieties of request input
 * @package Littled\Request
 */
abstract class RequestInput
{
    /** Data type identifier used with bind_param() calls */
    protected static string     $bind_param_type = 's';

    /** Content type within HTML form, e.g., type="text", type="tel", type="email", etc. */
    public string               $content_type='text';
    /** If FALSE, this property will be passed over when retrieving or saving its value from or to the database. The default value is TRUE. */
    public bool                 $is_database_field=true;
    /** Name to use to override the default name of the column in the database holding the value linked to this property. The default value is the name of the property in the parent class. */
    public string               $column_name='';
    /** Flag indicating that the object value should not be assigned from request variable values. */
    public bool                 $bypass_collect_request_data=false;
    public array                $attributes=[];
    public bool                 $allow_multiple = false;
    /** Flag indicating that an error was detected with the value supplied for this form data. */
    public bool                 $has_errors=false;
    /** If an error was detected with the value of form data, a description of the error will be stored in this property. */
    public string               $error='';
    /** When supplying an array of values for a single key, the value can be used to sort them. */
    public string|int|null      $index=null;
    /** Label to display where descriptions of the input are needed. */
    public string               $label='';
    /** Name of script argument. Name of a key in query string or form data. */
    public string               $key='';
    /** Set to TRUE if a value for this form data is required. */
    public bool                 $required=false;
    /** Size of data being held. Used to specify the size of varchar arguments in database calls. Also used to limit the length of input in textarea inputs. */
    public int                  $size_limit=0;
    /** Value of the script argument. Value collected from form data. */
    public mixed                $value;
    /** If supplied, this value will be used to specify the width of a form input through its "style" attribute. E.g. "240 px" */
    public string               $width='';

    /**
     * class constructor
     * @param string $label Input label
     * @param string $key value of the name attribute of the input
     * @param bool $required Optional flag indicating if this form data is required. Defaults to FALSE.
     * @param mixed $value Optional initial value of the input. Defaults to NULL.
     * @param int $size_limit Optional maximum size in bytes of the value when it is stored in the database (for strings). Defaults to 0.
     * @param ?int $index Optional index of this input if it is part of an array of inputs with the same name attribute. Defaults to NULL.
     */
    function __construct (
        string      $label       = '',
        string      $key         = '',
        bool        $required    = false,
        mixed       $value       = null,
        int         $size_limit  = 0,
        ?int        $index       = null )
    {
        $this->label        = $label;
        $this->key          = $key;
        $this->size_limit   = $size_limit;
        $this->required     = $required;
        $this->index        = $index;
        $this->value        = $value;
    }

    /**
     * "Allow multiple" setter
     * @return bool
     */
    public function allowMultiple(): bool
    {
        return $this->allow_multiple;
    }

    /**
     * Clears any error properties of the object.
     */
    public function clearValidationErrors(): void
    {
        $this->has_errors = false;
        $this->error = '';
    }

    /**
     * Resets the object's value property to a default value.
     */
    public function clearValue(): void
    {
        $this->value = null;
    }

    /**
     * Assigns property value from corresponding value in JSON data passed along with a client request.
     * @param object $data Collection of client ajax request data containing the key/value pair to use to assign the property value.
     */
    public function collectAjaxRequestData(object $data): void
    {
        if ($this->isBypassingRequestData()) {
            return;
        }
        if (property_exists($data, $this->key)) {
            $this->value = filter_var($data->{$this->key}, FILTER_UNSAFE_RAW);
        }
    }

    /**
     * Collects the value corresponding to the $key property value in GET, POST, session, or cookies.
     */
    abstract public function collectRequestData(?array $src=null);

    /**
     * Copies property values from another RequestInput object.
     * @param RequestInput $src
     * @return void
     */
    public function copy(RequestInput $src): void
    {
        foreach($src as $property => $value) {
            $this->{$property} = $src->{$property};
        }
    }

    /**
     * Escapes the object's value property for inclusion in SQL queries.
     * @deprecated Use mysqli parameterized queries instead.
     * @param bool $include_quotes Optional. If TRUE, the escape string will be enclosed in quotes. Default is FALSE.
     * @return bool|int|float|string|null Escaped value.
     */
    public function escapeSQL(bool $include_quotes=false): bool|float|int|string|null
    {
        if ($this->value===null) {
            return null;
        }
        $value = $this->value;
        if ($value===true) {
            $value = '1';
        }
        elseif ($value===false) {
            $value = 0;
        }
        return (($include_quotes)?("'"):('')).$value.(($include_quotes)?("'"):(''));
    }

    /**
     * Sets the $value property of the object from the value of the session value corresponding to the object's
     * $key property.
     * @param string $cookie_name Name of the cookie collection containing the value to be retrieved.
     */
    public function fillFromSession(string $cookie_name): void
    {
        if (isset($_SESSION[$this->key])) {
            $this->value = $_SESSION[$this->key];
        }
        elseif(isset($_COOKIE[$cookie_name][$this->key])) {
            $this->value = $_COOKIE[$cookie_name][$this->key];
        }
    }

    /**
     * Returns a consistently formatted label string for use in error messages.
     * Default format is first letter capitalized.
     * @return string Error label string.
     */
    public function formatErrorLabel(): string
    {
        return (ucfirst(strtolower($this->getLabel())));
    }

    /**
     * Returns the HTML element attributes for the form input that collects this variable.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Column name getter, if the column_name property has been set to override the default column name,
     * which is the name of the RequestInput variable itself. Pass in the variable name as a default.
     * @param string $property (Optional) column name to use if the $column_name property value has not been set.
     * @return string
     */
    public function getColumnName(string $property=''): string
    {
        return trim($this->column_name) ?: $property;
    }

    /**
     * Index property getter.
     * @return int|string|null
     */
    public function getIndex(): int|string|null
    {
        return $this->index;
    }

    /**
     * Value property getter..
     * @return mixed
     */
    public function getInputValue(): mixed
    {
        return $this->value;
    }

    /**
     * Gets the input's key value
     * @return string
     */
    public function getKey( ): string
    {
        return $this->key;
    }

    /**
     * Label property getter.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns an identifier to use when using the value with a mysqli prepared statement.
     * @return string
     */
    public static function getPreparedStatementTypeIdentifier(): string
    {
        return static::$bind_param_type;
    }

    /**
     * Tests if the value of the object is not currently set.
     * @return bool True/false depending on whether the value is set or not.
     */
    abstract public function hasData(): bool;

    /**
     * Has validation errors flag getter.
     * @return bool
     */
    public function hasValidationErrors(): bool
    {
        return $this->has_errors;
    }

    /**
     * Sets a flag that will cause this variable to be ignored when processing request data sent to the page.
     */
    public function ignoreRequestData(): void
    {
        $this->bypass_collect_request_data = true;
    }

    /**
     * Returns a flag indicating if this object is set to not collect request data from the client.
     * @return bool
     */
    public function isBypassingRequestData(): bool
    {
        return ($this->bypass_collect_request_data===true);
    }

    /**
     * Returns flag indicating this object corresponds to a database field.
     * @return bool
     */
    public function isDatabaseField(): bool
    {
        return $this->is_database_field;
    }

    /**
     * Required flag value getter.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @deprecated Use formatMarkupValue() instead.
     * Returns string safe from XSS attacks that can be embedded in HTML.
     * @param array|int $options Combination of tokens to pass along, e.g., FILTER_SANITIZE_FULL_SPECIAL_CHARS
     * Same values as the 3rd argument to PHP's filter_var() routine.
     * @return string XSS-safe string.
     */
    public function safeValue(array|int $options=[]): string
    {
        return (filter_var($this->getInputValue() ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options));
    }

    /**
     * Chainable "allow multiple" property setter.
     * @param bool $allow
     * @return $this
     */
    public function setAllowMultiple(bool $allow=true): static
    {
        $this->allow_multiple = $allow;
        return $this;
    }

    /**
     * Alias for: setAsOptional()
     * @return $this
     */
    public function setAsNotRequired(): static
    {
        return $this->setAsOptional();
    }

    /**
     * Sets a flag to indicate that this input value is not required.
     * @return $this
     */
    public function setAsOptional(): static
    {
        $this->required = false;
        return $this;
    }

    /**
     * Sets a flag to indicate that this input value is required.
     * @return $this
     */
    public function setAsRequired(): static
    {
        $this->required = true;
        return $this;
    }

    /**
     * Sets what will be an attribute of the HTML element representing the form input that collects this variable.
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return $this
     */
    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Sets the value of the object's "bypass collect request data" property.
     * @param bool $collect
     * @return $this
     */
    public function setCollectRequestData(bool $collect=true): static
    {
        $this->bypass_collect_request_data = !$collect;
        return $this;
    }

    /**
     * Sets the value of the object's "has errors" flag and error message.'
     * @param string $error
     * @return $this
     */
    public function setValidationError(string $error): static
    {
        $this->has_errors = true;
        $this->error = $error;
        return $this;
    }

    /**
     * Chainable routine that sets column name property value.
     * @param string $column_name The name of the column in the database corresponding to this object.
     * @return $this
     */
    public function setColumnName(string $column_name): static
    {
        $this->column_name = $column_name;
        return $this;
    }

    /**
     * Chainable routine that sets the "is database field" flag to TRUE or FALSE.
     * @param bool $is_field Value for "is database field".
     * @return $this
     */
    public function setIsDatabaseField(bool $is_field): static
    {
        $this->is_database_field = $is_field;
        return $this;
    }

    /**
     * Override this routine in derived classes in case any extra assignments
     * need to be made in addition to settings the object's "value" property.
     * @param mixed $value Base value to assign.
     * @return $this
     */
    public function setInputValue(mixed $value ): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Chainable input key setter.
     * @param string $key
     * @return $this
     */
    public function setKey( string $key ): static
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Container CSS class setter.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Sets the value of an arbitrary property of the class.
     * @param string $property Property name.
     * @param mixed $value Value to assign to the object property.
     */
    public function setProperty(string $property, mixed $value ): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Size limit setter.
     * @param int $size_limit
     * @return $this
     */
    public function setSizeLimit(int $size_limit): static
    {
        $this->size_limit = $size_limit;
        return $this;
    }

    /**
     * Utility routine for standardized invalid content error handling.
     * @param string $err Error message
     * @throws ContentValidationException
     */
    protected function throwValidationError( string $err )
    {
        $this->setValidationError($err);
        throw new ContentValidationException($this->error);
    }

    /**
     * Validates the object's current value stored in its $value property.
     * @throws ContentValidationException
     */
    public function validate(): void
    {
        if ($this->required) {
            if (!$this->hasData()) {
                $this->throwValidationError($this->formatErrorLabel(). ' is required.');
            }
        }
    }
}