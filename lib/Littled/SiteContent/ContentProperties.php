<?php
namespace Littled\SiteContent;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\IntegerTextField;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;


/**
 * Class SiteContent
 * @package Littled\SiteContent
 */
class ContentProperties extends ContentOperations
{
	/** @var StringSelect Format of images linked to the record. */
	public $format;
	/** @var BooleanCheckbox Flag indicating that the record's thumbnail is a link to a image in the gallery linked to
	 * the record (as opposed to a stand-alone image record). */
	public $gallery_thumbnail;
	/** @var IntegerTextField Target height of images linked to the parent record. */
	public $height;
	/** @var string Name of the argument used to pass the record id to and from pages and scripts. Stored in the section_operations table. */
	public $id_param;
	/** @var StringTextField Label to use to refer to images linked to the main record. */
	public $image_label;
	/** @var StringTextField Path to the directory storing image files linked to the main record. */
	public $image_path;
	/** @var BooleanCheckbox Flag indicating that content for this content type should be cached. */
	public $is_cached;
	/** @var string Alternate name for the content type explicitly intended to be displayed with form controls. Stored in the section_operations table. */
	public $label;
	/** @var IntegerTextField Target height for medium-sized images. */
	public $med_height;
	/** @var IntegerTextField Target width for medium-sized images. */
	public $med_width;
	/** @var IntegerTextField Target height for smallest-sized images. */
	public $mini_height;
	/** @var IntegerTextField Target width for smallest-sized images. */
	public $mini_width;
	/** @var StringTextField Name of the content type. */
	public $name;
	/** @var StringTextField Prefix to add to variables used by the CMS to collect record data. */
	public $param_prefix;
	/** @var string Parent content type name. */
	public $parent;
	/** @var IntegerSelect Id of a content type that serves as a parent to the current content type. */
	public $parent_id;
	/** @var StringTextField Root path to the templates serving content for the content type. */
	public $root_dir;
	/** @var StringTextField Slug used as a root for the content. */
	public $slug;
	/** @var StringTextField Path to add onto the $root_dir path. */
	public $sub_dir;
	/** @var StringTextField Name of the table in the database storing the records for this content type. */
	public $table;
	/** @var array Array of templates used to render the section's content. */
	public $templates;
	/** @var IntegerTextField Target width of images linked to the main record. */
	public $width;

	const ID_PARAM = 'cpId';
	public static function TABLE_NAME()
	{
		return 'site_section';
	}

	/**
	 * ContentProperties constructor.
	 * @param int $id[optional] Initial id value
	 * @throws ConfigurationUndefinedException Database connection properties not set.
	 */
	function __construct($id=null)
	{
		parent::__construct();
		$this->id = new IntegerInput("Id", self::ID_PARAM, true, $id);
		$this->format = new StringSelect("Image format", 'cpFormat', false, '', 4);
		$this->gallery_thumbnail = new BooleanCheckbox("Has gallery thumbnail", "cpGallThumb", false, false);
		$this->height = new IntegerTextField("Target image height", "cpImgH", false, null);
		$this->image_label = new StringTextField("Image label", "cpImgLabel", false, '', 100);
		$this->image_path = new StringTextField("Image path", "cpImgPath", false, '', 255);
		$this->is_cached = new BooleanCheckbox("Cached", "cpCached", false, false);
		$this->med_height = new IntegerSelect("Medium-sized image target height", "cpMedH", false, null);
		$this->med_width = new IntegerSelect("Medium-sized image target width", "cpMedW", false, null);
		$this->mini_height = new IntegerSelect("Small-sized image target height", "cpMiniH", false, null);
		$this->mini_width = new IntegerSelect("Small-sized image target width", "cpMiniW", false, null);
		$this->name = new StringTextField("Name", "cpName", true, '', 50);
		$this->param_prefix = new StringTextField("Parameter prefix", "cpParamPrefix", false, '', 8);
		$this->parent_id = new IntegerSelect("Parent", "cpParentId", false, null);
		$this->root_dir = new StringTextField("Root path", "cpRoot", false, '', 255);
		$this->slug = new StringTextField("Slug", "cpSlug", false, '', 50);
		$this->sub_dir = new StringTextField("Sub-directory path", "cpSubPath", false, '', 255);
		$this->table = new StringTextField("Table name", "cpTable", true, '', 50);
		$this->width = new IntegerSelect("Target image width", "cpImgW", false, null);

		$this->id_param = '';
		$this->label = '';
		$this->parent = '';
	}

	/**
	 * Deletes the record from the database. Uses the value object's id property to look up the record.
	 * @return string Message indicating result of the deletion.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Exception Error running query.
	 */
	public function delete()
	{
		$query = "UPDATE `".self::TABLE_NAME()."` SET `parent_id` = NULL WHERE `id` = {$this->id->value}";
		$this->query($query);
		return parent::delete(); 
	}

	/**
	 * Retrieves the parent id of the parent record of the current site_section record, if a parent exists.
	 * @return int|null Id of parent record.
	 * @throws \Exception Error running query.
	 */
	public function getParentId()
	{
		$query = "SELECT `parent_id` FROM ``".self::TABLE_NAME()."`` WHERE id = {$this->id->value}";
		$data = $this->fetchRecords($query);
		if (count($data)>0) {
			return ($data[0]->parent_id);
		}
		return null;
	}
	
	/**
	 * Retrieves the id of the parent content type if it exists.
	 * @return int|null Parent content type id if it is found, null otherwise.
	 * @throws \Exception Error running query.
	 */
	public function getParentTypeId()
	{
		$parent_content_id = null;
		$query = <<<SQL
SELECT ps.`id` 
FROM `site_section` ps 
INNER JOIN `site_section` cs ON ps.`id` = cs.`parent_id` 
WHERE cs.`id` = {$this->id->value}
SQL;
		$data = $this->fetchRecords($query);
		if (count($data)>0) {
			return ($data[0]->id);
		}
		return null;
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool Returns TRUE if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	function hasData()
	{
		return ($this->id->value>0 || $this->name->value);
	}

	/**
	 * Retrieves object property values from database and uses them to hydrate the object.
	 * @throws RecordNotFoundException Record not found.
	 * @throws \Exception Error running query.
	 */
	public function read()
	{
		$query = "CALL `contentPropertiesSelect`({$this->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("The requested content properties could not be found.");
		}
		$this->hydrate($data[0]);
		$this->id_param = $data[0]->id_param;
		$this->label = $data[0]->label;
		$this->parent = $data[0]->parent;
	}

	/**
	 * Retrieves template records linked to the main record.
	 */
	public function readTemplates()
	{
		$this->templates = array();
		$query = "SELECT t.id, t.`name`, t.`path`, t.`location` ".
		          "FROM `content_template` t ".
		          "WHERE (t.site_section_id = {$this->id->value})";
		$data = $this->fetchRecords($query);
		foreach($data as $row) {
			$i = count($this->templates);
			$this->templates[$i] = new ContentTemplate(
				$row->id,
				$this->id->value,
				$row->name,
				$this->root_dir->value,
				$row->path,
				$row->location
			);
		}
	}
	
	/**
	 * Returns plural label to use to represent the object based on the $count value.
	 * @param int $count Number of items being displayed.
	 * @param string $property_name Property of the object to use as the basis for the label.
	 * @return mixed Plural label
	 */
	public function pluralLabel($count, $property_name='name')
	{
		return(parent::pluralLabel($count, $property_name));
	}
}