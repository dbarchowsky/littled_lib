<?php

namespace Littled\PageContent\Albums;

use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Images\ImageLink;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Request\IntegerInput;
use stdClass;


/**
 * A collection of images.
 */
class Gallery extends MySQLConnection
{
    /** @var ContentProperties Section properties. */
    public ContentProperties $content_properties;
    /** @var ?int Parent record id. */
    public ?int $parent_id;
    /** @var string Label for inserting into page content. */
    public string $label;
    /** @var ImageLink[] List of image_link_class objects representing the images in the gallery */
    public array $list;
    /** @var ImageLink Thumbnail record. */
    public ImageLink $tn;
    /** @var IntegerInput Pointer to the thumbnail id object for convenience. */
    public IntegerInput $tn_id;
    /** @var IntegerInput to the content type id object for convenience. */
    public IntegerInput $type_id;
    /** @var integer Current number of images in the gallery. */
    public int $image_count;
    /** @var string[] List of errors found in object property values. */
    public array $validation_errors;

    /**
     * Gallery constructor.
     * @param int $content_type_id Content type of the gallery
     * @param int|null[optional] $parent_id The id of the gallery's parent record.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    function __construct(int $content_type_id, ?int $parent_type_id = null)
    {
        parent::__construct();
        $this->content_properties = new ContentProperties($content_type_id);
        $this->parent_id = $parent_type_id;
        $this->label = "Image";

        $this->list = array();
        $this->tn = new ImageLink("", "", $content_type_id, $this->parent_id);
        $this->tn_id = &$this->tn->id;
        $this->type_id = &$this->content_properties->id;
        $this->image_count = -1;
        $this->retrieveSectionProperties();
        $this->validation_errors = array();
    }

    /**
     * Returns the form data members of the objects as series of nested associative arrays.
     * @param array|null $exclude_keys (Optional) array of parameter names to exclude from the returned array.
     * @return array Associative array containing the object's form data members as name/value pairs.
     */
    public function arrayEncode(?array $exclude_keys = null): array
    {
        $ar = array();
        foreach ($this as $key => $item) {
            if (is_object($item)) {
                if (!is_array($exclude_keys) || !in_array($key, $exclude_keys)) {
                    if (is_subclass_of($item, "RequestInput")) {
                        $ar[$key] = $item->value;
                    } elseif (is_subclass_of($item, "SerializedContent")) {
                        /** @var SerializedContent $item */
                        $ar[$key] = $item->arrayEncode();
                    }
                }
            } elseif ($key == "list") {
                $ar[$key] = array();
                if (is_array($item)) {
                    foreach ($item as $img_lnk) {
                        $ar[$key][count($ar[$key])] = $img_lnk->arrayEncode(array("site_section"));
                    }
                }
            }
        }
        return ($ar);
    }

