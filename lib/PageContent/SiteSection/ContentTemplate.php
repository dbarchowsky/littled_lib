<?php
namespace Littled\PageContent\SiteSection;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Exception;
use Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentTestHarness;

/**
 * Class ContentTemplate
 * @package Littled\Tests\SiteContent
 */
class ContentTemplate extends SerializedContentTestHarness
{
	/** @var int Value of this record in the site section table. */
	protected static $content_type_id = 33;
	/** @var string */
	protected static $table_name = "content_template";

	/** @var StringTextField Template name. */
	public $name;
	/** @var IntegerInput Content type id. */
	public $content_id;
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
		$this->content_id = new IntegerInput("Content type", "contentTypeID", true, $content_type_id);
		$this->name = new StringTextField("Name", "templateName", true, $name, 45);
		$this->template_dir = new StringTextField("Template directory", "templateDir", false, $base_dir, 200);
		$this->path = new StringTextField("Template file", "templatePath", true, $path, 255);
		$this->location = new StringSelect("Location", "templateLocation", false, $location, 20);

		/* non-default column names in database table */
		$this->template_dir->isDatabaseField = false;
		$this->content_id->columnName = 'site_section_id';

		/* pointer to site section id for the benefit of editing these
		 * records in the CMS */
		$this->parentID = &$this->content_id;

		/* ensure this has a trailing slash */
		if ($base_dir) {
			$this->template_dir->setInputValue(rtrim($base_dir, '/').'/');
		}
	}

	/**
	 * Checks the instance for data that would require the record to be saved.
	 * @return bool True/false depending on whether valid data was found in the instance.
	 */
	public function hasData(): bool
	{
		return ($this->id->value > 0 || $this->path->value || $this->name->value);
	}

	/**
	 * Format full path to template file as a convenience, pointing it at
	 * either the common directory or local depending on the "location" value.
	 * @return string Full path to template file, taking into account if the location
	 * is set to the shared location or the local location.
     * @throws Exception
	 */
	public function formatFullPath(): string
	{
        $app_root = ((defined('APP_BASE_DIR'))?(APP_BASE_DIR):(''));

		$path = $this->path->value;
		if ($path) {
			switch ($this->location->value)
			{
				case 'shared':
					$path = rtrim(LittledGlobals::getTemplatePath(), '/').'/'.$path;
					break;
				case 'shared-cms':
					$path = rtrim(LittledGlobals::getCMSTemplatePath(), '/').'/'.$path;
					break;
				default:
                    $path = (($app_root)?(rtrim($app_root, '/').'/'):('')).
                        (($this->template_dir->value)?(trim($this->template_dir->value, '/').'/'):('')).
                        ltrim($path,'/');
					break;
			}
		}
		return ($path);
	}

    /**
     * Tests for any existing records in the database that would conflict with the
     * property values of this object instance.
     * @return string Name of content type matching the object's content_type_id property value
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
    public function testForDuplicateTemplate(): string
    {
        if (null === $this->id->value &&
            0 < $this->content_id->value &&
            $this->name->value) {
            $this->connectToDatabase();
            $query = "CALL contentTemplateSectionNameSelect(?,?)";
            $data = $this->fetchRecords($query, 'is', $this->content_id->value, $this->name->value);
            if (0 < count($data)) {
                return $data[0]->section;
            }
        }
        return '';
    }

	/**
	 * Validates the data stored in the instance. Error messages are stored in the instance's $validation_errors
	 * property.
	 * @param string[] $exclude_properties (Optional) Names of class properties to exclude from validation.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws Exception
     */
	public function validateInput(array $exclude_properties=[])
	{
		try {
			parent::validateInput(['parentID']);
		}
		catch (ContentValidationException $ex) { /* continue */ }

		if (!$this->template_dir->value && !$this->location->value) {
			$this->validationErrors[] = "Either a template path or location must be specified.";
		}

        if ($section = $this->testForDuplicateTemplate()) {
            $error = "A \"{$this->name->value}\" template already exists for the \"$section\" area of the site.";
            $this->validationErrors[] = $error;
        }

		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Error validating content templates.");
		}
	}
}