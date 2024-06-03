<?php
namespace Littled\PageContent\Images;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Request\StringInput;
use Littled\Validation\Validation;

/**
 * Class ImageUpload
 * @package Littled\PageContent\Images
 */
class ImageUpload extends ImageLink
{
	/** @var StringInput $new_name Form input to allow changing the name of an image. */
	public StringInput $new_name;
	/** @var StringInput $page Page form input. */
	public StringInput $page;
	/** @var StringInput $upload_type Form input specifying upload types. */
	public StringInput $upload_type;
	/** @var string $label Label text to accompany buttons and other editing controls for the images. */
	public string $label;
	/** @var bool $generic_params Flag to indicate that generic parameters should be used to retrieve image, type and parent id values. */
	public bool $generic_params;

	const UPLOAD_TYPE_PARAM = 'ut';
	const SINGLE_UPLOAD = 'single';
	const LISTINGS_UPLOAD = 'listings';
	public static function ID_PARAM() { return(self::vars['id']); }
	public static function PARENT_PARAM() { return(self::vars['parent_id']); }
	public static function TYPE_PARAM() { return(self::vars['content_type']); }


    /**
     * Class constructor.
     * @param int|null $content_type_id Record id of this image's site section.
     * @param int|null $parent_type_id
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
	function __construct(?int $content_type_id=null, ?int $parent_type_id=null )
	{
		parent::__construct('', '', $content_type_id, $parent_type_id);

		$this->label = '';
		$this->generic_params = false;
		if ($content_type_id > 0) {
			$this->retrieveSectionProperties();
		}
		$this->new_name = new StringInput('Replace name', 'rn', false, '', 100);
		$this->page = new StringInput('Page', 'pg', false, '', 50);
		$this->upload_type = new StringInput('Upload Type', $this::UPLOAD_TYPE_PARAM, false, '', 50);

		$this->parent_id->required = false;
	}

	/**
	 * Collects and parses form data, and assigns internal variables using the form data.
	 * @param array|null $src
	 */
	public function collectRequestData(?array $src=null ): void
    {
		parent::collectRequestData($src);
		$this->new_name->collectRequestData($src);
		$this->page->collectRequestData($src);
		$this->upload_type->collectRequestData($src);
	}

	/**
	 * Collects variables needed to load inline edit forms. Sets internal variables based on type id, etc.
	 * @param array|null $src
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
	public function collectInlineInput( array $src=null ): void
	{
		/* first attempt to collect "id" value using the derived class's
		 * input parameter, if that isn't available, use a generic parameter.
		 */
		$this->id->collectRequestData($src);
		if ($this->id->value === null && $this->id->key != 'id') {
			$this->id->value = Validation::collectIntegerRequestVar('id', null, $src);
		}

		/* collect parent record's id value, giving derived class's
		 * parameter precedence over the generic parameter.
		 */
		$this->parent_id->collectRequestData($src);
		if ($this->parent_id->value === null && $this->parent_id->key != LittledGlobals::PARENT_ID_KEY) {
			$this->parent_id->value = Validation::collectIntegerRequestVar(LittledGlobals::PARENT_ID_KEY, null, $src);
		}

		/* collect content type id value, giving derived class's
		 * parameter precedence over the generic parameter.
		 */
		$this->content_properties->id->collectRequestData($src);
		if ($this->content_properties->id->value === null && $this->content_properties->id->key != LittledGlobals::CONTENT_TYPE_KEY) {
			$this->content_properties->id->value = Validation::collectIntegerRequestVar(LittledGlobals::CONTENT_TYPE_KEY, null, $src);
		}
		$this->new_name->collectRequestData($src);
		$this->page->collectRequestData($src);
		$this->randomize->collectRequestData($src);