    /**
     * Sets internal values of the object with form data.
     * @param int[optional] $section_id If specified, sets section id before retrieving form data, otherwise uses the current internal section id value.
     * @param array|null[optional] $src If specified, this array will be used to extract values that are stored in
     * the object properties. Otherwise, the values are extracted from POST data.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    public function collectFromInput($section_id = null, $src = null)
    {
        if ($section_id > 0) {
            $this->content_properties->id->value = $section_id;
        }

        $this->testForContentType();
        $this->retrieveSectionProperties();

        $this->list = array();
        $id_param = $this->content_properties->getRecordsetPrefix() . ImageLink::vars['id'];
        $img_id_param = $this->content_properties->getRecordsetPrefix() . ImageLink::vars['id'];
        $iCount = 0;
        if ($src === null) {
            $src = $_POST;
        }
        if (isset($src[$id_param])) {
            $iCount = count($src[$id_param]);
        } elseif (isset($src[$img_id_param])) {
            /* with new records there won't be an image_link id, only an image id */
            $iCount = count($src[$img_id_param]);
        }

        for ($i = 0; $i < $iCount; $i++) {
            $this->list[$i] = new ImageLink('', $this->content_properties->getRecordsetPrefix(), $this->content_properties->id->value);
            $this->list[$i]->collectRequestData();
        }

        $this->tn->collectRequestData();
    }

    /**
     * Deletes all images records attached to the current gallery including the image files on disk and all the keywords assigned to the images. Also deletes the gallery thumbnail.
     * @return string String describing the results of the operation.
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    function delete(): string
    {
        $status = "";
        $image_ids = array();
        foreach ($this->list as $image_link) {
            $image_ids[] = $image_link->id->value;
            $status .= $image_link->delete();
        }
        $this->list = array();

        if ($this->tn->id->value > 0 && !in_array($this->tn->id->value, $image_ids)) {
            $status .= $this->tn->delete();
        }
        return $status;
    }

    /**
     * Retrieves the "gallery thumbnail" setting for the gallery, which indicates that a thumbnail image is expected for the gallery.
     * @return array Containing the gallery thumbnail setting and the parent id of the gallery.
     * @throws InvalidQueryException
     * @throws Exception
     */
    protected function fetchGalleryThumbnail(): array
    {
        $query = "CALL galleryGalleryThumbnailSettingSelect(?)";
        $content_type_id = $this->getContentTypeId();
        $data = $this->fetchRecords($query, 'i', $content_type_id);
        if (count($data) > 0) {
            return (array($data[0]->gallery_thumbnail, $data[0]->parent_id));
        }
        return array(null, null);
    }

    /**
     * Assigns ImageLink property values using data from query.
     * @param ImageLink $image_link Image set to fill with the data from the recordset row.
     * @param stdClass $row Recordset row containing data to store in ImageLink object.
     * @param bool $read_keywords (Optional) Flag indicating that keywords should be retrieved for each image. Default value is FALSE.
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function fillImageSetFromRecordset(ImageLink $image_link, stdClass $row, bool $read_keywords = false)
    {
        $image_link->fillFromRecordset($row);
        if ($read_keywords) {
            $image_link->readKeywords();
        }
    }

    /**
     * Formats a string that reports the number of items in the gallery and
     * the type of items represented by the gallery.
     * @return string String reporting number of items and type of items.
     */
    public function formatItemCountString(): string
    {
        return (count($this->list) . " " . strtolower($this->content_properties->image_label->value) . ((count($this->list) != 1) ? ("s") : ("")));
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
     * @return int|null The id of the objects content type.
     */
    public function getContentTypeId(): ?int
    {
        return $this->content_properties->getRecordId();
    }

    /**
     * Returns the number of images in the gallery even if the gallery array hasn't been filled yet.
     * @param string[optional] $access Limits count to a particular access level.
     * @return int image count
     */
    public function getImageCount($access = ""): int
    {
        if (isset($this->list)) {
            return count($this->list);
        } elseif ($this->image_count >= 0) {
            return $this->image_count;
        } else {
            $query = "SELECT COUNT(1) AS `count` FROM `image_link` " .
                "WHERE parent_id = $this->parent_id " .
                "AND type_id = {$this->type_id->value}";
            $types_str = 'ii';
            $vars = array($this->parent_id, $this->type_id->value);
            if ($access) {
                $query .= " AND access = ?";
                $types_str .= 's';
                $vars[] = $access;
            }
            array_unshift($vars, $query, $types_str);
            $data = call_user_func_array([$this, 'fetchRecords'], $vars);
            return $data[0]->count;
        }
    }

    /**
     * Checks if any form data has been stored in the object that in turns requires storage in the database.
     * @return bool TRUE if data is found in the object. FALSE if data is not found in the object.
     */
    public function hasData(): bool
    {
        foreach ($this->list as $image_link) {
            if ($image_link->hasData()) {
                return (true);
            }
        }
        return (false);
    }

    /**
     * Returns TRUE/FALSE if the current page is the first page in the gallery.
     * @return bool TRUE if the current page is the first page in the gallery. FALSE otherwise.
     */
    public function isFirstPage(): bool
    {
        return (
            property_exists($this->list[0], "is_first_page") && (
                ($this->list[0]->is_first_page->value == true) ||
                (count($this->list) > 1 && $this->list[1]->is_first_page->value == true)));
    }

    /**
     * Returns TRUE/FALSE if the current page is the last page in the gallery.
     * @return bool TRUE if the current page is the last page in the gallery. FALSE otherwise.
     */
    public function isLastPage(): bool
    {
        return (
            property_exists($this->list[0], "is_last_page") && (
                ($this->list[0]->is_last_page->value == true) ||
                (count($this->list) > 1 && $this->list[1]->is_last_page->value == true)));
    }

    /**
     * Retrieve all images in the collection.
     * @param bool[optional] $bReadKW Optional flag to control if keywords are read for the listings. Defaults to false.
     * @param bool[optional] $bReadTn Optional flag to control if the collection's thumbnail record will be retrieved. Defaults to true.
     * @param bool[optional] $bPublicOnly Optional flag to indicate that only public images should be retrieved.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function read($read_keywords = false, $read_thumbnails = true, $public_only = false)
    {
        $this->testForContentTypeAndParent();

        $this->retrieveSectionProperties();

        $content_type_id = $this->getContentTypeId();
        $data = $this->fetchRecords("CALL gallerySelect(?,?,?)",
            'iii',
            $this->parent_id,
            $content_type_id,
            $public_only);

        $this->list = array();
        foreach ($data as $row) {
            $i = count($this->list);
            $this->list[$i] = new ImageLink(
                $this->content_properties->image_path->value,
                $this->content_properties->param_prefix->value,
                $this->content_properties->id->value,
                $this->parent_id,
                $row->id);
            $this->fillImageSetFromRecordset($this->list[$i], $row, $read_keywords);
        }

        if ($read_thumbnails) {
            $this->readThumbnail();
        }
    }

    /**
     * Retrieves the thumbnail properties for the gallery.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function readThumbnail()
    {
        $this->testForContentTypeAndParent();
        $this->tn->parent_id->value = $this->parent_id;

        list($gallery_thumbnail, $parent_content_id) = $this->fetchGalleryThumbnail();

        if ($gallery_thumbnail === null || $parent_content_id > 0) {
            return;
        }

        $content_type_id = $this->getContentTypeId();
        $data = $this->fetchRecords("CALL galleryExternalThumbnailSelect(?,?)",
            'ii',
            $this->parent_id,
            $content_type_id);
        if (count($data) > 0) {
            $this->tn_id->value = $data[0]->thumbnail_id;
        }

        if ($this->tn_id->value > 0) {
            $this->tn->read();
        }
    }

    /**
     * Retrieves the gallery's content properties from database. Sets object's internal values.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    public function retrieveSectionProperties()
    {
        $this->testForContentType();
        $this->content_properties->read();
        list($parent_gallery_thumbnail) = $this->fetchGalleryThumbnail();

        if ($parent_gallery_thumbnail) {
            /**
             * If the thumbnail is a pointer to one of the images in the gallery its content type value
             * should match the gallery's content type.
             */
            $this->tn->type_id->value = $this->type_id->value;
        } elseif ($this->content_properties->parent_id->value > 0) {
            $this->tn->type_id->value = $this->content_properties->parent_id->value;
        }
        $this->tn->retrieveSectionProperties();

        if ($this->content_properties->image_label->value !== null &&
            $this->content_properties->image_label->value != "") {
            $this->label = $this->content_properties->image_label->value;
        }
    }

    /**
     * Commits the object's internal properties to the database.
     * @param boolean[optional] $save_thumbnail Flag to specify that a thumbnail record should be saved along with
     * the gallery's core properties. FALSE by default.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws OperationAbortedException
     * @throws RecordNotFoundException
     * @throws ResourceNotFoundException
     */
    public function save($save_thumbnail = false)
    {
        /** @var ImageLink $img */
        foreach ($this->list as $img) {
            if ($img->hasData()) {
                $img->parent_id->value = $this->parent_id;
                $img->save();
            }
        }

        if ($save_thumbnail) {
            $this->saveThumbnail();
        }
    }

    /**
     * Updates parent table's tn_id column with the id of the current thumbnail record.
     * @throws InvalidQueryException|Exception
     */
    public function saveThumbnail()
    {
        try {
            $this->testForContentTypeAndParent();
        } catch (ConfigurationUndefinedException $ex) {
            return;
        }

        /* get parent content properties */
        $parent_content_id = $parent_table = null;
        $query = "SELECT p.`id`, p.`table` " .
            "FROM 'site_section' p " .
            "INNER JOIN `site_section` c ON p.`id` = c.`parent_id` " .
            "WHERE c.`id` = ?";
        $content_type_id = $this->getContentTypeId();
        $data = $this->fetchRecords($query, 'i', $content_type_id);
        if (count($data) > 0) {
            $parent_content_id = $data[0]->id;
            $parent_table = $data[0]->table;
        }

        if ($parent_content_id === null || $parent_content_id < 1) {
            return;
        }

        /* set parent thumbnail id if the parent table supports it */
        if ($this->columnExists("tn_id", $parent_table)) {
            $query = "UP" . "DATE `$parent_table` SET tn_id = ? WHERE id = ?";
            $this->query($query, 'ii', $this->tn->id->value, $this->parent_id);

            $query = "UPDATE `image_link` SET parent_id = ? WHERE id = ?";
            $this->query($query, 'ii', $this->parent_id, $this->tn->id->value);
        }
    }

    /**
     * Tests if the content type of the object is set in cases where a content type is required.
     * @throws ConfigurationUndefinedException Content type is not currently set for the object.
     */
    protected function testForContentType()
    {
        if ($this->content_properties->id->value === null || $this->content_properties->id->value < 1) {
            throw new ConfigurationUndefinedException("Site section not set. ");
        }
    }

    /**
     * Tests if the content type and parent of the object is set in cases where a content type and parent is required.
     * @throws ConfigurationUndefinedException Content type is not currently set for the object.
     */
    protected function testForContentTypeAndParent()
    {
        $this->testForContentType();
        if ($this->parent_id === null || $this->parent_id < 1) {
            throw new ConfigurationUndefinedException("Gallery parent not set.");
        }
    }

    /**
     * Validate image collection form input.
     * @throws ContentValidationException
     */
    function validateInput()
    {
        foreach ($this->list as $image_link) {
            /** @var ImageLink $image_link */
            try {
                $image_link->validateInput();
            } catch (ContentValidationException $ex) {
                $this->validation_errors = array_merge($this->validation_errors, $image_link->validationErrors);
            }
        }
        if (count($this->validation_errors) > 0) {
            throw new ContentValidationException("Errors were found in the gallery.");
        }
    }
}