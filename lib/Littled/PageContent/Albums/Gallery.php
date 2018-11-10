<?php
namespace Littled\PageContent\Albums;


use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\Images\ImageLink;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\SiteContent\ContentProperties;
use Littled\Request\IntegerInput;


/**
 * Class Gallery
 * @package Littled\PageContent\Albums
 */
class Gallery extends MySQLConnection
{
	/** @var ContentProperties Section properties. */
	public $contentProperties;
	/** @var integer Parent record id. */
	public $parent_id;
	/** @var string Label for inserting into page content. */
	public $label;
	/** @var ImageLink[] List of image_link_class objects representing the images in the gallery */
	public $list;
	/** @var ImageLink Thumbnail record. */
	public $tn;
	/** @var IntegerInput Pointer to the thumbnail id object for convenience. */
	public $tn_id;
	/** @var IntegerInput to the content type id object for convenience. */
	public $type_id;
	/** @var integer Current number of images in the gallery. */
	public $image_count;
	/** @var array List of errors found in object property values. */
	public $validationErrors;


	/**
	 * Gallery constructor.
	 * @param int $content_type_id Content type of the gallery
	 * @param int|null[optional] $parent_id Id of the gallery's parent record.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function __construct ($content_type_id, $parent_id=null )
	{
		parent::__construct();
		$this->contentProperties = new ContentProperties($content_type_id);
		$this->parent_id = $parent_id;
		$this->label = "Image";

		$this->list = array();
		$this->tn = new ImageLink("", "", $content_type_id, $this->parent_id);
		$this->tn_id = &$this->tn->id;
		$this->type_id = &$this->contentProperties->id;
		$this->image_count = -1;
		$this->retrieveSectionProperties();
		$this->validationErrors = array();
	}

	/**
	 * Returns the form data members of the objects as series of nested associative arrays.
	 * @param array $exclude_keys (Optional) array of parameter names to exclude from the returned array.
	 * @return array Associative array containing the object's form data members as name/value pairs.
	 */
	public function arrayEncode($exclude_keys=null)
	{
		$ar = array();
		foreach ($this as $key => $item) {
			if (is_object($item)) {
				if (!is_array($exclude_keys) || !in_array($key, $exclude_keys)) {
					if (is_subclass_of($item, "RequestInput")) {
						$ar[$key] = $item->value;
					}
					elseif (is_subclass_of($item, "SerializedContent")) {
						/** @var SerializedContent $item */
						$ar[$key] = $item->arrayEncode();
					}
				}
			}
			elseif ($key=="list") {
				$ar[$key] = array();
				if (is_array($item)) {
					foreach($item as &$img_lnk) {
						$ar[$key][count($ar[$key])] = $img_lnk->arrayEncode(array("site_section"));
					}
				}
			}
		}
		return ($ar);
	}

