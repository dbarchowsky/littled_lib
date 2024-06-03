<?php
namespace Littled\PageContent\Images;


use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\BooleanInput;
use Littled\Request\DateTextField;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerTextField;
use Littled\Request\PrimaryKeyInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;
use stdClass;

class ImageLink extends KeywordSectionContent
{
	protected static string $table_name = 'image_link';
	/** @var string Name of class to use to cache content. */
	protected static string $cache_class = ContentCache::class;

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

	/** @var PrimaryKeyInput $id image_link record id */
	public PrimaryKeyInput $id;
	/** @var IntegerInput $parent_id Parent record id. */
	public IntegerInput $parent_id;
	/** @var StringTextField $title Image title. */
	public StringTextField $title;
	/** @var StringTextarea $description Image description. */
	public StringTextarea $description;
	/** @var IntegerTextField $slot Position of the image record relative to other images linked to the same parent record. */
	public IntegerTextField $slot;
	/** @var IntegerTextField $page_number The page number of the image, e.g. the page number of a sketchbook that corresponds with the image. */
	public IntegerTextField $page_number;
	/** @var StringSelect $access Access level of the image, e.g. "public", "private", "disabled", etc. */
	public StringSelect $access;
	/** @var DateTextField $release_date Date after which the record will be accessible as front-end content. */
	public DateTextField $release_date;
	/** @var Image $full Full-resolution image record. */
	public Image $full;
	/** @var Image $med Medium-resolution image record. */
	public Image $med;
	/** @var Image $mini Smallest-resolution image record. */
	public Image $mini;
	/** @var IntegerInput $type_id Pointer to site_section id property. */
	public IntegerInput $type_id;
	/** @var StringInput $randomize Flag to indicate that the filename of the images should be randomized after they uploaded to the server. */
	public StringInput $randomize;
	/** @var string Name of the content type of this set of images. */
	public string $type_name;
	/** @var BooleanInput Flag indicating this record is the first image in a series of images. */
	public BooleanInput $isFirstPage;
	/** @var BooleanInput Flag indicating this record is the last image in a series of images. */
	public BooleanInput $isLastPage;

