<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Utility\LittledUtility;
use Littled\Validation\ContentConversion;
use Littled\Validation\Validation;
use Exception;
use mysqli;

/**
 * Class RequestInput
 * Base class for all varieties of request input
 * @package Littled\Request
 */
class RequestInput
{
    /** @var string             Path to form input templates. */
    protected static string     $template_base_path = '';
    /** @var string             Input template filename. */
    protected static string     $template_filename = 'hidden-input.php';
    /** @var string             Input template filename. */
    protected static string     $hidden_template_filename = 'hidden-input.php';
    /** @var string             Form input element filename. */
    protected static string     $input_template_filename = '';
    /** @var string             Required field indicator string. */
    protected static string     $required_field_indicator = ' (*)';
    /** @var string             Error indicator CSS class. */
    protected static string     $error_class = 'form-error';
    /** @var string */
    protected static string     $input_error_css_class = 'input-error';
    /** @var string             Data type identifier used with bind_param() calls */
    protected static string     $bind_param_type = 's';

    /** @var string             Name of CSS class to be used when displaying the form input. */
    public string               $container_css_class='form-cell';
    /** @var string             Content type within HTML form, e.g. type="text", type="tel", type="email", etc. */
    public string               $content_type='text';
    /** @var bool                If FALSE this property will be passed over when retrieving or saving its value from or to the database. Default value is TRUE. */
    public bool                 $is_database_field=true;
    /** @var string              Name to use to override the default name of the column in the database holding the value linked to this property. The default value is the name of the property in the parent class. */
    public string               $column_name='';
    /** @var bool               Flag indicating that the object value should not be assigned from request variable values. */
    public bool                 $bypass_collect_request_data=false;

    public array                $attributes=[];

