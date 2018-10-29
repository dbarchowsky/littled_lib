<?php
namespace Littled\PageContent\Images;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\Request\StringTextField;
use Littled\Request\StringTextarea;


/**
 * Class Image
 * @package Littled\PageContent\Images
 */
class Image extends SerializedContent
{
	protected $vars = array(
		'id' => 'imid',
		'path' => 'pat',
		'original_path' => 'pat_orig',
		'width' => 'wid',
		'height' => 'hei',
		'alt' => 'imat',
		'url' => 'imur',
		'target' => 'imtg',
		'caption' => 'imca'
	);

	const TABLE_NAME = "images";

	/** @var IntegerInput Image record id. */
	public $id;
	/** @var StringInput Image filename. */
	public $path;
	/** @var IntegerInput Image width. */
	public $width;
	/** @var IntegerInput Image height. */
	public $height;
	/** @var StringTextField Alternate text description of image. */
	public $alt;
	/** @var StringTextField URL if image is linked. */
	public $url;
	/** @var StringTextField Target if image is linked. */
	public $target;
	/** @var StringTextarea Image caption. */
	public $caption;
	/** @var string Path to upload destination for image. */
	public $image_dir;
	/** @var string String to use before all property keys. */
	public $key_prefix;
	/** @var array List of allowable extensions. */
	public $allowed_extensions = array("jpg","jpeg","gif","tif","tiff","pdf","bmp","png");

	/**
	 * Returns the name of the table in the database that stores this object's data.
	 * @return string Name of the table storing this object's data in the database.
	 */
	public static function TABLE_NAME () { return(self::TABLE_NAME); }

	/**
	 * class constructor
	 * @param string $image_dir Upload destination.
	 * @param string $key_prefix Prepend this string to all variables involved with uploading the image data in forms.
	 * @param int[optional] $id Initial value to assign to the object's id property.
	 * @param int[optional] $index Index of this image when handling a series of images, either as uploads or as properties retrieved from database.
	 * @param string[optional] $path Path (or filename) of image file.
	 * @param int[optional] $width Image width.
	 * @param int[optional] $height Image height.
	 * @param string[optional] $alt Value for image alt tag.
	 * @param string[optional] $url Arbitrary URI that the image links to.
	 * @param string[optional] $target Value for target attribute when linking images.
	 * @param string[optional] $caption Image caption/description.
	 */
	function __construct ($image_dir, $key_prefix="", $id=null, $index=null, $path=null, $width=null, $height=null, $alt=null, $url=null, $target=null, $caption=null)
	{
		parent::__construct($id);
		$this->id->label = "Image id";
		$this->id->key = $key_prefix.$this->vars['id'];
		$this->id->index = $index;
		$this->path = new StringInput("Image path", $key_prefix.$this->vars['path'], true, $path, 255, $index);
		$this->width = new IntegerInput("Image width", $key_prefix.$this->vars['width'], false, $width, $index);
		$this->height = new IntegerInput("Image height", $key_prefix.$this->vars['height'], false, $height, $index);
		$this->alt = new StringTextField("Alt tag", $key_prefix.$this->vars['alt'], false, $alt, 255, $index);
		$this->url = new StringTextField("URL", $key_prefix.$this->vars['url'], false, $url, 255, $index);
		$this->target = new StringTextField("Target", $key_prefix.$this->vars['target'], false, $target, 16, $index);
		$this->caption = new StringTextarea("Caption", $key_prefix.$this->vars['caption'], false, $caption, 400, $index);
		$this->key_prefix = $key_prefix;
		$this->image_dir = $image_dir;
	}

	/**
	 * Verifies that the file specified with the $path argument is of a recognized file type.
	 * @param string|null[optional] $path If $path is not specified, the object's $path property value is used instead.
	 * @return bool TRUE if the file type is valid. FALSE if it is invalid.
	 */
	public function validateFileType( $path=null )
	{
		if ($path===null) {
			$path=$this->path->value;
		}
		return (in_array(substr(strrchr($path, "."), 1), $this->allowed_extensions));
	}

	/**
	 * Resets the values of the object properties to their original values.
	 */
	public function clearProperties()
	{
		$this->id->value = null;
		$this->path->value = "";
		$this->width->value = null;
		$this->height->value = null;
		$this->alt->value = "";
		$this->url->value = "";
		$this->target->value = "";
		$this->caption->value = "";
	}

