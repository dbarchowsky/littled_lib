<?php
namespace Littled\PageContent\SiteSection;


use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;

/**
 * Class ContentTemplate
 * @package Littled\Tests\SiteContent
 */
class ContentTemplate extends SerializedContent
{
	/** @var int Value of this record in the site section table. */
	Const SECTION_ID = 33;
	/** @var string Name of the table containing data for this class. */
	const TABLE_NAME = "content_template";
	public static function TABLE_NAME() { return (self::TABLE_NAME); }

	/** @var StringTextField Template name. */
	public $name;
	/** @var IntegerInput Content type id. */
	public $content_type_id;
	/** @var string Root directory of the content type, as specified in the parent site_section table. */
	public $template_dir;
	/** @var StringTextField Relative path to the content template. */
	public $path;
	/** @var StringSelect Location of the template, e.g. local vs. shared. */
	public $location;
	/** @var IntegerInput Pointer to $site_section_id property */
	public $parentID;

	/**
	 * ContentTemplate constructor.
	 * @param null[optional] $id ID value of the record. Defaults to NULL.
	 * @param null[optional] $content_type_id Site section id of the record. Defaults to NULL.
	 * @param string[optional] $name Name of the template. Defaults to empty string.
	 * @param string[optional] $base_dir Base path where the templates are located. Defaults to empty string.
	 * @param string[optional] $path Path to the template file. Defaults to empty string.
	 * @param string[optional] $location Context in which the template is used. Defaults to empty string.
	 */
	function __construct($id=null, $content_type_id=null, $name='', $base_dir='', $path='', $location='')
	{
		parent::__construct($id);

		$this->id->label = "Template id";
		$this->id->key = 'templateID';
		$this->id->required = false;
		$this->content_type_id = new IntegerInput("Content type", "contentTypeID", true, $content_type_id);
		$this->name = new StringTextField("Name", "templateName", true, $name, 45);
		$this->template_dir = new StringTextField("Template directory", "templateDir", false, $base_dir, 200);
		$this->path = new StringTextField("Template file", "templatePath", true, $path, 255);
		$this->location = new StringSelect("Location", "templateLocation", false, $location, 20);

		/* non-default column names in database table */
		$this->template_dir->isDatabaseField = false;
		$this->content_type_id->columnName = 'site_section_id';

		/* pointer to site section id for the benefit of editing these
		 * records in the CMS */
		$this->parentID = &$this->content_type_id;

		/* ensure this has a trailing slash */
		if ($base_dir) {
			$this->template_dir->setInputValue(rtrim($base_dir, '/').'/');
		}
	}

	/**
	 * Checks the instance for data that would require the record to be saved.
	 * @return bool True/false depending on whether valid data was found in the instance.
	 */
	public function hasData()
	{
		return ($this->id->value > 0 || $this->path->value || $this->name->value);
	}

	/**
	 * Format full path to template file as a convenience, pointing it at
	 * either the common directory or local depending on the "location" value.
	 * @return string Full path to template file, taking into account if the location
	 * is set to the shared location or the local location.
	 */
	public function formatFullPath()
	{
		$path = $this->path->value;
		if ($path) {
			switch ($this->location->value)
			{
				case 'shared':
					$path = rtrim(COMMON_TEMPLATE_DIR, '/').'/'.$path;
					break;
				case 'shared-cms':
					$path = rtrim(CMS_COMMON_TEMPLATE_DIR, '/').'/'.$path;
					break;
				default:
					if ($this->template_dir->value) {
						$path = rtrim(APP_BASE_DIR, '/').'/'.trim($this->template_dir->value, '/').'/'.ltrim($path,'/');
					}
					break;
			}
		}
		return ($path);
	}

	/**
	 * Validates the data stored in the instance. Error messages are stored in the instance's $validation_errors
	 * property.
	 * @param array[optional] $exclude_properties Names of class properties to exclude from validation.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function validateInput($exclude_properties=[])
	{
		try {
			parent::validateInput(['parentID']);
		}
		catch (ContentValidationException $ex) { ; /* continue */ }

		if (!$this->template_dir->value && !$this->location->value) {
			array_push($this->validationErrors, "Either a template path or location must be specified.");
		}

		if ($this->id->value===null && $this->content_type_id->value > 0 && $this->name->value) {
			$this->connectToDatabase();
			$escaped_name = $this->name->escapeSQL($this->mysqli);
			$query = "CALL contentTemplateSectionNameSelect({$this->content_type_id->value}, {$escaped_name})";
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$error = "A \"{$this->name->value}\" template already exists for the \"{$data[0]->section}\" area of the site.";
				array_push($this->validationErrors, $error);
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Error validating content templates.");
		}
	}
}