		if ($this->content_properties->id->value>0) {
			$this->retrieveSectionProperties();
		}
	}

	/**
	 * Resets internal variables to their default value, while saving some values such as parent id and section properties.
	 */
	public function clearValues(): void
    {
		parent::clearValues();
		$this->new_name->value = '';
		$this->page->value = '';
		$this->upload_type->value = '';
		$this->setParameterNames(false);
	}

	/**
	 * Retrieve image properties from database.
	 * @param bool $read_keywords Flag to suppress retrieving keywords linked to the image_link record. Defaults to TRUE.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
	public function read( bool $read_keywords=true ): void
    {
		parent::read($read_keywords);
		$this->retrieveLabel();
	}

	/**
	 * Retrieves label for the edit form from database based on the content type of the image.
	 * - The label is stored in the object's "label" property.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
	public function retrieveLabel(): void
    {
		if ($this->content_properties->id->value>0) {

			$query = "SELECT `label` from `section_operations` WHERE section_id = {$this->content_properties->id->value}";
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$this->label = $data[0]->label;
			}
		}
		elseif ($this->id->value>0) {

			$query = <<<SQL
SELECT so.`label` 
FROM `section_operations` so
INNER JOIN `image_link` il ON so.section_id = il.type_id 
WHERE il.id = {$this->id->value}
SQL;
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$this->label = $data[0]->label;
			}
		}
	}

	/**
	 * Overrides parent class's routine to set the image id, image type id, and image parent id parameter names to different values.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
	public function retrieveSectionProperties(): void
    {
		parent::retrieveSectionProperties();
		$this->retrieveLabel();
		$this->setParameterNames();
	}

	/**
	 * Overrides the image_upload_class's default parameter names for
	 * - image id
	 * - image parent id
	 * - image type id
	 *
	 * @param mixed|null $generic_params (Optional) Flag to override parameter names of the object's id, parent id, and type id parameters will be set to generic names, ie "id", "pid", and "tid".
	 *		Defaults to NULL which will not change the current setting of the "generic_params" property.
	 *		Pass in true or false to change the "generic_params" setting of the object.
	 */
	public function setParameterNames(mixed $generic_params=null ): void
    {
		if ($generic_params!==null) {
			$this->generic_params = $generic_params;
		}
		if ($this->generic_params) {
			$this->id->key = LittledGlobals::CONTENT_TYPE_KEY;
			$this->content_properties->id->key = LittledGlobals::CONTENT_TYPE_KEY;
			$this->parent_id->key = LittledGlobals::PARENT_ID_KEY;
		}
		// else {

            // property $param_prefix is not defined for ContentProperties
            // $this->setPrefix($this->content_properties->param_prefix->value);

            //$this->id->param = $this->ID_PARAM();
			//$this->parent_id->param = $this->PARENT_PARAM();
			//$this->site_section->id->param = $this->TYPE_PARAM();
		// }
	}

	/**
	 * Upload and process each of the images attached to this object,
	 * including operations such as extracting keywords, resizing, and renaming.
	 * @param bool $randomize_filename Flag if set to true the new image file will be given a randomized filename
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws RecordNotFoundException
	 * @throws ResourceNotFoundException
	 */
	public function upload(bool $randomize_filename=false ): void
    {
		parent::upload($randomize_filename);

		/* if new name is present, rename the video file */
		if ($this->new_name->value) {
			$properties = array('full', 'med', 'mini');
			foreach($properties as $property) {
				if($this->$property->path->value) {
					$this->$property->changeFilename($this->new_name->value);
				}
			}
		}
	}

	/**
	 * Validates basic data sent to run AJAX script as opposed to validation needed after edit form is submitted.
	 * @throws ContentValidationException
	 */
	public function validateInlineInput(): void
    {
		if (
			($this->id->value===null) &&
			($this->content_properties->id->value===null && $this->parent_id->value===null))
		{
			throw new ContentValidationException('Either an image or a parent and image type is required.');
		}
	}

	/**
	 * Validates form data. Throws exception with detailed error message if any invalid form data is detected.
	 * @param array $exclude_properties
	 * @throws ContentValidationException
	 */
	public function validateInput( $exclude_properties = [] ): void
	{
		try {
			parent::validateInput($exclude_properties);
		}
		catch (ContentValidationException) {
            /* continue evaluating form data */
		}
		try {
			$this->new_name->validate();
		}
		catch (ContentValidationException $ex) {
			$this->addValidationError($ex->getMessage());
		}
		try {
			$this->page->validate();
		}
		catch (ContentValidationException $ex) {
			$this->addValidationError($ex->getMessage());
		}
		if (count($this->validationErrors())) {
			throw new ContentValidationException('Errors found in image.');
		}
	}
}