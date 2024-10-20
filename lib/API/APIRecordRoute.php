<?php

namespace Littled\API;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Validation\Validation;
use mysqli;


class APIRecordRoute extends APIRoute
{
    public const LISTINGS_TOKEN = 'listings';

    protected static string $listings_token = self::LISTINGS_TOKEN;

    public SectionContent       $content;

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws NotInitializedException|InvalidQueryException
     * @throws RecordNotFoundException|InvalidStateException
     */
    public function collectContentProperties(string $key = LittledGlobals::CONTENT_TYPE_KEY): APIRoute
    {
        parent::collectContentProperties($key);
        $this->collectRecordId();
        return $this;
    }

    /**
     * Convenience routine that will collect the content id from POST
     * data using first the content object's internal id parameter, and then if
     * that value is unavailable, a default id parameter ("id").
     * @param ?array $src Optional array of variables to use instead of POST data.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    public function collectRecordId(?array $src = null): APIRecordRoute
    {
        // first, try extracting the record id from the api route
        if ($this->operation->hasData()) {
            $rp_id = $this->lookupRecordIdRoutePart();
            if ($rp_id) {
                $this->content->setRecordId(Validation::parseNumeric($rp_id));
                if ($this->content->id->value > 0) {
                    return $this;
                }
            }
        }

        // next, collect record id value from ajax/post data using input property's internal parameter name
        $this->content->id->collectRequestData($src);
        if ($this->content->id->value > 0) {
            // Call this routine to assign record id to any linked properties
            $this->content->setRecordId($this->content->id->value);
            return $this;
        }

        // if the internal key value doesn't hold anything, and it's non-default, try looking up the record id value
        // in ajax/post data using the default record id key
        if ($this->content->id->key != LittledGlobals::ID_KEY) {
            $this->content->id->value =
                Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @return $this
     */
    public function collectRequestData(?array $src = null): APIRoute
    {
        parent::collectRequestData($src);
        $this->collectPageAction($src);
        if (!isset($this->content)) {
            $this->initializeContentObject(null, $src);
        }
        $this->content->collectRequestData($src);
        $this->retrieveContentProperties();
        return $this;
    }

    /**
     * Confirm that the child object and its content properties both share a database connection with this parent.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function confirmContentDBConnection(): void
    {
        if (isset($this->content)) {
            $this->content->content_properties->setMySQLi($this->content->getMySQLi());
        }
        elseif (isset($this->filters)) {
            $this->filters->content_properties->setMySQLi($this->filters->getMySQLi());
        }
    }

    /**
     * @inheritDoc
     */
    public function getContentProperties(): ContentProperties
    {
        if ($this->hasContentPropertiesObject()) {
            $this->confirmContentDBConnection();
            if (isset($this->content)) {
                return $this->content->content_properties;
            }
            else {
                return $this->filters->content_properties;
            }
        }
        return parent::getContentProperties();
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
     * Returns singular record id value if that is what is currently stored in the $record_ids property.
     * Null is returned if $record_ids is storing no values or multiple values.
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        if (!isset($this->content)) {
            return null;
        }
        return ($this->content->id->value === false ? null : $this->content->id->value);
    }

    /**
     * Route wildcard getter.
     * @return string
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws RecordNotFoundException
     */
    public function getRouteWildcard(): string
    {
        try {
            $this->confirmRouteIsLoaded();
        }
        catch (NotInitializedException) {
            return '';
        }
        return $this->route->wildcard->value;
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
        return isset($this->content) || isset($this->filters);
    }

