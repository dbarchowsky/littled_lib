<?php

namespace Littled\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerTextField;
use Littled\Request\IntegerSelect;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Exception;

/**
 * Properties of different content types, e.g. content type id, table name, routes, and templates.
 */
class ContentProperties extends SerializedContent
{
	/** @var string Variable name that holds the site section id value. */
	const ID_KEY = 'ssid';
	/** @var int Record id value representing site section data in the database. */
	const SECTION_ID = 27;
	/** @var string Name of the table holding site section data. */
	const DEFAULT_TABLE_NAME = 'site_section';
	/** @var int */
	protected static int $content_type_id = self::SECTION_ID;
	/** @var string */
	protected static string $table_name = 'site_section';

	/** @var StringTextField Name of the content. */
	public StringTextField $name;
    /**
     * @var StringTextField Name of the content.
     * @todo Audit the use of this property.
     */
    public StringTextField $slug;
	/**
	 * @var StringTextField Root directory for section content.
	 * @todo Audit the use of this property now that routes are the principle method for responding to client requests.
	 */
	public StringTextField $root_dir;
	/** @var StringTextField Target path for image uploads. */
	public StringTextField $image_path;
	/**
	 * @var StringTextField Subdirectory for section content.
	 * @todo Audit the use of this property along with $root_dir.
	 */
	public StringTextField $sub_dir;
	/** @var StringTextField Label used when displaying images as a group on the front-end. */
	public StringTextField $image_label;
    /**
     * @todo consider replacing hard-coded image dimension fields with a new table linked to this one
     * with separate records for each image spec.
     */
	/**
	 * @var IntegerTextField Target width of full-resolution images.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $width;
	/**
	 * @var IntegerTextField Target height of full-resolution images.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $height;
	/**
	 * @var IntegerTextField Target width of medium-sized thumbnail images.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $med_width;
	/**
	 * @var IntegerTextField Target height of medium-sized thumbnail images.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $med_height;
	/**
	 * @var BooleanCheckbox Flag indicating that miniature thumbnail versions of images are to be generated when uploading and editing images.
	 * @todo Replace with generic $image_list property
	 */
	public BooleanCheckbox $save_mini;
	/**
	 * @var IntegerTextField Target width of the smallest image set.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $mini_width;
	/**
	 * @var IntegerTextField Target height of the smallest image set.
	 * @todo Replace with generic $image_list property
	 */
	public IntegerTextField $mini_height;
	/**
	 * @var StringSelect Image format, e.g. jpeg, png, etc.
	 * @todo Replace with generic $image_list property
	 */
	public StringSelect $format;
	/**
	 * @var StringTextField Parameter prefix to use for this content type.
	 * @todo Replace with generic $image_list property
	 */
	public StringTextField $param_prefix;
	/**
	 * @var StringTextField Content able name.
	 * @todo Audit this field to determine if it should be deprecated. Consider using SerializedContent::$table_name in its place.
	 */
	public StringTextField $table;
	/** @var IntegerSelect Numeric identifier of the content type that is a parent to the principal content type */
	public IntegerSelect $parent_id;
	/** @var BooleanCheckbox Flag indicating that this section's content gets cached. */
	public BooleanCheckbox $is_cached;
	/** @var BooleanCheckbox Flag indicating to use gallery thumbnails. */
	public BooleanCheckbox $gallery_thumbnail;
	/** @var string Name of the argument used to pass the record id to and from pages and scripts. Stored in the section_operations table. */
	public string $id_key = '';
	/** @var string Parent content type name. */
	public string $parent = '';
	/** @var string Alternate name for the content type explicitly intended to be displayed with form controls. Stored in the section_operations table. */
	public string $label='';
	/** @var ContentTemplate[] List of templates used to render pages displaying record data */
	public array $templates=[];
	/** @var ContentRoute[] List of routes to pages displaying record data */
	public array $routes=[];

