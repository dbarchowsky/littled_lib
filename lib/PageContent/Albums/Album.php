<?php
namespace Littled\PageContent\Albums;


use JsonSchema\Exception\ValidationException;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\Validation\Validation;
use Littled\Request\IntegerInput;
use Littled\Request\DateInput;
use Littled\Request\DateTextField;
use Littled\Request\IntegerTextField;
use Littled\Request\StringSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;
use Littled\PageContent\PageController;
use Littled\PageContent\Images\ImageLink;
use Littled\PageContent\SiteSection\KeywordSectionContent;

/**
 * Class Album
 * @package Littled\PageContent\Albums
 */
class Album extends KeywordSectionContent
{
	/** @var string Id http variable name. */
	const ID_PARAM = "abid";
	/** @var string Title http variable name. */
	const TITLE_PARAM = "abti";
	/** @var string Description http variable name. */
	const DESCR_PARAM = "abds";
	/** @var string Name of table in the database. */
	const TABLE_NAME = "album";

	/** @var Gallery Album gallery containing images, pages, clips, views, etc. */
	public $gallery;
	/** @var StringTextField Name/title of the record. */
	public $title;
	/** @var StringTextField Record slug. */
	public $slug;
	/** @var StringTextarea Album description. */
	public $description;
	/** @var int Token representing the content type of the record. */
	public $section_id;
	/** @var StringTextField Plain english display date for the album. */
	public $date;
	/** @var DateInput Creation date of the album record. */
	public $create_date;
	/** @var DateInput Last modification date of the album record. */
	public $mod_date;
	/** @var IntegerTextField Position of this album record relative to all the others of the same content type. */
	public $slot;
	/** @var IntegerTextField Token representing the access-level required to view this album record (disabled, public, private, etc.) */
	public $access;
	/** @var DateTextField Date determining when the album record can be viewed publicly.  */
	public $release_date;
	/** @var StringSelect Token determining the layout to use to display the album. */
	public $layout;
	/** @var boolean $view_thumbnails Flag to control the display of thumbnail images. */
	public $view_thumbnails;
	/** @var boolean $check_access Flag to override the $access property value. */
	public $check_access;

	public static $albumMetadataTemplate = '';
	public static $galleryListingsTemplate = '';
	public static $thumbnailLinkContainerTemplate = '';
	public static $thumbnailOverlayButtonsTemplate = '';
	public static $thumbnailUploadTemplate = '';

	public static function TABLE_NAME() { return(self::TABLE_NAME); }

