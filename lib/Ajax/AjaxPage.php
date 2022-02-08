<?php
namespace Littled\Ajax;

use Exception;
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
    /** @var string Name of the default template to use in derived classes to generate markup. */
    protected static $default_template_name = '';

	/** @var string */
	const COMMIT_ACTION = 'commit';
	/** @var string */
	const CANCEL_ACTION = 'cancel';
    /** @var string */
    const TEMPLATE_TOKEN_KEY = 'templateToken';

	/** @var string String indicating the action to be taken on the page. */
	public $action;
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
    public $template_token;
	/** @var ContentTemplate Current content template properties. */
	public $template;

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
        $this->template_token = new StringInput('Template token', self::TEMPLATE_TOKEN_KEY, false, static::getDefaultTemplateName(), 45);
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

        $this->template_token->collectRequestData();
        if (!$this->template_token->value) {
            $this->template_token->value = static::getDefaultTemplateName();
        }
        $this->lookupTemplate();
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
        $msg = "$err_str [$err_no]";
        $msg .= (($err_file)?(" in $err_file"):(''));
        $msg .= (($err_line)?("($err_line)"):(''));
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
     * Content type id getter.
     * @return ?int
     */
    public function getContentTypeId(): ?int
    {
        return ($this->content_properties->id->value);
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
     * Retrieve from the database the path to details template.
     * @param string $template_name Token indicating which type of template to retrieve: details, listings, edit, delete, etc.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws Exception
     */
	public function getTemplatePath( string $template_name )
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
     * Looks for the template matching $template_name in the currently loaded templates. Sets the object's template
     * property value to that template object.
     * @param string $template_name
     * @return void
     */
    public function lookupTemplate(string $template_name='')
    {
        $template_name = $template_name ?: $this->template_token->value;
        $this->template = $this->content_properties->getContentTemplateByName($template_name);
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
	 * Inserts content into content template. Stores the resulting markup in the object's internal "json" property.
	 * @param string $content_path Path to content template.
	 * @param SectionContent|null $content (Optional) Object containing content values to insert into content templates.
	 * @param FilterCollection|null $filters (Optional) Filter values to be saved in any forms or to used to display the content.
	 * @throws ResourceNotFoundException
	 */
	public function loadContent(string $content_path, ?SectionContent &$content=null, ?FilterCollection &$filters=null )
	{
		$context = array();
		if (null !== $content) {
			$context['content'] = &$content;
		}
		else {
			$context['content'] = &$this->content;
		}
		if (null !== $filters) {
			$context['filters'] = &$filters;
			$context['qs'] = $filters->formatQueryString();
		}
		$this->json->loadContentFromTemplate($content_path, $context);
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
     * Content type id setter.
     * @param int $content_id
     * @return void
     */
    public function setContentTypeId(int $content_id)
    {
        $this->content_properties->id->setInputValue($content_id);
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
