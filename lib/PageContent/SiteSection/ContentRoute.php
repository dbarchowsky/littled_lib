<?php
namespace Littled\PageContent\SiteSection;

use Littled\Exception\InvalidValueException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Littled\Request\URLTextField;

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
	/** @var string */
	protected static string         $table_name = "content_route";

	/** @var IntegerSelect          Record id representing the site content. Corresponds to table `site_section`. */
	public IntegerSelect            $site_section_id;
	/** @var StringTextField        Token representing the action taken on the content, e.g. 'listings', 'details', or 'edit'. */
	public StringTextField          $operation;
    /** @var StringTextField        The route on the site to this content. */
    public StringTextField          $route;
	/** @var StringTextField        The URL used to retrieve and refresh content. */
	public StringTextField          $api_route;

	/**
	 * Class constructor
	 * @param int|null $id
	 * @param int|null $route_content_type_id
	 * @param string $operation
     * @param string $route
	 * @param string $api_route
	 */
	public function __construct(
        ?int $id = null,
        ?int $route_content_type_id=null,
        string $operation='',
        string $route='',
        string $api_route='')
	{
		parent::__construct($id);

		$this->id->label = "Content route id";
		$this->id->key = 'routeId';
		$this->id->required = false;
		$this->site_section_id = new IntegerSelect('Site Section', 'routeSectionId', true, $route_content_type_id);
		$this->operation = new StringTextField('Name', 'routeOp', true, $operation, 45);
        $this->route = new StringTextField('Route', 'route', false, $route, 255);
		$this->api_route = new URLTextField('URL', 'apiRoute', true, $api_route, 256);
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
	public function generateUpdateQuery(): ?array
	{
		return array('CALL contentRouteUpdate(@record_id,?,?,?,?)',
			'isss',
			&$this->site_section_id->value,
			&$this->operation->value,
            &$this->route->value,
			&$this->api_route->value);
	}

    /**
     * Returns the values of the RequestInput properties.
     * @param string $property
     * @return mixed The value of the requested property.
     * @throws InvalidValueException
     */
    public function getPropertyValue(string $property)
    {
        switch($property) {
            case self::PROPERTY_TOKEN_OPERATION:
                return $this->operation->value;
            case self::PROPERTY_TOKEN_ROUTE:
                return $this->route->value;
            case self::PROPERTY_TOKEN_ROUTE_AS_ARRAY:
                return explode('/', trim(''.$this->route->value, '/'));
            case self::PROPERTY_TOKEN_API_ROUTE:
                return $this->api_route->value;
            case self::PROPERTY_TOKEN_API_ROUTE_AS_ARRAY:
                return explode('/', trim(''.$this->api_route->value, '/'));
            default:
                throw new InvalidValueException('Invalid property token.');
        }
    }

	/**
	 * @inheritDoc
	 */
	public function hasData(): bool
	{
		return ($this->id->value > 0 || $this->api_route->value || $this->operation->value || $this->route->value);
	}
}