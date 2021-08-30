<?php

namespace Littled\PageContent\Metadata;

use Littled\Exception\InvalidValueException;

class MetadataElement
{
    /** @var string */
    protected $type;
    /** @var string */
    public $name;
    /** @var string */
    public $value;
    /** @var array */
    protected const valid_types = array('name', 'http-equiv', 'charset', 'itemprop');

    /**
     * @param string $type
     * @param string $name
     * @param string $value
     * @throws InvalidValueException
     */
    function __construct (string $type, string $name, string $value='')
    {
        $this->setType($type);
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws InvalidValueException
     */
    public function setType(string $type)
    {
        if (!array_key_exists($type, MetadataElement::valid_types)) {
            throw new InvalidValueException("\"$type\" is not a valid metadata element type.");
        }
        $this->type = $type;
    }
}