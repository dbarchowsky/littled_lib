<?php
namespace Littled\API;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\PageContent\PageContentBase;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Request\StringInput;
use Littled\Utility\LittledUtility;
use Exception;

abstract class APIRouteProperties extends PageContentBase
{
    /** @var string */
    public const                TEMPLATE_TOKEN_KEY = 'templateToken';

    /** @var string             Name of a \Littled\PageContent\Cache\ContentCache class to use to cache content. */
    protected static string     $cache_class = ContentCache::class;
    /** @var string             Name a \Littled\PageContent\ContentController class to use as content controller. */
    protected static string     $controller_class = ContentController::class;
    /** @var string             Name of the default template to use in derived classes to generate markup. */
    protected static string     $default_template_dir = '';
    protected static string     $default_template_name = '';
    /** @var string             String indicating the action to be taken on the page. */
    public string               $action = '';
    /** @var JSONRecordResponse JSON response object. */
    public JSONRecordResponse   $json;
    /** @var StringInput        Token to use to select which content template to load. Corresponds to the "name" field of the content_template table. */
    public StringInput          $operation;
    /** @var ?ContentTemplate   Current content template properties. */
    public ?ContentTemplate     $template;
    /** @var ?ContentRoute      Current content route properties. */
    public ?ContentRoute        $route;

    public function __construct()
    {
        parent::__construct();
        $this->json = new JSONRecordResponse();
        $this->operation = new StringInput('Template token', self::TEMPLATE_TOKEN_KEY, false, static::getDefaultTemplateName(), 45);
        $this->action = "";
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
     * @throws ConfigurationUndefinedException
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
     * @throws ConfigurationUndefinedException
     */
    public function getContentProperties(): ContentProperties
    {
        if (isset($this->filters->content_properties)) {
            return $this->filters->content_properties;
        }
        return (new ContentProperties())->setMySQLi(static::getMysqli());
    }

    /**
     * Content type id getter.
     * @return ?int
     * @throws ConfigurationUndefinedException
     */
    public function getContentTypeId(): ?int
    {
        return $this->getContentProperties()->id->value;
    }

    /**
     * Returns the current key value used to access the content type value in request data.
     * @return string
     */
    abstract public function getContentTypeKey(): string;

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
     * Returns the string value of the currently loaded api route.
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    public function getAPIRoutePath(): string
    {
        $this->confirmRouteIsLoaded();
        return $this->route->api_route->value;
    }

    /**
     * Returns the string value of the currently loaded route. This should be overwritten in derived classes
     * to return the api_route property value if that is the appropriate route for a given request.
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    public function getRoutePath(): string
    {
        $this->confirmRouteIsLoaded();
        return $this->route->route->value;
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
     * Content cache class setter.
     * @param string $class_name Name of class to use to cache ajax content. Must be derived from \Littled\PageContent\Cache\ContentCache
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setCacheClass(string $class_name)
    {
        if ($class_name === ContentCache::class) {
            throw new ConfigurationUndefinedException('Cache type must inherit from base cache type.');
        }
        if (!is_a($class_name, ContentCache::class, true)) {
            throw new InvalidTypeException("\"$class_name\" is not a valid content cache type.");
        }
        static::$cache_class = $class_name;
    }

    /**
     * Content type id setter.
     * @param int $content_id
     * @return $this
     */
    abstract public function setContentTypeId(int $content_id): APIRouteProperties;

    /**
     * Content cache class setter.
     * @param string $class_name Name of class to use as content controller. Must be derived from \Littled\PageContent\ContentController
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setControllerClass(string $class_name)
    {
        if ($class_name === ContentController::class) {
            throw new ConfigurationUndefinedException('Controller type must be derived from base controller type.');
        }
        if (!is_a($class_name, ContentController::class, true)) {
            throw new InvalidTypeException(Log::getShortMethodName() . ' Invalid controller type. ');
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
     * Operation value setter.
     * @param string $operation
     * @return $this
     */
    public function setOperation(string $operation): APIRouteProperties
    {
        $this->operation->setInputValue($operation);
        return $this;
    }

    /**
     * Response container id value setter.
     * @param string $container_id If a container id value is not provided, the routine will attempt to pull the
     * container id value from the currently loaded template data.
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function setResponseContainerId(string $container_id=''): APIRouteProperties
    {
        if (!$container_id) {
            if (!isset($this->template) || !$this->template->hasData() || !$this->template->container_id->hasData()) {
                $err = 'No template data is available, or a container id value is not present. ';
                throw new ConfigurationUndefinedException($err);
            }
            $container_id = $this->template->container_id->value;
        }
        $this->json->setResponseContainerId($container_id);
        return $this;
    }

    /**
     * Response content value setter.
     * @param string $content
     * @return $this
     */
    public function setResponseContent(string $content): APIRouteProperties
    {
        $this->json->setResponseContent($content);
        return $this;
    }

    /**
     * Response data setter after api request has been successfully processed.
     * @param string $content
     * @param string $status
     * @param string $container_id
     * @return $this
     */
    public function setResponseData(string $content, string $status, string $container_id): APIRouteProperties
    {
        $this->json->setResponseData($content, $status, $container_id);
        return $this;
    }

    /**
     * Response error value setter.
     * @param string $err
     * @return $this
     */
    public function setResponseError(string $err): APIRouteProperties
    {
        $this->json->setErrorMessage($err);
        return $this;
    }

    /**
     * Response status value setter.
     * @param string $status
     * @return $this
     */
    public function setResponseStatus(string $status): APIRouteProperties
    {
        $this->json->setResponseStatus($status);
        return $this;
    }
}