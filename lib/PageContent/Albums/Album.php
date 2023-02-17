<?php
namespace Littled\PageContent\Albums;

use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
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
 * SectionContent that is an album of images. Inherits from KeywordSectionContent to support keywords.
 */
class Album extends KeywordSectionContent
{
    protected static string $table_name = 'album';
	public static ?int $pages_content_type_id=null;
	public static string $albumMetadataTemplate = '';
	public static string $galleryListingsTemplate = '';
	public static string $thumbnailLinkContainerTemplate = '';
	public static string $thumbnailOverlayButtonsTemplate = '';
	public static string $thumbnailUploadTemplate = '';

	/** @var string Id http variable name. */
	const ID_PARAM = "abid";
	/** @var string Title http variable name. */
	const TITLE_PARAM = "abti";
	/** @var string Description http variable name. */
	const DESCR_PARAM = "abds";

	/** @var Gallery Album gallery containing images, pages, clips, views, etc. */
	public Gallery              $gallery;
	/** @var StringTextField Name/title of the record. */
	public StringTextField      $title;
	/** @var StringTextField Record slug. */
	public StringTextField      $slug;
	/** @var StringTextarea Album description. */
	public StringTextarea       $description;
	/** @var IntegerInput Token representing the content type of the record. */
	public IntegerInput         $section_id;
	/** @var StringTextField Plain english display date for the album. */
	public StringTextField      $date;
	/** @var DateInput Creation date of the album record. */
	public DateInput            $create_date;
	/** @var DateInput Last modification date of the album record. */
	public DateInput            $mod_date;
	/** @var IntegerTextField Position of this album record relative to all the others of the same content type. */
	public IntegerTextField     $slot;
	/** @var StringSelect Token representing the access-level required to view this album record (disabled, public, private, etc.) */
	public StringSelect     $access;
	/** @var DateTextField Date determining when the album record can be viewed publicly.  */
	public DateTextField        $release_date;
	/** @var StringSelect Token determining the layout to use to display the album. */
	public StringSelect         $layout;
    /** @var ImageFormat[] List of image formats associated with this album. */
    public array                $image_formats=[];
	/** @var bool Flag to control the display of thumbnail images. */
	public bool                 $view_thumbnails;
	/** @var bool Flag to override the $access property value. */
	public bool                 $check_access;

	/**
	 * class constructor
	 * @param int $content_type_id The id of the content's site section.
	 * @param int $images_content_type_id The id of the gallery's site section
	 * @param ?int $id (Optional) The id of the content record.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 */
	function __construct (int $content_type_id, int $images_content_type_id, ?int $id=null)
    {
		parent::__construct($id, $content_type_id);
		$this->id = new IntegerInput("Gallery ID", self::ID_PARAM, false, $id);
		$this->title = new StringTextField("Title", self::TITLE_PARAM, false, "", 100);
		$this->slug = new StringTextField("Slug", 'AlbumSlug', true, "", 50);
		$this->description = new StringTextarea("Description", self::DESCR_PARAM, false, "", 4000);
		$this->description->input_css_class = "mce-editor";
		$this->date = new StringTextField("Display date", "abdt", false, "", 50);
		$this->create_date = new DateInput("Create date", "abcd", false, "");
		$this->mod_date = new DateInput("Modified date", "abmd", false, "");
		$this->slot = new IntegerTextField("Slot", "absl", false, null);
		$this->access = new StringSelect("Access", "abac", false, "public", 20);
		$this->release_date = new DateTextField("Release date", "abrd", false, date("n/j/Y"));
		$this->layout = new StringSelect("Layout", "ablo", false, "", 20);
		$this->section_id = &$this->content_properties->id;

		if ($images_content_type_id > 0) {
			static::setPagesContentType($images_content_type_id);
		}
		$this->gallery = new Gallery(static::getPagesContentType(), $this->getRecordId());

		$this->keyword_input->label = "keywords";

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
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	public function collectAlbumID( $src=null )
	{
		/* generic id parameter */
		$this->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY);
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

		throw new ContentValidationException("A record was not specified.");
	}