	/**
	 * class constructor
	 * @param int $content_type_id Id of the content's site section.
	 * @param int $images_content_type_id Id of the gallery's site section
	 * @param int[optional] $id Id of the content record.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function __construct ( $content_type_id, $images_content_type_id, $id=null)
    {
		parent::__construct($id, $content_type_id, "abkw");
		$this->id = new IntegerInput("Gallery ID", self::ID_PARAM, false, $id);
		$this->title = new StringTextField("Title", self::TITLE_PARAM, false, "", 100);
		$this->slug = new StringTextField("Slug", 'AlbumSlug', true, "", 50);
		$this->description = new StringTextarea("Description", self::DESCR_PARAM, false, "", 4000);
		$this->description->class = "mce-editor";
		$this->date = new StringTextField("Display date", "abdt", false, "", 50);
		$this->create_date = new DateInput("Create date", "abcd", false, "");
		$this->mod_date = new DateInput("Modified date", "abmd", false, "");
		$this->slot = new IntegerTextField("Slot", "absl", false, null);
		$this->access = new StringSelect("Access", "abac", false, "public", 20);
		$this->release_date = new DateTextField("Release date", "abrd", false, date("n/j/Y"));
		$this->layout = new StringSelect("Layout", "ablo", false, "", 20);
		$this->section_id = &$this->contentProperties->id;

		$this->gallery = new Gallery($images_content_type_id, $this->id->value);

		$this->keywordInput->label = "keywords";

		$this->view_thumbnails = true;
		$this->check_access = false;
	}

	/**
	 * Tries to collect the album id from the following sources: default id
	 * parameter (GET and POST), derived class's specific id parameter (GET and
	 * POST), and any slug that may have been used to request the album.
	 * Throws exception if album id cannot be found.
	 * @param array[optional] Array of variables to use to fill object properties if not using POST data to fill object property values.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function collectAlbumID( $src=null )
	{
		/* generic id parameter */
		$this->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_PARAM);
		if ($this->id->value > 0) {
			return;
		}

		/* id parameter specific to the derived class */
		$this->id->collectRequestData($src);
		if ($this->id->value > 0) {
			return;
		}

		/* album slug passed in through the URL. NB INPUT_SERVER is unreliable with filter_input() */
		$php_self = $_SERVER['PHP_SELF'];
		$exclude = array(rtrim(dirname($php_self), '/').'/', $php_self);
		$controller = new PageController();
		$controller->collectAlbumProperties($exclude);
		$this->id->value = $controller->album_id;
		if ($this->id->value > 0) {
			return;
		}

		throw new ValidationException("A record was not specified.");
	}

	/**
	 * Fill object properties from form input, including the gallery images.
	 * @param array|null[optional] $src Array of variables to use to fill the object's property values instead of using POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function collectRequestData ($src=null )
	{
		$this->section_id->bypassCollectPostData = true;
		parent::collectRequestData($src);
		$this->gallery->collectFromInput($src);
		if ($this->title->value && $this->slug->value=="") {
			$this->slug->value = $this->generateDefaultSlug();
		}
	}

	/**
	 * Routine to collect input values when evaluating just the slug value
	 * of the record. Intended for AJAX routines.
	 * Update the $id, $section_id, $title and $slug properties of the object.
	 * The $section_id property value is set from the object's internal constant
	 * value and not from data passed to the script.
	 * @param array|null[optional] $src Array of variables to use to fill object property values in place of request variables.
	 */
	public function collectSlugInput( $src=null )
	{
		$this->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_PARAM, null, $src);
		if ($this->id->value===null) {
			$this->id->collectRequestData($src);
		}
		$this->section_id->value = $this->getContentTypeID();
		$this->title->collectFromInput($src);
		if ($this->id->value > 0) {
			$this->slug->collectFromInput($src);
		}
		else {
			/* if this is a new record, make sure to regenerate the $slug
			 * value using the $title value by not collecting any initial
			 * value for it.
			 */
			$this->slug->value = '';
		}
		$this->formatSlug();
	}

	/**
	 * Deletes core content record, along with all gallery images and keywords.
	 * @return string Status message reporting the results of the operation.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function delete()
	{
		$this->testForAlbumID();

		/* make sure all object properties are available. */
		$this->read();

		$status = $this->gallery->delete();

		/* N.B. logic for deleting keyword records linked to the album and
		 * the album's thumbnail were removed. I believe that keywords are
		 * deleted in the db_keyword_content_class::delete() routine, and
		 * that the thumbnail record is deleted as part of the
		 * gallery_class::delete() routine.
		 */

		/* deleting the album record */
		$success = $this->formatDeleteStatusMessage();
		$status .= parent::delete();
		return ($status.$success);
	}

	/**
	 * Returns message to display to indicate that a record was successfully removed.
	 * @returns string Message to display to indicate successful deletion operation.
	 */
	protected function formatDeleteStatusMessage ( )
	{
		return ("The &ldquo;{$this->title->value}&rdquo; ".strtolower($this->contentProperties->label)." was successfully deleted.");
	}

	/**
	 * Generate the slug value using the current $title property value, or
	 * an explicit value passed to the routine with the $slug argument.
	 * @param string[optional] $slug Explicit value to use as the basis for the
	 * slug value.
	 */
	public function formatSlug( $slug='' )
	{
		if ($slug) {
			$this->slug->value = $slug;
		}

		/* slug value is based on the title of the album by default */
		if (!$this->slug->value) {
			$this->slug->value = $this->title->value;
		}

		/* remove duplicate spaces */
		$this->slug->value = preg_replace('/\s+/', ' ', $this->slug->value);

		/* replace spaces with dashes */
		$this->slug->value = preg_replace('/\s/', '-', $this->slug->value);

		/* remove non-alphanumeric characters */
		$this->slug->value = preg_replace('/[^A-Za-z0-9\-]/', '', $this->slug->value);

		if (strlen($this->slug->value) > $this->slug->sizeLimit) {
			/* avoid overruns */
			$this->slug->value = substr($this->slug->value, 0, $this->slug->sizeLimit);
		}
	}

	/**
	 * Generate a default slug value using the existing title value.
	 * - Checks for incompatibilities with exisitng slug values.
	 * - Ensures that the generated slug value is unique.
	 * - Stores the slug value in the object's $slug property.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function generateDefaultSlug()
	{
		$this->formatSlug();
		$slug_base = $this->slug->value;

		$index = 1;
		while($this->validateSlug()===false) {
			if (strlen("{$slug_base}-{$index}") > $this->slug->sizeLimit) {
				/* avoid overruns */
				$slug_base = substr($slug_base, -1 * (strlen("-{$index}")));
			}
			$this->slug->value = "{$slug_base}-{$index}";
			$index++;
		}
	}

	public static function getAlbumMetadataTemplatePath()
    {
        return (static::$albumMetadataTemplate);
    }

	/**
	 * Returns the title of the album.
	 * @return string Title of the album.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function getBookTitle() {
		return ($this->getRecordLabel());
	}

	/**
	 * Retrieves the first page in the album's gallery
	 * @param bool[optional] $read_keywords If set to true, keywords associated with the image will also be retrieved. defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function getDefaultPage( $read_keywords=false )
	{
		$this->testForAlbumID();
		$query = "CALL albumFirstPageSelect({$this->id->value},{$this->contentProperties->id->value})";
		$data = $this->fetchRecords($query);
		$page_id = ((count($data)>0)?($data[0]->id):(null));

		if ($page_id===null || $page_id < 1) {
			throw new RecordNotFoundException("A default page could not be found.");
		}

		$this->readPage($page_id, $read_keywords);
		$this->markLimits();
	}

    /**
     * @return string Gallery listings template path.
     */
    public static function getGalleryListingsTemplatePath()
    {
        return (static::$galleryListingsTemplate);
    }

    /**
     * @return string Album linked-thumbnail selection tool template path.
     */
    public static function getThumbnailLinkContainerTemplatePath()
    {
        return (static::$thumbnailLinkContainerTemplate);
    }

    /**
     * @return string Thumbnail overlay buttons template path.
     */
	public static function getThumbnailOverlayButtonsTemplatePath()
    {
        return (static::$thumbnailOverlayButtonsTemplate);
    }

    /**
     * @return string Thumbnail upload container template path.
     */
    public static function getThumbnailUploadTemplatePath()
    {
        return (static::$thumbnailUploadTemplate);
    }

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return boolean Returns true if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData ()
	{
		return ($this->id->value>0 || strlen($this->title->value)>0 || $this->gallery->hasData());
	}

	/**
	 * Tests to see if the album is allowed to have gallery thumbnails that are images uploaded
	 * independently of the images in the gallery.
	 * @return bool TRUE if unlinked images are allowed.
	 */
	public function hasIndependentGalleryThumbnail()
	{
		return (
			false === isset($this->contentProperties) ||
			true === $this->contentProperties->gallery_thumbnail->value
		);
	}

	/**
	 * Tests the album properties to determine if a link to one of the images
	 * in the album's gallery should be saved as the thumbnail image for the album.
	 * @return bool TRUE/FALSE depending on if the album properties
	 * dictate that a link to the thumbnail image should be saved in the album record
	 */
	protected function hasThumbnailLink()
	{
		return (
			/* manual setting from database specifying to use a gallery image as the album thumbnail */
			$this->contentProperties->gallery_thumbnail->value == true &&

			/* test that there is one and only one image currently in the gallery */
			is_array($this->gallery->list) &&
			count($this->gallery->list)==1 &&

			/* not sure what this accomplishes :( ? */
			$this->gallery->list[0]->full->path->required==false &&

			/* don't attempt to save the thumbnail id unless image uploads are required */
			$this->gallery->tn_id->value > 0
		);
	}

	/**
	 * Looks up the id of the record matching the current slug property value of the object. The id
	 * value will be stored in the object's id property. An exception is thrown if a corresponding
	 * slug value is not located.
	 * @throws ConfigurationUndefinedException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function lookupSlug()
	{
		$this->connectToDatabase();
		$query = "SEL"."ECT `id` FROM `".$this::TABLE_NAME()."` ".
			"WHERE (`section_id` = {$this->section_id->value}) ".
			"AND (`slug` = ".$this->slug->escapeSQL($this->mysqli).")";
		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Slug not found.");
		}
		$this->id->value = $data[0]->id;
	}

	/**
	 * Marks the current pages as being either at the start, end, or middle of the book.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function markLimits()
	{
		$first_page_id = $last_page_id = null;
		$query = "CALL albumFirstPageSelect({$this->id->value},{$this->contentProperties->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			$first_page_id = $data[0]->id;
		}

		$query = "CALL albumLastPageSelect({$this->id->value},{$this->contentProperties->id->value})";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			$last_page_id = $data[0]->id;
		}

		foreach ($this->gallery->list as &$page) {
			$page->isFirstPage->value = ($first_page_id > 0 && $page->id->value == $first_page_id);
			$page->isLastPage->value = ($last_page_id > 0 && $page->id->value == $last_page_id);
		}
	}

	/**
	 * Read the content record, along with it's site section properties, gallery images, and keywords.
	 * @param bool[optional] $read_images Flag to additionally read all the images attached to the record. Defaults to true.
	 * @param bool[optional] $read_image_keywords Flag to additionally read the keywords for all of the images in the gallery. Defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read ($read_images=true, $read_image_keywords=false)
	{
		if ($this->check_access==true) {
			$query = "SEL"."ECT COUNT(1) AS `count` FROM `".$this->TABLE_NAME()."` WHERE id = {$this->id->value} AND `access` NOT IN ('public')";
			$data = $this->fetchRecords($query);
			$is_protected = ($data[0]->count > 0);

			if ($is_protected==-true) {
				throw new ConfigurationUndefinedException("Access denied.");
			}
		}
		parent::read();

		if ($this->release_date->value) {
			$this->release_date->value = date('n/j/Y',strtotime($this->release_date->value));
		}

		/* retrieve this object's content properties */
		$this->contentProperties->read();

		/* check if the gallery's content property type has been set */
		if ($this->gallery->contentProperties->id->value<1) {
			/* assumes that the gallery's content properties exist
			 * as a child of this object's content type. Although -- what
			 * happens when there is some other record attached to this
			 * content type???
			 */
			$query = "SELECT `id` FROM `site_section` WHERE `parent_id` = {$this->contentProperties->id->value}";
			$data = $this->fetchRecords($query);
			$this->gallery->contentProperties->id->value = ((count($data) > 0)?($data[0]->id):(null));
		}

		$this->gallery->parent_id = $this->id->value;
		if ($read_images==true) {
			$this->gallery->read($read_image_keywords, true);
		}
		else {
			$this->gallery->readThumbnail();
		}
	}

	/**
	 * Retrieves single page and puts it in gallery array.
	 * @param int $page_id Id of the image_link record to load.
	 * @param boolean $read_keywords (optional) if set to true, keywords associated with the image will also be retrieved. defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function readPage( $page_id, $read_keywords=false )
	{
		$this->gallery->list = array();
		$this->gallery->list[0] =
			new ImageLink(
				$this->gallery->contentProperties->image_path->value,
				$this->gallery->contentProperties->param_prefix->value,
				$this->gallery->contentProperties->id->value,
				$this->id,
				$page_id);
		$this->gallery->list[0]->read($read_keywords);

		$this->markLimits();
	}

	/**
	 * Saves content record, along with gallery images and keywords.
	 * @param boolean $save_thumbnail (Optional) Flag to additionally save a thumbnail record (dbo: image_link) as opposed to linking to an existing image in the gallery list as this content item's thumbnail. Default TRUE.
	 * @param boolean $update_cache (Optional) Flag to additionally update content cache. Default TRUE.
	 * @return string Message to display to user indicating the result of the operation.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function save ($save_thumbnail=true, $update_cache=true)
	{
		$status = "";
		$is_new = ($this->id->value===null);

		$this->create_date->isDatabaseField = false;
		$this->mod_date->isDatabaseField = false;
		parent::save();

		if ($this->contentProperties->table->value=="album" ||
			(
				$this->contentProperties->table->value &&
				$this->columnExists("create_date") &&
				$this->columnExists("mod_date")
			)) {
			/* update create/mod dates */
			$query = "UPDATE `".$this->TABLE_NAME()."` SET ";
			if ($is_new) {
				$query .= "create_date = NOW(), ";
			}
			$query .= "mod_date = NOW() WHERE id = {$this->id->value}";
			$this->query($query);
		}

		if ($is_new) {
			$this->setDefaultSlotValue();
		}

		/* save images currently in this album's gallery */
		$this->gallery->parent_id = $this->id->value;
		$this->gallery->save($save_thumbnail && $is_new);

		/* link an image in the gallery as this album's thumbnail image */
		if ($is_new && $this->hasThumbnailLink()) {
			$this->saveThumbnailLink();
		}

		$this->updateFulltextKeywords();

		$status .= "The &ldquo;{$this->title->value}&rdquo; ".strtolower($this->contentProperties->label)." was successfully saved. \n";

		if (method_exists($this, "updateCacheFile") && $update_cache) {
			/** TODO set the path to the cache file */
			$status .= $this->updateCacheFile('', '');
		}
		return ($status);
	}

	/**
	 * Sets the album thumbnail to the first image in the gallery when creating a new album
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function saveThumbnailLink()
	{
		$query = "UPDATE `".$this->TABLE_NAME()."` ".
			"SET tn_id = {$this->gallery->list[0]->id->value} ".
			"WHERE id = {$this->id->value}";
		$this->query($query);
	}

	/**
	 * Updates the slot value on new album records to put the new record at the top of the list.
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function setDefaultSlotValue()
	{
		/* confirm that the necessary columns and object properties exist */
		$has_slot = $has_section_id = false;
		$this->testForSlotColumns($has_slot, $has_section_id);
		if (!$has_slot) {
			return;
		}

		/* query to update the values of the pre-existing albums in this
		 * group to push them back to make space for the new one at the front
		 */
		$query = "UPDATE `".$this->TABLE_NAME()."` SET slot = IFNULL(slot,0)+1";
		if ($has_section_id) {
			$query .= " WHERE (section_id = {$this->section_id->value})";
		}
		$this->query($query);

		/* query to update the slot value of this record to put it at the
		 * front of the list of albums.
		 */
		$query = "UPDATE `".$this->TABLE_NAME()."` SET slot = 0 WHERE id = {$this->id->value}";
		$this->query($query);
	}

	/**
	 * Tests if the object's album id value is set.
	 * @throws ContentValidationException Album id property value is not currently set.
	 */
	protected function testForAlbumID()
	{
		if ($this->id->value===null || $this->id->value<1) {
			throw new ContentValidationException("Album not set.");
		}
	}

	/**
	 * Tests for the existence of columns that affect the insertion of slot
	 * values into the record in the database.
	 * @param bool $has_slot
	 * @param bool $has_section_id
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function testForSlotColumns ( &$has_slot, &$has_section_id )
	{
		$query = "SHOW COLUMNS ".
			"FROM `".$this->TABLE_NAME()."` ".
			"WHERE `field` LIKE 'slot' ".
			"OR `field` LIKE 'section_id'";
		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			return;
		}

		foreach($data as $row) {
			switch ($row->field) {
				case 'slot':
					$has_slot = true;
					break;
				case 'section_id':
					$has_section_id = true;
					break;
			}
		}

		if ($has_slot) {
			$has_slot = property_exists($this, 'slot');
		}
		if ($has_section_id) {
			$has_section_id = property_exists($this, 'section_id');
			/** TODO Also check for $section property ? */
		}
	}

	/**
	 * Updates the internal column that stores all keywords for this record and all of its child records used for fulltext searches.
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function updateFulltextKeywords()
	{
		/* avoid exceeding limit on GROUP_CONCAT() */
		$this->query("SET @@group_concat_max_len := @@max_allowed_packet;");

		$query = "CALL albumFulltextKeywordsUpdate(".
			$this->TABLE_NAME().",".
			"{$this->id->value},".
			"{$this->contentProperties->id->value},".
			"{$this->gallery->contentProperties->id->value})";
		$this->query($query);
	}

	/**
	 * Validate form data collected with fill_from_input() routine.
	 * @param array[optional] List of object properties that do not require validation.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function validateInput ( $exclude_properties=array() )
	{
		try {
			parent::validateInput();
		}
		catch (ContentValidationException $ex) {
			; /* continue */
		}

		try {
			$this->gallery->validateInput();
		}
		catch (ContentValidationException $ex) {
			array_push($this->validationErrors, $ex->getMessage());
			$this->validationErrors = array_merge($this->validationErrors, $this->gallery->validationErrors);
		}

		if ($this->slug->value) {
			try {
				$this->validateSlug();
			}
			catch (ContentValidationException $ex) {
				array_push($this->validationErrors, $ex->getMessage());
			}
		}

		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Errors were found in the album.");
		}
	}

	/**
	 * Validates the current value of the object's $slug property against existing records in the database.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function validateSlug()
	{
		if ($this->id->value > 0) {
			/* get the existing slug. if they are the same then the current
			 * value of the object's $slug property doesn't need to be checked.
			 */
			$query = "SEL"."ECT `slug` FROM `".$this->TABLE_NAME()."` WHERE (`id` = {$this->id->value})";
			$data = $this->fetchRecords($query);
			if (count($data) < 1) {
				throw new ContentValidationException("Slug value could not be retrieved for validation.");
			}
			$slug = $data[0]->slug;

			if ($slug == $this->slug->value && $slug != "") {
				/* current $slug value matches the value in the database */
				return;
			}
		}

		if (!$this->slug->value) {
			/* create a default value if the $slug property value isn't already set */
			$this->formatSlug();
		}

		/**
		 * Query to search for existing records with slug values that match the object's $slug property value.
		 */
		$query = "SEL"."ECT COUNT(1) AS `count` FROM `".$this->TABLE_NAME()."` ";
		if (property_exists($this, "section_id") && $this->section_id->value > 0) {
			$query .= "WHERE (section_id = {$this->section_id->value}) ";
		}
		$query .= ((strpos($query, "WHERE") > 0)?('AND '):('WHERE '));
		$query .= "(`slug` LIKE '".$this->slug->escapeSQL($this->mysqli)."') ";
		if ($this->id->value > 0) {
			$query .= "AND (`id` <> {$this->id->value}) ";
		}
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			throw new ContentValidationException("A &ldquo;{$this->slug->value}&rdquo; slug already exists.");
		}
	}
}