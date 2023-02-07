<?php
namespace Littled\PageContent\SiteSection;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Exception;
use Littled\Utility\LittledUtility;

/**
 * Class ContentTemplate
 * @package Littled\Tests\SiteContent
 */
class ContentTemplate extends SerializedContent
{
	/** @var int Value of this record in the site section table. */
	protected static int $content_type_id = 33;
	/** @var string */
	protected static string $table_name = "content_template";
	protected static string $local_path_token = 'local';

	/** @var StringTextField Template name. */
	public StringTextField $name;
	/** @var IntegerInput Content type id. */
	public IntegerInput $content_id;
	/** @var StringTextField Root directory of the content type, as specified in the parent site_section table. */
	public StringTextField $template_dir;
	/** @var StringTextField Relative path to the content template. */
	public StringTextField $path;
	/** @var StringSelect Location of the template, e.g. local vs. shared. */
	public StringSelect $location;
	/** @var IntegerInput Pointer to $site_section_id property */
	public IntegerInput $parentID;

	/**
	 * ContentTemplate constructor.
	 * @param int|null $id ID value of the record. Defaults to NULL.
	 * @param int|null $content_type_id Site section id of the record. Defaults to NULL.
	 * @param string $name Name of the template. Defaults to empty string.
	 * @param string $base_dir Base path where the templates are located. Defaults to empty string.
	 * @param string $path Path to the template file. Defaults to empty string.
	 * @param string $location Context in which the template is used. Defaults to empty string.
	 */
	function __construct(?int $id=null, ?int $content_type_id=null, string $name='', string $base_dir='', string $path='', string $location='')
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
		$this->template_dir->is_database_field = false;
		$this->content_id->column_name = 'site_section_id';

		/* pointer to site section id for the benefit of editing these
		 * records in the CMS */
		$this->parentID = &$this->content_id;

		/* ensure this has a trailing slash */
		if ($base_dir) {
			$this->template_dir->setInputValue(rtrim($base_dir, '/').'/');
		}
	}

	/**
	 * @inheritDoc
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
		$template_dir = '';
		if ($this->path->value) {
            if ($this->template_dir->value) {
                return LittledUtility::joinPathParts(array($this->template_dir->value, $this->path->value));
            }
            switch ($this->location->value) {
	            case static::getLocalPathToken():
                    $template_dir = LittledGlobals::getLocalTemplatesPath();
                    break;
                default:
                    $template_dir = LittledGlobals::getSharedTemplatesPath();
                    break;
            }
		}
		return LittledUtility::joinPathParts(array($template_dir, $this->path->value));
	}

	/**
	 * @inheritDoc
	 */
	public function generateUpdateQuery(): ?array
	{
		return null;
	}

	/**
	 * Local path token getter.
	 * @return string
	 */
	public static function getLocalPathToken(): string
	{
		return static::$local_path_token;
	}

    /**
     * Looks up and retrieves template properties from the database using section type id and operation name.
     * @param int|null $content_type_id
     * @param string $operation
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function retrieveUsingContentTypeAndOperation(?int $content_type_id=null, string $operation='')
    {
        $content_type_id = $content_type_id ?: $this->content_id->value;
        $operation = $operation ?: $this->name->value;

        if ($content_type_id===null || $content_type_id < 1) {
            throw new ConfigurationUndefinedException('['.Log::getShortMethodName().'] Content type not provided.');
        }
        if (!$operation) {
            throw new ConfigurationUndefinedException('['.Log::getShortMethodName().'] Operation not provided.');
        }
        $data = $this->fetchRecords('CALL contentTemplateLookup(?,?)',
            'is',
            $content_type_id,
            $operation);
        if (count($data) < 1) {
            throw new RecordNotFoundException('['.Log::getShortMethodName().'] '.ucfirst($this->name->value).' template not found.');
        }
        $this->id->value = $data[0]->id;
        $this->path->value = $data[0]->template_path;
        $this->location->value = $data[0]->location;
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