<?php

namespace Littled\PageContent\SiteSection;


use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerTextField;
use Littled\Request\IntegerSelect;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;

/**
 * Class ContentProperties
 * @package Littled\PageContent\SiteSection
 */
class ContentProperties extends SerializedContent
{
	/** @var string Variable name that holds the site section id value. */
	const ID_PARAM = 'ssid';
	/** @var int Id value representing site section data in the database. */
	const SECTION_ID = 27;
	/** @var string Name of the table holding site section data. */
	const TABLE_NAME = 'site_section';
	public static function TABLE_NAME() { return ContentProperties::TABLE_NAME; }

	/** @var StringTextField Name of the content. */
	public $name;
	/** @var StringTextField Root directory for section content. */
	public $root_dir;
	/** @var StringTextField Target path for image uploads. */
	public $image_path;
	/** @var StringTextField Subdirectory for section content. */
	public $sub_dir;
	/** @var StringTextField Label used when displaying images as a group on the front-end. */
	public $image_label;
	/** @var IntegerTextField Target width of full-resolution images. */
	public $width;
	/** @var IntegerTextField Target height of full-resoluation images. */
	public $height;
	/** @var IntegerTextField Target width of medium-sized thumbnail images. */
	public $med_width;
	/** @var IntegerTextField Target height of medium-sized thumbnail images. */
	public $med_height;
	/** @var BooleanCheckbox Flag indicating that miniature thumbnail verions of images are to be generated when uploading and editing images. */
	public $save_mini;
	/** @var IntegerTextField Target width of smallest images. */
	public $mini_width;
	/** @var IntegerTextField Target height of smallest images. */
	public $mini_height;
	/** @var StringSelect Image format, e.g. jpeg, png, etc. */
	public $format;
	/** @var StringTextField Parameter prefix to use for this content type. */
	public $param_prefix;
	/** @var StringTextField Content able name. */
	public $table;
	/** @var IntegerSelect Id of parent content type record in site_section table. */
	public $parent_id;
	/** @var BooleanCheckbox Flag indicating that this section's content gets cached. */
	public $is_cached;
	/** @var BooleanCheckbox Flag indicating to use gallery thumbnails. */
	public $gallery_thumbnail;
	/** @var string Name of the argument used to pass the record id to and from pages and scripts. Stored in the section_operations table. */
	public $id_param;
	/** @var string Parent content type name. */
	public $parent;
	/** @var string Alternate name for the content type explicitly intended to be displayed with form controls. Stored in the section_operations table. */
	public $label;
	/** @var array Array of templates used to render the section's content. */
	public $templates;
	
	/**
	 * SiteSection constructor.
	 * @param integer[optional] Initial value to assign to the object's id property.
	 */
	public function __construct($id=null)
	{
		parent::__construct($id);
		$this->id->key = ContentProperties::ID_PARAM;
		$this->name = new StringTextField("Name", "ssna", true, "", 50);
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
		$this->initializeExtraProperties();
	}

	/**
	 * Sets initial values for the object's extra properties.
	 */
	protected function initializeExtraProperties()
	{
		$this->templates = array();
		$this->id_param = "";
		$this->label = "";
		$this->parent = "";
	}

	/**
	 * Resets the object's property values.
	 */
	public function clearValues()
	{
		parent::clearValues();
		$this->initializeExtraProperties();
	}

	/**
	 * Delete this record from the database. Clears parent id of any child records.
	 * @return string Message indicating result of the deletion.
	 * @throws \Littled\Exception\ContentValidationException Record id not provided.
	 * @throws \Littled\Exception\InvalidQueryException Table name not set in inherited class.
	 * @throws \Littled\Exception\NotImplementedException SQL error raised running deletion query.
	 */
	public function delete()
	{
		/* Update parent id for any child records. */
		$query = "UPDATE `site_section` SET `parent_id` = NULL WHERE `parent_id` = {$this->id->value}";
		$this->query($query);
		return(parent::delete());
	}

	/**
	 * Retrieves the parent id of the parent record of the current site_section record, if a parent exists.
	 * @return integer|null Id of parent record.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getParentID()
	{
		if ($this->id->value===null || $this->id->value < 1) {
			return (null);
		}
		$query = "CALL siteSectionParentIDSelect({$this->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			return($data[0]->parent_id);
		}
		return (null);
	}

	/**
	 * Retrieves the content type for the parent of the current content type.
	 * @return int|null Content type id of the parent record.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getParentTypeID()
	{
		if ($this->id->value===null || $this->id->value < 1) {
			return (null);
		}
		$query = "CALL siteSectionParentTypeID({$this->id->value});";
		$data = $this->fetchRecords($query);
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
	public function hasData()
	{
		return ($this->id->value > 0 || $this->name->value);
	}

	/**
	 * Returns a single or plural version of the content type identifier, depending on the number of records.
	 * @param int $count Number of records being worked on.
	 * @param string[optional] $property_name Object property holding the identifier for this content. Uses the "name" property unless overridden.
	 * @return string String formatted to match the number of records. Either singular or plural.
	 */
	public function pluralLabel($count, $property_name='name')
	{
		return parent::pluralLabel($count, $property_name);
	}

	/**
	 * Retrieves site section data from the database using the value of the object's id property.
	 * Assigns values to the object's properties using data the site section table in the database.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function read()
	{
		parent::read();
		$query = "CALL siteSectionExtraPropertiesSelect({$this->id->value})";
		$data = $this->fetchRecords($query);
		$this->initializeExtraProperties();
		if (count($data) > 0) {
			$this->id_param = $data[0]->id_param;
			$this->parent = $data[0]->parent;
			$this->label = $data[0]->label;
		}
		$this->readTemplates();
	}

	/**
	 * Retrieve content templates linked to this content type.
	 * @throws \Littled\Exception\InvalidQueryException Error executing query.
	 */
	public function readTemplates()
	{
		$query = "CALL contentTemplateSelectBySectionID({$this->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			return;
			// throw new RecordNotFoundException("Error retrieving content templates.");
		}
		foreach($data as $row) {
			$i = count($this->templates);
			$this->templates[$i] = new ContentTemplate(
				$row->id,
				$this->id->value,
				$row->name,
				$this->root_dir->value,
				$row->path,
				$row->location);
		}
	}
}