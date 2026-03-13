<?php

namespace Littled\API;

use Littled\App\AppBase;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidCredentialsException;
use Littled\Exception\InvalidPropertyException;
use Littled\Exception\InvalidRouteException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\LittledException;
use Littled\Exception\NotInitializedException;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\ResponseException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\RequestValidation;
use Littled\Validation\Validation;
use Exception;
use Throwable;


/**
 * Extends PageContent to add a JSONRecordResponse property used to convert the page content from the content normally sent as an HTML response to content sent as JSON.
 *
 * @method sendErrorResponse(string|LittledException $err): void
 * @method static sendErrorResponse(string|LittledException $err): void
 */
abstract class APIRoute extends APIRouteProperties
{
    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     * @throws ResponseException
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'sendErrorResponse') {
            $this->_sendErrorResponse(...$arguments);
        }
        return parent::__call($name, $arguments);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     * @throws ResponseException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'sendErrorResponse') {
            static::_sendErrorResponseStatic(...$arguments);
        }
        return parent::__callStatic($name, $arguments);
    }

    /**
     * Send an error message as a JSON response along with the rest of the object's property values.
     * The ResponseException should be caught and handled by exiting the script.
     * @param string|LittledException $err
     * @return void
     * @throws ResponseException
     */
    protected function _sendErrorResponse(string|LittledException $err): void
    {
        $json = $this->json->formatJson();
        $json['error'] = is_string($err) ? $err : $err->getFrontendError();

        header('Content-Type: application/json');
        echo(json_encode($json));

        static::exitWithError($err);
    }

    /**
     * Send an error message as a JSON response.
     * @param string|LittledException $err
     * @return void
     * @throws ResponseException
     */
    public static function _sendErrorResponseStatic(string|LittledException $err): void
    {
        header('Content-Type: application/json');
        echo(json_encode(['error' => is_string($err) ? $err : $err->getFrontendError()]));
        static::exitWithError($err);
    }

    /**
     * @return void
     * @throws InvalidCredentialsException
     */
    protected static function checkIfDevOnly(): void
    {
        if (static::$dev_only && !in_array(AppBase::getAppEnv(), [LittledGlobals::ENV_DEVELOPMENT, LittledGlobals::ENV_STAGING])) {
            throw new InvalidCredentialsException('Not allowed.');
        }
    }

    /**
     * Retrieves content type id from script arguments/form data and uses that value to retrieve content properties from the database.
     * @param string $key (Optional) Key used to retrieve content type id value from script arguments/form data.
     * Defaults to LittledGlobals::CONTENT_TYPE_ID.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws InvalidPropertyException
     * @throws RecordUnavailableException
     */
    public function collectContentProperties(string $key = ContentProperties::ID_KEY): APIRoute
    {
        // use ajax request data by default
        $ajax_data = static::getAjaxRequestData();
        $this->retrieveCoreContentProperties($ajax_data, $key);
        $this->collectOperation($ajax_data);
        $this->lookupRoute();
        $this->lookupTemplate();
        return $this;
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
     * Assigns filter values from client request data.
     * @param ?array $src Optional array containing client data to use to populate filter values.
     * @param ?int $content_type_id Optional content type numerical identifier that will be assigned as any new filter collection instances' content type.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     * @throws RecordUnavailableException
     */
    public function collectFiltersRequestData(?array $src = null, ?int $content_type_id = null): void
    {
        if ($src === null) {
            $src = static::getAjaxRequestData() ?: $_POST;
        }
        if (!isset($this->filters)) {
            $content_type_id ??= $this->getContentTypeId();
            if (!$content_type_id) {
                throw new ConfigurationUndefinedException('Content type not provided.');
            }
            $this->initializeFiltersObject($content_type_id);
        }
        $this->filters->collectFilterValues(true, [], $src);
    }

    /**
     * Assign an operation value using AJAX or POST data.
     * AJAX or POST data will overwrite any existing value.
     * If no other values are available, the default value will be used.
     * @param ?array $src Optional array containing client data to use to populate filter values.
     * @return void
     * @throws InvalidPropertyException
     */
    protected function collectOperation(?array $src=null): void
    {
        // AJAX data has priority
        $src ??= static::getAjaxRequestData();
        $saved = $this->operation->value;

        // Fallback to POST data
        $this->operation->collectRequestData($src);

        // Restore the previous value if nothing was available in AJAX or POST
        if (!$this->operation->value) {
            $this->operation->value = $saved;
        }

        // Finally, use the default value if available
        if (!$this->operation->value) {
            $this->operation->value = static::getDefault('operation');
        }
    }

    /**
     * Sets the object's action property value based on a value of the variable passed by the commit button in an HTML form.
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
            $this->action = static::COMMIT_ACTION;
            return ($this);
        }
        if (Validation::collectBooleanRequestVar(LittledGlobals::CANCEL_KEY, null, $src) === true) {
            $this->action = static::CANCEL_ACTION;
            return ($this);
        }
        return ($this);
    }

    /**
     * Fills out input values from request data.
     * @param ?array $src Optional array containing request data that will be used as the default source of request data of GET and POST data.
     * @return $this;
     */
    public function collectRequestData(?array $src = null): APIRoute
    {
        $this->operation->collectRequestData($src);
        return $this;
    }

    /**
     * Validates CSRF token from request data.
     * @param ?array $src Optional array containing request data that will be used as the default source of request data of GET and POST data.
     * @return bool
     */
    public static function validateCSRF(?array $src = null): bool
    {
        return RequestValidation::validateCSRF($src === null ? null : (object)$src);
    }

    /**
     * Error handler. Catch the error and return the error message to the client making an ajax request.
     * @param int $err_no
     * @param string $err_str
     * @param string $err_file
     * @param ?int $err_line
     * @return void
     * @throws ResponseException
     */
    public function errorHandler(int $err_no, string $err_str, string $err_file = '', ?int $err_line = null): void
    {
        // remove anything that might currently be in the output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // collect information for the error message
        $msg = "$err_str [$err_no]";
        $msg .= (($err_file) ? (" in $err_file") : (''));
        $msg .= (($err_line) ? ("($err_line)") : (''));

        // populate the "error" attribute of the response
        $this->json->returnError($msg);
    }

    /**
     * Exception handler. Catch exceptions and return the error message to the client making ajax request.
     * @param Exception $ex
     * @return void
     * @throws ResponseException
     */
    public function exceptionHandler(Throwable $ex): void
    {
        $this->json->returnError($ex->getMessage());
    }

    /**
     * Throws a ResponseException exception to indicate the script should exit with an error response.
     * @param string|LittledException $err
     * @return void
     * @throws ResponseException
     */
    protected static function exitWithError(string|LittledException $err): void
    {
        if (is_string($err)) {
            throw new ResponseException($err);
        }
        if ($err instanceof ResponseException) {
            /** @noinspection PhpRedundantVariableDocTypeInspection */
            /** @var ResponseException $err */
            throw $err;
        }
        else {
            throw new ResponseException(message: $err->getMessage(), previous: $err);
        }
    }

    /**
     * Fetches the properties of the template matching the object's content type and, optionally, the name of the
     * template passed to the method. Will use the internal property value if a value is not supplied for the $name
     * argument.
     * @param ?string $operation
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws InvalidRouteException
     * @throws RecordUnavailableException
     */
    public function fetchContentRoute(?string $operation=null): APIRoute
    {
        $operation ??= $this->operation->value;
        if (!$this->getContentTypeId()) {
            $err_msg = 'The content route could not be retrieved. Content type not available.';
            throw new ConfigurationUndefinedException($err_msg);
        }
        if (Validation::isStringBlank($operation)) {
            $err_msg = 'The content route could not be retrieved. Operation not available.';
            throw new ConfigurationUndefinedException($err_msg);
        }
        $this->route = (new ContentRoute())
            ->shareConnection($this)
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
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function fetchContentTemplate(?string $name=null): APIRoute
    {
        $name ??= $this->operation->value;
        if (!$this->getContentTypeId()) {
            $err_msg = 'The content template could not be retrieved. Content type not available.';
            throw new ConfigurationUndefinedException($err_msg);
        }
        if (empty($name)) {
            $err_msg = 'The content template could not be retrieved. Operation not available.';
            throw new ConfigurationUndefinedException($err_msg);
        }
        try {
            $this->template = (new ContentTemplate())
                ->shareConnection($this)
                ->setContentType($this->getContentTypeId())
                ->setOperation($name)
                ->lookupTemplateProperties();
        }
        catch (ConfigurationUndefinedException|NotInitializedException|RecordNotFoundException $e) {
            throw new RecordUnavailableException($e->throwMessage('Unable to load content template'));
        }
        return $this;
    }

    /**
     * Sets the data to be injected into templates.
     * @return array
     */
    public function getTemplateContext(): array
    {
        $context = [
            'page_data' => $this,
            'content' => null,
            'filters' => null];
        if (isset($this->filters)) {
            return array_merge($context, [
                'filters' => &$this->filters,
                'qs' => $this->filters->formatQueryString()]);
        }
        return $context;
    }

    /**
     * Assigns a ContentFilters instance to the $filters property.
     * @param int|null $content_type_id
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    protected function initializeFiltersObject(?int $content_type_id = null): void
    {
        $content_type_id ??= $this->getContentTypeId();
        if (($content_type_id ?: 0) < 1) {
            throw new ConfigurationUndefinedException('Content type not provided in ' . Log::getShortMethodName());
        }
        try {
            $this->filters = call_user_func(
                [static::getControllerClass(), 'getContentFiltersObject'],
                $content_type_id ?: $this->getContentTypeId(),
                $this);
            $this->getContentProperties()->setRecordId($content_type_id);
        }
        catch(ConfigurationUndefinedException|ConnectionException|InvalidTypeException $e) {
            throw new RecordUnavailableException($e->throwMessage('Unable to load content filters'));
        }
    }

    /**
     * Inserts content into a content template. Stores the resulting markup in the object's internal "json" property.
     * @param array|null $context Optional array containing data to inject into the template.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws InvalidPropertyException
     * @throws ResourceNotFoundException
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
     * @throws RecordUnavailableException
     */
    public function lookupRoute(string $operation = ''): APIRoute
    {
        $operation = $operation ?: $this->operation->value;
        if (empty($operation)) {
            return $this;
        }
        $this->route = $this->getContentProperties()->getContentRouteByOperation($operation);
        return $this;
    }

    /**
     * It looks for the template matching $template_name in the currently loaded templates. Sets the object's template
     * property value to that template object.
     * @param string $operation
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function lookupTemplate(string $operation = ''): APIRoute
    {
        $operation = $operation ?: $this->operation->value;
        if (empty($operation)) {
            return $this;
        }
        $this->template = $this->getContentProperties()->getContentTemplateByName($operation);
        return $this;
    }

    /**
     * Returns a new ContentProperties instance. Can be used in derived classes to provide customized
     * ContentProperties objects to the APIRoute class's methods.
     * @param int|null $record_id Initial content type record id value.
     * @return ContentProperties
     * @throws RecordUnavailableException
     */
    protected function newContentPropertiesInstance(?int $record_id = null): ContentProperties
    {
        return new ContentProperties($record_id ?: static::getContentTypeId());
    }

    /**
     * Returns a new ContentTemplate instance. Can be used in derived classes to provide customized ContentTemplate
     * objects to the APIRoute class's methods.
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
        return (new ContentTemplate($record_id, $content_type_id, $operation, $base_dir, $template, $location))
            ->shareConnection($this);
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws InvalidPropertyException
     * @throws ResourceNotFoundException
     */
    public function processRequest(): static
    {
        $this->loadTemplateContent();
        return $this;
    }

    /**
     * Refresh content after performing an AJAX edit on a record. The markup that is generated is stored in the
     * class's JSON property's content property, which is then sent back to the client.
     * @param string $next_operation Token determining which template to load.
     * @param array $context (Optional) Variables to insert into the template. When an array is provided, it will override the default template context. If not provided, the context will be generated using the object's
     * getTemplateContext() routine.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws RecordUnavailableException
     * @throws ResourceNotFoundException
     */
    public function refreshContentAfterEdit(string $next_operation, array $context=[]): void
    {
        $template = ($this->newTemplateInstance())->shareConnection($this);
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
     * @throws RecordUnavailableException
     */
    public function retrieveContentProperties(?int $content_type_id = null): static
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
     * @param array|null $src Optional array containing client data to use to populate filter values.
     * @param string $key Optional key to use to retrieve the content type id from the array.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    protected function retrieveCoreContentProperties(?array $src = null, string $key = ContentProperties::ID_KEY): static
    {
        $cp = $this->getContentProperties();
        if (($cp->getRecordId() ?: 0) < 1) {
            $content_type_id = $this->collectContentTypeIdFromRequestData($src, [$key]);
            if ($content_type_id === null) {
                throw new ConfigurationUndefinedException('Content type not specified.');
            }
            $this->setContentTypeId($content_type_id);
        }
        if ($this->getContentTypeId() === null) {
            throw new ConfigurationUndefinedException('Content type not available.');
        }
        if (!isset($this->filters)) {
            // make sure the content properties are attached to this object.
            try {
                $this->initializeFiltersObject();
                $this->filters->retrieveContentProperties();
                $this->lookupRoute();
                $this->lookupTemplate();
            }
            catch (ConfigurationUndefinedException |
                FailedQueryException |
                InvalidTypeException |
                ReadException |
                RecordNotFoundException $e) {
                throw new RecordUnavailableException($e->throwMessage('Unable to initialize filters object.'));
            }
        }
        return $this;
    }

    /**
     * Retrieve template properties from the database and store them in the page's template property.
     * @param string $template_name Token indicating which type of template to retrieve: details, listings, edit, delete, etc.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws Exception
     */
    public function retrieveTemplateProperties(string $template_name): void
    {
        $this->connectToDatabase();
        $query = 'CALL contentTemplateLookup(?,?)';
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
            $data[0]->location)
        ->shareConnection($this);
    }

    /**
     * @param array $json
     * @return void
     */
    public function sendJsonResponse(array $json): void
    {
        $this->json->sendJsonResponse($json);
    }

    /**
     * Sends out whatever values are currently stored within the object's "json" property as JSON.
     */
    public function sendResponse(string $template_path = '', ?array $context = null): void
    {
        $this->json->sendResponse();
    }

    /**
     * Send current JSON content value as plain text.
     * @param string $response Text to send as a response, if not using value stored in JSON property.
     * @return void
     */
    public function sendTextResponse(string $response = ''): void
    {
        header("Content-Type: text/plain\n\n");
        print($response ?: $this->json->content->value);
    }

    /**
     * Sets property values and throws an exception after the content type value is unsuccessfully validated.
     * @param ContentValidationException|RecordUnavailableException $e
     * @return void
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    protected function throwContentTypeValidationException(ContentValidationException|RecordUnavailableException $e): void
    {
        $this->content_type_id->has_errors = true;
        $this->content_type_id->error = $e->getMessage();
        if (isset($this->filters->content_properties)) {
            $this->filters->content_properties->addValidationError($e->getMessage());
            $this->filters->content_properties->id->has_errors = true;
            $this->filters->content_properties->id->error = $e->getMessage();
        }
        throw $e;
    }

    /**
     * Test object properties for a content type value.
     * @return void
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    protected function validateContentTypeValue(): void
    {
        try {
            if ($this->getContentTypeId() > 0) {
                return;
            }
            $this->throwContentTypeValidationException(new ContentValidationException('Content type is required.'));
        }
        catch(RecordUnavailableException) {
            $this->throwContentTypeValidationException(new RecordUnavailableException('Invalid content type.'));
        }
    }
}
