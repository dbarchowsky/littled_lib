<?php
namespace Littled\PageContent\Navigation;

use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;
use Littled\Filters\FilterCollection;
use Littled\Log\Log;
use Littled\PageContent\PageContent;
use Littled\PageContent\SiteSection\SectionContent;

class RoutedPageContent extends PageContent
{
    /** @var string Content class name */
    protected static string $content_class='';
    /** @var string Filters class name */
    protected static string $filters_class='';
    /** @var string Routes class for section navigation */
    protected static string $routes_class='';
    /** @var string */
    protected static string $add_token = 'add';
    /** @var string */
    protected static string $edit_token = 'edit';
    /** @var string */
    protected static string $default_template_path='';

    /** @var SectionNavigationRoutes Section navigation routes. */
    public SectionNavigationRoutes $routes;

    /**
     * @throws ConfigurationUndefinedException
     */
    public function checkAndCommitUpdates()
    {
        if (LittledGlobals::COMMIT_KEY === $this->edit_action) {
            // save any edits made on a page
            $this->updateRecord();
        }
        if ( $this->content instanceof SectionContent &&
            false === $this->content->hasValidationErrors() &&
            in_array($this->edit_action, [PageContent::COMMIT_ACTION, PageContent::CANCEL_ACTION])) {
            // load page selected to be the next page after editing and saving a record
            $page = $this->getUpdateResponsePage();
        }
    }

    /**
     * Parses route components to fetch record id and action values. Route is expected to be in the format of
     * $route[0] > record_id, $route[1] > action
     * @param array $route
     * @return array
     */
    public static function collectActionFromRoute(array $route): array
    {
        $action = array('action' => '');
        $action['record_id'] = static::collectRecordIdFromRoute($route);
        if (null===$action['record_id']) {
            if (count($route) > 1 && preg_match('/^[a-zA-Z\-_]*$/', $route[1])) {
                $action['action'] = $route[1];
            }
        }
        else {
            if (count($route) > 2) {
                $action['action'] = $route[2];
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
     * Filters class name setter.
     * @return string
     */
    public static function getFiltersClassName(): string
    {
        return static::$filters_class;
    }

    /**
     * Default template path getter.
     * @return string
     */
    public static function getDefaultTemplatePath(): string
    {
        return static::$default_template_path;
    }

    /**
     * Details uri getter.
     * @param ?int $record_id
     * @return string
     */
    public function getDetailsURI(?int $record_id=null): string
    {
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
     * Returns listings uri with current filter values added on as get variables.
     * @return string
     */
    public function getListingsURI(): string
    {
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
     * Sets the page template to load after updating a database record.
     * @return RoutedPageContent
     * @throws ConfigurationUndefinedException
     */
    public function getUpdateResponsePage(): RoutedPageContent
    {
        /** placeholder for child classes */
        if ($this->filters instanceof ContentFilters &&
            ContentFilters::NEXT_OP_PREVIOUS === $this->filters->next->value) {
            if ($this->filters->referer_uri) {
                $this->formatQueryString();
                header("Location: {$this->filters->referer_uri}$this->query_string");
            }
            throw new ConfigurationUndefinedException('Referer URI for previous page was not specified.');
        }
        return $this;
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
        if (!$this->filters instanceof FilterCollection) {
            return;
        }
        $this->filters->collectFilterValues();
        $this->formatPageStateQueryString();
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