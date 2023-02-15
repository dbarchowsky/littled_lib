<?php
namespace Littled\API;

use Exception;
use Throwable;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\PageContent;
use Littled\PageContent\PageContentBase;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;


/**
 * Extends PageContent to add a JSONRecordResponse property used to convert the page content from the content normally sent as an HTML response to content sent as JSON.
 * @todo rename APIRoute
 */
class AjaxPage extends PageContentBase
{
    /** @var string */
    public const TEMPLATE_TOKEN_KEY = 'templateToken';

    /** @var string Name of a \Littled\PageContent\Cache\ContentCache class to use to cache content. */
    protected static string $cache_class = ContentCache::class;
    /** @var string Name a \Littled\PageContent\ContentController class to use as content controller. */
    protected static string $controller_class = ContentController::class;
    /** @var string Name of the default template to use in derived classes to generate markup. */
    protected static string $default_template_dir='';
    protected static string $default_template_name = '';
    /** @var string Input stream of API client requests */
    protected static string $ajax_input_stream = 'php://input';

	/** @var string String indicating the action to be taken on the page. */
	public string               $action='';
	/**
	 * @var mixed Content article.
	 * @todo Audit this property to see if it could be replaced with PageContent::$content
	 */
	public                      $content;
	protected ?array            $context;
	/** @var ContentProperties Content properties. */
	public ContentProperties    $content_properties;
	/** @var JSONRecordResponse JSON response object. */
	public JSONRecordResponse   $json;
	/** @var IntegerInput Content record id. */
	public IntegerInput         $record_id;
    /** @var StringInput Token to use to select which content template to load. Corresponds to the "name" field of the content_template table. */
    public StringInput          $operation;
	/** @var ?ContentTemplate Current content template properties. */
	public ?ContentTemplate     $template;
	/** @var ?ContentRoute Current content route properties. */
	public ?ContentRoute        $route;

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