	/**
	 * Fill object properties from form input, including the gallery images.
	 * @param array|null[optional] $src Array of variables to use to fill the object's property values instead of using POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 */
	public function collectRequestData ($src=null )
	{
		$this->section_id->bypass_collect_request_data = true;
		parent::collectRequestData($src);
		$this->gallery->collectFromInput($src);
		if ($this->title->value && $this->slug->value=="") {
			$this->generateDefaultSlug();
		}
	}

	/**
	 * Routine to collect input values when evaluating just the slug value
	 * of the record. Intended for AJAX routines.
	 * Update the $id, $section_id, $title and $slug properties of the object.
	 * The $section_id property value is set from the object's internal constant
	 * value and not from data passed to the script.
	 * @param array|null $src Array of variables to use to fill object property values in place of request variables.
	 */
	public function collectSlugInput( ?array $src=null )
	{
		$this->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
		if ($this->id->value===null) {
			$this->id->collectRequestData($src);
		}
		$this->section_id->value = $this->getContentPropertyId();
		$this->title->collectRequestData($src);
		if ($this->id->value > 0) {
			$this->slug->collectRequestData($src);
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
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 */
	public function delete(): string
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
	protected function formatDeleteStatusMessage ( ): string
	{
		return ("The &ldquo;{$this->title->value}&rdquo; ".strtolower($this->content_properties->label)." was successfully deleted.");
	}

	/**
	 * Generate the slug value using the current $title property value, or
	 * an explicit value passed to the routine with the $slug argument.
	 * @param string[optional] $slug Explicit value to use as the basis for the
	 * slug value.
	 */
	public function formatSlug( string $slug='' )
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

		if (strlen($this->slug->value) > $this->slug->size_limit) {
			/* avoid overruns */
			$this->slug->value = substr($this->slug->value, 0, $this->slug->size_limit);
		}
	}

	/**
	 * Generate a default slug value using the existing title value.
	 * - Checks for incompatibilities with existing slug values.
	 * - Ensures that the generated slug value is unique.
	 * - Stores the slug value in the object's $slug property.
	 */
	public function generateDefaultSlug()
	{
		$this->formatSlug();
		$slug_base = $this->slug->value;

		$index = 1;
		$valid_slug = false;
		while(!$valid_slug) {
			try {
				$this->validateSlug();
				$valid_slug = true;
			}
			catch(Exception $e) {
				if (strlen("$slug_base-$index") > $this->slug->size_limit) {
					/* avoid overruns */
					$slug_base = substr($slug_base, -1 * (strlen("-$index")));
				}
				$this->slug->value = "$slug_base-$index";
				$index++;
			}
		}
	}

	/**
	 * Album metadata template path getter.
	 * @return string
	 */
	public static function getAlbumMetadataTemplatePath(): string
    {
        return static::$albumMetadataTemplate;
    }

	/**
	 * Returns the title of the album.
	 * @return string Title of the album.
	 */
	public function getBookTitle(): string
	{
		return $this->getContentLabel();
	}

	/**
	 * Retrieves the first page of the album's gallery
	 * @param bool[optional] $read_keywords If set to true, keywords associated with the image will also be retrieved. defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws Exception
	 */
	public function getDefaultPage( $read_keywords=false )
	{
		$this->testForAlbumID();
		$query = "CALL albumFirstPageSelect({$this->id->value},{$this->content_properties->id->value})";
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
    public static function getGalleryListingsTemplatePath(): string
    {
        return static::$galleryListingsTemplate;
    }

	/**
	 * Pages content type getter.
	 * @return int
	 */
	public static function getPagesContentType(): int
	{
		return static::$pages_content_type_id;
	}

    /**
     * @return string Album linked-thumbnail selection tool template path.
     */
    public static function getThumbnailLinkContainerTemplatePath(): string
    {
        return static::$thumbnailLinkContainerTemplate;
    }

    /**
     * @return string Thumbnail overlay buttons template path.
     */
	public static function getThumbnailOverlayButtonsTemplatePath(): string
    {
        return static::$thumbnailOverlayButtonsTemplate;
    }

    /**
     * @return string Thumbnail upload container template path.
     */
    public static function getThumbnailUploadTemplatePath(): string
    {
        return (static::$thumbnailUploadTemplate);
    }

	/**
	 * @inheritDoc
	 */
	public function hasData (): bool
	{
		return ($this->id->value>0 || strlen($this->title->value)>0 || $this->gallery->hasData());
	}

	/**
	 * Tests to see if the album is allowed to have gallery thumbnails that are images uploaded
	 * independently of the images in the gallery.
	 * @return bool TRUE if unlinked images are allowed.
	 */
	public function hasIndependentGalleryThumbnail(): bool
	{
		return (
			false === isset($this->content_properties) ||
			true === $this->content_properties->gallery_thumbnail->value
		);
	}

	/**
	 * Tests the album properties to determine if a link to one of the album's gallery images should be saved as the thumbnail image for the album.
	 * @return bool TRUE/FALSE depending on if the album properties
	 * dictate that a link to the thumbnail image should be saved for the album record
	 */
	protected function hasThumbnailLink(): bool
	{
		return (
			/* manual setting from database specifying to use a gallery image as the album thumbnail */
			$this->content_properties->gallery_thumbnail->value == true &&

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
	 * @throws ConnectionException
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	public function lookupSlug()
	{
		$this->connectToDatabase();
		$query = "SEL"."ECT `id` FROM `".static::getTableName()."` ".
			"WHERE `section_id` = ? ".
			"AND `slug` = ?";
		$data = $this->fetchRecords($query, 'is', $this->section_id->value, $this->slug->value);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Slug not found.");
		}
		$this->id->value = $data[0]->id;
	}

	/**
	 * Marks the current pages as being either at the start, end, or middle of the book.
	 * @throws Exception
	 */
	protected function markLimits()
	{
		$first_page_id = $last_page_id = null;
		$record_id = $this->getRecordId();
		$content_type_id = $this->getContentPropertyId();
		$query = "CALL albumFirstPageSelect(?,?)";
		$data = $this->fetchRecords($query, 'ii', $record_id, $content_type_id);
		if (count($data) > 0) {
			$first_page_id = $data[0]->id;
		}

		$query = "CALL albumLastPageSelect(?,?)";
		$data = $this->fetchRecords($query, 'ii', $record_id, $content_type_id);
		if (count($data) > 0) {
			$last_page_id = $data[0]->id;
		}

		foreach ($this->gallery->list as $page) {
			$page->isFirstPage->value = ($first_page_id > 0 && $page->id->value == $first_page_id);
			$page->isLastPage->value = ($last_page_id > 0 && $page->id->value == $last_page_id);
		}
	}

	/**
	 * Read the content record, along with its site section properties, gallery images, and keywords.
	 * @param bool $read_images (Optional) Flag to additionally read all the images attached to the record. Defaults to true.
	 * @param bool $read_image_keywords (Optional) Flag to additionally read the keywords for all the images in the gallery. Defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws Exception
	 */
	public function read (bool $read_images=true, bool $read_image_keywords=false)
	{
		if ($this->check_access) {
			$query = "SEL"."ECT COUNT(1) AS `count` FROM `".static::getTableName()."` WHERE id = ? AND `access` NOT IN ('public')";
			$data = $this->fetchRecords($query, 'i', $this->id->value);
			$is_protected = ($data[0]->count > 0);

			if ($is_protected===true) {
				throw new ConfigurationUndefinedException("Access denied.");
			}
		}
		parent::read();

		if ($this->release_date->value) {
			$this->release_date->value = date('n/j/Y',strtotime($this->release_date->value));
		}

		/* retrieve this object's content properties */
		$this->content_properties->read();

		/* check if the gallery's content property type has been set */
		if ($this->gallery->content_properties->id->value<1) {
			/* assumes that the gallery's content properties exist
			 * as a child of this object's content type. Although -- what
			 * happens when there is some other record attached to this
			 * content type???
			 */
			$query = "SELECT `id` FROM `site_section` WHERE `parent_id` = ?";
			$content_type_id = $this->getContentPropertyId();
			$data = $this->fetchRecords($query, 'i', $content_type_id);
			$this->gallery->content_properties->id->value = ((count($data) > 0)?($data[0]->id):(null));
		}

		$this->gallery->parent_id = $this->id->value;
		if ($read_images) {
			$this->gallery->read($read_image_keywords);
		}
		else {
			$this->gallery->readThumbnail();
		}
	}

	/**
	 * Retrieves single page and puts it in gallery array.
	 * @param int $page_id The id of the image_link record to load.
	 * @param bool $read_keywords (optional) if set to true, keywords associated with the image will also be retrieved. defaults to false.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 * @throws Exception
	 */
	public function readPage( int $page_id, bool $read_keywords=false )
	{
		$this->gallery->list = array();
		$this->gallery->list[0] =
			new ImageLink(
				$this->gallery->content_properties->image_path->value,
				$this->gallery->content_properties->param_prefix->value,
				$this->gallery->content_properties->id->value,
				$this->id,
				$page_id);
		$this->gallery->list[0]->read($read_keywords);

		$this->markLimits();
	}

	/**
	 * Saves content record, along with gallery images and keywords.
	 * @param bool $save_thumbnail (Optional) Flag to additionally save a thumbnail record (dbo: image_link) as opposed to linking to an existing image in the gallery list as this content item's thumbnail. Default TRUE.
	 * @param bool $update_cache (Optional) Flag to additionally update content cache. Default TRUE.
	 * @return string Message to display to user indicating the result of the operation.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function save (bool $save_thumbnail=true, bool $update_cache=true): string
	{
		$status = "";
		$is_new = ($this->id->value===null);

		$this->create_date->is_database_field = false;
		$this->mod_date->is_database_field = false;
		parent::save();

		if ($this->content_properties->table->value=="album" ||
			(
				$this->content_properties->table->value &&
				$this->columnExists("create_date") &&
				$this->columnExists("mod_date")
			)) {
			/* update create/mod dates */
			$query = "UPDATE `".static::getTableName()."` SET ";
			if ($is_new) {
				$query .= "create_date = NOW(), ";
			}
			$query .= "mod_date = NOW() WHERE id = ?";
			$this->query($query, 'i', $this->id->value);
		}

		if ($is_new) {
			$this->setDefaultSlotValue();
		}

		/* save all images currently loaded into this album's gallery */
		$this->gallery->parent_id = $this->id->value;
		$this->gallery->save($save_thumbnail && $is_new);

		/* link an image in the gallery as this album's thumbnail image */
		if ($is_new && $this->hasThumbnailLink()) {
			$this->saveThumbnailLink();
		}

		$this->updateFulltextKeywords();

		$status .= "The &ldquo;{$this->title->value}&rdquo; ".strtolower($this->content_properties->label)." was successfully saved. \n";

		if (method_exists($this, "updateCacheFile") && $update_cache) {
			/** TODO set the path to the cache file */
			try {
				$this->updateCacheFile('', '');
				$status .= "The cache file was updated. \n";
			}
			catch(Exception $e) {
				$status .= "Error updating the cache file. ".$e->getMessage()." \n";
			}

		}
		return ($status);
	}

	/**
	 * Sets the album thumbnail to the first image in the gallery when creating a new album
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	protected function saveThumbnailLink()
	{
		$query = "UPDATE `".static::getTableName()."` SET tn_id = ? WHERE id = ?";
		$this->query($query, 'ii', $this->gallery->list[0]->id->value, $this->id->value);
	}

	/**
	 * Updates the slot value on new album records to put the new record at the top of the list.
	 * @throws NotImplementedException
	 * @throws Exception
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
		$types_str = '';
		$vars = [];
		$query = "UPDATE `".static::getTableName()."` SET slot = IFNULL(slot,0)+1";
		if ($has_section_id) {
			$query .= " WHERE (section_id = ?)";
			$types_str .= 'i';
			$vars[] = $this->section_id->value;
		}
		array_unshift($vars, $query, $types_str);
		call_user_func_array([$this, 'query'], $vars);

		/* query to update the slot value of this record to put it at the
		 * front of the list of albums.
		 */
		$query = "UPDATE `".static::getTableName()."` SET slot = 0 WHERE id = ?";
		$this->query($query, 'i', $this->id->value);
	}

