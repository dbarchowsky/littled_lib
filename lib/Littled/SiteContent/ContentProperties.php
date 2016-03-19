<?php
namespace Littled\SiteContent;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use Littled\Request\StringTextFieldInput;


/**
 * Class SiteContent
 * @package Littled\SiteContent
 */
class ContentProperties extends ContentOperations
{
	/** @var StringTextFieldInput Format of images linked to the record. */
	public $format;
	/** @var BooleanCheckbox Flag indicating that the record's thumbnail is a link to a image in the gallery linked to
	 * the record (as opposed to a stand-alone image record). */
	public $gallery_thumbnail;
	/** @var IntegerInput Target height of images linked to the parent record. */
	public $height;
	/** @var StringTextFieldInput Label to use to refer to images linked to the main record. */
	public $image_label;
	/** @var StringTextFieldInput Path to the directory storing image files linked to the main record. */
	public $image_path;
	/** @var BooleanCheckbox Flag indicating that content for this content type should be cached. */
	public $is_cached;
	/** @var IntegerInput Target height for medium-sized images. */
	public $med_height;
	/** @var IntegerInput Target width for medium-sized images. */
	public $med_width;
	/** @var IntegerInput Target height for smallest-sized images. */
	public $mini_height;
	/** @var IntegerInput Target width for smallest-sized images. */
	public $mini_width;
	/** @var StringTextFieldInput Name of the content type. */
	public $name;
	/** @var StringTextFieldInput Prefix to add to variables used by the CMS to collect record data. */
	public $param_prefix;
	/** @var IntegerInput Id of a content type that serves as a parent to the current content type. */
	public $parent_id;
	/** @var StringTextFieldInput Root path to the templates serving content for the content type. */
	public $root_dir;
	/** @var StringTextFieldInput Slug used as a root for the content. */
	public $slug;
	/** @var StringTextFieldInput Path to add onto the $root_dir path. */
	public $sub_dir;
	/** @var StringTextFieldInput Name of the table in the database storing the records for this content type. */
	public $table;
	/** @var IntegerInput Target width of images linked to the main record. */
	public $width;

	const ID_PARAM = 'cpId';
	public static function TABLE_NAME()
	{
		return 'site_section';
	}

	function __construct()
	{
		parent::__construct();
		$this->id = new IntegerInput("Id", self::ID_PARAM, null, true);
		$this->format = new StringInput("Image format", 'cpFormat', '', false, 4);
		$this->gallery_thumbnail = new BooleanCheckbox("Has gallery thumbnail", "cpGallThumb", false, false);
		$this->height = new IntegerInput("Target image height", "cpImgH", null, false);
		$this->image_label = new StringTextFieldInput("Image label", "cpImgLabel", '', false, 100);
		$this->image_path = new StringTextFieldInput("Image path", "cpImgPath", '', false, 255);
		$this->is_cached = new BooleanCheckbox("Cached", "cpCached", false, false);
		$this->med_height = new IntegerInput("Medium-sized image target height", "cpMedH", null, false);
		$this->med_width = new IntegerInput("Medium-sized image target width", "cpMedW", null, false);
		$this->mini_height = new IntegerInput("Small-sized image target height", "cpMiniH", null, false);
		$this->mini_width = new IntegerInput("Small-sized image target width", "cpMiniW", null, false);
		$this->name = new StringTextFieldInput("Name", "cpName", '', true, 50);
		$this->param_prefix = new StringTextFieldInput("Parameter prefix", "cpParamPrefix", '', false, 8);
		$this->parent_id = new IntegerInput("Parent", "cpParentId", null, false);
		$this->root_dir = new StringTextFieldInput("Root path", "cpRoot", '', false, 255);
		$this->slug = new StringTextFieldInput("Slug", "cpSlug", '', false, 50);
		$this->sub_dir = new StringTextFieldInput("Sub-directory path", "cpSubPath", '', false, 255);
		$this->table = new StringTextFieldInput("Table name", "cpTable", '', true, 50);
		$this->width = new IntegerInput("Target image width", "cpImgW", null, false);
	}

	public function read()
	{
		$query = "CALL `contentPropertiesSelect`({$this->id})";
		try {
			$this->hydrateFromQuery($query);
		}
		catch (RecordNotFoundException $ex) {
			throw new RecordNotFoundException("The requested content properties could not be found.");
		}
	}
}