<?php
namespace Littled\Request;

use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Keyword\Keyword;
use Littled\Log\Log;
use Littled\Utility\LittledUtility;

class CategorySelect extends MySQLConnection
{
    protected static int    $content_type_id;
    protected static string $container_template = 'category-container.php';

    /** @var Keyword[] */
    public array            $categories=[];
    public StringSelect     $category_input;
    public StringTextField  $new_category;
    protected int           $parent_id;

    public function __construct()
    {
        parent::__construct();
        $this->category_input = new StringSelect('Category', 'catTerm', false, [], 100);
        $this->category_input->allowMultiple();

        $this->new_category = new StringTextField('New category', 'catNew', false, '', 100);
    }

    /**
     * Collect and store client request data.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public function collectRequestData()
    {
        foreach($this as $property) {
            if (is_object($property) && method_exists($property, 'collectRequestData')) {
                $property->collectRequestData();
            }
        }
        if ($this->new_category->value &&
            !in_array($this->new_category->value, $this->getCategoryTermList())) {
            $this->categories[] = new Keyword($this->new_category->value, $this->getParentId(), static::getContentTypeId());
        }
    }

    /**
     * Returns list of strings with all category terms in use for this content type.
     * @return string[] List of all category terms in use for this content type.
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public static function retrieveCategoryOptions(): array
    {
        $query = 'SELECT term FROM keyword WHERE type_id = ? GROUP BY term';
        $content_type_id = static::getContentTypeId();
        $conn = new MySQLConnection();
        $data = $conn->fetchRecords($query, 'i', $content_type_id);
        return array_map(function ($e) { return $e->term; }, $data);
    }

    /**
     * Returns all the current category terms as a string array.
     * @return string[]
     */
    public function getCategoryTermList(): array
    {
        return array_map(function ($e) { return $e->term->value; }, $this->categories);
    }

    /**
     * Container template filename getter.
     * @return string
     */
    public static function getContainerTemplateFilename(): string
    {
        return static::$container_template;
    }

    /**
     * Container template full path getter.
     * @return string
     */
    public static function getContainerTemplatePath(): string
    {
        return LittledUtility::joinPaths(RequestInput::getTemplatePath(), static::getContainerTemplateFilename());
    }

    /**
     * Content type id value getter.
     * @return int
     * @throws ConfigurationUndefinedException
     */
    public static function getContentTypeId(): int
    {
        if (!isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException(Log::getShortMethodName().' Category content type not configured.');
        }
        return static::$content_type_id;
    }

    /**
     * Parent record id value getter.
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parent_id;
    }

    /**
     * Tests if the instance is in possession of a valid parent record id.
     * @return bool
     */
    public function hasValidParent(): bool
    {
        return (isset($this->parent_id) && $this->parent_id > 0);
    }

    /**
     * For a single parent record, retrieve from the database all categories linked to that parent record.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function read()
    {
        if (!$this->hasValidParent() || !isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException(Log::getShortMethodName().' Parent properties not configured. ');
        }
        $query = 'SELECT `id`, `term` FROM `keyword` WHERE parent_id = ? AND type_id = ?';
        $type_id = static::getContentTypeId();
        $data = $this->fetchRecords($query, 'ii',$this->parent_id, $type_id);
        foreach($data as $row) {
            $this->categories[] = new Keyword($row->term, $this->parent_id, static::getContentTypeId());
        }
    }

    /**
     * Commit category terms to database.
     * @throws Exception
     */
    public function save()
    {
        foreach($this->categories as $category) {
            $category->save();
        }
    }

    /**
     * Container template filename setter.
     * @param string $filename
     * @return void
     */
    public static function setContainerTemplateFilename(string $filename)
    {
        static::$container_template = $filename;
    }

    /**
     * Parent record id setter.
     * @param int $parent_id
     * @return void
     */
    public function setParentId( int $parent_id )
    {
        $this->parent_id = $parent_id;
    }
}