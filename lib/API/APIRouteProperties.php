<?php
namespace Littled\API;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidPropertyException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Log\Log;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\PageContent\RouteBase;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use Littled\Utility\LittledUtility;


abstract class APIRouteProperties extends RouteBase
{
    /** @var string */
    public const                TEMPLATE_TOKEN_KEY = 'templateToken';
    public const                DETAILS_TOKEN = 'details';
    public const                LISTINGS_TOKEN = 'listings';


    /** @var string             Name of a \Littled\PageContent\Cache\ContentCache class to use to cache content. */
    protected static string     $cache_class = ContentCache::class;
    /** @var string             Name a \Littled\PageContent\ContentController class to use as a content controller. */
    protected static string     $controller_class = ContentController::class;
    protected static APIRouteDefaultProperties $default;
    /** @var string             String indicating the action to be taken on the page. */
    public string               $action = '';
    public IntegerInput         $content_type_id;
    /** @var JSONRecordResponse JSON response object. */
    public JSONRecordResponse   $json;
    /** @var StringInput        Token to use to select which content template to load. Corresponds to the "name" field of the content_template table. */
    public StringInput          $operation;
    /** @var ?ContentTemplate   Current content template properties. */
    public ?ContentTemplate     $template;
    /** @var ?ContentRoute      Current content route properties. */
    public ?ContentRoute        $route;

    /**
     * APIRouteProperties constructor.
     * @throws InvalidPropertyException
     */
    public function __construct()
    {
        $this->json = new JSONRecordResponse();
        $this->operation = new StringInput('Template token', self::TEMPLATE_TOKEN_KEY, false, static::getDefault('operation'), 45);
        $this->action = '';
        $this->content_type_id = (new IntegerInput())
            ->setLabel('Content type')
            ->setKey(ContentProperties::ID_KEY);
    }

    /**
     * Resets default property values to their defaults.
     * @return void
     */
    public static function clearDefaults(): void
    {
        static::$default = new APIRouteDefaultProperties();
    }

    /**
     * Confirms that a content route has been initialized or attempts to initialize the $route property of the object
     * if a route has not been initialized.
     * @return bool
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    protected function confirmRouteIsLoaded(): bool
    {
        if (isset($this->route) &&
            $this->route->operation->hasData() &&
            $this->route->route->hasData()) {
            return true;
        }
        if (($this->getContentTypeId() ?: 0) < 1) {
            throw new ConfigurationUndefinedException('A content type value is required to load the route.');
        }
        if (!$this->operation->hasData()) {
            throw new ConfigurationUndefinedException('An operation is required to load the route.');
        }
        if ($this->getContentTypeId() > 0 && $this->operation->hasData()) {
            $this->fetchContentRoute();
        }
        return isset($this->route) && $this->route->route->hasData();
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
     * @throws NotInitializedException
     * @throws RecordUnavailableException
     */
    public function getContentLabel(): string
    {
        return $this->getContentProperties()->getContentLabel();
    }

    /**
     * Returns ContentProperties instance. Derived classes may override to check $filters or $content properties
     * for already initialized ContentProperties objects
     * @return ContentProperties
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function getContentProperties(): ContentProperties
    {
        if (isset($this->filters->content_properties)) {
            return $this->filters->content_properties;
        }
        return ($this->newContentPropertiesInstance())->shareConnection($this);
    }

    /**
     * Content type id getter.
     * @return ?int
     * @throws RecordUnavailableException
     */
    public function getContentTypeId(): ?int
    {
        // first try content type id property value
        if ($this->content_type_id->value > 0) {
            return $this->content_type_id->value;
        }

        // fall back to the content properties object
        if ($this->hasContentPropertiesData()) {
            try {
                $record_id = $this->getContentProperties()->getRecordId();
                if ($record_id > 0) {
                    return $record_id;
                }
            } catch (ConfigurationUndefinedException) {
                /* quiet IDE inspections */
            }
        }

        // fall back to request data
        $this->content_type_id->collectRequestData($_POST ?? []);
        if ($this->content_type_id->value > 0) {
            return $this->content_type_id->value;
        }

        // fall back to AJAX data
        $this->content_type_id->collectAjaxRequestData((object)static::getAjaxRequestData());
        return $this->content_type_id->value;
    }

