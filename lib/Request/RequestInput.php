<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;

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
	public $bypassCollectFromInput;

	/**
	 * @var boolean Flag to control the insertion of a "placeholder" attribute
	 * when rendering the input. If TRUE, a placeholder attribute will be added
	 * (to text fields), using the object's "label" property value as its value.
	 */
	public $displayPlaceholder;
	/** @var boolean Flag indicating that an error was detected with the value supplied for this form data. */
	public $hasErrors;
	/** @var string If an error was detected with the value of a form data, an description of the error will be stored in this property. */
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
	protected static $template_filename;
	/** @var string Form input element filename. */
	protected static $input_template_filename;

	/**
	 * class constructor
	 * @param string $label Input label
	 * @param string $param value of the name attribute of the input
	 * @param boolean[optional] $required Flag indicating if this form data is required. Defaults to FALSE.
	 * @param mixed[optional] $value Initial value of the input. Defaults to NULL.
	 * @param int $size_limit[optional] Maximum size in bytes of the value when it is stored in the database (for strings). Defaults to 0.
	 * @param int $index[optional] Index of this input if it is part of an array of inputs with the same name attribute. Defaults to NULL.
	 */
	function __construct ( $label, $param, $required=false, $value=null, $size_limit=0, $index=null )
	{
		$this->label              = $label;
		$this->key                = $param;
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
		$this->bypassCollectFromInput = false;
	}

	/**
	 * Resets the object's value property to a default value.
	 */
	public function clearValue()
	{
		$this->value = null;
	}

	/**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @throws NotImplementedException
	 */
	public function collectFromInput()
	{
		throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
	 * @return string Escaped value.
	 */
	public function escapeSQL($mysqli, $include_quotes=true)
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
	 * @return void
	 */
	public function fillFromSession($cookie_name)
	{
		if (isset($_SESSION[$this->key])) {
			$this->value = $_SESSION[$this->key];
		}
		elseif(isset($_COOKIE[$cookie_name][$this->key])) {
			$this->value = $_COOKIE[$cookie_name][$this->key];
		}
	}

	/**
	 * Default routine for rendering the label of the input.
	 * @param string $label Text to display as the label for the form input. A null value will cause the internal label value to be used. An empty string will cause the label to not be rendered at all.
	 * @return string Label markup to insert into form content.
	 * @throws ResourceNotFoundException Template not found.
	 */
	public function formatLabelMarkup( $label )
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
	 * Template path getter.
	 * @return string Current internal template path value.
	 */
	public static function getTemplateBasePath()
	{
		return (static::$template_base_path);
	}

    /**
     * Returns the filename of the template used to render just the input element.
     * @return string Form input template filename.
     */
	public static function getInputTemplateFilename()
    {
        return (static::$input_template_filename);
    }

    /**
     * Returns full path to input element template file.
     * @return string Path to input element template.
     */
    public static function getInputTemplatePath()
    {
        return(static::$template_base_path.static::$input_template_filename);
    }

    /**
     * Template filename getter.
     * @return string Current internal template filename.
     */
	public static function getTemplateFilename()
    {
        return (static::$template_filename);
    }

    /**
     * Get full path to form input element template file.
     * @return string Full path to form input element template file.
     */
    public static function getTemplatePath()
    {
        return (static::$template_base_path.static::$template_filename);
    }

    /**
	 * Tests if the value of the object is not currently set.
	 * @return bool True/false depending on whether the value is set or not.
	 */
	public function isEmpty()
	{
		return ($this->value===null || $this->value==='');
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
     * @param string[optional] $label
     * @param string[optional] $css_class
     * @param array[optional] $options
	 * @throws NotImplementedException
	 */
	public function render( $label=null, $css_class=null, $options=[] )
	{
	    if (!$label) {
	        $label = $this->label;
        }
		throw new NotImplementedException("\"".__METHOD__."\" not implemented for {$label}. {$css_class} ".join('', $options));
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
     * @param string[optional] $label Label to display with input element.
	 * @throws NotImplementedException
	 */
	public function renderInput()
	{
		throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
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
            $this->render($label, $css_class, $options);
        }
        catch(\Exception $ex) {
            PageContent::printError($ex->getMessage());
        }
    }

	/**
	 * Returns string safe from XSS attacks that can be embedded in HTML.
	 * @param int $options Combination of tokens to pass along, e.g. FILTER_SANITIZE_FULL_SPECIAL_CHARS
	 * Same values as 3rd argument to PHP's filter_var() routine.
	 * @return string XSS-safe string.
	 */
	public function safeValue( $options=null )
	{
		return (filter_var($this->value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options));
	}

	/**
	 * Prints out markup to save input value in a hidden form input element.
     * @param string[optional] @key Key to use to override default key value for the variable.
	 * @throws ResourceNotFoundException Template not found.
	 */
	public function saveInForm( $key=null )
	{
	    if ($key===null) {
	        $key = $this->key;
        }
		PageContent::render(self::getTemplateBasePath()."hidden-input.php", array(
			'key' => $key,
			'value' => $this->value,
			'index' => ((is_numeric($this->index))?("[{$this->index}]"):(""))
		));
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
	public function setProperty( $property, $value )
	{
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
	}

	/**
	 * Sets the internal template path value.
	 * @param string $path Path to template directory.
	 */
	public static function setTemplateBasePath( $path )
	{
		static::$template_base_path = $path;
	}

    /**
     * Form input element template filename setter.
     * @param string $filename Template filename.
     */
	public static function setInputTemplateFilename( $filename )
    {
        static::$input_template_filename = $filename;
    }

    /**
     * Template filename setter.
     * @param string $filename template filename
     */
	public static function setTemplateFilename( $filename )
    {
        static::$template_filename = $filename;
    }

	/**
	 * Utility routine for standardized invalid content error handling.
	 * @param string $err Error message
	 * @throws ContentValidationException
	 */
	protected function throwValidationError( $err )
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
				$this->throwValidationError(ucfirst($this->label)." is required.");
			}
		}
	}
}