	/**
	 * ImageLink constructor.
     * @param string $image_dir
     * @param string $param_prefix
     * @param int|null $content_type_id
     * @param int|null $parent_id
     * @param int|null $id
     * @param int|null $image_id
     * @param string|null $path
     * @param int|null $width
     * @param int|null $height
     * @param string $alt_tag
     * @param string|null $url
     * @param string|null $target
     * @param string $caption
     * @param int|null $slot
     * @param string $access
     * @throws ConfigurationUndefinedException
     * @throws InvalidStateException
     */
	function __construct (
        string $image_dir = '',
        string $param_prefix = '',
        ?int $content_type_id = null,
        ?int $parent_id = null,
        ?int $id = null,
        ?int $image_id = null,
        ?string $path = null,
        ?int $width = null,
        ?int $height = null,
        string $alt_tag = '',
        ?string $url = null,
        ?string $target = null,
        string $caption = '',
        ?int $slot = null,
        string $access = 'public')
	{
		parent::__construct($id, $content_type_id, $param_prefix);
		$this->id = new PrimaryKeyInput("ID", $param_prefix.$this::vars['id'], false, $id);
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

		$this->content_properties->image_path->value = $image_dir;
		$this->content_properties->sub_dir->value = "full/";
		$this->content_properties->id->key = $param_prefix.$this::vars['content_type'];
		$this->type_id = &$this->content_properties->id;
		$this->randomize = new StringInput("Randomize filename", $this::vars['randomize_filename'], false, false);
		$this->randomize->is_database_field = false;

		$this->isFirstPage = new BooleanInput("Is first page", "ifp", false, false);
		$this->isLastPage = new BooleanInput("Is last page", "ilp", false, false);
		$this->isFirstPage->is_database_field = false;
		$this->isLastPage->is_database_field = false;
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
	 * @param array|null $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectRequestData(?array $src=null ): void
	{
		$this->collectInlineInput($src);
		$this->title->collectRequestData(null, $src);
		$this->description->collectRequestData(null, $src);
		$this->full->collectRequestData($src);
		$this->med->collectRequestData($src);
		$this->mini->collectRequestData($src);
		$this->slot->collectRequestData(null, $src);
		$this->page_number->collectRequestData(null, $src);
		$this->access->collectRequestData(null, $src);
		$this->release_date->collectRequestData(null, $src);
	}

	/**
	 * Assign values to only the object's id, parent_id and type_id properties based on script input or form data.
	 * @param array|null $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectInlineInput( ?array $src=null ): void
	{
		$this->id->collectRequestData(null, $src);
		$this->parent_id->collectRequestData(null, $src);
		if (($this->parent_id->key != $this::vars['parent_id']) &&
			($this->parent_id->value===null)) {
			/* sometimes in the case of image uploads the script doesn't know about individualized form parameters */
			$this->parent_id->collectRequestData($src, $this::vars['parent_id']);
		}
		$this->content_properties->id->collectRequestData();
		if (($this->type_id->key != $this::vars['content_type']) &&
			($this->type_id->value===null)) {
			/* sometimes in the case of image uploads the script doesn't know about individualized form parameters */
			$this->type_id->collectRequestData($src, $this::vars['content_type']);
		}
		$this->randomize->collectRequestData(null, $src);
	}

	/**
	 * Deletes image_link record along with all images records and files attached to the record.
	 * @return string Status message detailing the results of the operation.
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws Exception
	 */
	public function delete(): string
	{
		if ($this->id->value===null || $this->id->value<1) {
			throw new ContentValidationException("Id not provided.");
		}

		$status = $this::deleteLinkedImage($this->full, "full-resolution");
		$status .= $this::deleteLinkedImage($this->med, "medium-resolution");
		$status .= $this::deleteLinkedImage($this->mini, "thumbnail");

		$query = "CALL keywordDeleteLinked({$this->id->value},{$this->content_properties->id->value});";
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
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws InvalidQueryException
	 * @throws Exception
	 */
	protected static function deleteLinkedImage( Image $image, string $description): string
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
	 * @throws InvalidQueryException|Exception
	 */
	protected function deleteThumbnailLink()
	{
		/* delete the thumbnail link in the parent table if one is detected */
		if ($this->parent_id->value > 0 and $this->type_id->value > 0) {
			/**
			 * Get parent table name and flag indicating that the thumbnail
			 * points to a record in the gallery (as opposed to an image_link
			 * record independent of the gallery)
			 */
			$parent_table = '';
			$query = "CALL siteSectionParentTableSelect($this->content_properties->id->value);";
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
				$query = "CALL siteSectionThumbnailTableSelect($this->content_properties->id->value);";
				$data = $this->fetchRecords($query);
				if (count($data) > 0) {
					$parent_table = $data[0]->table;
				}
			}

			if (!$parent_table) {
				return;
			}

			/* make sure parent table has thumbnail column */
			$query = "SHOW COLUMNS FROM `$parent_table` LIKE 'tn_id'";
			$data = $this->fetchRecords($query);
			$found_tn_column = count($data) > 0;

			if ($found_tn_column) {
				/**
				 * If this content type has thumbnails pointing at records in its
				 * gallery, then use the first image uploaded into the gallery
				 * as the thumbnail.
				 */
				$query = "SELECT COUNT(1) as `count` ".
					"FROM `image_link` ".
					"WHERE `parent_id` = ? ".
					"AND `type_id` = ?";
				$content_type_id = $this->getContentPropertyId();
				$data = $this->fetchRecords($query, 'ii', $this->parent_id->value, $content_type_id);
				$page_count = $data[0]->count;

				if ($page_count===0) {
					/* Updates the parent record's thumbnail to point at the image that was just uploaded. */
					$query = "CALL thumbnailUnsetParentLink(?,?)";
					$this->query($query, 'si', $parent_table, $this->parent_id->value);
				}
				else {
					$query = "CALL thumbnailUpdateParentWithImageLink(?,?,?,?)";
					$content_type_id = $this->getContentPropertyId();
					$this->query($query,
						'siii',
						$parent_table,
						$this->parent_id->value,
						$this->id->value,
						$content_type_id);
				}
			}
		}
	}

	/**
	 * Wrapper around SQL statement to insert new image_link record in the database
	 * @throws InvalidQueryException|Exception
	 */
	protected function executeInsertQuery()
	{
		$query = "INS"."ERT INTO `image_link` ".
			"(`fullres_id`,`med_id`,`mini_id`,`parent_id`,`type_id`,`slot`,`page_number`,`access`,".
			"`title`,`description`,`release_date`) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
		$this->query($query,
		'iiiiiiissss',
			$this->full->id->value,
			$this->med->id->value,
			$this->mini->id->value,
			$this->parent_id->value,
			$this->content_properties->id->value,
			$this->slot->value,
			$this->page_number->value,
			$this->access->value,
			$this->title->value,
			$this->description->value,
			$this->release_date->value);
		$this->id->value = $this->retrieveInsertID();
	}

	/**
	 * Wrapper around SQL statement to update existing image_link record in the database.
	 * @throws InvalidQueryException|Exception
	 */
	protected function executeUpdateQuery()
	{
		$query = "UPDATE `image_link` SET ".
			"`fullres_id` = ?, ".
			"`med_id` = ?, ".
			"`mini_id` = ?, ".
			"`parent_id` = ?, ".
			"`type_id` = ?, ".
			"`title` = ?, ".
			"`description` = ?, ".
			"`slot` = ?, ".
			"`page_number` = ?, ".
			"`access` = ?, ".
			"`release_date` = ? ".
			"WHERE `id` = ?";
		$this->query($query,
		'iiiiissiissi',
			$this->full->id->value,
			$this->med->id->value,
			$this->mini->id->value,
			$this->parent_id->value,
			$this->content_properties->id->value,
			$this->title->value,
			$this->description->value,
			$this->slot->value,
			$this->page_number->value,
			$this->access->value,
			$this->release_date->value,
			$this->id->value);
	}

	/**
	 * Gets thumbnail data for thumbnails linked to an album.
	 * @param int $parent_id ID of the parent record to which the thumbnails are linked.
	 * @param int[optional] $limit Number of records to return. Defaults to 5.
	 * @return array Thumbnail data
	 * @throws InvalidQueryException|Exception
	 */
	public static function fetchPageThumbnails(int $parent_id, int $limit=5 ): array
	{
		$conn = new MySQLConnection();
		$query = "CALL imageLinkPageThumbnailsSelect($parent_id,$limit)";
		return ($conn->fetchRecords($query));
	}

	/**
	 * Populate object's property values from a database recordset.
	 * @param stdClass $data Recordset containing values.
	 */
	public function fillFromRecordset( stdClass $data)
	{
		if (property_exists($data, 'parent_id')) {
			$this->parent_id->value = $data->parent_id;
		}
		if (property_exists($data, 'type_id')) {
			$this->content_properties->id->value = $data->type_id;
		}
		if (property_exists($data, 'type_name')) {
			$this->type_name = $data->type_name;
		}
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
		if (property_exists($data, 'slot')) {
			$this->slot->value = $data->slot;
		}
		if (property_exists($data, 'page_number')) {
			$this->page_number->value = $data->page_number;
		}
		if (property_exists($data, 'access')) {
			$this->access->value = $data->access;
		}
		if (property_exists($data, 'release_date')) {
			$this->release_date->value = date('n/j/Y', strtotime($data->release_date));
		}
	}

	/**
	 * Returns the content type id of the parent of this ImageLink record.
	 * @return int|null Parent content type id.
	 * @throws RecordNotFoundException
	 */
	public function getParentContentTypeID(): ?int
	{
		return ($this->content_properties->getParentTypeID());
	}

	/**
	 * @inheritDoc
	 */
	public function hasData(): bool
	{
		return ($this->id->value>0 || $this->full->id->value>0 || $this->full->path->value);
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function read( bool $read_keywords=true )
	{
		$this->connectToDatabase();
		$query = "CALL imageLinkSelect(".$this->id->escapeSQL($this->mysqli).
			",".$this->parent_id->escapeSQL($this->mysqli).
			",".$this->content_properties->id->escapeSQL($this->mysqli).");";
		$data = $this->fetchRecords($query);
		$this->fillFromRecordset($data[0]);

		$this->retrieveSectionProperties();
		if ($read_keywords) {
			$this->readKeywords();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function retrieveSectionProperties()
	{
		parent::retrieveSectionProperties();

		$this->full->image_dir = $this->content_properties->image_path->value;
		if ($this->content_properties->param_prefix->value) {
			$this->setPrefix($this->content_properties->param_prefix->value);
		}
	}

	/**
	 * Upload images attached to the object, and save their properties in the database.
	 * @param bool $save_keywords (optional) Update keywords for the record. Defaults to true.
	 * @param bool $randomize_filename (Optional) flag if set to true the new image file will be given a randomized filename
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function save(bool $save_keywords=true, bool $randomize_filename=false )
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
			$this->full->extractKeywords($this->keywords, $this->id->value, $this->content_properties->id->value);
			$this->saveKeywords();
		}
	}

	/**
	 * Adds to parent's save_keywords routine to also save a cached set of the keywords in a single column of the image_link record to be used with fulltext searches.
	 * Also updates parent's keywords if the parent object has a keyword cache.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
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
	public function setPrefix( string $prefix )
	{
		foreach($this::vars as $property => $default_name) {
			if (property_exists($this, $property) && $this->$property instanceof RequestInput) {
				$this->$property->key = $prefix.$default_name;
			}
		}
		$this->title->key = ImageBase::vars['alt'];
		$this->description->key = ImageBase::vars['caption'];
		$this->content_properties->id->key = $this::vars['content_type'];
		$this->full->setPrefix($prefix);
		$this->med->setPrefix($prefix.'md');
		$this->mini->setPrefix($prefix.'mn');
	}

	/**
	 * updates the destination directory for all the versions of the image
	 * @param string $path Path to the image upload directory (relative to the web image root).
	 */
	public function setImageDestinationPath( string $path )
	{
		$this->full->image_dir = $path;
		$this->med->image_dir = $path;
		$this->mini->image_dir = $path;
		$this->content_properties->image_path->value = $path;
	}

	/**
	 * Sets the values of the object's "thumbnail" size to the values passed in to the function.
	 * @param int $id Thumbnail image id.
	 * @param string $path Thumbnail image path.
	 * @param int $width Thumbnail image width.
	 * @param int $height Thumbnail image height.
	 */
	public function setThumbnail (int $id, string $path, int $width, int $height)
	{
		$this->mini->id->value = $id;
		$this->mini->path->value = $path;
		$this->mini->width->value = $width;
		$this->mini->height->value = $height;
	}

	/**
	 * Sets the value of the image label. Updates the image_link field as well
	 * as all the children image objects.
	 * @param string $title The label for the image.
	 */
	public function setTitle ( string $title )
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
	 * @throws Exception
	 */
	public function updateFulltextKeywords()
	{
		$query = "CALL imageLinkUpdateKeywords(?);";
		$this->query($query, 'i', $this->id->value);

		/**
		 * The logic here may have gotten garbled here when refactored from common_lib. Not 100% sure what the
		 * goal of this logic is.
		 */
		if ($this->parent_id->value>0 && $this->content_properties->getParentTypeID()) {
			ContentCache::updateKeywords($this->parent_id->value, $this->getContentPropertyId());
		}
	}

	/**
	 * Upload and process each of the images attached to this object,
	 * including operations such as extracting keywords, resizing, and renaming.
	 * @param bool[optional] $randomize_filename Optional flag if set to true the new image file will be given a randomized filename. Defaults to FALSE.
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 */
	public function upload(bool $randomize_filename=false )
	{
		if (!isset($_FILES[$this->full->path->key])) {
			return;
		}

		$this->connectToDatabase();
		$make_thumbnail = ($_FILES[$this->full->path->key]["name"]);

		$target_dims = new ImageDims($this->content_properties->width->value, $this->content_properties->height->value);
		$this->full->save($target_dims, null, $this->content_properties->sub_dir->value, null, $randomize_filename);

		if ($make_thumbnail && ($this->content_properties->med_width->value>0 || $this->content_properties->med_height->value>0)) {
			$medium_dims = new ImageDims($this->content_properties->med_width->value, $this->content_properties->med_height->value);
			$this->med->id->value = $this->full->makeThumbnailCopy(basename($this->full->path->value), $medium_dims, "jpg", "med/", "med_id");
		}

		if ($make_thumbnail && $this->content_properties->save_mini->value && ($this->content_properties->mini_width->value>0 || $this->content_properties->mini_height->value>0)) {
			$mini_dims = new ImageDims($this->content_properties->mini_width->value, $this->content_properties->mini_height->value);
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
		$this->content_properties->id->required = true;
		try {
			$this->parent_id->validate();
		}
		catch (ContentValidationException $ex) {
			$this->validationErrors[] = $ex->getMessage();
		}
		try {
			$this->content_properties->id->validate();
		}
		catch (ContentValidationException $ex) {
			$this->validationErrors[] = $ex->getMessage();
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
			/* continue */
		}
		try {
			$this->full->validateInput();
		}
		catch(ContentValidationException $ex) {
			$this->addValidationError($this->full->validationErrors());
		}
		if (count($this->validationErrors()) > 0) {
			throw new ContentValidationException('Errors found in image set.');
		}
	}
}