    /**
     * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
     * @param ?int $content_type_id Optional content type id to use to retrieve content instance.
     * @param ?array $src Optional array of variables to use instead of POST data.
     * @return $this
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     */
    public function initializeContentObject(?int $content_type_id = null, ?array $src = null): APIRecordRoute
    {
        if (isset($this->content) && Validation::isSubclass($this->content, SerializedContent::class)) {
            // already initialized
            return $this;
        }

        if (!$content_type_id) {
            if ($src === null) {
                // ignore GET request data
                $src = &$_POST;
            }
            $content_type_id = $this->collectContentTypeIdFromRequestData($src);
            if (!$content_type_id) {
                throw new ContentValidationException("Content type not provided.");
            }
        }
        $this->content = call_user_func([static::getControllerClass(), 'getContentObject'], $content_type_id);
        $this->content->setMySQLi($this->getMySQLi());
        return $this;
    }

    /**
     * Takes the current request URI and compares it to the object's route in order to determine if a record id
     * value is embedded in the request URI. It then returns the record id value as determined by the position of
     * the wildcard character or sequence stored in the corresponding content_route record.
     * @param string|null $wildcard
     * @return false|int
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    protected function lookupRecordIdRoutePart(?string $wildcard = null): false|int
    {
        // load the route
        $this->confirmRouteIsLoaded();
        $wildcard ??= $this->route->wildcard->value;
        if (Validation::isStringBlank($wildcard)) {
            return false;
        }

        // offset in request uri to first route part
        if (!isset($_SERVER) || !array_key_exists('REQUEST_URI', $_SERVER)) {
            return false;
        }
        $uri = $_SERVER['REQUEST_URI'];
        $uri_parts = explode('/', trim($uri, '/'));
        $route_parts = explode('/', trim($this->getRoutePath(), '/'));

        $index = array_search($wildcard, $route_parts);
        if ($index !== false) {
            if (count($uri_parts) > $index) {
                $result = Validation::parseInteger($uri_parts[$index]);
                return ($result === null ? false : $result);
            }
            else {
                return false;
            }
        }
        return false;
    }

    /**
     * Retrieves content data from the database
     * @return APIRecordRoute
     * @throws ConfigurationUndefinedException
     */
    public function retrieveContentData(): APIRecordRoute
    {
        if (!$this->hasContentPropertiesObject()) {
            return $this;
        }
        if ($this->content->id->value === null || $this->content->id->value < 1) {
            throw new ConfigurationUndefinedException('A record id was not provided.');
        }
        call_user_func_array([$this::getControllerClass(), 'retrieveContentDataByType'], array($this->content));
        return $this;
    }

    /**
     * Loads the content object and uses the internal record id property value to hydrate the object's property value from the database.
     * @return $this
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws NotInitializedException|InvalidQueryException
     * @throws RecordNotFoundException|ContentValidationException|InvalidStateException
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
     * Hydrates the content properties object by retrieving data from the database.
     * @return mixed
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws FailedQueryException
     * @throws InvalidValueException
     */
    public function retrieveCoreContentProperties(): mixed
    {
        if (!$this->hasContentPropertiesObject()) {
            throw new ConfigurationUndefinedException('Content object not available.');
        }
        $this->confirmContentDBConnection();
        $this->content->content_properties->read();
        return null;
    }

    /**
     * Renders a page content template based on the current content filter values and stores the markup in the object's $json property.
     * @throws ResourceNotFoundException|NotImplementedException
     */
    public function retrievePageContent(): void
    {
        $this->filters->collectFilterValues();
        $this->json->content->value = $this->content->refreshContentAfterEdit($this->filters);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     */
    public function setContentTypeId(int $content_id): APIRoute
    {
        if (!$this->hasContentPropertiesObject()) {
            $this->initializeContentObject($content_id);
        }
        $this->content->content_properties->id->setInputValue($content_id);
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

    public function setResponseContainerId(string $container_id = ''): APIRoute
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

    /**
     * @inheritDoc
     */
    public function setMysqli(mysqli $mysqli): APIRecordRoute
    {
        $this->mysqli = $mysqli;
        if (isset($this->content)) {
            $this->content->setMySQLi($this->getMySQLi());
        }
        return $this;
    }
}