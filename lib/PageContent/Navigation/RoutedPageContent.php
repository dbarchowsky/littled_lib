<?php
namespace Littled\PageContent\Navigation;

use Exception;
use Littled\Account\LoginAuthenticator;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;
use Littled\Log\Log;
use Littled\PageContent\PageContent;
use Littled\PageContent\PageContentInterface;
use Littled\Utility\LittledUtility;


/**
 * Extends PageContent to add methods to register and load record classes, filter classes, and routes for a specific content type.
 */
abstract class RoutedPageContent extends PageContent
{
    /** @var string                 SectionContent record class name */
    protected static string         $content_class='';
    /** @var string                 ContentFilters filters class name */
    protected static string         $filters_class='';
    /**
     * @var string                  SectionNavigationRoutes routes class name for section navigation
     * @todo Audit this property to see of $this->content->content_properties->routes or $this->filters->content_properties->routes couldn't be used in its place.
     */
    protected static string         $routes_class='';
    /**
     * @var SectionNavigationRoutes Section navigation routes.
     * @todo Audit this property to see of $this->content->content_properties->routes or $this->filters->content_properties->routes couldn't be used in its place.
     */
    public SectionNavigationRoutes  $routes;
	/**
	 * @var string
	 * @todo Audit the use of this property. It could potentially be replaced with a "content_route" record in the database dedicated to a route to add new records.
	 */
    protected static string         $add_token = 'add';
	/**
	 * @var string
	 * @todo Similar to $add_token, audit this property to see if it can be replaced with a "content_route" record.
	 */
    protected static string         $edit_token = 'edit';
	protected static string         $template_filename='';
	/**
	 * @var int
	 * @todo Audit this property. Is this not already provided by some higher level class?
	 */
	protected static int            $access_level;

	/**
	 * @inheritDoc
	 * @throws ConfigurationUndefinedException
	 */
	function __construct()
	{
		parent::__construct();
		$this->verifyLogin();
	}

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 */
	public function verifyLogin()
	{
		if (static::getAccessLevel() > 0) {
			$login = new LoginAuthenticator();
			$login->requireLogin(static::getAccessLevel());
			unset($login);
		}
	}

    /**
     * @throws ConfigurationUndefinedException
     */
    public function checkAndCommitUpdates(): ?RoutedPageContent
    {
        $page = $this;
        if (LittledGlobals::COMMIT_KEY === $this->edit_action) {
            // save any edits made on a page
            $this->updateRecord();
        }
        if (isset($this->content) &&
            false === $this->content->hasValidationErrors() &&
            in_array($this->edit_action, [PageContentInterface::COMMIT_ACTION, PageContentInterface::CANCEL_ACTION])) {
            // load page selected to be the next page after editing and saving a record
            $page = $this->getUpdateResponsePage();
        }
        return $page;
    }

    /**
     * Parses route components to fetch record id and action values. Route is expected to be in the format of
     * $route[0] > record_id, $route[1] > action
     * @param array $route
     * @return RouteAction
     */
    public static function collectActionFromRoute(array $route): RouteAction
    {
        $action = new RouteAction();
        $action->record_id = static::collectRecordIdFromRoute($route);
        if (null===$action->record_id) {
            if (count($route) > 1 && preg_match('/^[a-zA-Z\-_]*$/', $route[1])) {
                $action->token = $route[1];
            }
        }
        else {
            if (count($route) > 2) {
                $action->token = $route[2];
            }
        }
        return $action;
    }

    /**
     * Returns a record id embedded in request route parts.
     * @param array $route
     * @return int
     */
    public static function collectRecordIdFromRoute(array $route): ?int
    {
        if (1 < count($route) && is_numeric($route[1])) {
            return (int)$route[1];
        }
        return null;
    }

    /**
     * Formats and returns path to use to reach this page.
     * @param int|null $record_id
     * @return string
     */
    abstract public function formatRoutePath(?int $record_id=null): string;

	/**
	 * Access level getter.
	 * @return ?int
	 */
	public static function getAccessLevel(): ?int
	{
		return ((isset(static::$access_level)?(static::$access_level):(null)));
	}