	/**
	 * Pages content type setter.
	 * @param int $content_type_id
	 * @return void
	 */
	public function setPagesContentType(int $content_type_id)
	{
		static::$pages_content_type_id = $content_type_id;
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
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	protected function testForSlotColumns ( bool &$has_slot, bool &$has_section_id )
	{
		$query = "SHOW COLUMNS ".
			"FROM `".static::getTableName()."` ".
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
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	public function updateFulltextKeywords()
	{
		/* avoid exceeding limit on GROUP_CONCAT() */
		$this->query("SET @@group_concat_max_len := @@max_allowed_packet;");

		$query = "CALL albumFulltextKeywordsUpdate(".static::getTableName().",?,?,?";
		$record_id = $this->getRecordId();
		$content_type_id = $this->getContentPropertyId();
		$gallery_content_type_id = static::getPagesContentType();
		$this->query($query, 'iii', $record_id, $content_type_id, $gallery_content_type_id);
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function validateInput ( array $exclude_properties=[] )
	{
		try {
			parent::validateInput();
		}
		catch (ContentValidationException $ex) {
			/* continue */
		}

		try {
			$this->gallery->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->validationErrors[] = $ex->getMessage();
			$this->validationErrors = array_merge($this->validationErrors, $this->gallery->validation_errors);
		}

		if ($this->slug->value) {
			try {
				$this->validateSlug();
			}
			catch (ContentValidationException $ex) {
				$this->validationErrors[] = $ex->getMessage();
			} catch (NotImplementedException $e) {
			}
		}

		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Errors were found in the album.");
		}
	}

	/**
	 * Validates the current value of the object's $slug property against existing records in the database.
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	public function validateSlug()
	{
		if ($this->id->value > 0) {
			/* get the existing slug. if they are the same then the current
			 * value of the object's $slug property doesn't need to be checked.
			 */
			$query = "SEL"."ECT `slug` FROM `".static::getTableName()."` WHERE (`id` = ?)";
			$data = $this->fetchRecords($query, 'i', $this->id->value);
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
		$query = "SEL"."ECT COUNT(1) AS `count` FROM `".static::getTableName()."` ";
		$types_str = '';
		$vars = [];
		if (property_exists($this, "section_id") && $this->section_id->value > 0) {
			$query .= "WHERE (section_id = ? ";
			$types_str .= 'i';
			$vars[] = $this->section_id->value;
		}
		$query .= ((strpos($query, "WHERE") > 0)?('AND '):('WHERE '));
		$query .= "(`slug` LIKE ?) ";
		$types_str .= 's';
		$vars[] = $this->slug->value;
		if ($this->id->value > 0) {
			$query .= "AND (`id` <> ?) ";
			$types_str .= 'i';
			$vars[] = $this->id->value;
 		}
		array_unshift($vars, $query, $types_str);
		$data = call_user_func_array([$this, 'fetchRecords'], $vars);
		if (count($data) > 0) {
			throw new ContentValidationException("A &ldquo;{$this->slug->value}&rdquo; slug already exists.");
		}
	}
}