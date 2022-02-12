<?php
namespace Littled\Ajax;

use Exception;
use Littled\Filters\ContentFilters;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\Request\StringInput;
use Throwable;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\Log\Debug;
use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Validation\Validation;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Request\IntegerInput;
use Littled\PageContent\SiteSection\ContentProperties;

/**
 * Class AjaxPage
 * @package Littled\PageContent\Ajax
 */
class AjaxPage extends MySQLConnection
{
    /** @var string Name of class to use to cache content. */
    protected static $cache_class = ContentCache::class;
    /** @var string Name of class to use as content controller. */
    protected static $controller_class = ContentController::class;
	/** @var string Path to directory containing template files */
	protected static $template_path = '';
    /** @var string Name of the default template to use in derived classes to generate markup. */
    protected static $default_template_name = '';

	/** @var string */
	const COMMIT_ACTION = 'commit';
	/** @var string */
	const CANCEL_ACTION = 'cancel';
    /** @var string */
    const TEMPLATE_TOKEN_KEY = 'templateToken';

	/** @var string String indicating the action to be taken on the page. */
	public $action='';
	/** @var SectionContent Content article. */
	public $content;
	/** @var ContentProperties Content properties. */
	public $content_properties;
	/** @var FilterCollection Content filters. */
	public $filters;
	/** @var JSONRecordResponse JSON response object. */
	public $json;
	/** @var IntegerInput Content record id. */
	public $record_id;
    /** @var StringInput Token to use to select which content template to load. Corresponds to the "name" field of the content_template table. */
    public $operation;
	/** @var ContentTemplate Current content template properties. */
	public $template=null;
	/** @var ContentRoute Current content route properties. */
	public $route=null;

