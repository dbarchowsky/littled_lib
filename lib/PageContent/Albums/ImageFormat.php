<?php
namespace Littled\PageContent\Albums;

use Exception;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\IntegerTextField;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;


/**
 * Interface to the image_properties table which holds information about storing and presenting album images.
 */
class ImageFormat extends SerializedContent
{
    protected static string     $table_name = 'image_formats';
    /** @var IntegerSelect Link to the image's content type. */
    public IntegerSelect        $site_section_id;
    /** @var IntegerSelect Link to the image's size category. */
    public IntegerSelect        $size_id;
    /** @var StringTextField Descriptor of what the image represents. */
    public StringTextField      $label;
    /** @var IntegerTextField Image target width. */
    public IntegerTextField     $width;
    /** @var IntegerTextField Image target height. */
    public IntegerTextField     $height;
    /** @var StringSelect Format of the image, e.g. jpeg, gif, png, webp */
    public StringSelect         $format;
    /** @var StringTextField Token to prepend to variable names used to collect image properties record data. */
    public StringTextField      $key_prefix;
    /** @var StringTextField  Path to where the image's location on the server. */
    public StringTextField      $path;
    protected string            $section_name='';
    protected string            $size_name='';

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->site_section_id = new IntegerSelect('Section', 'ipSection', true);
        $this->size_id = new IntegerSelect('Size', 'ipSize', true);
        $this->label = new StringTextField('Label', 'ipLabel', true, '', 59);
        $this->width = new IntegerTextField('Width', 'ipw', false);
        $this->height = new IntegerTextField('Height', 'iph', false);
        $this->format = new StringSelect('Format', 'ipFormat', false, '', 8);
        $this->key_prefix = new StringTextField('Key prefix', 'ipKP', false, '', 16);
        $this->path = new StringTextField('Path', 'ipPath', false, '', 255);
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = 'CALL imageFormatUpdate(?,?,?,?,?,?,?,?,?)';
        return array($query, 'iiisiisss',
            &$this->id->value,
            &$this->site_section_id->value,
            &$this->size_id->value,
            &$this->label->value,
            &$this->width->value,
            &$this->height->value,
            &$this->format->value,
            &$this->key_prefix->value,
            &$this->path->value);
    }

    public function getContentLabel(): string
    {
        /* replace this with content properties value when this class gets refactored. */
        return 'Image format';
    }

    /**
     * Section name getter.
     * @return string
     */
    public function getSectionName(): string
    {
        return $this->section_name;
    }

    /**
     * Size name getter.
     * @return string
     */
    public function getSizeName(): string
    {
        return $this->size_name;
    }

    /**
     * @inheritDoc
     * Overrides parent routine to use stored procedure that returns extended properties.
     * @throws Exception
     */
    function read()
    {
        if ($this->id->value===null || $this->id->value < 1) {
            throw new ContentValidationException('Record id not supplied.');
        }
        $query = 'CALL imageFormatSelect(?, null)';
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) < 1) {
            throw new RecordNotFoundException('Requested image format not found.');
        }
        $this->hydrateFromRecordsetRow($data[0]);
        $this->section_name = $data[0]->section;
        $this->size_name = $data[0]->size;
    }
}