<?php
namespace Littled\PageContent\Images;


use Littled\Cache\ContentCache;
use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\BooleanInput;
use Littled\Request\DateTextField;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerTextField;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;

/**
 * Class ImageLink
 * @package Littled\PageContent\Images
 */
class ImageLink extends KeywordSectionContent
{
	/** @var array HTTP request variable names. */
	const vars = array(
		'id' => 'ilid',
		'parent_id' => 'ilpi',
		'content_type' => 'ilti',
		'slot' => 'ilsl',
		'page_number' => 'ilpn',
		'access' => 'ilac',
		'release_date' => 'ilrd',
		'randomize_filename' => 'ilrf'
	);

	/** @var string Name of table in the database that stores this object's data. */
	const TABLE_NAME = 'image_link';

	/** @var IntegerInput $id image_link record id */
	public $id;
	/** @var IntegerInput $parent_id Parent record id. */
	public $parent_id;
	/** @var StringTextField $title Image title. */
	public $title;
	/** @var StringTextarea $description Image description. */
	public $description;
	/** @var IntegerTextField $slot Position of the image record relative to other images linked to the same parent record. */
	public $slot;
	/** @var IntegerTextField $page_number The page number of the image, e.g. the page number of a sketchbook that corresponds with the image. */
	public $page_number;
	/** @var StringSelect $access Access level of the image, e.g. "public", "private", "disabled", etc. */
	public $access;
	/** @var DateTextField $release_date Date after which the record will be accessible as front-end content. */
	public $release_date;
	/** @var Image $full Full-resolution image record. */
	public $full;
	/** @var Image $med Medium-resolution image record. */
	public $med;
	/** @var Image $mini Smallest-resolution image record. */
	public $mini;
	/** @var IntegerInput $type_id Pointer to site_section id property. */
	public $type_id;
	/** @var StringInput $randomize Flag to indicate that the filename of the images should be randomized after they uploaded to the server. */
	public $randomize;
	/** @var string Name of the content type of this set of images. */
	public $type_name;
	/** @var BooleanInput Flag indicating this record is the first image in a series of images. */
	public $isFirstPage;
	/** @var BooleanInput Flag indicating this record is the last image in a series of images. */
	public $isLastPage;

	public static function TABLE_NAME() { return (ImageLink::TABLE_NAME); }

	/**
	 * ImageLink constructor.
	 * @param string[optional] $image_dir
	 * @param string[optional] $param_prefix
	 * @param int[optional] $section_id
	 * @param int[optional] $parent_id
	 * @param int[optional] $id
	 * @param int[optional] $image_id
	 * @param string[optional] $path
	 * @param int[optional] $width
	 * @param int[optional] $height
	 * @param string[optional] $alt_tag
	 * @param string[optional] $url
	 * @param string[optional] $target
	 * @param string[optional] $caption
	 * @param int[optional] $slot
	 * @param string[optional] $access
	 */
	function __construct ($image_dir="", $param_prefix="", $section_id=null, $parent_id=null, $id=null, $image_id=null, $path=null, $width=null, $height=null, $alt_tag="", $url=null, $target=null, $caption="", $slot=null, $access="public")
	{
		parent::__construct($section_id, $param_prefix);
		$this->id = new IntegerInput("ID", $param_prefix.$this::vars['id'], false, $id);
		$this->parent_id = new IntegerInput("Image parent", $param_prefix.$this::vars['parent_id'], false, $parent_id);
		$this->title = new StringTextField("Title", $param_prefix.ImageBase::vars['alt'], false, "", 50);
		$this->description = new StringTextarea("Description", $param_prefix.ImageBase::vars['caption'], false, "", 1000);
		$this->slot = new IntegerTextField("Slot", $param_prefix.$this::vars['slot'], false, $slot);
		$this->page_number = new IntegerTextField("Page Number", $param_prefix.$this::vars['page_number'], false, null);
		$this->access = new StringSelect("Access", $param_prefix.$this::vars['access'], true, $access, "20");
		$this->release_date = new DateTextField("Release date", $param_prefix.$this::vars['release_date'], false, date("n/j/Y"));
		$this->full = new Image(null, $image_dir, $param_prefix, $image_id, $path, $width, $height, $alt_tag, $url, $target, $caption);
		$this->full->alt->label = "Name";
		$this->full->caption->label = "Text";
		$this->med = new Image(null, $image_dir, $param_prefix."md");
		$this->mini = new Image(null, $image_dir, $param_prefix."mn");

		$this->contentProperties->image_path->value = $image_dir;
		$this->contentProperties->sub_dir->value = "full/";
		$this->contentProperties->id->key = $param_prefix.$this::vars['content_type'];
		$this->type_id = &$this->contentProperties->id;
		$this->randomize = new StringInput("Randomize filename", $this::vars['randomize_filename'], false, false);
		$this->randomize->isDatabaseField = false;

		$this->isFirstPage = new BooleanInput("Is first page", "ifp", false, false);
		$this->isLastPage = new BooleanInput("Is last page", "ilp", false, false);
		$this->isFirstPage->isDatabaseField = false;
		$this->isLastPage->isDatabaseField = false;
	}