		$this->content_properties = $this->newContentPropertiesInstance();
        $this->operation = new StringInput('Template token', self::TEMPLATE_TOKEN_KEY, false, static::getDefaultTemplateName(), 45);
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
		if (!isset($this->filters)) {
			$this->filters = call_user_func_array([static::getControllerClass(), 'getContentFiltersObject'], array($this->getContentTypeId()));
		}
		$this->loadContentAndFiltersData();
		$this->loadTemplateContent();
	}

	/**
	 * Convenience routine that will collect the content id from POST
	 * data using first the content object's internal id parameter, and then if
	 * that value is unavailable, a default id parameter ("id").
	 * @param ?array $src Optional array of variables to use instead of POST data.
	 */
	public function collectContentID( ?array $src=null )
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
     * @throws RecordNotFoundException
     */
    public function collectContentProperties(string $key=LittledGlobals::CONTENT_TYPE_KEY)
    {
        // use ajax request data by default
        $ajax_rd = AjaxPage::getAjaxClientRequestData();

		if (!$this->content_properties->id->value) {
			$this->content_properties->id->value = Validation::collectIntegerRequestVar($key, null, $ajax_rd);
		}
        if ($this->content_properties->id->value === null) {
            throw new ContentValidationException("Content type not specified.");
        }
        $this->content_properties->read();

		$saved = $this->operation->value;
		$this->operation->collectRequestData($ajax_rd);
		if(!$this->operation->value) {
			$this->operation->value = $saved;
		}
        if (!$this->operation->value) {
            $this->operation->value = static::getDefaultTemplateName();
        }
        $this->lookupTemplate();
    }

    /**
     * Fills out filter values from request data.
     * ?array $src Optional array containing request data that will be used as the default source of request data of GET and POST data.
     * @throws NotImplementedException
     */
    public function collectFiltersRequestData( ?array $src=null )
    {
        $this->filters->collectFilterValues(true, [], $src);
    }

    /**
	 * Sets the object's action property value based on value of the variable passed by the commit button in an HTML form.
	 * @param ?array $src Optional array of variables to use instead of POST data.
	 * @return AjaxPage
	 */
	public function collectPageAction( ?array $src=null ): AjaxPage
	{
		if ($src===null) {
			/* use only POST, not GET */
			$src = $_POST;
            if (!is_array($src) || count($src) < 1) {
                $json = file_get_contents(static::$ajax_input_stream);
                if (!$json) {
                    return $this;
                }
                $src = (array)json_decode($json);
            }
		}
		if (Validation::collectBooleanRequestVar(LittledGlobals::COMMIT_KEY, null, $src)===true) {
			$this->action = self::COMMIT_ACTION;
			return($this);
		}
		if (Validation::collectBooleanRequestVar(LittledGlobals::CANCEL_KEY, null, $src)===true) {
			$this->action = self::CANCEL_ACTION;
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
	 * Fetches the properties of the template matching the object's content type and the name of the template passed to the method.
	 * @param string $name
	 * @return void
	 * @throws RecordNotFoundException
	 * @throws Exception
	 */
	public function fetchContentTemplate(string $name)
	{
		$data = $this->fetchRecords('CALL contentTemplateLookup(?,?)', 'is', $this->content_properties->id->value, $name);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Content template \"$name\" not found.");
		}
		$this->template = new ContentTemplate(
			$data[0]->id,
			$this->getContentTypeId(),
			$data[0]->name,
			$data[0]->base_path,
			$data[0]->template_path,
			$data[0]->location
		);
	}

    /**
     * Read the AJAX input stream. Convert its contents into an array of variables containing client request variables.
     * Returns NULL instead of an empty array if no variables are found in the input stream. The result of this method
     * can then be used as the default source of request variables for request collection routines that normally default
     * to using GET and POST data ahead of AJAX input streams.
     * @return array|null
     */
    protected static function getAjaxClientRequestData(): ?array
    {
        $data = Validation::getAjaxClientRequestData();
        if (count($data)===0) {
            $data = null;
        }
        return $data;
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
    public static function getDefaultTemplateDir(): string
    {
        return static::$default_template_dir;
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
     * @inheritDoc
	 * @throws ConfigurationUndefinedException
	 * @throws Exception
	 */
	public function getTemplatePath(): string
	{
		if (!isset($this->template)) {
			throw new ConfigurationUndefinedException("Content template is not set.");
		}
		if (!static::getDefaultTemplateDir()) {
			return $this->template->formatFullPath();
		}
		return LittledUtility::joinPaths(static::getDefaultTemplateDir(), $this->template->path->value);
	}

	/**
	 * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
	 * @param ?array $src Optional array of variables to use instead of POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 */
	public function initializeContentObject( ?array $src=null )
	{
		if ($src===null) {
			$src = &$_POST;
		}
		/* get object type from POST data */
		$class_name = Validation::collectStringRequestVar('class', FILTER_UNSAFE_RAW, null, $src);
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
	 * @param array|null $context Optional array containing data to inject into the template.
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function loadTemplateContent(?array $context=null)
	{
		$this->context = $context;
		if ($this->context===null) {
			$this->setTemplateContext();
		}
		$this->json->loadContentFromTemplate($this->getTemplatePath(), $this->context);
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
     * Returns new ContentProperties instance. Can be used in derived classes to provide customized ContentProperties objects to the AjaxPage class's methods.
     * @param int|null $record_id Initial content type record id value.
     * @return ContentProperties
     */
    protected function newContentPropertiesInstance(?int $record_id=null): ContentProperties
    {
        return new ContentProperties($record_id);
    }

    /**
     * Returns instance of a PageContent class used to render front-end content.
     * @return PageContent
     * @throws ConfigurationUndefinedException|InvalidValueException
     * @throws InvalidQueryException
     */
	protected function newRoutedPageContentInstance(): PageContent
	{
        if (!isset($this->content_properties)) {
            throw new ConfigurationUndefinedException('Content properties not loaded.');
        }
        $p = &$this->content_properties;
        if (!isset($p->routes) || count($p->routes) < 1) {
            $p->readRoutes();
        }
        $route_parts = $p->getContentRouteByOperation('listings')->getPropertyValue(ContentRoute::PROPERTY_TOKEN_ROUTE_AS_ARRAY);
		$rpc_class = call_user_func([static::getControllerClass(), 'getRoutedPageContentClass'], $route_parts);
		return new $rpc_class();
	}

    /**
     * Returns new ContentTemplate instance. Can be used in derived classes to provide customized ContentTemplate objects to the AjaxPage class's methods.
     * @param int|null $record_id
     * @param int|null $content_type_id
     * @param string $operation
     * @param string $base_dir
     * @param string $template
     * @param string $location
     * @return ContentTemplate
     */
    protected function newTemplateInstance(?int $record_id=null, ?int $content_type_id=null, string $operation='', string $base_dir='', string $template='', string $location=''): ContentTemplate
    {
        return new ContentTemplate($record_id, $content_type_id, $operation, $base_dir, $template, $location);
    }

    /**
     * Refresh content after performing an AJAX edit on a record. The markup that is generated is stored in the class's json property's content property, which is then sent back to the client.
     * @param string $next_operation Token determining which template to load.
     * @throws Exception
     */
    public function refreshContentAfterEdit (string $next_operation)
    {
        $template = $this->newTemplateInstance();
        $template->retrieveUsingContentTypeAndOperation($this->getContentTypeId(), $next_operation);
        $this->json->loadContentFromTemplate($template->formatFullPath(), array(
			'page' => $this->newRoutedPageContentInstance(),
            'content' => &$this->content,
            'filters' => &$this->filters
        ));
    }

    /**
     * Wrapper for json_response_class::load_content_from_template() preserved
     * here for legacy reasons. Better to use the json_response_class routine directly.
     * @throws ResourceNotFoundException
     * @throws ConfigurationUndefinedException|InvalidValueException
     * @throws InvalidQueryException
     */
	public function render(?array $context=null)
	{
		$this->context = $context;
		if ($this->context===null) {
			$this->setTemplateContext();
		}
		$this->json->loadContentFromTemplate($this->template_path, $this->context);
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
		if ($this->content->id->value===null || $this->content->id->value < 1) {
			throw new ConfigurationUndefinedException('A record id was not provided.');
		}
        call_user_func_array([$this::getControllerClass(), 'retrieveContentDataByType'], array($this->content));
    }

	/**
	 * Loads the content object and uses the internal record id property value to hydrate the object's property value from the database.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 */
	public function retrieveContentObjectAndData()
	{
		$this->content = call_user_func_array([static::getControllerClass(), 'getContentObject'], array($this->getContentTypeId()));
		$this->retrieveContentData();
	}

	/**
	 * Hydrates the content properties object by retrieving data from the database.
	 * @param int|null $content_type_id (Optional) The id of the content type. The instance's internal value will be updated with this value if provided.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
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
		$this->template = $this->newTemplateInstance(
			$data[0]->id,
			$this->getContentTypeId(),
			$data[0]->name,
			$data[0]->base_path,
			$data[0]->template_path,
			$data[0]->location);
	}

	/**
	 * Sends out whatever values are currently stored within the object's "json" property as JSON.
	 */
	public function sendResponse(string $template_path='', ?array $context=null)
	{
		$this->json->sendResponse();
	}

	/**
	 * Send current JSON content value as plain text.
	 * @param string $response Text to send as a response, if not using value stored in JSON property.
	 * @return void
	 */
	public function sendTextResponse(string $response='')
	{
		header("Content-Type: text/plain\n\n");
		print($response ?: $this->json->content->value);
	}

    /**
     * API input stream setter.
     * @param string $input_stream
     * @return void
     */
    public static function setAjaxInputStream(string $input_stream)
    {
        static::$ajax_input_stream = $input_stream;
    }

    /**
     * Content cache class setter.
     * @param string $class_name Name of class to use to cache ajax content. Must be derived from \Littled\PageContent\Cache\ContentCache
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setCacheClass(string $class_name)
    {
        if($class_name === ContentCache::class) {
            throw new ConfigurationUndefinedException('Cache type must inherit from base cache type.');
        }
        if(!is_a($class_name, ContentCache::class, true)) {
            throw new InvalidTypeException("\"$class_name\" is not a valid content cache type.");
        }
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
     * @throws ConfigurationUndefinedException
     */
	public static function setControllerClass(string $class_name)
	{
        if ($class_name===ContentController::class) {
            throw new ConfigurationUndefinedException('Controller type must be derived from base controller type.');
        }
		if(!is_a($class_name, ContentController::class, true)) {
			throw new InvalidTypeException(Log::getShortMethodName().' Invalid controller type. ');
		}
		unset($o);
		static::$controller_class = $class_name;
	}

    /**
     * Default template directory path setter.
     * @param string $path
     * @return void
     */
    public static function setDefaultTemplateDir(string $path)
    {
        static::$default_template_dir = $path;
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
			return true;
		}
		if (!isset($this->content)) {
			return false;
		}
		$this->content_properties->id->value = $this->content->getRecordId();
		return ($this->content_properties->id->value>0);
	}

	/**
	 * Sets the data to be injected into templates.
	 * @throws ConfigurationUndefinedException|InvalidValueException|InvalidQueryException
     */
	public function setTemplateContext()
	{
		$this->context = array(
			'page_data' => $this->newRoutedPageContentInstance(),
			'content' => &$this->content,
			'filters' => null
		);
		if (isset($this->filters)) {
			$this->context['filters'] = &$this->filters;
            $this->context['qs'] = $this->filters->formatQueryString();
		}
	}
}
