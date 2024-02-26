<?php

namespace Littled\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Exception;


/**
 * Properties of different content types, e.g. content type id, table name, routes, and templates.
 */
class ContentProperties extends SerializedContent
{
    const                           ID_KEY = 'ssid';
    protected static int            $content_type_id = 27;
    protected static string         $table_name = 'site_section';
    /** @var StringTextField        Name of the content. */
    public StringTextField          $name;
    /** @var StringTextField        Label to use to describe the content records on the frontend */
    public StringTextField          $label;
    /** @var StringTextField        Name of variable used to make requests for a particular type of content record. */
    public StringTextField          $id_key;
    /**
     * @var StringTextField         Text token used to identify the content type.
     * @todo Audit the use of this property.
     */
    public StringTextField          $slug;
    /**
     * @var StringTextField         Root directory for section content.
     * @todo Audit the use of this property now that routes are the principle method for responding to client requests.
     */
    public StringTextField          $root_dir;
    /**
     * @var StringTextField         Content able name.
     * @todo Audit this field to determine if it should be deprecated. Consider using SerializedContent::$table_name in its place.
     */
    public StringTextField          $table;
    /** @var IntegerSelect          Numeric identifier of content type that is parent to the principal content type */
    public IntegerSelect            $parent_id;
    /** @var BooleanCheckbox        Flag indicating that this section's content gets cached. */
    public BooleanCheckbox          $is_cached;
    /** @var BooleanCheckbox        Flag indicating that the order of the records on listings pages can be manually reorganized. */
    public BooleanCheckbox          $is_sortable;
    /** @var BooleanCheckbox        Flag indicating to use gallery thumbnails. */
    public BooleanCheckbox          $gallery_thumbnail;
    /** @var string                 Parent content type name. */
    public string                   $parent = '';
    /** @var ContentTemplate[]      List of templates used to render pages displaying record data */
    public array                    $templates = [];
    /** @var ContentRoute[]         List of routes to pages displaying record data */
    public array                    $routes = [];

