<?php

namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\SiteSection\ContentProperties;
use Exception;
use Littled\Validation\Validation;


/**
 * Extends FilterCollection to add properties that provide information about the content being retrieved for listings data.
 */
class ContentFilters extends FilterCollection
{
    /** @var string */
    public const NEXT_OP_ADD = 'add';
    /** @var string */
    public const NEXT_OP_VIEW = 'view';
    /** @var string */
    public const NEXT_OP_ADD_IMAGE = 'add_img';
    /** @var string */
    public const NEXT_OP_PREVIOUS = 'prev';
    /** @var string */
    public const NEXT_OP_LIST = 'list';
    public ContentProperties $content_properties;
    protected static ?int $content_type_id = null;

    /**
     * ContentFilters constructor.
     * @param string $properties_class Optional subclass of ContentProperties.
     * @throws ConfigurationUndefinedException Database connections properties not set.
     * @throws Exception Error retrieving content section properties.
     */
    function __construct(string $properties_class = ContentProperties::class)
    {
        parent::__construct();
        if (!Validation::isSubclass($properties_class, ContentProperties::class)) {
            throw new InvalidTypeException('Invalid content properties type.');
        }
        $this->content_properties = new $properties_class(self::getContentTypeId());
        $this->content_properties->setMySQLi(static::getMysqli());
        $this->content_properties->read();
    }

    /**
     * Return the label describing this filter's content type.
     * @return string
     */
    public function getContentLabel(): string
    {
        if (isset($this->content_properties)) {
            return $this->content_properties->getContentLabel();
        }
        return '';
    }

    /**
     * Content type id getter.
     * @return int
     * @throws NotImplementedException
     */
    public static function getContentTypeId(): int
    {
        if (!static::$content_type_id) {
            throw new NotImplementedException('Content type id not set in ' . get_called_class() . '.');
        }
        return static::$content_type_id;
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function pluralLabel(): string
    {
        return $this->content_properties->pluralLabel($this->record_count);
    }

    /**
     * Content type id setter.
     * @param int $content_id
     * @return void
     */
    public static function setContentTypeId(int $content_id)
    {
        static::$content_type_id = $content_id;
    }
}