	/**
	 * SiteSection constructor.
	 * @param ?int $id Initial value to assign to the object's id property.
	 */
	public function __construct(?int $id=null)
	{
		parent::__construct($id);
		$this->id->key = ContentProperties::ID_KEY;
		$this->name = new StringTextField("Name", "ssna", true, '', 50);
        $this->slug = new StringTextField("Slug", "ssSlug", false, '', 50);
		$this->root_dir = new StringTextField("Root directory", "ssrd", false, "", 255);
		$this->image_path = new StringTextField("Image directory", "ssdr", false, "", 255);
		$this->sub_dir = new StringTextField("Thumbnail subdirectory", "ssts", false, "", 100);
		$this->image_label = new StringTextField("Image label", "ssil", false, "", 100);
		$this->width = new IntegerTextField("Image width", "ssiw", false, null);
		$this->height = new IntegerTextField("Image height", "ssih", false, null);
		$this->med_width = new IntegerTextField("Medium target width", "sstw", false, null);
		$this->med_height = new IntegerTextField("Medium target height", "ssth", false, null);
		$this->save_mini = new BooleanCheckbox("Save mini image", "ssmn", false, false);
		$this->mini_width = new IntegerTextField("Mini target width", "ssmw", false, null);
		$this->mini_height = new IntegerTextField("Mini target height", "ssmh", false, null);
		$this->format = new StringSelect("Thumbnail image format", "sstf", false, "");
		$this->param_prefix = new StringTextField("Image parameter prefix", "sspp", false, "", 20);
		$this->table = new StringTextField("Table name", "sstb", false, "", 50);
		$this->parent_id = new IntegerSelect("Parent", "sspi", false, null);
		$this->is_cached = new BooleanCheckbox("Cache content", "sscc", false, false);
		$this->gallery_thumbnail = new BooleanCheckbox("Gallery thumbnail", "ssgt", false, false);
	}

	/**
	 * Resets the object's property values.
	 */
	public function clearValues()
	{
		parent::clearValues();
		$this->resetExtraProperties();
	}

	/**
	 * Delete this record from the database. Clears parent id of any child records.
	 * @return string Message indicating result of the deletion.
	 * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws Exception
	 */
	public function delete(): string
	{
		/* Update parent id for any child records. */
		$query = "UPDATE `".$this::getTableName()."` SET `parent_id` = NULL WHERE `parent_id` = ?";
		$this->query($query, 'i', $this->id->value);
		return(parent::delete());
	}

    public function generateUpdateQuery(): ?array
    {
        return array('CALL contentPropertiesUpdate(@record_id,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'ssssssiiiiiiisssiii',
            &$this->name->value,
            &$this->slug->value,
            &$this->root_dir->value,
            &$this->image_path->value,
            &$this->sub_dir->value,
            &$this->image_label->value,
            &$this->width->value,
            &$this->height->value,
            &$this->med_width->value,
            &$this->med_height->value,
            &$this->save_mini->value,
            &$this->mini_width->value,
            &$this->mini_height->value,
            &$this->format->value,
            &$this->param_prefix->value,
            &$this->table->value,
            &$this->parent_id->value,
            &$this->is_cached->value,
            &$this->gallery_thumbnail->value);
    }

	/**
	 * @param string $operation
	 * @return ContentRoute|null
	 */
	public function getContentRouteByOperation(string $operation): ?ContentRoute
	{
		foreach($this->routes as $route) {
			if ($operation === $route->operation->value) {
				return $route;
			}
		}
		return null;
	}

	/**
     * @param string $name
     * @return ContentTemplate|null
     */
    public function getContentTemplateByName(string $name): ?ContentTemplate
    {
        foreach($this->templates as $template) {
            if ($name === $template->name->value) {
                return $template;
            }
        }
        return null;
    }

	/**
	 * Content label getter.
	 * @return string
	 */
	public function getContentLabel(): string
	{
		return $this->name->value;
	}

	/**
	 * Retrieves the parent id of the parent record of the current site_section record, if a parent exists.
	 * @return ?int Record id of parent record.
	 * @throws InvalidQueryException|Exception
     */
	public function getParentID(): ?int
	{
		if ($this->id->value===null || $this->id->value < 1) {
			return null;
		}
		$query = "CALL siteSectionParentIDSelect(?)";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		if (count($data) > 0) {
			return($data[0]->parent_id);
		}
		return null;
	}