	/**
	 * Class constructor.
	 */
	public function __construct ()
	{
		parent::__construct();

		/* Set exception handler to return JSON error message */
		set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));

		$this->json = new JSONRecordResponse();
		$this->record_id = new IntegerInput("Record id", LittledGlobals::ID_KEY, false);

		$this->content_properties = new ContentProperties();
        $this->operation = new StringInput('Template token', self::TEMPLATE_TOKEN_KEY, false, static::getDefaultTemplateName(), 45);
		$this->template = null;
		$this->filters = null; /* set in derived classes */
		$this->action = "";
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		foreach($this as $item) {
			if (is_object($item) || is_array($item)) {
				unset($item);
			}
		}
	}

	/**
	 * Retrieves content and filters based on page object's content type id setting.
	 * Inserts content into template and saves resulting markup in page object's "json" property.
	 * @throws Exception
	 */
	public function collectAndLoadJsonContent()
	{
		/* retrieve content object if needed */
		if (!is_object($this->content)) {
			$this->content = call_user_func_array([static::getControllerClass(), 'getContentObject'], array($this->getContentTypeId()));
		}

		/** retrieve filters object if needed */
		if (!is_object($this->filters)) {
			$this->filters = call_user_func_array([static::getControllerClass(), 'getContentFiltersObject'], array($this->getContentTypeId()));
		}
		$this->loadContentAndFiltersData();
		$this->loadTemplateContent();
	}

	/**
	 * Convenience routine that will collect the content id from POST
	 * data using first the content object's internal id parameter, and then if
	 * that value is unavailable, a default id parameter ("id").
	 * @param array|null[optional] $src Array of variables to use instead of POST data.
	 */
	public function collectContentID( $src=null )
	{
		$this->content->id->collectRequestData($src);
		if ($this->content->id->value===null && $this->content->id->key != LittledGlobals::ID_KEY) {
			$this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
		}
	}

    /**
     * Retrieves content type id from script arguments/form data and uses that value to retrieve content properties from the database.
     * @param string $key (Optional) Key used to retrieve content type id value from script arguments/form data.
     * Defaults to LittledGlobals::CONTENT_TYPE_ID.
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    public function collectContentProperties(string $key=LittledGlobals::CONTENT_TYPE_KEY )
    {
        $this->content_properties->id->value = Validation::collectIntegerRequestVar($key);
        if ($this->content_properties->id->value === null) {
            throw new ContentValidationException("Content type not specified.");
        }
        $this->content_properties->read();

        $this->operation->collectRequestData();
        if (!$this->operation->value) {
            $this->operation->value = static::getDefaultTemplateName();
        }
        $this->lookupTemplate();
    }

    /**
     * Fills out filter values from request data.
     * @throws NotImplementedException
     */
    public function collectFiltersRequestData()
    {
        $this->filters->collectFilterValues();
    }

    /**
	 * Sets the object's action property value based on value of the variable passed by the commit button in an HTML form.
	 * @param array|null[optional] $src Optional array of variables to use instead of POST data.
	 * @return AjaxPage
	 */
	public function collectPageAction( $src=null ): AjaxPage
	{
		if ($src===null) {
			/* use only POST, not GET */
			$src = &$_POST;
		}
		if (Validation::collectBooleanRequestVar(LittledGlobals::P_COMMIT, null, $src)===true) {
			$this->action = 'commit';
			return($this);
		}
		if (Validation::collectBooleanRequestVar(LittledGlobals::P_CANCEL, null, $src)===true) {
			$this->action = 'cancel';
			return($this);
		}
		return ($this);
	}

    /**
     * Error handler. Catch error and return the error message to client making ajax request.
     * @param int $err_no
     * @param string $err_str
     * @param string $err_file
     * @param ?int $err_line
     */
    public function errorHandler(int $err_no, string $err_str, string $err_file='', ?int $err_line=null)
    {
		// remove anything that might currently be in the output buffer
	    while (ob_get_level()) {
		    ob_end_clean();
	    }

		// collect information for error message
        $msg = "$err_str [$err_no]";
        $msg .= (($err_file)?(" in $err_file"):(''));
        $msg .= (($err_line)?("($err_line)"):(''));

		// populate "error" attribute of the response
        $this->json->returnError($msg);
    }

	/**
	 * Exception handler. Catch exceptions and return the error message to client making ajax request.
	 * @param Exception $ex
	 */
	public function exceptionHandler(Throwable $ex)
	{
		$this->json->returnError($ex->getMessage());
	}

    /**
     * Cache class name getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getCacheClass(): string
    {
        if (ContentCache::class === static::$cache_class) {
            throw new ConfigurationUndefinedException('Cache class not configured.');
        }
        return static::$cache_class;
    }

	/**
	 * Content label getter.
	 * @return string
	 */
	public function getContentLabel(): string
	{
		if (1 > $this->content_properties->getRecordId()) {
			return '';
		}
		return $this->content_properties->getContentLabel();
	}

    /**
     * Content type id getter.
     * @return ?int
     */
    public function getContentTypeId(): ?int
    {
        return ($this->content_properties->id->value);
    }

	/**
	 * Controller class name getter.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getControllerClass(): string
	{
		if (ContentController::class === static::$controller_class) {
			throw new ConfigurationUndefinedException('Controller class not configured.');
		}
		return static::$controller_class;
	}

	/**
     * Default token name getter.
     * @return string
     */
    public static function getDefaultTemplateName(): string
    {
        return static::$default_template_name;
    }

	/**
	 * @throws ConfigurationUndefinedException
	 */
	public function getFullTemplatePath(): string
	{
		if (''===static::$template_path) {
			throw new ConfigurationUndefinedException("Content template location is not set.");
		}
		if (!$this->template instanceof ContentTemplate) {
			throw new ConfigurationUndefinedException("Content template is not set.");
		}
		return static::$template_path.$this->template->path->value;
	}

	/**
	 * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
	 * @param array[optional] $src Array of variables to use instead of POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 */
	public function initializeContentObject( $src=null )
	{
		if ($src===null) {
			$src = &$_POST;
		}
		/* get object type from POST data */
		$class_name = Validation::collectStringRequestVar('class', null, $src);
		if (!$class_name) {
			throw new ContentValidationException("Content type not provided.");
		}
		if (!class_exists($class_name)) {
			throw new ConfigurationUndefinedException("Content type not available.");
		}

		/* instantiate object */
		$this->content = new $class_name();
	}

	/**
     * Collects filter values from request data, and reads the content data from the database.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     */
    public function loadContentAndFiltersData()
    {
        $this->collectFiltersRequestData();
        $this->retrieveContentData();
    }

	/**
	 * Inserts content into content template. Stores the resulting markup in the object's internal "json" property.
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function loadTemplateContent()
	{
		$context = array(
			'content' => $this->content,
			'filters' => $this->filters
		);
		if ($this->filters instanceof ContentFilters) {
			$context['qs'] = $this->filters->formatQueryString();
		}
		$this->json->loadContentFromTemplate($this->template->formatFullPath(), $context);
	}

	/**
	 * Looks for the route matching $route_name in the currently loaded templates. Sets the object's route
	 * property value to that route object.
	 * @param string $operation
	 * @return void
	 */
	public function lookupRoute(string $operation='')
	{
		$operation = $operation ?: $this->operation->value;
		$this->route = $this->content_properties->getContentRouteByOperation($operation);
	}

	/**
     * Looks for the template matching $template_name in the currently loaded templates. Sets the object's template
     * property value to that template object.
     * @param string $operation
     * @return void
     */
    public function lookupTemplate(string $operation='')
    {
        $operation = $operation ?: $this->operation->value;
        $this->template = $this->content_properties->getContentTemplateByName($operation);
    }

	/**
	 * Wrapper for json_response_class::load_content_from_template() preserved
	 * here for legacy reasons. Better to use the json_response_class routine directly.
	 * @param string $template_path Path to content template to use to generate markup.
	 * @param array $context Associative array of variables referenced in the template.
	 * @throws ResourceNotFoundException
	 */
	public function renderToJSON( string $template_path, array $context )
	{
		$this->json->loadContentFromTemplate($template_path, $context);
	}

    /**
     * Retrieves content data from the database
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public function retrieveContentData()
    {
        if(!is_object($this->content)) {
            return;
        }
        if ($this->record_id->value>0) {
            $this->content->id->value = $this->record_id->value;
        }
        call_user_func_array([$this::getControllerClass(), 'retrieveContentDataByType'], array($this->content));
    }

	/**
	 * Hydrates the content properties object by retrieving data from the database.
	 * @param int|null $content_type_id (Optional) The id of the content type. The instance's internal value will be updated with this value if provided.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 */
	public function retrieveContentProperties(?int $content_type_id=null)
	{
		if (0 < $content_type_id) {
			$this->setContentTypeId($content_type_id);
		}
		if (1 > $this->getContentTypeId()) {
			throw new ConfigurationUndefinedException('Content properties could not be retrieved. A content type was not specified.');
		}
        // retrieve content properties from databases
		$this->content_properties->read();

        // set the active template and route properties if an operation has been specified
        if ($this->operation->value) {
            $this->lookupRoute();
            $this->lookupTemplate();
        }
	}

	/**
	 * Renders a page content template based on the current content filter values and stores the markup in the object's $json property.
	 * @throws RecordNotFoundException
     * @throws ResourceNotFoundException|NotImplementedException
     */
	public function retrievePageContent()
	{
		$this->filters->collectFilterValues();
		$this->json->content->value = $this->content->refreshContentAfterEdit($this->filters);
	}

	/**
	 * Retrieve template properties from the database and store them in the page's template property.
	 * @param string $template_name Token indicating which type of template to retrieve: details, listings, edit, delete, etc.
	 * @throws ConfigurationUndefinedException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws Exception
	 */
	public function retrieveTemplateProperties(string $template_name)
	{
		if (!is_object($this->content)) {
			throw new ConfigurationUndefinedException("Content not set.");
		}
		if (!$this->setInternalContentTypeValue()) {
			throw new ConfigurationUndefinedException("Content properties not available.");
		}
		$this->connectToDatabase();
		$query = "CALL contentTemplateLookup(?,?)";
		$data = $this->fetchRecords($query, 'is', $this->content_properties->id->value, $template_name);
		if (count($data) < 1) {
			throw new RecordNotFoundException("\"".ucfirst($template_name)."\" template not found.");
		}
		$this->template = new ContentTemplate(
			$data[0]->id,
			$this->content_properties->id->value,
			$data[0]->name,
			$data[0]->base_path,
			$data[0]->template_path,
			$data[0]->location);
	}

	/**
	 * Sends out whatever values are currently stored within the object's "json" property as JSON.
	 */
	public function sendResponse()
	{
		$this->json->sendResponse();
	}

    /**
     * Content cache class setter.
     * @param string $class_name Name of class to use to cache ajax content. Must be derived from \Littled\PageContent\Cache\ContentCache
     * @return void
     * @throws InvalidTypeException
     */
    public static function setCacheClass(string $class_name)
    {
        $o = new $class_name;
        if(!$o instanceof ContentCache) {
            throw new InvalidTypeException("\"$class_name\" is not a valid content cache type.");
        }
        unset($o);
        static::$cache_class = $class_name;
    }

    /**
     * Content type id setter.
     * @param int $content_id
     * @return void
     */
    public function setContentTypeId(int $content_id)
    {
        $this->content_properties->id->setInputValue($content_id);
    }

	/**
	 * Content cache class setter.
	 * @param string $class_name Name of class to use as content controller. Must be derived from \Littled\PageContent\ContentController
	 * @return void
	 * @throws InvalidTypeException
	 */
	public static function setControllerClass(string $class_name)
	{
		$o = new $class_name;
		if(!$o instanceof ContentController) {
			throw new InvalidTypeException(Debug::getShortMethodName().' Invalid controller type. ');
		}
		unset($o);
		static::$controller_class = $class_name;
	}

	/**
     * Default template name setter.
     * @param string $name
     * @return void
     */
    public static function setDefaultTemplateName(string $name)
    {
        static::$default_template_name = $name;
    }

	/**
	 * Ensures that the internal content type id value has been set before its value is accessed.
	 * @return bool TRUE/FALSE depending on if a valid content type id value could be found.
	 */
	public function setInternalContentTypeValue(): bool
	{
		if ($this->content_properties->id->value>1) {
			return (true);
		}
		if (!$this->content instanceof SectionContent) {
			return (false);
		}
		$this->content_properties->id->value = $this->content->getRecordId();
		return ($this->content_properties->id->value>0);
	}
}