	/**
     * Add token getter.
     * @return string
     */
    public static function getAddToken(): string
    {
        return static::$add_token;
    }

    /**
     * Content class name setter.
     * @return string
     */
    public static function getContentClassName(): string
    {
        return static::$content_class;
    }

    /**
     * Details uri getter.
     * @param ?int $record_id
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getDetailsURI(?int $record_id=null): string
    {
        try {
            $this->verifyAndLoadRoutes();
        }
        catch(ConfigurationUndefinedException $e) {
            throw new ConfigurationUndefinedException('Routes class unavailable for details uri.');
        }
        return $this->routes::getDetailsRoute( $record_id ?: $this->getRecordId());
    }

    /**
     * Returns the details route including a query string including variables used to filter listings content.
     * @param int|null $record_id The record id to inject into the URI. The content property's internal value will be used if a record id value is not passed in this argument.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getDetailsURIWithFilters(?int $record_id=null): string
    {
        return $this->getDetailsURI($record_id).$this->getQueryString(true);
    }

    /**
     * Edit token getter.
     * @return string
     */
    public static function getEditToken(): string
    {
        return static::$edit_token;
    }

    /**
     * Edit record uri getter.
     * @param int|null $record_id
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getEditURI(?int $record_id=null): string
    {
        try {
            $this->verifyAndLoadRoutes();
        }
        catch(ConfigurationUndefinedException $e) {
            throw new ConfigurationUndefinedException('Routes class unavailable for edit uri.');
        }
        return $this->routes::getEditRoute($record_id ?: $this->getRecordId());
    }

    /**
     * Returns edit uri with current filter values added on as get variables.
     * @param ?int $record_id ;
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getEditURIWithFilters(?int $record_id=null): string
    {
        return $this->getEditURI($record_id).$this->getQueryString(true);
    }

    /**
     * Filters class name setter.
     * @return string
     */
    public static function getFiltersClassName(): string
    {
        return static::$filters_class;
    }

    /**
     * Returns listings uri with current filter values added on as get variables.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getListingsURI(): string
    {
        try {
            $this->verifyAndLoadRoutes();
        }
        catch(ConfigurationUndefinedException $e) {
            throw new ConfigurationUndefinedException('Routes class unavailable for listings uri.');
        }
        return $this->routes::getListingsRoute();
    }

    /**
     * Returns listings uri with current filter values added on as get variables.
     * @return string
     */
    public function getListingsURIWithFilters(): string
    {
        if (!isset($this->routes)) {
            return '';
        }
        return $this->routes::getListingsRoute().$this->getQueryString(true);
    }

    /**
     * Record id getter.
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        if (isset($this->content) && $this->content->id->value) {
            return $this->content->id->value;
        }
        return null;
    }

    /**
     * Section routes class name getter.
     * @return string
     */
    public static function getRoutesClassName(): string
    {
        return static::$routes_class;
    }

