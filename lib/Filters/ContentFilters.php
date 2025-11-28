<?php
namespace Littled\Filters;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentInitializationException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;


/**
 * Extends FilterCollection to add properties that provide information about the content being retrieved for the listings' data.
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
     * @param MySQLConnection|null $conn
     * @throws ContentInitializationException
     */
    function __construct(string $properties_class = ContentProperties::class, ?MySQLConnection $conn = null)
    {
        try {
            parent::__construct();
            if ($conn) {
                $this->shareConnection($conn);
            }
        }
        catch (NotImplementedException $ex) {
            throw new ContentInitializationException('Class constructor not implemented. ' . $ex->getMessage());
        }
        try {
            $this->content_properties = static::newContentPropertiesInstance(
                properties_class: $properties_class,
                content_type_id: static::getContentTypeId())
                ->shareConnection($this)
                ->read();
            if (!$this->hasConnection()) {
                $this->shareConnection($this->content_properties);
            }
        }
        catch (
            ConfigurationUndefinedException |
            ConnectionException |
            ContentValidationException |
            FailedQueryException |
            InvalidTypeException |
            InvalidValueException |
            NotImplementedException |
            NotInitializedException |
            RecordNotFoundException $ex) {
            $msg = 'Error loading content properties. (' . Log::getClassBaseName($ex::class) . ') ' .$ex->getMessage();
            throw new ContentInitializationException($msg);
        }
    }

    /**
     * Return the label describing this filter's content type.
     * @return string
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
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
     * Return a new instance of this class's ContentProperties class.
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
}