    /**
     * @var boolean Flag to control the insertion of a "placeholder" attribute
     * when rendering the input. If TRUE, a placeholder attribute will be added
     * (to text fields), using the object's "label" property value as its value.
     */
    public bool                 $display_placeholder=false;
    /** @var boolean Flag indicating that an error was detected with the value supplied for this form data. */
    public bool                 $has_errors=false;
    /** @var string If an error was detected with the value of a form data, a description of the error will be stored in this property. */
    public string               $error='';
    /** @var string|int|null When supplying an array of values for a single key, the  value can be used to sort them. */
    public                      $index=null;
    /** @var string Label to display where descriptions of the input are needed. */
    public string               $label='';
    /** @var string  Name of script argument. Name of key in query string or form data. */
    public string               $key='';
    /** @var string CSS class identifier. */
    public string               $input_css_class='';
    /** @var bool Set to TRUE if a value for this form data is required. */
    public bool                 $required=false;
    /** @var int Size of data being held. Used to specify the size of varchar arguments in database calls. Also used to limit the length of input in textarea inputs. */
    public int                  $size_limit=0;
    /** @var mixed Value of the script argument. Value collected from form data. */
    public                      $value;
    /** @var string If supplied, this value will be used to specify the width of a form input through its "style" attribute. E.g. "240px" */
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
        string $label,
        string $key,
        bool $required=false,
        $value=null,
        int $size_limit=0,
        ?int $index=null )
    {
        $this->label              = $label;
        $this->key                = $key;
        $this->size_limit         = $size_limit;
        $this->required           = $required;
        $this->index              = $index;
        $this->value              = $value;
    }

    /**
     * Resets the object's value property to a default value.
     */
    public function clearValue()
    {
        $this->value = null;
    }

    /**
     * Assigns property value from corresponding value in JSON data passed along with a client request.
     * @param object $data Collection of client ajax request data containing the key/value pair to use to assign the property value.
     */
    public function collectAjaxRequestData(object $data)
    {
        if ($this->isBypassingRequestData()) {
            return;
        }
        if (property_exists($data, $this->key)) {
            $this->value = filter_var($data->{$this->key}, FILTER_UNSAFE_RAW);
        }
    }

    /**
     * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
     * @throws NotImplementedException
     */
    public function collectRequestData(?array $src=null)
    {
        throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
    }

    /**
     * Escapes the object's value property for inclusion in SQL queries.
     * @param mysqli $mysqli Database connection.
     * @param bool $include_quotes Optional. If TRUE, the escape string will be enclosed in quotes. Default is FALSE.
     * @return int|float|string|null Escaped value.
     */
    public function escapeSQL(mysqli $mysqli, bool $include_quotes=false)
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
        return (($include_quotes)?("'"):("")).$value.(($include_quotes)?("'"):(""));
    }

    /**
     * Sets the $value property of the object from the value of the session value corresponding to the object's
     * $param property.
     * @param string $cookie_name Name of the cookie collection containing the value to be retrieved.
     */
    public function fillFromSession(string $cookie_name)
    {
        if (isset($_SESSION[$this->key])) {
            $this->value = $_SESSION[$this->key];
        }
        elseif(isset($_COOKIE[$cookie_name][$this->key])) {
            $this->value = $_COOKIE[$cookie_name][$this->key];
        }
    }

    /**
     * Returns string containing markup containing all attributes and their values stored in the object.
     * @return string
     */
    public function formatAttributesMarkup(): string
    {
        $markup = implode(' ', array_map(
            function($key, $value) {return "$key=\"$value\""; },
            array_keys($this->attributes),
            $this->attributes));
        if($markup) {
            $markup = ' '.$markup;
        }
        return $markup;
    }

    /**
     * Formats the css class attribute string to be injected into markup of the input's container element.
     * @param string $css_class (Optional) An additional CSS class to apply to the element in addition to the CSS class stored in the object.
     * @param callable|null $css_callback (Optional) Routine to use to fetch the class from the input object that will be applied to the element. The markup element is either the input element itself or its container. Defaults to applying the input elements css class.
     * @return string
     */
    public function formatClassAttributeMarkup(string $css_class='', ?callable $css_callback=null): string
    {
        if ($css_callback===null) {
            $css_callback = array($this, 'getInputCssClass');
        }
        $base_class = call_user_func($css_callback);
        $error_class = '';
        if ($this->has_errors) {
            $error_class = (($css_callback[1]==='getInputCssClass')?(static::getInputErrorClass()):(static::getErrorClass()));
        }
        $classes = trim(implode(' ', array_filter(array($base_class, $css_class, $error_class))));
        return (($classes)?(" class=\"$classes\""):(''));
    }

    /**
     * Returns a consistently formatted label string for use in error messages.
     * Default format is first letter capitalized.
     * @return string Error label string.
     */
    public function formatErrorLabel(): string
    {
        return (ucfirst(strtolower($this->label)));
    }

    /**
     * Formats a string that can be inserted into markup to utilize the $index property value.
     * @return string
     */
    public function formatIndexMarkup(): string
    {
        return ContentConversion::formatIndexMarkup($this->index);
    }

    /**
     * Default routine for rendering the label of the input.
     * @param string $label Text to display as the label for the form input. A null value will cause the internal label value to be used. An empty string will cause the label to not be rendered at all.
     * @return string Label markup to insert into form content.
     * @throws ResourceNotFoundException Template not found.
     */
    public function formatLabelMarkup( string $label ): string
    {
        if (strlen($label) > 0 && $this->display_placeholder===false) {
            return (ContentUtils::loadTemplateContent(static::$template_base_path."form-input-label.php", array(
                'label' => $label,
                'input' => &$this
            )));
        }
        return ('');
    }

    /**
     * Returns markup to inject into input container element to indicate on the front-end that the input form data is required.
     * @return string
     */
    public function formatRequiredIndicatorMarkup(): string
    {
        return (($this->required)?(static::getRequiredIndicator()):(''));
    }

    /**
     * Formats the value of the object in a way where it can be inserted into markup.
     * @return string
     */
    public function formatValueMarkup(): string
    {
        return ("".$this->value);
    }

    /**
      * Container CSS class getter.
      * @return string
      */
    public function getContainerCssClass(): string
    {
        return $this->container_css_class;
    }

    /**
     * Error css class getter.
     * @return string Current error css class value.
     */
    public static function getErrorClass(): string
    {
        return (static::$error_class);
    }

    /**
     * Hidden template filename getter.
     * @return string
     */
    public static function getHiddenTemplateFilename(): string
    {
        return static::$hidden_template_filename;
    }

    /**
     * Get full path to the hidden form input element template file.
     * @return string Full path to form input element template file.
     */
    public static function getHiddenTemplatePath(): string
    {
        return (LittledUtility::joinPaths(static::$template_base_path, static::$hidden_template_filename));
    }

    /**
     * Error CSS class getter for the container element.
     * @return string
     */
    public function getInputCssClass(): string
    {
        return $this->input_css_class;
    }

    /**
     * Error CSS class getter for the input element.
     * @return string Current error css class value.
     */
    public static function getInputErrorClass(): string
    {
        return (static::$input_error_css_class);
    }

    /**
     * Returns the filename of the template used to render just the input element.
     * @return string Form input template filename.
     */
    public static function getInputTemplateFilename(): string
    {
        return (static::$input_template_filename);
    }

    /**
     * Returns full path to input element template file.
     * @return string Path to input element template.
     */
    public static function getInputTemplatePath(): string
    {
        return(LittledUtility::joinPaths(static::$template_base_path, static::$input_template_filename));
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
     * Returns an identifier to use when using the value with a mysqli prepared statement.
     * @return string
     */
    public static function getPreparedStatementTypeIdentifier(): string
    {
        return static::$bind_param_type;
    }

    /**
     * Returns string to insert into front-end templates that will indicate that field is required to submit form data.
     * @return string Content to insert into template.
     */
    public static function getRequiredIndicator(): string
    {
        return(static::$required_field_indicator);
    }

    /**
     * Template path getter.
     * @return string Current internal template path value.
     */
    public static function getTemplateBasePath(): string
    {
        return (static::$template_base_path);
    }

    /**
     * Template filename getter.
     * @return string Current internal template filename.
     */
    public static function getTemplateFilename(): string
    {
        return (static::$template_filename);
    }

    /**
     * Get full path to form input element template file.
     * @return string Full path to form input element template file.
     */
    public static function getTemplatePath(): string
    {
        return (LittledUtility::joinPaths(static::$template_base_path, static::$template_filename));
    }

    /**
     * Sets flag that will cause this variable to be ignored when processing request data sent to the page.
     */
    public function ignoreRequestData()
    {
        $this->bypass_collect_request_data = true;
    }

    /**
     * Returns flag indicating if this object is set to not collect request data from the client.
     * @return bool
     */
    public function isBypassingRequestData(): bool
    {
        return ($this->bypass_collect_request_data===true);
    }

    /**
     * Tests if the value of the object is not currently set.
     * @return bool True/false depending on whether the value is set or not.
     */
    public function isEmpty(): bool
    {
        return ($this->value===null || ($this->value !== false && trim($this->value)===''));
    }

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isInputTemplateDefined(): bool
    {
        return (Validation::isStringWithContent($this::getInputTemplateFilename()));
    }

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isTemplateDefined(): bool
    {
        return (Validation::isStringWithContent($this::getTemplateFilename()));
    }

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isInputTemplateBasePathDefined(): bool
    {
        return (Validation::isStringWithContent($this::getTemplateBasePath()));
    }

    /**
     * Renders the corresponding form field with a label to collect the input data.
     * @param string $label
     * @param string $css_class
     * @throws NotImplementedException
     */
    public function render( string $label='', string $css_class='' )
    {
        if (!$label) {
            $label = $this->label;
        }
        throw new NotImplementedException(__METHOD__."() not implemented for $label. $css_class ");
    }

    /**
     * Renders the corresponding form field with a label to collect the input data.
     * @param ?string $label Label to display with input element.
     * @throws NotImplementedException
     */
    public function renderInput(?string $label=null)
    {
        throw new NotImplementedException(__METHOD__."() not implemented.");
    }

    /**
     * Wrapper for render() method that prints error message if an exception is thrown rendering the form input element.
     * @param ?string $label Optional label that will override the object's internal property value.
     * @param ?string $css_class Optional CSS class name that will override the object's internal property value.
     */
    public function renderWithErrors(?string $label=null, ?string $css_class=null)
    {
        try {
            $this->render($label, $css_class);
        }
        catch(Exception $ex) {
            ContentUtils::printError($ex->getMessage());
        }
    }

    /**
     * Returns string safe from XSS attacks that can be embedded in HTML.
     * @param int|array $options Combination of tokens to pass along, e.g. FILTER_SANITIZE_FULL_SPECIAL_CHARS
     * Same values as 3rd argument to PHP's filter_var() routine.
     * @return string XSS-safe string.
     */
    public function safeValue($options=[] ): string
    {
        return (filter_var($this->value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options));
    }

    /**
     * Prints out markup to save input value in a hidden form input element.
     * @param string $template Path to template to use to override current template path stored in the object.
     * @param string $key Key to use to override default key value for the variable.
     */
    public function saveInForm( string $template='', string $key='' )
    {
        if (!$key) {
            $key = $this->key;
        }
        if(!$template) {
            $template = RequestInput::getTemplatePath();
        }
        ContentUtils::renderTemplateWithErrors($template, array(
            'key' => $key,
            'input' => $this
        ));
    }

    /**
     * Sets flag to indicate that this input value is not required.
     */
    public function setAsNotRequired()
    {
        $this->required = false;
    }

    /**
     * Sets flag to indicate that this input value is required.
     */
    public function setAsRequired()
    {
        $this->required = true;
    }

    /**
     * Sets what will be an attribute of the html element representing the form input that collects this variable.
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return $this
     */
    public function setAttribute(string $key, $value): RequestInput
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Chainable routine that sets column name property value.
     * @param string $column_name Name of the column in the database corresponding to this object.
     * @return $this
     */
    public function setColumnName(string $column_name): RequestInput
    {
        $this->column_name = $column_name;
        return $this;
    }

    /**
     * Container CSS class setter.
     * @param string $class
     * @return RequestInput
     */
    public function setContainerCSSClass(string $class): RequestInput
    {
        $this->container_css_class = $class;
        return $this;
    }

    /**
     * Error css class setter.
     * @param string $css_class CSS class name.
     */
    public function setErrorClass( string $css_class )
    {
        static::$error_class = $css_class;
    }

    /**
     * Input CSS class setter.
     * @param string $class
     * @return RequestInput
     */
    public function setInputCSSClass(string $class): RequestInput
    {
        $this->input_css_class = $class;
        return $this;
    }

    /**
     * Hidden template filename setter.
     * @param string $filename
     * @return void
     */
    public static function setHiddenTemplateFilename(string $filename)
    {
        static::$hidden_template_filename = $filename;
    }

    /**
     * Form input element template filename setter.
     * @param string $filename Template filename.
     * @return void
     */
    public static function setInputTemplateFilename( string $filename )
    {
        static::$input_template_filename = $filename;
    }

    /**
     * Override this routine in derived classes in case any extra assignments
     * need to be made in addition to settings the object's "value" property.
     * @param mixed $value Base value to assign.
     */
    public function setInputValue( $value )
    {
        $this->value = $value;
    }

    /**
     * Sets the input's key value
     * @param string $key
     */
    public function setKey( string $key )
    {
        $this->key = $key;
    }

    /**
     * Sets the value of an arbitrary property of the class.
     * @param string $property Property name.
     * @param mixed $value Value to assign to the object property.
     */
    public function setProperty( string $property, $value )
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Required field indicator string setter.
     * @param string $str Required field indicator string.
     */
    public static function setRequiredIndicator( string $str )
    {
        static::$required_field_indicator = $str;
    }

    /**
     * Sets the internal template path value.
     * @param string $path Path to template directory.
     */
    public static function setTemplateBasePath( string $path )
    {
        static::$template_base_path = $path;
    }

    /**
     * Template filename setter.
     * @param string $filename template filename
     */
    public static function setTemplateFilename( string $filename )
    {
        static::$template_filename = $filename;
    }

    /**
     * Clears any error properties of the object.
     */
    public function clearValidationErrors()
    {
        $this->has_errors = false;
        $this->error = '';
    }

    /**
     * Utility routine for standardized invalid content error handling.
     * @param string $err Error message
     * @throws ContentValidationException
     */
    protected function throwValidationError( string $err )
    {
        $this->has_errors = true;
        $this->error = $err;
        throw new ContentValidationException($this->error);
    }

    /**
     * Validates the object's current value stored in its $value property.
     * @throws ContentValidationException
     */
    public function validate()
    {
        if ($this->required) {
            if ($this->isEmpty()) {
                $this->throwValidationError($this->formatErrorLabel()." is required.");
            }
        }
    }
}