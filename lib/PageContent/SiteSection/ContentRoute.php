<?php

namespace Littled\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\StringInput;
use Littled\Request\StringTextField;
use Littled\Request\URLTextField;
use Littled\Validation\Validation;
use mysqli;

/**
 * Extends SerializedContent to store and retrieve content route properties.
 */
class ContentRoute extends SerializedContent
{
    /** @var string                 Token representing operation property */
    const                           PROPERTY_TOKEN_OPERATION = 'operation';
    /** @var string                 Token representing route property */
    const                           PROPERTY_TOKEN_ROUTE = 'route';
    /** @var string                 Token representing api route path */
    const                           PROPERTY_TOKEN_API_ROUTE = 'apiRoute';
    /** @var string                 Token representing route property in array format */
    const                           PROPERTY_TOKEN_ROUTE_AS_ARRAY = 'routeArray';
    /** @var string                 Token representing api route property in array format */
    const                           PROPERTY_TOKEN_API_ROUTE_AS_ARRAY = 'apiRouteArray';

    /** @var int                    Value of this record in the site section table. */
    protected static int            $content_type_id = 34;
    protected static string         $table_name = 'content_route';

    /** @var IntegerSelect          Record id representing the site content. Corresponds to table `site_section`. */
    public IntegerSelect            $site_section_id;
    /** @var StringTextField        Token representing the action taken on the content, e.g. 'listings', 'details', or 'edit'. */
    public StringTextField          $operation;
    /** @var StringTextField        The route on the site to this content. */
    public StringTextField          $route;
    /** @var StringTextField        The URL used to retrieve and refresh content. */
    public StringTextField          $api_route;
    /** @var StringTextField        The wildcard used to inject record ids into route strings. */
    public StringTextField          $wildcard;

    /**
     * Class constructor
     * @param int|null $id
     * @param int|null $route_content_type_id
     * @param string $operation
     * @param string $route
     * @param string $api_route
     */
    public function __construct(
        ?int   $id = null,
        ?int   $route_content_type_id = null,
        string $operation = '',
        string $route = '',
        string $api_route = '')
    {
        parent::__construct($id);

        $this->id->label = 'Content route id';
        $this->id->key = 'routeId';
        $this->id->required = false;
        $this->site_section_id = new IntegerSelect('Site Section', 'routeSectionId', true, $route_content_type_id);
        $this->operation = new StringTextField('Name', 'routeOp', true, $operation, 45);
        $this->route = new StringTextField('Route', 'route', false, $route, 255);
        $this->api_route = new URLTextField('URL', 'apiRoute', true, $api_route, 256);
        $this->wildcard = new StringTextField('Wildcard', 'routeWC', false, '', 8);
    }

    /**
     * Returns API route as array of its components.
     * @return array
     */
    public function explodeAPIRoute(): array
    {
        return static::explodeRouteString($this->api_route->value);
    }

    /**
     * Returns page route as array of its components.
     * @return array
     */
    public function explodeRoute(): array
    {
        return static::explodeRouteString($this->route->value);
    }

