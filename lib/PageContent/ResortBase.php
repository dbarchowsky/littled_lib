<?php

namespace Littled\PageContent;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\PageContent\SiteSection\ContentProperties;
use Exception;


class ResortBase extends MySQLConnection
{
    /** @var StringInput Edit DOM id */
    public StringInput $edit_dom_id;
    /** @var array List of all record ids. */
    public array $id_list = [];
    /** @var ?int ID of parent record. */
    public ?int $parent_id = null;
    /** @var StringInput List of all record positions. */
    public StringInput $position_list;
    /** @var IntegerInput Position of active record within the overall list of records. */
    public IntegerInput $position_offset;
    /** @var ContentProperties Content properties. */
    public ContentProperties $content_properties;
    /** @var StringInput Table type. */
    public StringInput $type;
    /** @var ?int Content type id. */
    public ?int $type_id = null;
    /** @var array Validation errors list. */
    public array $validation_errors = [];

    /**
     * ResortBase constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->content_properties = new ContentProperties();
        $this->content_properties->id->required = true;
        $this->edit_dom_id = new StringInput('Edit DOM ID', 'rid', false, '', 100);
        $this->position_offset = new IntegerInput('Position offset', 'po', true);
        $this->position_list = new StringInput('ID array', 'id', true, '', 10000);
        $this->type = new StringInput('Table type', 't', false, '', 50);
    }

    /**
     * Collect script input and store it in the object's properties. Sets the object's filters property to the filter type appropriate to the section being edited.
     * @param array|null $src Array of variables to use instead of POST data.
     * @throws NotImplementedException
     */
    function collectFromInput(?array $src = null): void
    {
        if ($src === null) {
            $src = array_merge($_POST, $_GET);
        }
        $this->content_properties->id->collectRequestData($src);
        if ($this->content_properties->id->value > 0) {
            $this->retrieveSectionProperties();
        }

        $this->edit_dom_id->collectRequestData($src);
        $this->position_offset->collectRequestData($src);
        $position_str = '';
        if (array_key_exists($this->position_list->key, $src)) {
            $position_str = trim(filter_var($src[$this->position_list->key], FILTER_UNSAFE_RAW));
        }
        if ($position_str) {
            $this->position_list->value = json_decode($position_str);
        }
    }

    /**
     * Tests if any validation errors have been added to the stack.
     * @return bool TRUE if validation errors are detected.
     */
    public function hasValidationErrors(): bool
    {
        return (count($this->validation_errors) > 0);
    }

    /**
     * Get the id's of all the records for resorting.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    public function retrieveExistingIDs(): void
    {
        $data = array();
        switch ($this->content_properties->table->value) {
            case 'ImageLink':
                /*
                 * in the case of images you can't just get all the records in the table
                 * they must be filtered by type and parent id
                 */
                $data = $this->retrieveImageIDs();
                break;

            default:

                /* implement the following line in inherited classes that have a $filters property */
                // $rs = $this->filters->retrieveListings();
                break;
        }

        $this->id_list = array();
        foreach ($data as $row) {
            $this->id_list[] = $row->id;
        }
    }

    /**
     * Retrieve record ids for image_link records.
     * @return array Data set containing ImageLink record ids
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function retrieveImageIDs(): array
    {
        $pos_array = &$this->position_list->value;
        $query = 'SELECT `parent_id`, `type_id` FROM `image_link` WHERE `id` = ' . $pos_array[0];
        $data = $this->fetchRecords($query);
        if (count($data) > 0) {
            $this->parent_id = $data[0]->parent_id;
            $this->type_id = $data[0]->type_id;
        }

        if ((!$this->parent_id) || (!$this->type_id)) {
            throw new RecordNotFoundException('The image record could not be found.');
        }

        $query = 'SELECT il.id ' .
            'FROM image_link il ' .
            'INNER JOIN `images` `full` ON il.fullres_id = `full`.id ' .
            'WHERE il.parent_id = ? AND il.type_id = ? ' .
            'ORDER BY IF(il.access=\'public\', 0, 1), IFNULL(il.slot, 999999), il.id ';
        return ($this->fetchRecords($query, 'ii', $this->parent_id, $this->type_id));
    }

    /**
     * Sets internal filters to retrieve record ids in their current order.
     * To be defined in derived classes.
     * @throws NotImplementedException
     */
    public function retrieveSectionProperties()
    {
        throw new NotImplementedException(get_class($this) . 'retrieveSectionProperties() not defined.');
    }

    /**
     * Commit resorted slot values to database.
     * @return string String containing description of the results of the operation.
     * @throws OperationAbortedException
     */
    function save(): string
    {
        $i = 0;
        try {
            $status = '';
            $last = $this->position_offset->value + count($this->position_list->value);
            for ($i = $this->position_offset->value; $i < $last; $i++) {
                $query = "UPDATE `{$this->content_properties->table->value}` " .
                    'SET slot = ? ' .
                    'WHERE id = ?';
                $this->query($query, 'ii', $i, $this->id_list[$i]);
            }
            $status .= "The new order of the {$this->content_properties->label} records has been saved. \n";

            // updateCache() in class ContentCache is abstract. Figure out the appropriate way to handle this before uncommenting.
            // $status .= ContentCache::updateCache($this->contentProperties, $this->parentID);
        } catch (Exception $ex) {
            throw new OperationAbortedException('Error updating position of record #' . ((count($this->id_list) < $i) ? ($this->id_list[$i]) : ('unavailable')) . ': ' . $ex->getMessage());
        }
        return ($status);
    }

    /**
     * Resort the list of records to match the new order in the listings.
     * @throws InvalidValueException
     */
    public function updatePositions(): void
    {
        if ($this->position_offset->value > count($this->id_list)) {
            throw new InvalidValueException('Invalid offset.');
        }

        $new_positions = &$this->position_offset->value;

        for ($i = 0; $i < count($new_positions); $i++) {
            if (($this->position_offset->value + $i) > count($this->id_list)) {
                break;
            }
            $this->id_list[$this->position_offset->value + $i] = $new_positions[$i];
        }
    }

    /**
     * Validate parameters passed to the script.
     * @throws ContentValidationException
     */
    public function validateInput(): void
    {
        try {
            $this->content_properties->id->validate();
        } catch (ContentValidationException $ex) {
            $this->validation_errors[] = $ex->getMessage();
        }
        $properties = array('editDOMID', 'positionOffset');
        foreach ($properties as $key) {
            /** @var RequestInput $property */
            $property = $this->$key;
            try {
                $property->validate();
            } catch (ContentValidationException $ex) {
                $this->validation_errors[] = $ex->getMessage();
            }
        }
        if (!is_array($this->position_list->value)) {
            $this->validation_errors[] = 'Record ids are required.';
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException('Errors were found in the resort data.');
        }
    }
}