    /**
     * Fill object property values from http request variables.
     * @param array|null $src Variables to use to fill the object properties instead of POST or GET collections.
     */
    public function collectFromInput( $src=null )
    {
        $this->id->collectFromInput($src);
        if (isset($_FILES[$this->path->key])) {
            if (is_numeric($this->path->index)) {
                $this->path->value = $_FILES[$this->path->key]["name"][(int)$this->path->index];
                if (($this->id->value !== null) && (strlen($this->path->value) < 1)) {
                    if (!is_array($src)) {
                        $src = &$_REQUEST;
                    }
                    $this->path->value = trim($src[$this->path->key."_orig"][(int)$this->path->index]);
                }
            }
            else {
                $this->path->value = $_FILES[$this->path->key]["name"];
                if (($this->id->value !== null) && (strlen($this->path->value) < 1)) {
                    if (!is_array($src)) {
                        $src = &$_REQUEST;
                    }
                    $this->path->value = trim($src[$this->path->key."_orig"]);
                    $this->width->collectFromInput($src);
                    $this->height->collectFromInput($src);
                }
            }
        }
        $this->alt->collectFromInput($src);
        $this->url->collectFromInput($src);
        $this->target->collectFromInput($src);
        $this->caption->collectFromInput($src);
    }

	/**
	 * Returns SQL query used to save image properties to the database.
	 * @return string SQL query.
	 */
	protected function formatUpdateQuery( )
	{
		return("CALL imagesUpdate(".
			$this->id->escapeSQL($this->mysqli).",".
			$this->path->escapeSQL($this->mysqli).",".
			$this->width->escapeSQL($this->mysqli).",".
			$this->height->escapeSQL($this->mysqli).",".
			$this->alt->escapeSQL($this->mysqli).",".
			$this->url->escapeSQL($this->mysqli).",".
			$this->target->escapeSQL($this->mysqli).",".
			$this->caption->escapeSQL($this->mysqli).",".
			"1);");
	}

	/**
	 * Checks if the object currently holds any data that would require being saved to the database.
	 * @return bool TRUE if the object contains data. FALSE if the object doesn't contain data.
	 */
	public function hasData()
	{
		return ($this->id->value > 0 || strlen($this->path->value) > 0);
	}

	/**
	 * Sets the values of the object properties. Add a name/key pair to the $values argument to make the assignment.
	 * @param array $values Name/key pairs of values to assign to the object properties.
	 */
	public function initializeProperties( $values )
	{
		foreach($values as $key => $value) {
			if (property_exists($this, $key) && $this->$key instanceof RequestInput) {
				$this->$key->value = $value;
			}
		}
	}

	/**
	 * Prepends string to all property key values.
	 * @param string $prefix String to prepend to all property keys.
	 */
	public function setPrefix( $prefix )
	{
		foreach($this->vars as $property => $default_name) {
			if (property_exists($this, $property) && $this->$property instanceof RequestInput) {
				$this->$property->key = $prefix.$default_name;
			}
		}
		$this->key_prefix = $prefix;
	}

	/**
	 * Overrides parent method to ensure that the method returns silently if the object's id value is not set.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read()
	{
		if ($this->id->value===null || $this->id->value < 0) {
			return;
		}
		parent::read();
	}

	/**
	 * Upload, resize, and place an image file on the server submitted through a form.
	 * Save the image properties in the database.
	 * If this image has already been saved in the database, update its properties, and if a new image file is uploaded, delete the existing image file of the server.
     * @throws ConfigurationUndefinedException
     * @throws \Littled\Exception\ConnectionException
     * @throws \Littled\Exception\InvalidQueryException
     */
	public function save ()
	{
		if (!$this->hasData()) {
			return;
		}

		/* save image properties in the database */
		$this->connectToDatabase();
		$query = $this->formatUpdateQuery();
        $this->query($query);

		/* retrieve id of any new image records */
		if($this->id->value===null) {
			$this->id->value = $this->retrieveInsertID();
		}
	}

	/**
	 * Validates the current values of the object properties.
	 * @param string[][optional] $exclude_properties Names of object properties that should not be validated.
	 * @throws ContentValidationException Invalid property values found.
	 */
	public function validateInput($exclude_properties=array())
	{
		if ($this->id->value===null) {
			try {
				$this->path->validate();
			}
			catch(ContentValidationException $ex) {
				array_push($this->validationErrors, $ex->getMessage());
			}
		}
		if ($this->path->value) {
			if (isset($_REQUEST["MAX_FILE_SIZE"]) && $_REQUEST["MAX_FILE_SIZE"]>0) {
				if ($_SERVER["CONTENT_LENGTH"] > $_REQUEST["MAX_FILE_SIZE"]) {
					array_push($this->validationErrors,"The ".strtolower($this->path->label)." file is too large. Image file size is limited to ".$this->format_filesize($_REQUEST["MAX_FILE_SIZE"]).".");
				}
			}
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
				array_push($this->validationErrors,"The ".strtolower($this->path->label)." file size exceeds the maximum upload size allowed by the server.");
			}
			if (!$this->validateFileType()) {
				array_push($this->validationErrors,"The ".strtolower($this->path->label)." is not a valid image file type.");
			}
		}
		$properties = array('width', 'height', 'alt', 'url', 'target', 'caption');
		foreach($properties as $property) {
			if (in_array($property, $exclude_properties)) {
				continue;
			}
			try {
				$this->$property->validate();
			}
			catch(ContentValidationException $ex) {
				array_push($this->validationErrors, $ex->getMessage());
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Problems were found with the image properties.");
		}
	}
}