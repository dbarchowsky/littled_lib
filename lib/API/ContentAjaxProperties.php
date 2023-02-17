<?php
namespace Littled\API;

use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;

/**
 * This class is an interface to the section_operations table which has less and less utility since content_templates and content_routes were introduced.
 * @todo Audit to see if this class can be deleted.
 */
class ContentAjaxProperties extends SerializedContent
{
	/** @var string */
	protected static string $table_name = 'section_operations';

	/** @var IntegerInput Record id. */
	public IntegerInput $id;
	/**
	 * @var IntegerSelect Site section/content type identifier.
	 * TODO Rename this property to $content_type_id, which requires changing the name of the corresponding field
	 * in the database, and changing any SQL queries that reference that field.
	 */
	public IntegerSelect $section_id;
	/** @var StringTextField Content label. */
	public StringTextField $label;
	/** @var StringTextField Name of the variable used to pass the content type id value. */
	public StringTextField $id_param;
	/** @var ContentRoute[] All routes linked to this content type. */
	public array $routes=[];
	/** @var ContentTemplate[] All templates linked to this content type. */
	public array $templates=[];

    /**
     * @todo consider replacing hard-coded uri fields with a table linked to this one containing separate records for each possible ajax uri. See $routes and $templates property.
     */
	/** @var StringTextField URI CMS listings URI. */
	public StringTextField $listings_uri;
	/** @var StringTextField URI of AJAX listings utility script. */
	public StringTextField $ajax_listings_uri;
	/** @var StringTextField URI of AJAX record details utility script. */
	public StringTextField $details_uri;
	/** @var StringTextField URI of AJAX record editing utility script. */
	public StringTextField $edit_uri;
	/** @var StringTextField URI of AJAX record attachments upload utility script. */
	public StringTextField $upload_uri;
	/** @var StringTextField URI of AJAX record deletion utility script. */
	public StringTextField $delete_uri;
	/** @var StringTextField URI of AJAX record caching utility script. */
	public StringTextField $cache_uri;
	/** @var StringTextField URI of AJAX listings sorting utility script. */
	public StringTextField $sorting_uri;
	/** @var StringTextField URI of AJAX record keyword management utility script. */
	public StringTextField $keywords_uri;
	/** @var StringTextField Path to listings template. */
    public StringTextField $listings_template;
	/** @var StringTextField Path to keywords list template. */
	public StringTextField $keywords_template;
	/** @var StringTextarea Comments about the content type. */
	public StringTextarea $comments;
	/** @var BooleanCheckbox Flag indicating that the listings are sortable. */
	public BooleanCheckbox $is_sortable;

	/**
	 * ContentAjaxProperties constructor.
	 * @param ?int $content_type_id Initial content type id value.
	 */
	function __construct( ?int $content_type_id=null )
	{
		parent::__construct();
		$this->id = new IntegerInput("Id", "capId", false);
		$this->section_id = new IntegerSelect("Content type", LittledGlobals::CONTENT_TYPE_KEY, true, $content_type_id);
		$this->label = new StringTextField("Label", "capLabel", true, '', 50);
		$this->id_param = new StringTextField("Id parameter name", "capKeyName", true, '', 50);
		$this->listings_uri = new StringTextField("Listings URI", "capListURI", false, '', 255);
		$this->ajax_listings_uri = new StringTextField("AJAX Listings URI", "ajaxListURI", false, '', 255);
		$this->details_uri = new StringTextField("Details URI", "capDetailsURI", false, '', 255);
		$this->edit_uri  = new StringTextField("Edit URI", "capEditURI", false, '', 255);
		$this->upload_uri = new StringTextField("Upload URI", "capUploadURI", false, '', 255);
		$this->delete_uri = new StringTextField("Delete URI", "capDeleteURI", false, '', 255);
		$this->cache_uri = new StringTextField("Cache URI", "capCacheURI", false, '', 255);
		$this->sorting_uri = new StringTextField("Sorting URI", "capSortURI", false, '', 255);
		$this->keywords_uri = new StringTextField("Keywords URI", "capKeywordsURI", false, '', 255);
        $this->listings_template = new StringTextField("Listings template", "listingsTemplate", false, '', 255);
		$this->keywords_template = new StringTextField("Keywords template", "keywordsTemplate", false, '', 255);
		$this->comments = new StringTextarea("Comments", "capComments", false, '', 2000);
		$this->is_sortable = new BooleanCheckbox("Is sortable", "capIsSortable", false, false);
	}

    /**
     * Implements abstract method but throws error. When this class is used directly, it is for writing only.
     * @return array|null
     */
    public function generateUpdateQuery(): ?array
    {
        return array('Error: '.__METHOD__.' called. This class is not used for writing.');
    }

	/**
	 * Returns true/false depending on whether any data is detected in the object.
	 * @return bool TRUE if the object is holding usable data, false otherwise.
	 */
	public function hasData(): bool
	{
		return ($this->id->value>0 || $this->section_id->value>0 || ($this->label->value) || ($this->id_param->value));
	}

	/**
	 * {@inheritDoc}
     * @throws ConfigurationUndefinedException
     */
	public function pluralLabel(int $count, string $property_name='label'): string
	{
		return(parent::pluralLabel($count, $property_name));
	}

	/**
	 * Hydrates the object based on its current content id value.
	 * @throws RecordNotFoundException
	 * @throws Exception
	 */
	public function retrieveContentProperties()
	{
		try {
			$this->testForContentType();
		}
		catch(ContentValidationException $ex) {
			return;
		}
		$this->hydrateFromQuery('CALL siteSectionPropertiesSelect(?)', 'i', $this->section_id->value);
		$this->retrieveRoutes();
		$this->retrieveTemplates();
	}

	/**
	 * Hydrates the $routes property with data from database for all routes linked to this content type.
	 * @return void
	 * @throws Exception
	 */
	public function retrieveRoutes()
	{
		$this->routes = array();
		$record_id = $operation = null;
		$query = 'CALL contentRouteSelect(?,?,?)';
		$data = $this->fetchRecords($query, 'iis', $record_id, $this->section_id->value, $operation);
		foreach($data as $row) {
			$this->routes[] = new ContentRoute($row->id, $row->site_section_id, $row->operation, $row->route, $row->api_route);
		}
	}

	/**
	 * Hydrates the $templates property with data from database for all templates linked to this content type.
	 * @return void
	 * @throws Exception
	 */
	public function retrieveTemplates()
	{
		$this->templates = array();
		$query = 'CALL contentTemplateSelectBySectionId(?)';
		$data = $this->fetchRecords($query, 'i', $this->section_id->value);
		foreach($data as $row) {
			$this->templates[] = new ContentTemplate($row->id, $this->section_id->value, $row->name, '', $row->path, $row->location);
		}
	}

	/**
	 * @deprecated Use ContentAjaxProperties::retrieveContentProperties() instead.
	 * Hydrates the object based on its current content id value.
	 * @throws RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		$this->retrieveContentProperties();
	}

	/**
	 * Test for valid content type id value. Throws ContentValidationException if a value is not detected.
	 * @throws ContentValidationException
	 */
	public function testForContentType()
	{
		if ($this->section_id->value===null || $this->section_id->value < 1) {
			throw new ContentValidationException("Content type not set.");
		}
	}
}