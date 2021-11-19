<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
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
	/** @var string Name of CSS class to be used when displaying the form input. */
	public $cssClass;
	/** @var string Content type within HTML form, e.g. type="text", type="tel", type="email", etc. */
	public $contentType;
	/**
	 * @var boolean If FALSE this property will be passed over when retrieving or saving its value from or to the
	 * database. Default value is TRUE.
	 */
	public $isDatabaseField;
	/**
	 * @var string Name to use to override the default name of the column in the database holding the value linked to
	 * this property. The default value is the name of the property in the parent class.
	 */
	public $columnName;
	/** @var bool Flag indicating that the object value should not be assigned from request variable values. */
	public $bypassCollectPostData;

	/**
	 * @var boolean Flag to control the insertion of a "placeholder" attribute
	 * when rendering the input. If TRUE, a placeholder attribute will be added
	 * (to text fields), using the object's "label" property value as its value.
	 */
	public $displayPlaceholder;
	/** @var boolean Flag indicating that an error was detected with the value supplied for this form data. */
	public $hasErrors;
	/** @var string If an error was detected with the value of a form data, a description of the error will be stored in this property. */
	public $error;
	/** @var int When supplying an array of values for a single key, the  value can be used to sort them. */
	public $index;
	/** @var string Label to display where descriptions of the input are needed. */
	public $label;
	/** @var string  Name of script argument. Name of key in query string or form data. */
	public $key;
	/** @var string CSS class identifier. */
	public $class;
	/** @var bool Set to TRUE if a value for this form data is required. */
	public $required;
	/** @var int Size of data being held. Used to specify the size of varchar arguments in database calls. Also used to limit the length of input in textbox inputs. */
	public $sizeLimit;
	/** @var mixed Value of the script argument. Value collected from form data. */
	public $value;
	/** @var string If supplied, this value will be used to specify the width of a form input through its "style" attribute. E.g. "240px" */
	public $width;
	/** @var string Path to form input templates. */
	protected static $template_base_path = '';
	/** @var string Input template filename. */
	protected static $template_filename = 'hidden-input.php';
	/** @var string Form input element filename. */
	protected static $input_template_filename = '';
	/** @var string Error indicator CSS class. */
	protected static $error_class = 'form-error';
	/** @var string Required field indicator string. */
	protected static $required_field_indicator = ' (*)';

	/**
	 * class constructor
	 * @param string $label Input label
	 * @param string $key value of the name attribute of the input
	 * @param boolean[optional] $required Flag indicating if this form data is required. Defaults to FALSE.
	 * @param mixed[optional] $value Initial value of the input. Defaults to NULL.
	 * @param int $size_limit[optional] Maximum size in bytes of the value when it is stored in the database (for strings). Defaults to 0.
	 * @param ?int $index[optional] Index of this input if it is part of an array of inputs with the same name attribute. Defaults to NULL.
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
		$this->setInputValue($value);
		$this->sizeLimit          = $size_limit;
		$this->required           = $required;
		$this->index              = $index;
		$this->hasErrors          = false;
		$this->class              = "";
		$this->width              = "";
		$this->error              = "";
		$this->isDatabaseField    = true;
		$this->columnName         = "";
		$this->displayPlaceholder = false;
		$this->contentType        = "text";
		$this->bypassCollectPostData = false;
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
	 * @param object $data
	 */
	public function collectJsonRequestData(object $data)
	{
		if ($this->isBypassingRequestData()) {
			return;
		}
		if (property_exists($data, $this->key)) {
			$this->value = filter_var($data->{$this->key}, FILTER_SANITIZE_STRING);
		}
	}

	/**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @throws NotImplementedException
	 */
	public function collectPostData()
	{
		throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
	 * @return string Escaped value.
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=true): string
	{
		if ($this->value===null) {
			return ("null");
		}
		return (($include_quotes)?("'"):("")).$mysqli->real_escape_string($this->value).(($include_quotes)?("'"):(""));
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
	 * Returns a consistently formatted label string for use in error messages.
	 * Default format is first letter capitalized.
	 * @return string Error label string.
	 */
	public function formatErrorLabel(): string
	{
		return (ucfirst(strtolower("".$this->label)));
	}

	/**
	 * Default routine for rendering the label of the input.
	 * @param string $label Text to display as the label for the form input. A null value will cause the internal label value to be used. An empty string will cause the label to not be rendered at all.
	 * @return string Label markup to insert into form content.
	 * @throws ResourceNotFoundException Template not found.
	 */
	public function formatLabelMarkup( $label ): string
	{
		if ($label===null) { $label=$this->label;}
		if (strlen($label) > 0 && $this->displayPlaceholder===false) {
			return (PageContent::loadTemplateContent(static::$template_base_path."form-input-label.php", array(
				'label' => $label,
				'input' => &$this
			)));
		}
		return ('');
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
		return(static::$template_base_path.static::$input_template_filename);
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
        return (static::$template_base_path.static::$template_filename);
    }

	/**
	 * Sets flag that will cause this variable to be ignored when processing request data sent to the page.
	 */
	public function ignoreRequestData()
	{
		$this->bypassCollectPostData = true;
	}

	/**
	 * Returns flag indicating if this object is set to not collect request data from the client.
	 * @return bool
	 */
	public function isBypassingRequestData(): bool
	{
		return ($this->bypassCollectPostData===true);
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
     * @param ?string $label
     * @param ?string $css_class
	 * @throws NotImplementedException
	 */
	public function render( ?string $label=null, ?string $css_class=null )
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
     * @param string[optional] $label
     * @param string[optional] $css_class
     * @param array[optional] $options
     */
	public function renderWithErrors($label=null, $css_class=null, $options=[])
    {
        try {
            $this->render($label, $css_class);
        }
        catch(Exception $ex) {
            PageContent::printError($ex->getMessage());
        }
    }

	/**
	 * Returns string safe from XSS attacks that can be embedded in HTML.
	 * @param ?int $options Combination of tokens to pass along, e.g. FILTER_SANITIZE_FULL_SPECIAL_CHARS
	 * Same values as 3rd argument to PHP's filter_var() routine.
	 * @return string XSS-safe string.
	 */
	public function safeValue( ?int $options=null ): string
	{
		return (filter_var($this->value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options));
	}

	/**
	 * Prints out markup to save input value in a hidden form input element.
     * @param ?string $key Key to use to override default key value for the variable.
	 */
	public function saveInForm( ?string $key=null )
	{
	    if ($key===null) {
	        $key = $this->key;
        }
		PageContent::renderWithErrors(self::getTemplatePath(), array(
			'key' => $key,
			'value' => $this->value,
			'index' => ((is_numeric($this->index))?("[$this->index]"):(""))
		));
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
	 * Form input element template filename setter.
	 * @param string $filename Template filename.
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
	 * Utility routine for standardized invalid content error handling.
	 * @param string $err Error message
	 * @throws ContentValidationException
	 */
	protected function throwValidationError( string $err )
	{
		$this->hasErrors = true;
		$this->error .= $err;
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