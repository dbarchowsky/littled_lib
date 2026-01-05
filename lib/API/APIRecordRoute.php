<?php
namespace Littled\API;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Validation\Validation;


class APIRecordRoute extends APIRoute
{
    protected static string $listings_token = self::LISTINGS_TOKEN;

    public SectionContent       $content;

    public function __construct()
    {
        parent::__construct();
        static::$default->operation = static::DETAILS_TOKEN;
    }

    /**
     * @inheritDoc
     * @param string $key
     * @return APIRecordRoute
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    public function collectContentProperties(string $key = ContentProperties::ID_KEY): static
    {
        try {
            parent::collectContentProperties($key);
            $this->collectRecordId();
        }
        catch (ConfigurationUndefinedException|RecordUnavailableException $e) {
            throw new RecordUnavailableException($e->throwMessage('Error collection record id value'));
        }
        return $this;
    }

    /**
     * Convenience routine that will collect the content id from POST
     * data using first the content object's internal id parameter, and then if
     * that value is unavailable, a default id parameter ("id").
     * @param ?array $src Optional array of variables to use instead of POST data.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    public function collectRecordId(?array $src = null): APIRecordRoute
    {
        // first, try extracting the record id from the api route
        if ($this->operation->hasData()) {
            $rp_id = $this->lookupRecordIdRoutePart();
            if ($rp_id) {
                if (!isset($this->content)) {
                    $this->initializeContentObject($this->content_type_id->value, $src);
                }
                $this->content->setRecordId(Validation::parseNumeric($rp_id));
                if ($this->content->id->value > 0) {
                    return $this;
                }
            }
        }

        if (!isset($this->content)) {
            $this->initializeContentObject($this->content_type_id->value, $src);
        }

        // save this value in case nothing is available in the request data
        $start_id = $this->content->getRecordId();

        // next, collect record id value from an ajax or POST data using the input property's internal parameter name
        $this->content->id->collectRequestData($src);
        if ($this->content->id->value > 0) {
            // Call this routine to assign record id to any linked properties
            $this->content->setRecordId($this->content->id->value);
            return $this;
        }

        // if the internal key value doesn't hold anything, and it's non-default, try looking up the record id value in
        // AJAX or POST data using the default record id key
        if ($this->content->id->key != LittledGlobals::ID_KEY) {
            $this->content->id->value =
                Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
        }

        // restored the previous value if nothing was available in the request data
        if (($this->content->getRecordId() ?: 0) < 1) {
            $this->content->setRecordId($start_id);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @param array|null $src
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    public function collectRequestData(?array $src = null): APIRoute
    {
        parent::collectRequestData($src);
        $this->collectPageAction($src);
        if (!isset($this->content)) {
            $this->initializeContentObject(null, $src);
        }
        $this->content->collectRequestData($src);
        if ($this->content->getRecordId() > 0) {
            $this->retrieveContentData();
        }
        $this->retrieveContentProperties();
        return $this;
    }

    /**
     * Confirm that the child object and its content properties both share a database connection with this parent.
     * @return void
     */
    protected function confirmContentDBConnection(): void
    {
        if (isset($this->content)) {
            $this->content->shareConnection($this);
        }
        elseif (isset($this->filters)) {
            $this->filters->shareConnection($this);
        }
    }

    /**
     * @inheritDoc
     * @throws RecordUnavailableException
     */
    public function getContentProperties(): ContentProperties
    {
        try {
            if ($this->hasContentPropertiesObject()) {
                if (isset($this->content)) {
                    $this->content->retrieveSectionProperties();
                    return $this->content->content_properties;
                }
                else {
                    $this->filters->retrieveContentProperties();
                    return $this->filters->content_properties;
                }
            }
            $this->initializeFiltersObject($this->getContentTypeId());
            return $this->filters->content_properties;
        }
        catch (ConfigurationUndefinedException |
            FailedQueryException |
            InvalidTypeException |
            ReadException |
            RecordNotFoundException $e) {
            throw new RecordUnavailableException($e->throwMessage('Error retrieving content properties'));
        }
    }

    /**
     * @inheritDoc
     */
    public function getContentTypeKey(): string
    {
        if (isset($this->content)) {
            return $this->content->content_properties->id->key;
        }
        return '';
    }

    /**
     * Listings token getter
     * @return string
     */
    public static function getListingsToken(): string
    {
        return static::$listings_token;
    }

    /**
     * Returns a singular record id value if that is what is currently stored in the $record_ids property.
     * Null is returned if $record_ids is storing no values or multiple values.
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        if (!isset($this->content)) {
            return null;
        }
        return $this->content->getRecordId();
    }

    /**
     * @inheritDoc
     */
    public function getTemplateContext(): array
    {
        return array_merge(
            parent::getTemplateContext(),
            array('content' => (isset($this->content)) ? ($this->content) : (null)));
    }

