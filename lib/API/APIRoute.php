<?php
namespace Littled\API;

use Error;
use Exception;
use Littled\App\AppBase;
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
use Littled\Request\StringInput;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;


/**
 * Extends PageContent to add a JSONRecordResponse property used to convert the page content from the content normally sent as an HTML response to content sent as JSON.
 */
abstract class APIRoute extends PageContentBase
{
    /** @var string */
    public const                TEMPLATE_TOKEN_KEY = 'templateToken';

    /** @var string             Name of a \Littled\PageContent\Cache\ContentCache class to use to cache content. */
    protected static string     $cache_class = ContentCache::class;
    /** @var string             Name a \Littled\PageContent\ContentController class to use as content controller. */
    protected static string     $controller_class = ContentController::class;
    /** @var string             Name of the default template to use in derived classes to generate markup. */
    protected static string     $default_template_dir='';
    protected static string     $default_template_name = '';

	/** @var string             String indicating the action to be taken on the page. */
	public string               $action='';
	/** @var JSONRecordResponse JSON response object. */
	public JSONRecordResponse   $json;
    /** @var StringInput        Token to use to select which content template to load. Corresponds to the "name" field of the content_template table. */
    public StringInput          $operation;
	/** @var ?ContentTemplate   Current content template properties. */
	public ?ContentTemplate     $template;
	/** @var ?ContentRoute      Current content route properties. */
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
	abstract public function collectAndLoadJsonContent();

    /**
     * Retrieves content type id from script arguments/form data and uses that value to retrieve content properties from the database.
     * @param string $key (Optional) Key used to retrieve content type id value from script arguments/form data.
     * Defaults to LittledGlobals::CONTENT_TYPE_ID.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
    public function collectContentProperties(string $key=LittledGlobals::CONTENT_TYPE_KEY)
    {
	    // use ajax request data by default
        $ajax_rd = static::getAjaxRequestData();

		$cp = $this->getContentProperties();
		if (!$cp->id->value) {
            $content_type_id = Validation::collectIntegerRequestVar($key, null, $ajax_rd);
            if ($content_type_id===null) {
                throw new ContentValidationException("Content type not specified.");
            }
			$this->setContentTypeId($content_type_id);
		}
        if ($this->getContentTypeId() === null) {
            throw new ContentValidationException("Content type not specified.");
        }
        $this->getContentProperties()->read();

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
     * Assigns filter values from client request data.
     * @param ?array $src Optional array containing client data to use to populate filter values.
     * @param ?int $content_type_id Optional content type numerical identifier that will be assigned as any new filter collection instances' content type.
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     */
    public function collectFiltersRequestData(?array $src=null, ?int $content_type_id=null)
    {
		if ($src === null) {
			$src = self::getAjaxRequestData() ?: $_POST;
		}
        if (!isset($this->filters)) {
            if (!$content_type_id) {
                throw new ConfigurationUndefinedException('Content type not provided.');
            }
            $this->initializeFiltersObject($content_type_id);
        }
        $this->filters->collectFilterValues(true, [], $src);
    }

