<?php

namespace Littled\API;

use Error;
use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\InvalidStateException;
use Littled\Exception\NotInitializedException;
use Littled\PageContent\Serialized\SerializedContent;
use mysqli;
use Throwable;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;


/**
 * Extends PageContent to add a JSONRecordResponse property used to convert the page content from the content normally sent as an HTML response to content sent as JSON.
 */
abstract class APIRoute extends APIRouteProperties
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        /* Set exception handler to return JSON error message */
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        foreach ($this as $item) {
            if (is_object($item) || is_array($item)) {
                unset($item);
            }
        }
    }

    /**
     * Collects content type value from request data allowing for multiple key names storing the value.
     * @param ?array $src
     * @param array $keys
     * @return int|null
     */
    protected function collectContentTypeIdFromRequestData(?array $src=null, array $keys=[]): ?int
    {
        $key_options = [
            LittledGlobals::CONTENT_TYPE_KEY,
            ContentProperties::ID_KEY,
            $this->getContentTypeKey()];
        $key_options = array_unique(array_merge($key_options, $keys));
        $content_id = null;
        foreach($key_options as $key) {
            $content_id = Validation::collectIntegerRequestVar($key, null, $src);
            if ($content_id) {
                break;
            }
        }
        return $content_id;
    }

    /**
     * Retrieves content type id from script arguments/form data and uses that value to retrieve content properties from the database.
     * @param string $key (Optional) Key used to retrieve content type id value from script arguments/form data.
     * Defaults to LittledGlobals::CONTENT_TYPE_ID.
     * @return $this
     * @throws ConfigurationUndefinedException|ConnectionException|ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function collectContentProperties(string $key = LittledGlobals::CONTENT_TYPE_KEY): APIRoute
    {
        // use ajax request data by default
        $ajax_rd = static::getAjaxRequestData();

        $cp = $this->getContentProperties();
        if (!$cp->id->value) {
            $content_type_id = $this->collectContentTypeIdFromRequestData($ajax_rd, [$key]);
            if ($content_type_id === null) {
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
        if (!$this->operation->value) {
            $this->operation->value = $saved;
        }
        if (!$this->operation->value) {
            $this->operation->value = static::getDefaultTemplateName();
        }
        $this->lookupTemplate();
        return $this;
    }

    /**
     * Assigns filter values from client request data.
     * @param ?array $src Optional array containing client data to use to populate filter values.
     * @param ?int $content_type_id Optional content type numerical identifier that will be assigned as any new filter collection instances' content type.
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException|InvalidStateException
     */
    public function collectFiltersRequestData(?array $src = null, ?int $content_type_id = null)
    {
        if ($src === null) {
            $src = static::getAjaxRequestData() ?: $_POST;
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
    public function collectPageAction(?array $src = null): APIRoute
    {
        if ($src === null) {
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
        if (Validation::collectBooleanRequestVar(LittledGlobals::COMMIT_KEY, null, $src) === true) {
            $this->action = self::COMMIT_ACTION;
            return ($this);
        }
        if (Validation::collectBooleanRequestVar(LittledGlobals::CANCEL_KEY, null, $src) === true) {
            $this->action = self::CANCEL_ACTION;
            return ($this);
        }
        return ($this);
    }

    /**
     * Fills out input values from request data.
     * @param ?array $src Optional array containing request data that will be used as the default source of request
     * data of GET and POST data.
     * @return $this;
     */
    public function collectRequestData(?array $src = null): APIRoute
    {
        $this->operation->collectRequestData($src);
        return $this;
    }

    /**
     * Confirms that a content route has been initialized, or attempts to initialize the $route property of the object
     * if a route has not been initialized.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    protected function confirmRouteIsLoaded()
    {
        if (isset($this->route) && (
            $this->route->route->hasData() ||
            $this->route->api_route->hasData() ||
            $this->route->wildcard->hasData())) {
            return;
        }
        $this->fetchContentRoute();
    }

    /**
     * Error handler. Catch error and return the error message to client making ajax request.
     * @param int $err_no
     * @param string $err_str
     * @param string $err_file
     * @param ?int $err_line
     */
    public function errorHandler(int $err_no, string $err_str, string $err_file = '', ?int $err_line = null)
    {
        // remove anything that might currently be in the output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // collect information for error message
        $msg = "$err_str [$err_no]";
        $msg .= (($err_file) ? (" in $err_file") : (''));
        $msg .= (($err_line) ? ("($err_line)") : (''));

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
     * Fetches the properties of the template matching the object's content type and, optionally, the name of the
     * template passed to the method. Will use the internal property value if a value is not supplied for the $name
     * argument.
     * @param ?string $operation
     * @return $this
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws NotInitializedException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    public function fetchContentRoute(?string $operation=null): APIRoute
    {
        $operation ??= $this->operation->value;
        if (!$this->getContentTypeId()) {
            $err_msg = 'The content route could not be retrieved. Content type not available.';
            throw new NotInitializedException($err_msg);
        }
        if (Validation::isStringBlank($operation)) {
            $err_msg = 'The content route could not be retrieved. Operation not available.';
            throw new NotInitializedException($err_msg);
        }
        $this->route = (new ContentRoute())
            ->setMySQLi(static::getMysqli())
            ->setContentType($this->getContentTypeId())
            ->setOperation($operation)
            ->lookupRoute();
        return $this;
    }

    /**
     * Fetches the properties of the template matching the object's content type and, optionally, the name of the
     * template passed to the method. Will use the internal property value if a value is not supplied for the $name
     * argument.
     * @param ?string $name
     * @return $this
     * @throws ConfigurationUndefinedException|NotInitializedException
     * @throws RecordNotFoundException
     */
    public function fetchContentTemplate(?string $name=null): APIRoute
    {
        $name ??= $this->operation->value;
        if (!$this->getContentTypeId()) {
            $err_msg = 'The content template could not be retrieved. Content type not available.';
            throw new NotInitializedException($err_msg);
        }
        if (Validation::isStringBlank($name)) {
            $err_msg = 'The content template could not be retrieved. Operation not available.';
            throw new NotInitializedException($err_msg);
        }
        $this->template = (new ContentTemplate())
            ->setMySQLi(static::getMysqli())
            ->setContentType($this->getContentTypeId())
            ->setOperation($name)
            ->lookupTemplateProperties();
        return $this;
    }

    /**
     * Sets the data to be injected into templates.
     * @throws ConfigurationUndefinedException|InvalidValueException|InvalidQueryException
     * @throws RecordNotFoundException|ConnectionException
     */
    public function getTemplateContext(): array
    {
        $context = array(
            'page_data' => $this->newAPIRouteInstance(),
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
     * Assigns a ContentFilters instance to the $filters property.
     * @return void
     * @throws ConfigurationUndefinedException|InvalidStateException
     */
    protected function initializeFiltersObject(?int $content_type_id = null)
    {
        $this->filters = call_user_func(
            [static::getControllerClass(), 'getContentFiltersObject'],
            $content_type_id ?: $this->getContentTypeId(), static::getMysqli());
        $this->getContentProperties()->setRecordId($content_type_id);
    }

    /**
     * Inserts content into content template. Stores the resulting markup in the object's internal "json" property.
     * @param array|null $context Optional array containing data to inject into the template.
     * @return $this
     * @throws ResourceNotFoundException
     * @throws Exception
     */
    public function loadTemplateContent(?array $context = null): APIRoute
    {
        $this->json->loadContentFromTemplate($this->getTemplatePath(), $context ?: $this->getTemplateContext());
        return $this;
    }

    /**
     * Looks for the route matching $route_name in the currently loaded templates. Sets the object's route
     * property value to that route object.
     * @param string $operation (Optional) Operation token to use to look up the template. Will use internal operation
     * * property value to perform the lookup if the $operation parameter is not supplied.
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function lookupRoute(string $operation = ''): APIRoute
    {
        $operation = $operation ?: $this->operation->value;
        $this->route = $this->getContentProperties()->getContentRouteByOperation($operation);
        return $this;
    }

    /**
     * Looks for the template matching $template_name in the currently loaded templates. Sets the object's template
     * property value to that template object.
     * @param string $operation
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function lookupTemplate(string $operation = ''): APIRoute
    {
        $operation = $operation ?: $this->operation->value;
        $this->template = $this->getContentProperties()->getContentTemplateByName($operation);
        return $this;
    }

    /**
     * Returns new ContentProperties instance. Can be used in derived classes to provide customized ContentProperties objects to the APIRoute class's methods.
     * @param int|null $record_id Initial content type record id value.
     * @return ContentProperties
     */
    protected function newContentPropertiesInstance(?int $record_id = null): ContentProperties
    {
        return new ContentProperties($record_id);
    }

    /**
     * Returns instance of a PageContent class used to render front-end content.
     * @return APIRoute
     * @throws ConfigurationUndefinedException|InvalidValueException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException|ConnectionException
     */
    protected function newAPIRouteInstance(): APIRoute
    {
        if (!$this->hasContentPropertiesObject()) {
            throw new ConfigurationUndefinedException('Content properties not available.');
        }
        $this->getContentProperties()->readRoutes();
        try {
            $route_parts = $this
                ->getContentProperties()
                ->getContentRouteByOperation('listings')
                ->getPropertyValue(ContentRoute::PROPERTY_TOKEN_API_ROUTE_AS_ARRAY);
        } catch (Error $e) {
            throw new RecordNotFoundException('Content route not found.');
        }
        $rpc_class = call_user_func([static::getControllerClass(), 'getAPIRouteClassName'], $route_parts);
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
    protected function newTemplateInstance(?int $record_id = null, ?int $content_type_id = null, string $operation = '', string $base_dir = '', string $template = '', string $location = ''): ContentTemplate
    {
        return new ContentTemplate($record_id, $content_type_id, $operation, $base_dir, $template, $location);
    }

    /**
     * @inheritDoc
     * @throws ResourceNotFoundException
     */
    public function processRequest(): APIRoute
    {
        $this->loadTemplateContent();
        return $this;
    }

    /**
     * Refresh content after performing an AJAX edit on a record. The markup that is generated is stored in the
     * class's json property's content property, which is then sent back to the client.
     * @param string $next_operation Token determining which template to load.
     * @param array $context (Optional) Variables to insert into the template. When an array is provided, it will
     * override the default template context. If not provided, the context will be generated using the object's
     * getTemplateContext() routine.
     * @throws Exception
     */
    public function refreshContentAfterEdit(string $next_operation, array $context=[])
    {
        $template = $this->newTemplateInstance();
        $template->retrieveUsingContentTypeAndOperation($this->getContentTypeId(), $next_operation);
        $this->json->loadContentFromTemplate(
            $template->formatFullPath(),
            $context ?? $this->getTemplateContext());
    }

    /**
     * Hydrates the content properties object by retrieving data from the database.
     * @param null|int $content_type_id
     * @return APIRoute
     * @throws ConfigurationUndefinedException
     */
    public function retrieveContentProperties(?int $content_type_id = null): APIRoute
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
        return $this;
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
            throw new RecordNotFoundException("\"" . ucfirst($template_name) . "\" template not found.");
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
     * Send error message as response to ajax request and stop processing the request.
     * @param $err_msg
     * @return void
     */
    public static function sendErrorAndExit($err_msg)
    {
        echo(json_encode(['error' => $err_msg]));
        // header("HTTP/1.1 400 ".$e->getMessage());
        exit(-1);
    }

    /**
     * Sends out whatever values are currently stored within the object's "json" property as JSON.
     */
    public function sendResponse(string $template_path = '', ?array $context = null)
    {
        $this->json->sendResponse();
    }

    /**
     * Send current JSON content value as plain text.
     * @param string $response Text to send as a response, if not using value stored in JSON property.
     * @return void
     */
    public function sendTextResponse(string $response = '')
    {
        header("Content-Type: text/plain\n\n");
        print($response ?: $this->json->content->value);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function setMySQLi(mysqli $mysqli): MySQLConnection
    {
        parent::setMySQLi($mysqli);
        foreach($this as $item) {
            if ($item instanceof SerializedContent) {
                $item->setMySQLi(static::getMysqli());
            }
        }
        return $this;
    }
}