	/**
	 * Sets internal values of the object with form data.
	 * @param int[optional] $section_id If specified, sets section id before retrieving form data, otherwise uses the current internal section id value.
	 * @param array|null[optional] $src If specified, this array will be used to extract values that are stored in
	 * the object properties. Otherwise the values are extracted from POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function collectFromInput ( $section_id=null, $src=null )
	{
		if ($section_id > 0) {
			$this->contentProperties->id->value = $section_id;
		}

		$this->testForContentType();
		$this->retrieveSectionProperties();

		$this->list = array();
		$id_param = $this->contentProperties->param_prefix->value.ImageLink::vars['id'];
		$img_id_param = $this->contentProperties->param_prefix->value.ImageLink::vars['id'];
		$iCount = 0;
		if ($src===null) {
			$src = $_POST;
		}
		if (isset($src[$id_param])) {
			$iCount = count($src[$id_param]);
		}
		elseif (isset($src[$img_id_param])) {
			/* with new records there won't be an image_link id, only an image id */
			$iCount = count($src[$img_id_param]);
		}

		for ($i=0; $i<$iCount; $i++) {
			$this->list[$i] = new ImageLink($this->contentProperties->image_path->value, $this->contentProperties->param_prefix->value, $this->contentProperties->id->value);
			$this->list[$i]->collectFromInput();
		}

		$this->tn->collectFromInput();
	}

	/**
	 * Deletes all images records attached to the current gallery including the image files on disk and all of the keywords assigned to the images. Also deletes the gallery thumbnail.
	 * @return string String describing the results of the operation.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function delete( )
	{
		$status = "";
		$image_ids = array();
		foreach($this->list as &$image_link) {
			array_push($image_ids, $image_link->id->value);
			$status .= $image_link->delete();
		}
		$this->list = array();

		if ($this->tn->id->value>0 && !in_array($this->tn->id->value, $image_ids)) {
			$status .= $this->tn->delete();
		}
		return ($status);
	}

	/**
	 * Retrieves the "gallery thumbnail" setting for the gallery, which indicates that a thumbnail image is expected for the gallery.
	 * @return array Containing the gallery thumbnail setting and the parent id of the gallery.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function fetchGalleryThumbnail()
	{
		$query = "CALL galleryGalleryThumbnailSettingSelect({$this->contentProperties->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data[0]) > 0) {
			return (array($data[0]->gallery_thumbnail, $data[0]->parent_id));
		}
		return(array(null, null));
	}

	/**
	 * Assigns ImageLink property values using data from query.
	 * @param ImageLink $image_link Image set to fill with the data from the recordset row.
	 * @param object $row Recordset row containing data to store in ImageLink object.
	 * @param bool[optional] $read_keywords Flag indicating that keywords should be retrieved for each image. Default value is FALSE.
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function fillImageSetFromRecordset( $image_link, $row, $read_keywords=false)
	{
		$image_link->title->value = $row->title;
		$image_link->description->value = $row->description;
		$image_link->slot->value = $row->slot;
		$image_link->page_number->value = $row->page_number;
		$image_link->access->value = $row->access;
		$image_link->release_date->value = $row->release_date;
		$image_link->full->id->value = $row->full_id;
		$image_link->full->path->value = $row->full_path;
		$image_link->full->width->value = $row->full_width;
		$image_link->full->height->value = $row->full_height;
		$image_link->med->id->value = $row->med_id;
		$image_link->med->path->value = $row->med_path;
		$image_link->med->width->value = $row->med_width;
		$image_link->med->height->value = $row->med_height;
		$image_link->mini->id->value = $row->mini_id;
		$image_link->mini->path->value = $row->mini_path;
		$image_link->mini->width->value = $row->mini_width;
		$image_link->mini->height->value = $row->mini_height;
		if ($read_keywords) {
			$image_link->readKeywords();
		}
	}

	/**
	 * Formats a string that reports the number of items in the gallery and
	 * the type of items represented by the gallery.
	 * @return string String reporting number of items and type of items.
	 */
	public function formatItemCountString()
	{
		return (count($this->list)." ".strtolower($this->contentProperties->image_label->value).((count($this->list)!=1)?("s"):("")));
	}

	/**
	 * Returns the number of images in the gallery even if the gallery array hasn't been filled yet.
	 * @param string[optional] $access Limits count to a particular access level.
	 * @return int image count
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getImageCount( $access="" )
	{
		if (is_array($this->list)) {
			return (count($this->list));
		}
		elseif ($this->image_count >= 0) {
			return ($this->image_count);
		}
		else {
			$query = "SELECT COUNT(1) AS `count` FROM `image_link` WHERE parent_id = {$this->parent_id} AND type_id = {$this->type_id->value}";
			if ($access) {
				$query .= " AND access = '{$access}'";
			}
			$data = $this->fetchRecords($query);
			list($this->image_count) = $data[0]->count;
		}
		return ($this->image_count);
	}

	/**
	 * Checks if any form data has been stored in the object that in turns requires storage in the database.
	 * @return bool TRUE if data is found in the object. FALSE if data is not found in the object.
	 */
	public function hasData()
	{
		foreach($this->list as $image_link) {
			if ($image_link->hasData()) {
				return(true);
			}
		}
		return (false);
	}

	/**
	 * Returns TRUE/FALSE if the current page is the first page in the gallery.
	 * @return bool TRUE if the current page is the first page in the gallery. FALSE otherwise.
	 */
	public function isFirstPage()
	{
		return(
			property_exists($this->list[0], "is_first_page") && (
				($this->list[0]->is_first_page->value==true) ||
				(count($this->list)>1 && $this->list[1]->is_first_page->value==true)));
	}

	/**
	 * Returns TRUE/FALSE if the current page is the last page in the gallery.
	 * @return bool TRUE if the current page is the last page in the gallery. FALSE otherwise.
	 */
	public function isLastPage()
	{
		return(
			property_exists($this->list[0], "is_last_page") && (
				($this->list[0]->is_last_page->value==true) ||
				(count($this->list)>1 && $this->list[1]->is_last_page->value==true)));
	}

	/**
	 * Retrieve all images in the collection.
	 * @param bool[optional] $bReadKW Optional flag to control if keywords are read for the listings. Defaults to false.
	 * @param bool[optional] $bReadTn Optional flag to control if the collection's thumbnail record will be retrieved. Defaults to true.
	 * @param bool[optional] $bPublicOnly Optional flag to indicate that only public images should be retrieved.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read ($read_keywords=false, $read_thumbnails=true, $public_only=false )
	{
		$this->testForContentTypeAndParent();

		$this->retrieveSectionProperties();

		$query = "CALL gallerySelect({$this->parent_id},{$this->contentProperties->id->value},".(($public_only)?('1'):('0')).")";
		$data = $this->fetchRecords($query);

		$this->list = array();
		foreach($data as $row) {
			$i = count($this->list);
			$this->list[$i] = new ImageLink(
				$this->contentProperties->image_path->value,
				$this->contentProperties->param_prefix->value,
				$this->contentProperties->id->value,
				$this->parent_id,
				$row->id);
			$this->fillImageSetFromRecordset($this->list[$i], $row, $read_keywords);
		}

		if ($read_thumbnails) {
			$this->readThumbnail();
		}
	}

	/**
	 * Retrieves the thumbnail properties for the gallery.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function readThumbnail()
	{
		$this->testForContentTypeAndParent();
		$this->tn->parent_id->value = $this->parent_id;

		list($gallery_thumbnail, $parent_content_id) = $this->fetchGalleryThumbnail();

		if ($gallery_thumbnail===null || $parent_content_id > 0) {
			return;
		}

		$query = "CALL galleryExternalThumbnailSelect({$this->parent_id},{$this->contentProperties->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			$this->tn_id->value = $data[0]->thumbnail_id;
		}

		if ($this->tn_id->value>0) {
			$this->tn->read();
		}
	}

	/**
	 * Retrieves the gallery's content properties from database. Sets object's internal values.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		$this->testForContentType();
		$this->contentProperties->read();
		list($parent_gallery_thumbnail) = $this->fetchGalleryThumbnail();

		if ($parent_gallery_thumbnail) {
			/**
			 * If the thumbnail is a pointer to one of the images in the gallery its content type value
			 * should match the gallery's content type.
			 */
			$this->tn->type_id->value = $this->type_id->value;
		}
		elseif ($this->contentProperties->parent_id->value>0) {
			$this->tn->type_id->value = $this->contentProperties->parent_id->value;
		}
		$this->tn->retrieveSectionProperties();

		if ($this->contentProperties->image_label->value!==null &&
			$this->contentProperties->image_label->value!="") {
			$this->label = $this->contentProperties->image_label->value;
		}
	}

	/**
	 * Commits the object's internal properties to the database.
	 * @param boolean[optional] $save_thumbnail Flag to specify that a thumbnail record should be saved along with
	 * the gallery's core properties. FALSE by default.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function save ($save_thumbnail=false )
	{
		/** @var ImageLink $img */
		foreach ($this->list as &$img) {
			if ($img->hasData()) {
				$img->parent_id->value = $this->parent_id;
				$img->save();
			}
		}

		if ($save_thumbnail) {
			$this->saveThumbnail();
		}
	}

	/**
	 * Updates parent table's tn_id column with the id of the current thumbnail record.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function saveThumbnail( )
	{
		try {
			$this->testForContentTypeAndParent();
		}
		catch(ConfigurationUndefinedException $ex) {
			return;
		}

		/* get parent content properties */
		$parent_content_id = $parent_table = null;
		$query = "SELECT p.`id`, p.`table` ".
			"FROM 'site_section' p ".
			"INNER JOIN `site_section` c ON p.`id` = c.`parent_id` ".
			"WHERE c.`id` = {$this->contentProperties->id->value}";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			$parent_content_id = $data[0]->id;
			$parent_table = $data[0]->table;
		}

		if ($parent_content_id===null || $parent_content_id<1) {
			return;
		}

		/* set parent thumbnail id if the parent table supports it */
		if ($this->columnExists("tn_id", $parent_table)) {
			$query = "UP"."DATE `{$parent_table}` SET tn_id = {$this->tn->id->value} WHERE id = {$this->parent_id}";
			$this->query($query);

			$query = "UPDATE `image_link` SET parent_id = {$this->parent_id} WHERE id = {$this->tn->id->value}";
			$this->query($query);
		}
	}

	/**
	 * Tests if the content type of the object is set in cases where a content type is required.
	 * @throws ConfigurationUndefinedException Content type is not currently set for the object.
	 */
	protected function testForContentType()
	{
		if ($this->contentProperties->id->value===null || $this->contentProperties->id->value<1) {
			throw new ConfigurationUndefinedException("Site section not set. ");
		}
	}

	/**
	 * Tests if the content type and parent of the object is set in cases where a content type and parent is required.
	 * @throws ConfigurationUndefinedException Content type is not currently set for the object.
	 */
	protected function testForContentTypeAndParent()
	{
		$this->testForContentType();
		if ($this->parent_id===null || $this->parent_id<1) {
			throw new ConfigurationUndefinedException("Gallery parent not set.");
		}
	}

	/**
	 * Validate image collection form input.
	 * @throws ContentValidationException
	 */
	function validateInput ()
	{
		foreach ($this->list as &$image_link) {
			/** @var ImageLink $image_link */
			try {
				$image_link->validateInput();
			}
			catch (ContentValidationException $ex) {
				$this->validationErrors = array_merge($this->validationErrors, $image_link->validationErrors);
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Errors were found in the gallery.");
		}
	}
}