<?php
namespace Littled\PageContent\Images;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\Request\StringTextField;
use Littled\Request\StringTextarea;


/**
 * Class ImageBase
 * @package Littled\PageContent\Images
 */
abstract class ImageBase extends SerializedContent
{
	/** @var array HTTP request variable names. */
	const vars = [
		'id' => 'imid',
		'path' => 'pat',
		'original_path' => 'pat_orig',
		'width' => 'wid',
		'height' => 'hei',
		'alt' => 'imat',
		'url' => 'imur',
		'target' => 'imtg',
		'caption' => 'imca'
	];

	const TABLE_NAME = 'images';

	/** @var StringInput Image filename. */
	public StringInput $path;
	/** @var IntegerInput Image width. */
	public IntegerInput $width;
	/** @var IntegerInput Image height. */
	public IntegerInput $height;
	/** @var StringTextField Alternate text description of image. */
	public StringTextField $alt;
	/** @var StringTextField URL if image is linked. */
	public StringTextField $url;
	/** @var StringTextField Target if image is linked. */
	public StringTextField $target;
	/** @var StringTextarea Image caption. */
	public StringTextarea $caption;
	/** @var string Path to upload destination for image. */
	public string $image_dir;
	/** @var string String to use before all property keys. */
	public string $key_prefix;
	/** @var array List of allowable extensions. */
	public array $allowed_extensions = ['jpg','jpeg','gif','tif','tiff','pdf','bmp','png','webp'];

	/**
	 * Returns the name of the table in the database that stores this object's data.
	 * @return string Name of the table storing this object's data in the database.
	 */
	public static function TABLE_NAME (): string
    { return(self::TABLE_NAME); }

	/**
	 * class constructor
     * @param string $image_dir Upload destination.
     * @param string $key_prefix Prepend this string to all variabls involved with uploading the image data in forms.
     * @param int|null $id Initial value to assign to the objct's id property.
     * @param int|null $index Index of this image wihen handling a series of images, either as uploads or as
     * properties retrieved from a database.
     * @param string|null $path Path (or filename) of image file.
     * @param int|null $width Image width in pixels.
     * @param int|null $height Image height in pixels.
     * @param string|null $alt Image alt tag value.
     * @param string|null $url Arbitrary URI that the image links to.
     * @param string|null $target Value for target attribute when linking images.
     * @param string|null $caption Image caption/descrption.
     */
	function __construct (
        string $image_dir,
        string $key_prefix = '',
        ?int $id=null,
        ?int $index=null,
        ?string $path=null,
        ?int $width=null,
        ?int $height=null,
        ?string $alt=null,
        ?string $url=null,
        ?string $target=null,
        ?string $caption=null)
	{
		parent::__construct($id);
		$this->id->label = 'Image id';
		$this->id->key = $key_prefix.$this::vars['id'];
		$this->id->index = $index;
		$this->path = new StringInput('Image path', $key_prefix.$this::vars['path'], true, $path, 255, $index);
		$this->width = new IntegerInput('Image width', $key_prefix.$this::vars['width'], false, $width, $index);
		$this->height = new IntegerInput('Image height', $key_prefix.$this::vars['height'], false, $height, $index);
		$this->alt = new StringTextField('Alt tag', $key_prefix.$this::vars['alt'], false, $alt, 255, $index);
		$this->url = new StringTextField('URL', $key_prefix.$this::vars['url'], false, $url, 255, $index);
		$this->target = new StringTextField('Target', $key_prefix.$this::vars['target'], false, $target, 16, $index);
		$this->caption = new StringTextarea('Caption', $key_prefix.$this::vars['caption'], false, $caption, 400, $index);
		$this->key_prefix = $key_prefix;
		$this->image_dir = $image_dir;
	}

	/**
	 * Resets the values of the object properties to their original values.
	 */
	public function clearProperties(): void
    {
		$this->id->value = null;
		$this->path->value = '';
		$this->width->value = null;
		$this->height->value = null;
		$this->alt->value = '';
		$this->url->value = '';
		$this->target->value = '';
		$this->caption->value = '';
	}

    /**
     * Fill object property values from http request variables.
     * @param array|null $src Variables to use to fill the object properties instead of POST or GET collections.
     */
    public function collectRequestData(?array $src=null ): void
    {
        $this->id->collectRequestData($src);
        if (isset($_FILES[$this->path->key])) {
            if (is_numeric($this->path->index)) {
                $this->path->value = $_FILES[$this->path->key]['name'][(int)$this->path->index];
                if (($this->id->value !== null) && (strlen($this->path->value) < 1)) {
                    if (!is_array($src)) {
                        $src = &$_REQUEST;
                    }
                    $this->path->value = trim($src[$this->path->key. '_orig'][(int)$this->path->index]);
                }
            }
            else {
                $this->path->value = $_FILES[$this->path->key]['name'];
                if (($this->id->value !== null) && (strlen($this->path->value) < 1)) {
                    if (!is_array($src)) {
                        $src = &$_REQUEST;
                    }
                    $this->path->value = trim($src[$this->path->key. '_orig']);
                    $this->width->collectRequestData($src);
                    $this->height->collectRequestData($src);
                }
            }
        }
        $this->alt->collectRequestData($src);
        $this->url->collectRequestData($src);
        $this->target->collectRequestData($src);
        $this->caption->collectRequestData($src);
    }

