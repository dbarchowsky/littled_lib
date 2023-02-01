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
use Littled\Utility\LittledUtility;

class RoutedPageContent extends PageContent
{
    /** @var string Content class name */
    protected static string $content_class='';
    /** @var string Filters class name */
    protected static string $filters_class='';
    /** @var string Routes class for section navigation */
    protected static string $routes_class='';
    protected static string $add_token = 'add';
    protected static string $edit_token = 'edit';
	protected static string $template_filename='';
	protected static int $access_level;

	/**
	 * @inheritDoc
	 * @throws ConfigurationUndefinedException
	 */
	function __construct()
	{
		parent::__construct();
		$this->verifyLogin();
	}

	/** @var SectionNavigationRoutes Section navigation routes. */
    public SectionNavigationRoutes $routes;

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
            in_array($this->edit_action, [PageContent::COMMIT_ACTION, PageContent::CANCEL_ACTION])) {
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
     */
    public function getDetailsURI(?int $record_id=null): string
    {
        if (!isset($this->routes)) {
            return '';
        }
        $record_id = $record_id ?: $this->getRecordId();
        if ($record_id) {
            return rtrim($this->routes::getDetailsRoute(), '/') . "/$record_id";
        }
        return $this->routes::getDetailsRoute();
    }

    /**
     * Returns the details route including a query string including variables used to filter listings content.
     * @param int|null $record_id The record id to inject into the URI. The content property's internal value will be used if a record id value is not passed in this argument.
     * @return string
     */
    public function getDetailsURIWithFilters(?int $record_id=null): string
    {
        return $this->getDetailsURI($record_id).$this->query_string;
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
     */
    public function getEditURI(?int $record_id=null): string
    {
        $record_id = $record_id ?: $this->getRecordId();
        return rtrim($this->getDetailsURI($record_id),'/').'/'.(($record_id)?(static::getEditToken()):(static::getAddToken()));
    }

    /**
     * Returns edit uri with current filter values added on as get variables.
     * @param ?int $record_id;
     * @return string
     */
    public function getEditURIWithFilters(?int $record_id=null): string
    {
        $record_id = $record_id ?: $this->getRecordId();
        return $this->getEditURI($record_id).$this->query_string;
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
     */
    public function getListingsURI(): string
    {
        if (!isset($this->routes)) {
            return '';
        }
        return $this->routes::getListingsRoute();
    }

    /**
     * Returns listings uri with current filter values added on as get variables.
     * @return string
     */
    public function getListingsURIWithFilters(): string
    {
        return $this->getListingsURI().$this->query_string;
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
                header("Location: {$this->filters->referer_uri}$this->query_string");
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
        $this->formatPageStateQueryString();
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
}