    /**
     * Retrieves the content type for the parent of the current content type.
     * @return ?int Content type id of the parent record.
     * @throws RecordNotFoundException
     * @throws Exception
     */
	public function getParentTypeID(): ?int
	{
		if ($this->id->value===null || $this->id->value < 1) {
			return null;
		}
		$query = "CALL siteSectionParentTypeID(?);";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Parent content type not found.");
		}
		return($data[0]->content_type_id);
	}

    /**
     * Indicates if any form data has been entered for the current instance of the object.
     * @return boolean Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData(): bool
    {
        return ($this->id->value > 0 || $this->name->value);
    }

    /**
     * Returns a new ContentRoute instance. Derived classes can override to provide the object with custom route objects.
     * @param int|null $record_id
     * @param int|null $content_type_id
     * @param string $operation
     * @param string $route
     * @param string $url
     * @return ContentRoute
     */
    protected function newRouteInstance(
        ?int $record_id=null,
        ?int $content_type_id=null,
        string $operation='',
        string $route='',
        string $url=''
    ): ContentRoute
    {
        return new ContentRoute($record_id, $content_type_id, $operation, $route, $url);
    }

    /**
     * Returns new ContentTemplate instance. Can be used in derived classes to provide customized ContentTemplate objects to the APIPage class's methods.
     * @param int|null $record_id
     * @param int|null $content_type_id
     * @param string $operation
     * @param string $base_dir
     * @param string $template
     * @param string $location
     * @return ContentTemplate
     */
    protected function newTemplateInstance(?int $record_id=null, ?int $content_type_id=null, string $operation='', string $base_dir='', string $template='', string $location=''): ContentTemplate
    {
        return new ContentTemplate($record_id, $content_type_id, $operation, $base_dir, $template, $location);
    }

    /**
	 * Returns a single or plural version of the content type identifier, depending on the number of records.
	 * @param int $count Number of records being worked on.
	 * @param string $property_name (Optional) Object property holding the identifier for this content. Uses the "name" property unless overridden.
	 * @return string String formatted to match the number of records. Either singular or plural.
     * @throws ConfigurationUndefinedException
     */
	public function pluralLabel(int $count, string $property_name='name'): string
	{
		return parent::pluralLabel($count, $property_name);
	}

	/**
	 * Retrieves site section data from the database using the value of the object's id property.
	 * Assign values to the object's properties using data the site section table in the database.
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
     * @throws Exception
	 */
	public function read(): void
	{
		parent::read();

        // ensure all fields are in the expected format
        if (null === $this->save_mini->value) {
            $this->save_mini->value = false;
        }

        // retrieve extra properties from database
		$query = "CALL siteSectionExtraPropertiesSelect(?)";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		$this->resetExtraProperties();
		if (count($data) > 0) {
			if ($data[0]->id_param !== null) {
				$this->id_key = $data[0]->id_param;
			}
			if ($data[0]->parent !== null) {
				$this->parent = $data[0]->parent;
			}
			$this->label = $data[0]->label;
		}
		$this->readRoutes();
		$this->readTemplates();
	}

	/**
	 * Retrieve content routes linked to this content type.
	 * @throws InvalidQueryException|Exception
	 */
	public function readRoutes(): void
	{
		// clear out any existing data
		$this->routes = [];

		$query = "CALL contentRouteSelect(?,?,?)";
		$id = $name = null;
		$data = $this->fetchRecords($query, 'iis', $id, $this->id->value, $name);
		if (count($data) < 1) {
			return;
		}
		foreach($data as $row) {
			$this->routes[] = $this->newRouteInstance(
				$row->id,
				$this->id->value,
				$row->operation,
                $row->route,
				$row->api_route);
		}
	}

	/**
	 * Retrieve content templates linked to this content type.
	 * @throws InvalidQueryException|Exception
	 */
	public function readTemplates(): void
	{
        // clear out any existing data
        $this->templates = [];

		$query = "CALL contentTemplateSelectBySectionID(?)";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		if (count($data) < 1) {
			return;
			// throw new RecordNotFoundException("Error retrieving content templates.");
		}
		foreach($data as $row) {
			$this->templates[] = $this->newTemplateInstance(
				$row->id,
				$this->id->value,
				$row->name,
				'',
				$row->path,
				''.$row->location);
		}
	}

	/**
	 * Resets the values of class properties not initialized automatically by the parent class.
	 * @return void
	 */
	protected function resetExtraProperties()
	{
		$this->templates = array();
		$this->routes = array();
		$this->id_key = '';
		$this->parent = '';
		$this->label = '';
	}
}