    /**
     * Route wildcard getter.
     * @return string
     */
    public function getTemplateWildcard(): string
    {
        if (isset($this->template)) {
            return $this->template->wildcard->value;
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function hasContentPropertiesObject(): bool
    {
        return isset($this->content) || parent::hasContentPropertiesObject();
    }

    /**
     * Test if this instance has content properties currently loaded.
     * @return bool
     */
    public function hasContentPropertiesData(): bool
    {
        return (isset($this->content->content_properties) && $this->content->content_properties->id->hasData()) ||
            parent::hasContentPropertiesData();
    }

    /**
     * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
     * @param ?int $content_type_id Optional content type id to use to retrieve content instance.
     * @param ?array $runtime_data Optional array of variables to use instead of POST data.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    public function initializeContentObject(?int $content_type_id = null, ?array $runtime_data = null): APIRecordRoute
    {
        if (isset($this->content) && Validation::isSubclass($this->content, SerializedContent::class)) {
            // already initialized
            return $this;
        }

        $content_type_id ??= $this->getContentTypeId($runtime_data);
        if (!$content_type_id) {
            throw new ContentValidationException('Content type not provided.');
        }

        $this->content = call_user_func([static::getControllerClass(), 'getContentObject'], $content_type_id);
        $this->content->shareConnection($this);
        $this->content_type_id->value = $content_type_id;
        return $this;
    }

    /**
     * Takes the current request URI and compares it to the object's route to determine if a record id
     * value is embedded in the request URI. It then returns the record id value as determined by the position of
     * the wildcard character or sequence stored in the corresponding content_route record.
     * @return bool|int
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    protected function lookupRecordIdRoutePart(): bool|int
    {
        // load the route
        $this->confirmRouteIsLoaded();
        $route_parts = $this->route->explodeRoute();

        // offset in request uri to the first route part
        if (!isset($_SERVER) || !array_key_exists('REQUEST_URI', $_SERVER)) {
            return false;
        }
        $uri = $_SERVER['REQUEST_URI'];
        $uri_parts = explode('/', trim($uri, '/'));
        if (!$this->matchRouteParts($uri_parts, $route_parts)) {
            return false;
        }

        $int_wildcards = [];
        foreach (static::$placeholders as $ph) {
            if ($ph->type === 'int') {
                $int_wildcards[] = $ph->wildcard;
            }
        }
        $i = 0;
        foreach ($route_parts as $e) {
            if (in_array($e, $int_wildcards)) {
                if (count($uri_parts) > $i) {
                    $result = Validation::parseInteger($uri_parts[$i]);
                    return ($result === null ? false : $result);
                }
            }
            $i++;
        }
        return false;
    }

    /**
     * Retrieves content data from the database
     * @return APIRecordRoute
     * @throws ConfigurationUndefinedException
     * @throws RecordUnavailableException
     */
    public function retrieveContentData(): APIRecordRoute
    {
        if (!$this->hasContentPropertiesObject()) {
            return $this;
        }
        if (!($this->content->getRecordId() > 0)) {
            throw new ConfigurationUndefinedException('A record id was not provided.');
        }
        $this->content->read();
        return $this;
    }

    /**
     * Loads the content object and uses the internal record id property value to hydrate the object's property value from the database.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordUnavailableException
     */
    public function retrieveContentObjectAndData(): APIRecordRoute
    {
        $ajax_data = static::getAjaxRequestData();
        return $this
            ->initializeContentObject(null, $ajax_data)
            ->collectRecordId($ajax_data)
            ->retrieveContentData();
    }

    /**
     * @inheritDoc
     */
    public function retrieveCoreContentProperties(?array $src = null, string $key = ContentProperties::ID_KEY): static
    {
        $content_type_id = $this->getContentTypeId();
        if ($content_type_id === null) {
            throw new ConfigurationUndefinedException('Content type not available.');
        }
        try {
            if (isset($this->content)) {
                // considering the content type id value may come from AJAX or POST data and may be different from
                // an existing value, reload the content properties from the database with the current value
                $this->content->setContentType($content_type_id);
                $this->content->retrieveSectionProperties();
            } elseif (isset($this->filters)) {
                // reload content properties goes if there is a filters object initialized but no content object
                $this->filters->setContentTypeId($content_type_id);
                $this->filters->retrieveContentProperties();
            } else {
                // store content properties in the content object if there is currently no content or filters object
                $this->initializeContentObject($content_type_id, $src);
                $this->content->retrieveSectionProperties();
            }
        }
        catch (ContentValidationException|
            FailedQueryException|
            InvalidTypeException|
            ReadException|
            RecordNotFOundException $e) {
            throw new RecordUnavailableException($e->throwMessage('Error retrieving content properties'));
        }
        $this->lookupRoute();
        $this->lookupTemplate();
        return $this;
    }

    /**
     * Renders a page content template based on the current content filter values and stores the markup in the
     * object's JSON property.
     * @throws ResourceNotFoundException|NotImplementedException
     */
    public function retrievePageContent(): void
    {
        $this->filters->collectFilterValues();
        $this->json->content->value = $this->content->refreshContentAfterEdit($this->filters);
    }

    /**
     * @inheritDoc
     */
    public function setContentTypeId(?int $content_type_id): static
    {
        parent::setContentTypeId($content_type_id);
        if (isset($this->content)) {
            $this->content->setContentType($content_type_id);
        }
        return $this;
    }

    /**
     * Listings token getter.
     * @param string $token
     * @return void
     */
    public static function setListingsToken(string $token): void
    {
        static::$listings_token = $token;
    }

    /**
     * @inheritDoc
     */
    public function setResponseContainerId(string $container_id = ''): static
    {
        parent::setResponseContainerId($container_id);
        $container_id = $this->json->container_id->value;
        $wildcard = $this->getTemplateWildcard();
        if ($wildcard && $this->getRecordId() > 0 && str_contains($container_id, $wildcard)) {
            $container_id = str_replace($wildcard, (string)$this->getRecordId(), $container_id);
            $this->json->container_id->value = $container_id;
        }
        return $this;
    }
}