	/**
	 * Clears object of image data. Preserves the parent id, section id, and site_section properties.
	 */
	public function clearValues()
	{
		$this->id->value = null;
		// $this->parent_id->value = null;
		// $this->contentProperties->id->value = null;
		$this->title->value = "";
		$this->description->value = "";
		$this->slot->value = null;
		$this->page_number->value = null;
		$this->access->value = "";
		$this->release_date->value = "";
		$this->full->clearValues();
		$this->med->clearValues();
		$this->mini->clearValues();
	}

	/**
	 * Assign values to object's properties based on form data.
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput( $src=null )
	{
		$this->collectInlineInput($src);
		$this->title->collectFromInput(null, $src);
		$this->description->collectFromInput(null, $src);
		$this->full->collectFromInput($src);
		$this->med->collectFromInput($src);
		$this->mini->collectFromInput($src);
		$this->slot->collectFromInput(null, $src);
		$this->page_number->collectFromInput(null, $src);
		$this->access->collectFromInput(null, $src);
		$this->release_date->collectFromInput(null, $src);
	}

	/**
	 * Assign values to only the object's id, parent_id and type_id properties based on script input or form data.
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectInlineInput( $src=null )
	{
		$this->id->collectFromInput(null, $src);
		$this->parent_id->collectFromInput(null, $src);
		if (($this->parent_id->key != $this::vars['parent_id']) &&
			($this->parent_id->value===null)) {
			/* sometimes in the case of image uploads the script doesn't know about individualized form parameters */
			$this->parent_id->collectFromInput(null, $src, $this::vars['parent_id']);
		}
		$this->contentProperties->id->collectFromInput();
		if (($this->type_id->key != $this::vars['content_type']) &&
			($this->type_id->value===null)) {
			/* sometimes in the case of image uploads the script doesn't know about individualized form parameters */
			$this->type_id->collectFromInput(null, $src, $this::vars['content_type']);
		}
		$this->randomize->collectFromInput(null, $src);
	}

	/**
	 * Deletes image_link record along with all images records and files attached to the record.
	 * @return string Status message detailing the results of the operation.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function delete()
	{
		if ($this->id->value===null || $this->id->value<1) {
			throw new ContentValidationException("Id not provided.");
		}

		$status = "";
		$status .= $this::deleteLinkedImage($this->full, "full-resolution");
		$status .= $this::deleteLinkedImage($this->med, "medium-resolution");
		$status .= $this::deleteLinkedImage($this->mini, "thumbnail");

		$query = "CALL keywordDeleteLinked({$this->id->value},{$this->contentProperties->id->value});";
		$this->query($query);

		$query = "CALL imageLinkDelete({$this->id->value});";
		$this->query($query);

		$status .= "Image record ".(($this->title->value)?("\"{$this->title->value}\" "):(""))."(id:{$this->id->value}) was deleted. \n";

		$this->deleteThumbnailLink();

		$this->clearValues();
		return ($status);
	}

	/**
	 * Delete image record linked to the ImageLink record.
	 * @param Image $image Image object to be removed.
	 * @param string $description Description of the image being removed.
	 * @return string Description of the results of the operation.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected static function deleteLinkedImage( $image, $description)
	{
		if ($image->id->value===null || $image->id->value < 1) {
			return ("");
		}
		$image->deleteExistingImageFile($image->id->value);
		$query = "DELETE FROM `images` WHERE id = {$image->id->value}";
		$image->query($query);
		return(ucfirst($description)." image {$image->path->value} was removed.");
	}

	/**
	 * Tests to see if this image is a thumbnail image for a parent record. Deletes that link if it is found.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function deleteThumbnailLink()
	{
		/* delete the thumbnail link in the parent table if one is detected */
		if ($this->parent_id->value > 0 and $this->type_id->value > 0) {
			/**
			 * Get parent table name and flag indicating that the thumbnail
			 * points to a record in the gallery (as opposed to an image_link
			 * record independent from the gallery)
			 */
			$parent_table = '';
			$query = "CALL siteSectionParentTableSelect($this->contentProperties->id->value);";
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$parent_table = $data[0]->table;
			}

			if (!$parent_table) {
				/**
				 * In the case where the thumbnail is not part of the album's
				 * gallery, the thumbnail content type id will match the
				 * album's content id. I.e. the thumbnail's content type
				 * is the same as the album's and is not a child content type.
				 * Table properties need to be retrieved accordingly.
				 */
				$query = "CALL siteSectionThumbnailTableSelect($this->contentProperties->id->value);";
				$data = $this->fetchRecords($query);
				if (count($data) > 0) {
					$parent_table = $data[0]->table;
				}
			}

			if (!$parent_table) {
				return;
			}

			/* make sure parent table has thumbnail column */
			$query = "SHOW COLUMNS FROM `{$parent_table}` LIKE 'tn_id'";
			$data = $this->fetchRecords($query);
			$found_tn_column = count($data) > 0;

			if ($parent_table && $found_tn_column) {
				/**
				 * If this content type has thumbnails pointing to records in its
				 * gallery, then use the first image uploaded into the gallery
				 * as the thumbnail.
				 */
				$query = "SELECT COUNT(1) as `count` ".
					"FROM `image_link` ".
					"WHERE `parent_id` = {$this->parent_id->value} ".
					"AND `type_id` = {$this->contentProperties->id->value}";
				$data = $this->fetchRecords($query);
				$page_count = $data[0]->count;

				if ($page_count==0) {
					/* Updates the parent record's thumbnail to point at the image that was just uploaded. */
					$query = "CALL thumbnailUnsetParentLink('{$parent_table}',{$this->parent_id})";
					$this->query($query);
				}
				else {
					$query = "CALL thumbnailUpdateParentWithImageLink(".
						"'{$parent_table}',".
						"{$this->parent_id->value},".
						"{$this->id->value},".
						"{$this->contentProperties->id->value})";
					$this->query($query);
				}
			}
		}
	}

	/**
	 * Wrapper around SQL statement to insert new image_link record in the database
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function executeInsertQuery()
	{
		$query = "INSERT INTO `image_link` ".
			"(`fullres_id`,`med_id`,`mini_id`,`parent_id`,`type_id`,`slot`,`page_number`,`access`,".
			"`title`,`description`,`release_date`) VALUES (".
			$this->full->id->escapeSQL($this->mysqli).", ".
			$this->med->id->escapeSQL($this->mysqli).", ".
			$this->mini->id->escapeSQL($this->mysqli).", ".
			$this->parent_id->escapeSQL($this->mysqli).", ".
			$this->contentProperties->id->escapeSQL($this->mysqli).", ".
			$this->slot->escapeSQL($this->mysqli).", ".
			$this->page_number->escapeSQL($this->mysqli).", ".
			$this->access->escapeSQL($this->mysqli).", ".
			$this->title->escapeSQL($this->mysqli).", ".
			$this->description->escapeSQL($this->mysqli).", ".
			$this->release_date->escapeSQL($this->mysqli).")";
		$this->query($query);
		$this->id->value = $this->retrieveInsertID();
	}

	/**
	 * Wrapper around SQL statement to update existing image_link record in the database.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function executeUpdateQuery()
	{
		$query = "UPDATE `image_link` SET ".
			"`fullres_id` = ".$this->full->id->escapeSQL($this->mysqli).", ".
			"`med_id` = ".$this->med->id->escapeSQL($this->mysqli).", ".
			"`mini_id` = ".$this->mini->id->escapeSQL($this->mysqli).", ".
			"`parent_id` = ".$this->parent_id->escapeSQL($this->mysqli).", ".
			"`type_id` = ".$this->contentProperties->id->escapeSQL($this->mysqli).", ".
			"`title` = ".$this->title->escapeSQL($this->mysqli).", ".
			"`description` = ".$this->description->escapeSQL($this->mysqli).", ".
			"`slot` = ".$this->slot->escapeSQL($this->mysqli).", ".
			"`page_number` = ".$this->page_number->escapeSQL($this->mysqli).", ".
			"`access` = ".$this->access->escapeSQL($this->mysqli).", ".
			"`release_date` = ".$this->release_date->escapeSQL($this->mysqli)." ".
			"WHERE `id` = {$this->id->value}";
		$this->query($query);
	}

	/**
	 * Gets thumbnail data for thumbnails linked to an album.
	 * @param int $parent_id ID of the parent record to which the thumbnails are linked.
	 * @param int[optional] $limit Number of records to return. Defaults to 5.
	 * @return array Thumbnail data
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function fetchPageThumbnails($parent_id, $limit=5 )
	{
		$conn = new MySQLConnection();
		$query = "CALL imageLinkPageThumbnailsSelect({$parent_id},{$limit})";
		return ($conn->fetchRecords($query));
	}

	/**
	 * Populate object's property values from a database recordset.
	 * @param object $data Recordset containing values.
	 */
	protected function fillFromRecordset( $data )
	{
		$this->parent_id->value = $data->parent_id;
		$this->contentProperties->id->value = $data->type_id;
		$this->type_name = $data->type_name;
		$this->title->value = $data->title;
		$this->description->value = $data->description;
		$this->full->id->value = $data->fullres_id;
		$this->full->path->value = $data->path;
		$this->full->width->value = $data->width;
		$this->full->height->value = $data->height;
		$this->full->url->value = $data->url;
		$this->full->target->value = $data->target;
		$this->med->id->value = $data->med_id;
		$this->med->path->value = $data->med_path;
		$this->med->width->value = $data->med_width;
		$this->med->height->value = $data->med_height;
		$this->mini->id->value = $data->mini_id;
		$this->mini->path->value = $data->mini_path;
		$this->mini->width->value = $data->mini_width;
		$this->mini->height->value = $data->mini_height;
		$this->slot->value = $data->slot;
		$this->page_number->value = $data->page_number;
		$this->access->value = $data->access;
		$this->release_date->value = date('n/j/Y',strtotime($data->release_date));
	}

	/**
	 * Returns the content type id of the parent of this ImageLink record.
	 * @return int|null Parent content type id.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getParentContentTypeID()
	{
		return ($this->contentProperties->getParentTypeID());
	}

	/**
	 * Indicates if any form data that would require being stored in the database has been entered for the current instance of the object.
	 * @return boolean Returns TRUE if data exists. Returns FALSE if the object doesn't contain any relevant data.
	 */
	public function hasData()
	{
		return ($this->id->value>0 || $this->full->id->value>0 || $this->full->path->value);
	}

	/**
	 * Retrieves record corresponding to the object from the database and uses it to fill the object's property values.
	 * @param bool[optional] $read_keywords Retrieve keywords linked to the image if set to TRUE. Default value is TRUE.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read( $read_keywords=true )
	{
		$this->connectToDatabase();
		$query = "CALL imageLinkSelect(".$this->id->escapeSQL($this->mysqli).
			",".$this->parent_id->escapeSQL($this->mysqli).
			",".$this->contentProperties->id->escapeSQL($this->mysqli).");";
		$data = $this->fetchRecords($query);
		$this->fillFromRecordset($data[0]);

		$this->retrieveSectionProperties();
		if ($read_keywords) {
			$this->readKeywords();
		}
	}

	/**
	 * Retrieves site section properties and stores that data in object properties.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		parent::retrieveSectionProperties();

		$this->full->image_dir = $this->contentProperties->image_path->value;
		if ($this->contentProperties->param_prefix->value) {
			$this->setPrefix($this->contentProperties->param_prefix->value);
		}
	}

	/**
	 * Upload images attached to the object, and save their properties in the datbase.
	 * @param bool $save_keywords (optional) Update keywords for the record. Defaults to true.
	 * @param bool $randomize_filename (Optional) flag if set to true the new image file will be given a randomized filename
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function save($save_keywords=true, $randomize_filename=false )
	{
		$is_new_image = ((isset($_FILES[$this->full->path->key]) && $_FILES[$this->full->path->key]["name"]));

		$this->upload($randomize_filename);

		if ($this->id->value>0) {
			$this->executeUpdateQuery();
		}
		else {
			$this->connectToDatabase();
			$query = "SELECT `id` FROM `image_link` ".
				"WHERE `fullres_id` = ".$this->full->id->escapeSQL($this->mysqli)." ".
				"AND `parent_id` = ".$this->parent_id->escapeSQL($this->mysqli)." ".
				"AND `type_id` = ".$this->type_id->escapeSQL($this->mysqli);
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$this->id->value = $data[0]->id;
			}

			if ($this->id->value>0) {
				$this->executeUpdateQuery();
			}
			else {
				$this->executeInsertQuery();
			}
		}

		if ($is_new_image && $save_keywords) {
			/* extract keywords from image */
			$this->full->extractKeywords($this->keywords, $this->id->value, $this->contentProperties->id->value);
			$this->saveKeywords();
		}
	}

	/**
	 * Adds to parent's save_keywords routine to also save a cached set of the keywords in a single column of the image_link record to be used with fulltext searches.
	 * Also updates parent's keywords if the parent object has a keyword cache.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function saveKeywords()
	{
		parent::saveKeywords();
		$this->updateFulltextKeywords();
	}

	/**
	 * Prepends string to all property key values.
	 * @param string $prefix String to prepend to all property keys.
	 */
	public function setPrefix( $prefix )
	{
		foreach($this::vars as $property => $default_name) {
			if (property_exists($this, $property) && $this->$property instanceof RequestInput) {
				$this->$property->key = $prefix.$default_name;
			}
		}
		$this->title->key = ImageBase::vars['alt'];
		$this->description->key = ImageBase::vars['caption'];
		$this->contentProperties->id->key = $this::vars['content_type'];
		$this->full->setPrefix($prefix);
		$this->med->setPrefix($prefix.'md');
		$this->mini->setPrefix($prefix.'mn');
	}

	/**
	 * updates the destination directory for all the versions of the image
	 * @param string $path Path to the image upload directory (relative to the web image root).
	 */
	public function setImageDestinationPath( $path )
	{
		$this->full->image_dir = $path;
		$this->med->image_dir = $path;
		$this->mini->image_dir = $path;
		$this->contentProperties->image_path->value = $path;
	}

	/**
	 * Sets the values of the object's "thumbnail" size to the values passed in to the function.
	 * @param int $id Thumbnail image id.
	 * @param string $path Thumbnail image path.
	 * @param int $width Thumbnail image width.
	 * @param int $height Thumbnail image height.
	 */
	public function setThumbnail ($id, $path, $width, $height)
	{
		$this->mini->id->value = $id;
		$this->mini->path->value = $path;
		$this->mini->width->value = $width;
		$this->mini->height->value = $height;
	}

	/**
	 * Sets the value of the image label. Updates the image_link field as well
	 * as all of the children image objects.
	 * @param string $title The label for the image.
	 */
	public function setTitle ( $title )
	{
		$this->title->value = $title;
		$this->full->alt->value = $title;
		$this->med->alt->value = $title;
		$this->mini->alt->value = $title;
	}

	/**
	 * Updates the internal column that stores all keywords for this record and all of its child records used for fulltext searches.
	 * Also updates the keywords for the parent of the image_link records.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function updateFulltextKeywords()
	{
		$query = "CALL imageLinkUpdateKeywords({$this->id->value});";
		$this->query($query);

		/**
		 * The logic here may have gotten garbled here when refactored from common_lib. Not 100% sure what the
		 * goal of this logic is.
		 */
		if (class_exists("ContentCache") && method_exists("ContentCache", "updateKeywords")) {
			if ($this->parent_id->value>0) {
				$parent_type_id = $this->contentProperties->getParentTypeID();
				if ($parent_type_id) {
					ContentCache::updateKeywords($this->parent_id->value, $this->contentProperties);
				}
			}
		}
	}

	/**
	 * Upload and process each of the images attached to this object,
	 * including operations such as extracting keywords, resizing, and renaming.
	 * @param bool[optional] $randomize_filename Optional flag if set to true the new image file will be given a randomized filename. Defaults to FALSE.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function upload($randomize_filename=false )
	{
		if (!isset($_FILES[$this->full->path->key])) {
			return;
		}

		$this->connectToDatabase();
		$make_thumbnail = ($_FILES[$this->full->path->key]["name"]);

		$target_dims = new ImageDims($this->contentProperties->width->value, $this->contentProperties->height->value);
		$this->full->save($target_dims, null, $this->contentProperties->sub_dir->value, null, $randomize_filename);

		if ($make_thumbnail && ($this->contentProperties->med_width->value>0 || $this->contentProperties->med_height->value>0)) {
			$medium_dims = new ImageDims($this->contentProperties->med_width->value, $this->contentProperties->med_height->value);
			$this->med->id->value = $this->full->makeThumbnailCopy(basename($this->full->path->value), $medium_dims, "jpg", "med/", "med_id");
		}

		if ($make_thumbnail && ($this->contentProperties->save_mini->value==true) && ($this->contentProperties->mini_width->value>0 || $this->contentProperties->mini_height->value>0)) {
			$mini_dims = new ImageDims($this->contentProperties->mini_width->value, $this->contentProperties->mini_height->value);
			$this->mini->id->value = $this->full->makeThumbnailCopy(basename($this->full->path->value), $mini_dims, "png", "mini/", "mini_id");
		}
	}

	/**
	 * Validates input to inline scripts where what's needed is basic information to either retrieve a specific record for editing, or to load section properties for uploading an image into a CMS section.
	 * @throws ContentValidationException Errors found with the object's property values.
	 */
	public function validateInlineInput()
	{
		$this->parent_id->required = true;
		$this->contentProperties->id->required = true;
		try {
			$this->parent_id->validate();
		}
		catch (ContentValidationException $ex) {
			array_push($this->validationErrors, $ex->getMessage());
		}
		try {
			$this->contentProperties->id->validate();
		}
		catch (ContentValidationException $ex) {
			array_push($this->validationErrors, $ex->getMessage());
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Errors found in image set.");
		}
	}

	/**
	 * Validates object properties after they have been filled from form data.
	 * @param array[optional] $exclude_properties Array of properties that should not be validated.
	 * @throws ContentValidationException Errors found in object property values.
	 */
	public function validateInput($exclude_properties=array())
	{
		try {
			parent::validateInput($exclude_properties);
		}
		catch(ContentValidationException $ex) {
			; /* continue */
		}
		try {
			$this->full->validateInput();
		}
		catch(ContentValidationException $ex) {
			$this->validationErrors = array_merge($this->validationErrors, $this->full->validationErrors);
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Errors found in image set.");
		}
	}
}