	/**
	 * Returns file size in bytes as a descriptive string.
	 * @param int $file_size File size in bytes.
	 * @return string Formatted file size.
	 */
	protected function formatFileSize( int $file_size ): string
	{
        $decr = 1024;
        $step = 0;
        $prefix = array('Byte','KB','MB','GB','TB','PB');

        while(($file_size / $decr) > 0.9) {
            $file_size = $file_size / $decr;
            $step++;
        }
        return (round($file_size,2).' '.$prefix[$step]);
	}

	/**
	 * Returns SQL query used to save image properties to the database.
	 * @return string SQL query.
	 */
	protected function formatUpdateQuery( ): string
    {
		return('CALL imagesUpdate(' .
			$this->id->escapeSQL($this->mysqli). ',' .
			$this->path->escapeSQL($this->mysqli). ',' .
			$this->width->escapeSQL($this->mysqli). ',' .
			$this->height->escapeSQL($this->mysqli). ',' .
			$this->alt->escapeSQL($this->mysqli). ',' .
			$this->url->escapeSQL($this->mysqli). ',' .
			$this->target->escapeSQL($this->mysqli). ',' .
			$this->caption->escapeSQL($this->mysqli). ',' .
            '1);');
	}

	/**
	 * Checks if the object currently holds any data that would require being saved to the database.
	 * @return bool TRUE if the object contains data. FALSE if the object doesn't contain data.
	 */
	public function hasData(): bool
    {
		return ($this->id->value > 0 || strlen($this->path->value) > 0);
	}

	/**
	 * Sets the values of the object properties. Add a name/key pair to the $values argument to make the assignment.
	 * @param array $values Name/key pairs of values to assign to the object properties.
	 */
	public function initializeProperties( array $values ): void
    {
		foreach($values as $key => $value) {
			if (property_exists($this, $key) && $this->$key instanceof RequestInput) {
				$this->$key->value = $value;
			}
		}
	}

	/**
	 * Overrides parent method to ensure that the method returns silently if the object's id value is not set.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
	public function read(): void
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
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
	public function save (): void
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
	 * Prepends string to all property key values.
	 * @param string $prefix String to prepend to all property keys.
	 */
	public function setPrefix( string $prefix ): void
    {
		foreach($this::vars as $property => $default_name) {
			if (property_exists($this, $property) && $this->$property instanceof RequestInput) {
				$this->$property->key = $prefix.$default_name;
			}
		}
		$this->key_prefix = $prefix;
	}

	/**
	 * Verifies that the file specified with the $path argument is of a recognized file type.
	 * @param string|null $path If $path is not specified, the object's $path property value is used instead.
	 * @return bool TRUE if the file type is valid. FALSE if it is invalid.
	 */
	public function validateFileType( ?string $path=null ): bool
    {
		if ($path===null) {
			$path=$this->path->value;
		}
		return (in_array(substr(strrchr($path, '.'), 1), $this->allowed_extensions));
	}

	/**
	 * Validates the current values of the object properties.
	 * @param string[] $exclude_properties Names of object properties that should not be validated.
	 * @throws ContentValidationException Invalid property values found.
	 */
	public function validateInput(array $exclude_properties = []): void
	{
		if ($this->id->value===null) {
			try {
				$this->path->validate();
			}
			catch(ContentValidationException $ex) {
				$this->addValidationError($ex->getMessage());
			}
		}
		if ($this->path->value) {
			if (isset($_REQUEST['MAX_FILE_SIZE']) && $_REQUEST['MAX_FILE_SIZE']>0) {
				if ($_SERVER['CONTENT_LENGTH'] > $_REQUEST['MAX_FILE_SIZE']) {
					$this->addValidationError('The ' . strtolower($this->path->label) . ' file is too large. Image file size is limited to ' . $this->formatFileSize($_REQUEST['MAX_FILE_SIZE']) . '.');
				}
			}
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
				$this->addValidationError('The ' . strtolower($this->path->label) . ' file size exceeds the maximum upload size allowed by the server.');
			}
			if (!$this->validateFileType()) {
				$this->addValidationError('The ' . strtolower($this->path->label) . ' is not a valid image file type.');
			}
		}
		$properties = array('width', 'height', 'alt', 'url', 'target', 'caption');
		foreach($properties as $property) {
			if (in_array($property, $exclude_properties)) {
				continue;
			}
			$this->$property->validate();
		}
		if (count($this->validationErrors()) > 0) {
			throw new ContentValidationException('Problems were found with the image properties.');
		}
	}
}