    /**
     * Returns the current key value used to access the content type value in request data.
     * @return string
     */
    public function getContentTypeKey(): string
    {
        return $this->content_type_id->getKey();
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
     * Default properties getter.
     * @param string $property
     * @return mixed
     * @throws InvalidPropertyException
     */
    public static function getDefault(string $property): mixed
    {
        if (!isset(static::$default)) {
            static::$default = new APIRouteDefaultProperties();
        }
        if (!property_exists(static::$default, $property)) {
            throw new InvalidPropertyException("\"$property\" is not a valid default property.");
        }
        return static::$default->{$property} ?? null;
    }

    /**
     * Returns the string value of the currently loaded route. This should be overwritten in derived classes
     * to return the api_route property value if that is the appropriate route for a given request.
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function getRoutePath(): string
    {
        $this->confirmRouteIsLoaded();
        return $this->route->route->value;
    }

    /**
     * @inheritDoc
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws InvalidPropertyException
     */
    public function getTemplatePath(): string
    {
        if (!isset($this->template)) {
            throw new ConfigurationUndefinedException('Content template is not set.');
        }
        if (!static::getDefault('template_path')) {
            return $this->template->formatFullPath();
        }
        return LittledUtility::joinPaths(static::getDefault('template_path'), $this->template->path->value);
    }

    /**
     * Test if this instance has content properties currently loaded.
     * @return bool
     */
    public function hasContentPropertiesObject(): bool
    {
        return isset($this->filters);
    }

    /**
     * Test if this instance has content properties currently loaded.
     * @return bool
     */
    public function hasContentPropertiesData(): bool
    {
        return isset($this->filters->content_properties) && $this->filters->content_properties->id->hasData();
    }

    /**
     * Retrieves content properties from the database and loads them into the $filters property of the object.
     * @param ?int $content_type_id
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    protected function loadFiltersContentType(?int $content_type_id): void
    {
        if (isset($this->filters)) {
            try {
                $this->filters->content_properties->setRecordId($content_type_id);
                if ($content_type_id > 0) {
                    $this->filters->content_properties->read();
                }
            }
            catch(FailedQueryException|ReadException|RecordNotFoundException $e) {
                throw new RecordUnavailableException($e->throwMessage('Unable to load content properties'));
            }
            return;
        }

        try {
            $this->initializeFiltersObject($content_type_id);
        }
        catch(ConfigurationUndefinedException|ConnectionException|InvalidStateException $e) {
            throw new ConfigurationUndefinedException($e->throwMessage('Unable to load content properties'));
        }
    }

    /**
     * Assigns a new ContentFilters instance to the $filters property to clear any values that may have been
     * previously loaded.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function reloadFilters(): void
    {
        if (!isset($this->filters)) {
            return;
        }
        $this->filters = call_user_func(
            [static::getControllerClass(), 'getContentFiltersObject'],
            null,
            $this);
    }

    /**
     * Content cache class setter.
     * @param string $class_name Name of class to use to cache ajax content. Must be derived from \Littled\PageContent\Cache\ContentCache
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setCacheClass(string $class_name): void
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
     * @param ?int $content_type_id
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function setContentTypeId(?int $content_type_id): static
    {
        $this->content_type_id->setInputValue($content_type_id);
        if ($content_type_id > 0) {
            $this->loadFiltersContentType($content_type_id);
        }
        else {
            $this->reloadFilters();
        }
        return $this;
    }

    /**
     * Sets the key value used to access the content type value in request data.
     * @param string $key
     * @return $this
     */
    public function setContentTypeKey(string $key): static
    {
        $this->content_type_id->setKey($key);
        return $this;
    }

    /**
     * Content cache class setter.
     * @param string $class_name Name of class to use as a content controller. Must be derived from \Littled\PageContent\ContentController
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setControllerClass(string $class_name): void
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
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws InvalidPropertyException
     */
    public static function setDefault(string $property, mixed $value): void
    {
        if (!isset(static::$default)) {
            static::$default = new APIRouteDefaultProperties();
        }
        if (!property_exists(static::$default, $property)) {
            throw new InvalidPropertyException("\"$property\" is not a valid default property.");
        }
        static::$default->{$property} = $value;
    }

    /**
     * Operation value setter.
     * @param string $operation
     * @return $this
     */
    public function setOperation(string $operation): static
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
    public function setResponseContainerId(string $container_id=''): static
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
    public function setResponseContent(string $content): static
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
    public function setResponseData(string $content, string $status, string $container_id): static
    {
        $this->json->setResponseData($content, $status, $container_id);
        return $this;
    }

    /**
     * Response error value setter.
     * @param string $err
     * @return $this
     */
    public function setResponseError(string $err): static
    {
        $this->json->setErrorMessage($err);
        return $this;
    }

    /**
     * Response status value setter.
     * @param string $status
     * @return $this
     */
    public function setResponseStatus(string $status): static
    {
        $this->json->setResponseStatus($status);
        return $this;
    }
}