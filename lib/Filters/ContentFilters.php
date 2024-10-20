<?php

namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\SiteSection\ContentProperties;
use Exception;
use Littled\Validation\Validation;
use mysqli;


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
     * @param mysqli|null $mysqli Optional mysqli connection to assign to the filters object
     * @throws ConfigurationUndefinedException Database connections properties not set.
     * @throws Exception Error retrieving content section properties.
     */
    function __construct(string $properties_class = ContentProperties::class, ?mysqli $mysqli = null)
    {
        parent::__construct();
        if (!Validation::isSubclass($properties_class, ContentProperties::class)) {
            throw new InvalidTypeException('Invalid content properties type.');
        }
        $this->content_properties = self::newContentPropertiesInstance(
            properties_class: $properties_class,
            content_type_id: self::getContentTypeId())
            ->setMySQLi($mysqli ?: $this->getMySQLi())
            ->read();
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
     * Return new instance of this class's ContentProperties class.
     * @param string $properties_class
     * @param int|null $content_type_id
     * @return ContentProperties
     * @throws InvalidTypeException
     */
    protected static function newContentPropertiesInstance(
        string $properties_class = '',
        int|null $content_type_id = null): ContentProperties
    {
        if ($properties_class !== '') {
            if (!Validation::isSubclass($properties_class, ContentProperties::class)) {
                throw new InvalidTypeException("Invalid content properties type: \"$properties_class\"");
            }
            return new $properties_class($content_type_id);
        }
        return new ContentProperties($content_type_id);
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
    public static function setContentTypeId(int $content_id): void
    {
        static::$content_type_id = $content_id;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function setMySQLi(mysqli $mysqli): ContentFilters
    {
        parent::setMySQLi($mysqli);
        if (isset($this->content_properties)) {
            $this->content_properties->setMySQLi($this->getMySQLi());
        }
        return $this;
    }
}