	/**
	 * Sets the object's action property value based on value of the variable passed by the commit button in an HTML form.
	 * @param ?array $src Optional array of variables to use instead of POST data.
	 * @return APIRoute
	 */
	public function collectPageAction( ?array $src=null ): APIRoute
	{
		if ($src===null) {
			/* use only POST, not GET */
			$src = $_POST;
			if (!is_array($src) || count($src) < 1) {
				$json = file_get_contents(static::getAjaxInputStream());
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
	 * Fills out input values from request data.
	 * @param ?array $src Optional array containing request data that will be used as the default source of request data of GET and POST data.
	 */
	public function collectRequestData( ?array $src=null )
	{
		$this->operation->collectRequestData($src);
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
		$query = 'CALL contentTemplateLookup(?,?)';
		$content_type_id = $this->getContentTypeId();
		$data = $this->fetchRecords($query, 'is', $content_type_id, $name);
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
		$cp = $this->getContentProperties();
		if (1 > $cp->getRecordId()) {
			return '';
		}
		return $cp->getContentLabel();
	}

	/**
	 * Returns ContentProperties instance from either the content or filters properties of the instance, depending on
	 * which one has retrieved its content properties from the database. Returns a new ContentProperties instance if
	 * both content and filters have not yet been retrieved.
	 * @return ContentProperties
	 */
	public function getContentProperties(): ContentProperties
	{
		return new ContentProperties();
	}

    /**
     * Content type id getter.
     * @return ?int
     */
    public function getContentTypeId(): ?int
    {
        return $this->getContentProperties()->id->value;
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
     * Sets the data to be injected into templates.
     * @throws ConfigurationUndefinedException|InvalidValueException|InvalidQueryException
     * @throws RecordNotFoundException
     */
    public function getTemplateContext(): array
    {
        $context = array(
            'page_data' => $this->newRoutedPageContentInstance(),
            'content' => null,
            'filters' => null);
        if (isset($this->filters)) {
            return array_merge($context, array(
                'filters' => &$this->filters,
                'qs' => $this->filters->formatQueryString()));
        }
        return $context;
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
     * Test if this instance has content properties currently loaded.
     * @return bool
     */
    abstract public function hasContentPropertiesObject(): bool;

    /**
     * Assigns a ContentFilters instance to the $filters property.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function initializeFiltersObject(?int $content_type_id=null)
    {
        $this->filters = call_user_func(
            [static::getControllerClass(), 'getContentFiltersObject'],
            $content_type_id ?: $this->getContentTypeId());
        $this->getContentProperties()->setRecordId($content_type_id);
    }

    /**
	 * Inserts content into content template. Stores the resulting markup in the object's internal "json" property.
	 * @param array|null $context Optional array containing data to inject into the template.
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function loadTemplateContent(?array $context=null)
	{
		$this->json->loadContentFromTemplate($this->getTemplatePath(), $context ?: $this->getTemplateContext());
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
		$this->route = $this->getContentProperties()->getContentRouteByOperation($operation);
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
        $this->template = $this->getContentProperties()->getContentTemplateByName($operation);
    }

    /**
     * Returns new ContentProperties instance. Can be used in derived classes to provide customized ContentProperties objects to the APIRoute class's methods.
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
     * @throws RecordNotFoundException
     */
	protected function newRoutedPageContentInstance(): PageContent
	{
        if (!$this->hasContentPropertiesObject()) {
            throw new ConfigurationUndefinedException('Content properties not available.');
        }
		$this->getContentProperties()->readRoutes();
        try {
            $route_parts = $this
                ->getContentProperties()
                ->getContentRouteByOperation('listings')
                ->getPropertyValue(ContentRoute::PROPERTY_TOKEN_ROUTE_AS_ARRAY);
        }
        catch(Error $e) {
            throw new RecordNotFoundException('Content route not found.');
        }
		$rpc_class = call_user_func([static::getControllerClass(), 'getRoutedPageContentClass'], $route_parts);
		return new $rpc_class();
	}

    /**
     * Returns new ContentTemplate instance. Can be used in derived classes to provide customized ContentTemplate objects to the APIRoute class's methods.
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
        $this->json->loadContentFromTemplate(
			$template->formatFullPath(),
            $this->getTemplateContext());
    }

	/**
	 * Collects filter values from request data, and reads the content data from the database.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws NotImplementedException
	 */
	abstract public function retrieveContentData();

	/**
	 * Hydrates the content properties object by retrieving data from the database.
	 * @return void
	 */
	public function retrieveContentProperties(?int $content_type_id=null)
	{
		if ($content_type_id > 0) {
			$this->setContentTypeId($content_type_id);
		}
		$this->retrieveCoreContentProperties();

		// set the active template and route properties if an operation has been specified
		if ($this->operation->value) {
			$this->lookupRoute();
			$this->lookupTemplate();
		}
	}

	/**
	 * Hook for derived classes to fill their respective ContentProperties properties with data.
	 * @return mixed
	 */
	abstract protected function retrieveCoreContentProperties();

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
		$this->connectToDatabase();
		$query = "CALL contentTemplateLookup(?,?)";
		$content_type_id = $this->getContentTypeId();
		$data = $this->fetchRecords($query, 'is', $content_type_id, $template_name);
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
    abstract public function setContentTypeId(int $content_id);

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
}
