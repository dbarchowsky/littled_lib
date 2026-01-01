<?php
namespace Littled\Filters;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentInitializationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;


/**
 * Extends FilterCollection to add properties that provide information about the content being retrieved for the listings' data.
 *
 * @method getContentTypeId(): int|null
 * @method static getContentTypeId(): int|null
 */
class ContentFilters extends FilterCollection
{
    /** @var string */
    public const                    NEXT_OP_ADD = 'add';
    /** @var string */
    public const                    NEXT_OP_VIEW = 'view';
    /** @var string */
    public const                    NEXT_OP_ADD_IMAGE = 'add_img';
    /** @var string */
    public const                    NEXT_OP_PREVIOUS = 'prev';
    /** @var string */
    public const                    NEXT_OP_LIST = 'list';
    public ContentProperties        $content_properties;
    protected static ?int           $content_type_id = null;

    /**
     * @param string $name
     * @param array $arguments
     * @return int|null
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'getContentTypeId') {
            return $this->_getContentTypeId();
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return int|null
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'getContentTypeId') {
            return (new static())->_getContentTypeId();
        }
        return null;
    }

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
            if ($this->getContentTypeId() > 0) {
                $this->content_properties = static::newContentPropertiesInstance(
                    properties_class: $properties_class,
                    content_type_id: $this->getContentTypeId())
                    ->shareConnection($this)
                    ->read();
                if (!$this->hasConnection()) {
                    $this->shareConnection($this->content_properties);
                }
            }
        }
        catch (
            FailedQueryException |
            InvalidTypeException |
            ReadException |
            RecordNotFoundException $ex) {
            $msg = 'Error loading content properties. (' . Log::getClassBaseName($ex::class) . ') ' .$ex->getMessage();
            throw new ContentInitializationException($msg);
        }
    }

    /**
     * Checks if the content type id property exists and returns its value.
     * @return int|null Class's content type id value, if it has been defined.
     */
    protected function _getContentTypeId(): int|null
    {
        if (isset(static::$content_type_id)) {
            return static::$content_type_id;
        }
        if (isset($this->content_properties)) {
            return $this->content_properties->getRecordId();
        }
        return null;
    }

    /**
     * Return the label describing this filter's content type.
     * @return string
     * @throws NotInitializedException
     * @throws ReadException
     */
    public function getContentLabel(): string
    {
        if (isset($this->content_properties)) {
            return $this->content_properties->getContentLabel();
        }
        return '';
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
     * Retrieves content properties from the database and loads them into the $content_properties property of the object
     * if a content type has been set.
     * @return void
     * @throws FailedQueryException
     * @throws InvalidTypeException
     * @throws ReadException
     * @throws RecordNotFoundException
     */
    public function retrieveContentProperties(): void
    {
        if (!isset($this->content_properties)) {
            $this->content_properties = static::newContentPropertiesInstance(content_type_id: static::$content_type_id ?? null);
        }
        if ($this->content_properties->hasRecordData()) {
            return;
        }
        if ($this->content_properties->getRecordId() > 0) {
            $this->content_properties->read();
            return;
        }
        if (static::$content_type_id > 0) {
            $this->content_properties->setRecordId(static::$content_type_id);
            $this->content_properties->read();
        }
    }

    /**
     * Content type id setter.
     * @param int|null $content_type_id
     * @return $this
     * @throws RecordUnavailableException
     */
    public function setContentTypeId(int|null $content_type_id): static
    {
        try {
            if (isset($this->content_properties)) {
                $this->content_properties->setRecordId($content_type_id);
                if ($content_type_id > 0) {
                    $this->content_properties->read();
                }
            } elseif ($content_type_id > 0) {
                $this->content_properties = static::newContentPropertiesInstance(content_type_id: $content_type_id);
            }
            static::$content_type_id = $content_type_id;
        }
        catch (FailedQueryException|InvalidTypeException|ReadException|RecordNotFoundException $ex) {
            throw new RecordUnavailableException($ex->throwMessage('Unable to load content properties'));
        }
        return $this;
    }
}