	/**
	 * Template directory path getter.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getTemplateDir(): string
	{
		$routes_class = static::getValidatedRoutesClass('getTemplateDir');
		/** @var SectionNavigationRoutes $routes_class */
		return $routes_class::getTemplateDir();
	}

	/**
	 * Template directory path getter.
	 * @return string
	 */
	public static function getTemplateFilename(): string
	{
		return static::$template_filename;
	}

	/**
	 * Template full path getter.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getTemplateFullPath(): string
	{
		$routes_class = static::getValidatedRoutesClass('getTemplateDir');
		/** @var SectionNavigationRoutes $routes_class */
		return LittledUtility::joinPaths($routes_class::getTemplateDir(), static::$template_filename);
	}

	/**
     * Sets the page template to load after updating a database record.
     * @return RoutedPageContent
     * @throws ConfigurationUndefinedException
     */
    public function getUpdateResponsePage(): RoutedPageContent
    {
        /** placeholder for child classes */
        if (ContentFilters::NEXT_OP_PREVIOUS === $this->filters->next->value) {
            if ($this->filters->referer_uri) {
                $this->formatQueryString();
                header("Location: {$this->filters->referer_uri}".$this->getQueryString(true));
            }
            throw new ConfigurationUndefinedException('Referer URI for previous page was not specified.');
        }
        return $this;
    }

	/**
	 * Validates that the $routes_class property value is currently set to an appropriate class type before returning
	 * the name of the routes class.
	 * @param string $method Optional method name. If present, the class will be checked to make sure that the method
	 * exists within the class.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getValidatedRoutesClass(string $method=''): string
	{
		$routes_class = static::$routes_class;
		if (!class_exists($routes_class)) {
			throw new ConfigurationUndefinedException('Invalid route object in '.get_called_class().'.');
		}
		if ($method && !method_exists($routes_class, $method)) {
			throw new ConfigurationUndefinedException('Invalid interface.');
		}
		return $routes_class;
	}

    /**
     * Instantiates filters and routes objects for the class.
     * @throws InvalidTypeException
     */
    protected function instantiateProperties()
    {
        $routes_class = static::getRoutesClassName();
        $filters_class = static::getFiltersClassName();
        if ($routes_class) {
            if (!class_exists($routes_class)) {
                throw new InvalidTypeException(Log::getShortMethodName()." \"$routes_class\" is not a valid class.");
            }
            $this->routes = new $routes_class();
        }
        if ($filters_class) {
            if (!class_exists($filters_class)) {
                throw new InvalidTypeException(Log::getShortMethodName() . " \"$filters_class\" is not a valid class.");
            }
            $this->filters = new $filters_class();
        }
    }

    /**
     * Sets the object's $filters property as an object of the
     * type specified with the $filters_class parameter. Collects
     * the appropriate filter values from page variables. Sets the
     * $qs property to include the filter variables and their values.
     * @throws Exception
     * @throws NotImplementedException
     */
    protected function loadFilters()
    {
        if (!isset($this->filters)) {
            return;
        }
        $this->filters->collectFilterValues();
        $this->formatQueryString();
    }

	/**
	 * Access level setter.
	 * @param int $access_level
	 * @return void
	 */
	public static function setAccessLevel(int $access_level)
	{
		static::$access_level = $access_level;
	}

	/**
     * Content class name setter.
     * @param string $class
     * @return void
     */
    public static function setContentClassName(string $class)
    {
        static::$content_class = $class;
    }

	/**
	 * Filters property setter.
	 * @param ContentFilters $filters
	 * @return void
	 */
	public function setFilters(ContentFilters $filters)
	{
		$this->filters = $filters;
	}

    /**
     * Filters class name setter.
     * @param string $class
     * @return void
     */
    public static function setFiltersClassName(string $class)
    {
        static::$filters_class = $class;
    }

    /**
     * Navigation routes class setter.
     * @param string $routes_class
     * @return void
     */
    public static function setRoutesClassName(string $routes_class)
    {
        static::$routes_class = $routes_class;
    }

	/**
	 * Template directory path setter.
	 * @param string $path
	 * @return void
	 */
	public static function setTemplateDir(string $path)
	{
		static::$template_dir = $path;
	}

	/**
	 * Template filename setter.
	 * @param string $filename
	 * @return void
	 */
	public static function setTemplateFilename(string $filename)
	{
		static::$template_filename = $filename;
	}

    /**
     * Save content edited within a page.
     * @return void
     */
    public function updateRecord()
    {
        $this->content->collectRequestData();

        try {
            $this->content->validateInput();
        }
        catch(ContentValidationException $e) { /* continue */ }

        if ($this->content->hasValidationErrors()) {
            $this->content->unshiftValidationError('Problems were found in the information entered.');
            return;
        }

        try {
            $this->content->save();
        }
        catch(Exception $e) {
            $this->content->addValidationError($e->getMessage());
        }
    }

    /**
     * Confirm that the object has an instantiated routes object as the value of its $routes property.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function verifyAndLoadRoutes()
    {
        if (!isset($this->routes)) {
            if (!static::$routes_class) {
                throw new ConfigurationUndefinedException('Routes class unavailable.');
            }
            $class = $this::$routes_class;
            $this->routes = new $class();
        }
    }
}