    /**
     * SiteSection constructor.
     * @param ?int $id Initial value to assign to the object's id property.
     */
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->id->key = ContentProperties::ID_KEY;
        $this->name = new StringTextField("Name", "ssna", true, '', 50);
        $this->label = new StringTextField("Label", "ssLabel", false, '', 50);
        $this->id_key = new StringTextField("Id key", "ssIK", true, '', 20);
        $this->slug = new StringTextField("Slug", "ssSlug", false, '', 50);
        $this->root_dir = new StringTextField("Root directory", "ssrd", false, "", 255);
        $this->table = new StringTextField("Table name", "sstb", false, "", 50);
        $this->parent_id = new IntegerSelect("Parent", "sspi", false, null);
        $this->is_cached = new BooleanCheckbox("Cache content", "sscc", false, false);
        $this->is_sortable = new BooleanCheckbox("Is sortable", "ssSort", false, false);
        $this->gallery_thumbnail = new BooleanCheckbox("Gallery thumbnail", "ssgt", false, false);
    }

    /**
     * Resets the object's property values.
     */
    public function clearValues()
    {
        parent::clearValues();
        $this->resetExtraProperties();
    }

    /**
     * Delete this record from the database. Clears parent id of any child records.
     * @return string Message indicating result of the deletion.
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws Exception
     */
    public function delete(): string
    {
        /* Update parent id for any child records. */
        $query = "UPDATE `" . $this::getTableName() . "` SET `parent_id` = NULL WHERE `parent_id` = ?";
        $this->query($query, 'i', $this->id->value);
        return (parent::delete());
    }

    public function generateUpdateQuery(): ?array
    {
        return array('CALL siteSectionUpdate(@insert_id,?,?,?,?,?,?,?,?,?,?)',
            'ssssssiiii',
            &$this->name->value,
            &$this->label->value,
            &$this->id_key->value,
            &$this->slug->value,
            &$this->root_dir->value,
            &$this->table->value,
            &$this->parent_id->value,
            &$this->is_cached->value,
            &$this->is_sortable->value,
            &$this->gallery_thumbnail->value);
    }

    /**
     * @param string $operation
     * @return ContentRoute|null
     */
    public function getContentRouteByOperation(string $operation): ?ContentRoute
    {
        foreach ($this->routes as $route) {
            if ($operation === $route->operation->value) {
                return $route;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return ContentTemplate|null
     */
    public function getContentTemplateByName(string $name): ?ContentTemplate
    {
        foreach ($this->templates as $template) {
            if ($name === $template->name->value) {
                return $template;
            }
        }
        return null;
    }

    /**
     * Content label getter.
     * @return string
     */
    public function getContentLabel(): string
    {
        return $this->label->value ?: $this->name->value;
    }

    /**
     * Retrieves the parent id of the parent record of the current site_section record, if a parent exists.
     * @return ?int Record id of parent record.
     * @throws InvalidQueryException|Exception
     */
    public function getParentID(): ?int
    {
        if ($this->id->value === null || $this->id->value < 1) {
            return null;
        }
        $query = "CALL siteSectionParentIDSelect(?)";
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) > 0) {
            return ($data[0]->parent_id);
        }
        return null;
    }

    /**
     * Retrieves the content type for the parent of the current content type.
     * @return ?int Content type id of the parent record.
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function getParentTypeID(): ?int
    {
        if ($this->id->value === null || $this->id->value < 1) {
            return null;
        }
        $query = "CALL siteSectionParentTypeID(?);";
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) < 1) {
            throw new RecordNotFoundException("Parent content type not found.");
        }
        return ($data[0]->content_type_id);
    }

    /**
     * Indicates if any form data has been entered for the current instance of the object.
     * @return boolean Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData(): bool
    {
        return ($this->id->value > 0 || $this->name->value);
    }

    /**
     * Returns a new ContentRoute instance. Derived classes can override to provide the object with custom route objects.
     * @param int|null $record_id
     * @param int|null $content_type_id
     * @param string $operation
     * @param string $route
     * @param string $url
     * @return ContentRoute
     */
    protected function newRouteInstance(
        ?int   $record_id = null,
        ?int   $content_type_id = null,
        string $operation = '',
        string $route = '',
        string $url = ''
    ): ContentRoute
    {
        return new ContentRoute($record_id, $content_type_id, $operation, $route, $url);
    }

    /**
     * Returns new ContentTemplate instance. Can be used in derived classes to provide customized ContentTemplate objects to the APIRoute class's methods.
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
        return new ContentTemplate($record_id, $content_type_id, $operation, $base_dir, $template, $location);
    }

    /**
     * Returns a single or plural version of the content type identifier, depending on the number of records.
     * @param int $count Number of records being worked on.
     * @param string $property_name (Optional) Object property holding the identifier for this content. Uses the "name" property unless overridden.
     * @return string String formatted to match the number of records. Either singular or plural.
     * @throws ConfigurationUndefinedException
     */
    public function pluralLabel(int $count, string $property_name = 'name'): string
    {
        return parent::pluralLabel($count, $property_name);
    }

    /**
     * @inheritDoc
     * Overrides parent routine to call procedure to retrieve item properties along with extended item properties.
     * @throws Exception
     */
    public function read(): SerializedContent
    {
        if ($this->id->value === null || $this->id->value < 1) {
            throw new ContentValidationException("Record id not provided.");
        }

        $query = 'CALL siteSectionSelect(?)';
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) < 1) {
            throw new RecordNotFoundException("Requested record not found.");
        }
        $this->hydrateFromRecordsetRow($data[0]);

        // extended properties
        if ($data[0]->parent !== null) {
            $this->parent = $data[0]->parent;
        }

        $this->readRoutes();
        $this->readTemplates();
        return $this;
    }

    /**
     * Retrieve content routes linked to this content type.
     * @throws InvalidQueryException|Exception
     */
    public function readRoutes(): void
    {
        // clear out any existing data
        $this->routes = [];

        $query = "CALL contentRouteSelect(?,?,?)";
        $id = $name = null;
        $data = $this->fetchRecords($query, 'iis', $id, $this->id->value, $name);
        if (count($data) < 1) {
            return;
        }
        foreach ($data as $row) {
            $route = $this->newRouteInstance();
            $route->id->value = $row->id;
            $route->site_section_id->value = $this->id->value;
            $route->hydrateFromRecordsetRow($row);
            $this->routes[] = $route;
        }
    }

    /**
     * Retrieve content templates linked to this content type.
     * @throws InvalidQueryException|Exception
     */
    public function readTemplates(): void
    {
        // clear out any existing data
        $this->templates = [];

        $query = "CALL contentTemplateSelectBySectionID(?)";
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) < 1) {
            return;
            // throw new RecordNotFoundException("Error retrieving content templates.");
        }
        foreach ($data as $row) {
            $template = $this->newTemplateInstance();
            $template->id->value = $row->id;
            $template->content_id->value = $this->id->value;
            $template->hydrateFromRecordsetRow($row);
            $this->templates[] = $template;
        }
    }

    /**
     * Resets the values of class properties not initialized automatically by the parent class.
     * @return void
     */
    protected function resetExtraProperties()
    {
        $this->templates = array();
        $this->routes = array();
        $this->parent = '';
    }
}