    /**
     * Returns route string as array of its components.
     * @param string $route
     * @return array
     */
    protected static function explodeRouteString(string $route): array
    {
        $route = trim($route, '/');
        if (!$route) {
            return [];
        }
        return explode('/', $route);
    }

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        return array('CALL contentRouteUpdate(@insert_id,?,?,?,?,?)',
            'issss',
            &$this->site_section_id->value,
            &$this->operation->value,
            &$this->route->value,
            &$this->api_route->value,
            &$this->wildcard->value);
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'Content route';
    }

    /**
     * Returns the values of the RequestInput properties.
     * @param string $property
     * @return mixed The value of the requested property.
     * @throws InvalidValueException
     */
    public function getPropertyValue(string $property): mixed
    {
        return match ($property) {
            self::PROPERTY_TOKEN_OPERATION => $this->operation->value,
            self::PROPERTY_TOKEN_ROUTE => $this->route->value,
            self::PROPERTY_TOKEN_ROUTE_AS_ARRAY => explode('/', trim('' . $this->route->value, '/')),
            self::PROPERTY_TOKEN_API_ROUTE => $this->api_route->value,
            self::PROPERTY_TOKEN_API_ROUTE_AS_ARRAY => explode('/', trim('' . $this->api_route->value, '/')),
            default => throw new InvalidValueException('Invalid property token.'),
        };
    }

    /**
     * @inheritDoc
     */
    public function hasRecordData(): bool
    {
        return $this->api_route->value || $this->operation->value || $this->route->value;
    }

    /**
     * Inject record id value into an api route string containing a wildcard character holding the place for
     * the id value.
     * @param int $record_id Record id value to insert into the route.
     * @return string Route containing record id.
     * @throws InvalidValueException
     */
    public function insertRecordIdIntoAPIRoute(int $record_id): string
    {
        return $this->insertRecordIdIntoRouteProperty($record_id, 'api_route');
    }

    /**
     * Inject record id value into a route string containing a wildcard character holding the place for the id value.
     * @param int $record_id Record id value to insert into the route.
     * @return string Route containing record id.
     * @throws InvalidValueException
     */
    public function insertRecordIdIntoRoute(int $record_id): string
    {
        return $this->insertRecordIdIntoRouteProperty($record_id, 'route');
    }

    /**
     * Inject record id value into a route string containing a wildcard character holding the place for the id value.
     * @param int $record_id Record id value to insert into the route.
     * @param string $property Name of the route property to use to retrieve base route value.
     * @return string Route containing record id.
     * @throws InvalidValueException
     */
    protected function insertRecordIdIntoRouteProperty(int $record_id, string $property): string
    {
        if (!property_exists($this, $property) || !Validation::isSubclass($this->$property, StringInput::class)) {
            throw new InvalidValueException("Invalid route property \"$property\".");
        }
        return str_replace($this->wildcard->value, (string)$record_id, $this->$property->value);
    }

    /**
     * Retrieve content route properties using values currently stored in the object. Either the record id value
     * or a combination of site section id and operation values.
     * @return $this
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     */
    public function lookupRoute(): ContentRoute
    {
        if (($this->id->value===null || $this->id->value < 1) &&
            (($this->site_section_id->value === null || $this->site_section_id->value < 0) ||
            trim(''.$this->operation->value) === '')) {
            $err_msg = 'Either a route id or content type and operation are required.';
            throw new ConfigurationUndefinedException($err_msg);
        }
        $query = 'CALL contentRouteSelect(?,?,?)';
        $result = $this->fetchRecords($query, 'iis',
            $this->id->value,
            $this->site_section_id->value,
            $this->operation->value);
        if (count($result) < 1) {
            throw new RecordNotFoundException('A matching content route record was not found.');
        }
        /* id field is ignored in hydrateFromRRR() */
        $this->setRecordId($result[0]->id);
        $this->hydrateFromRecordsetRow($result[0]);
        return $this;
    }

    /**
     * Operation setter.
     * @param string $route
     * @return $this
     */
    public function setAPIRoute(string $route): ContentRoute
    {
        $this->api_route->value = $route;
        return $this;
    }

    /**
     * Operation setter.
     * @param int $content_id
     * @return $this
     */
    public function setContentType(int $content_id): ContentRoute
    {
        $this->site_section_id->value = $content_id;
        return $this;
    }

    public function setMySQLi(mysqli $mysqli): ContentRoute
    {
        parent::setMySQLi($mysqli);
        return $this;
    }

    /**
     * Operation setter.
     * @param string $operation
     * @return $this
     */
    public function setOperation(string $operation): ContentRoute
    {
        $this->operation->value = $operation;
        return $this;
    }

    /**
     * Operation setter.
     * @param string $route
     * @return $this
     */
    public function setRoute(string $route): ContentRoute
    {
        $this->route->value = $route;
        return $this;
    }

    /**
     * Site section id setter.
     * @param int $site_section_id
     * @return $this
     */
    public function setSiteSectionId(int $site_section_id): ContentRoute
    {
        $this->site_section_id->setInputValue($site_section_id);
        return $this;
    }

    /**
     * Site section id setter.
     * @param string $wildcard
     * @return $this
     */
    public function setWildcard(string $wildcard): ContentRoute
    {
        $this->wildcard->setInputValue($wildcard);
        return $this;
    }
}