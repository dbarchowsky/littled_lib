<?php

namespace Littled\PageContent\Metadata;

use Littled\Exception\InvalidValueException;

class MetadataElement
{
    protected string    $type;
    public string       $name;
    public string       $content;
    /** @var array */
    protected const     valid_types = array('name', 'http-equiv', 'charset', 'itemprop');

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
        $this->content = $value;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Injects metadata properties as page markup.
     * @return void
     */
    public function render()
    {
?>
<meta <?=$this->getType()?>="<?=$this->getName()?>" content="<?=$this->getContent()?>" />
<?php
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $type
     * @throws InvalidValueException
     */
    public function setType(string $type)
    {
        if (!in_array($type, MetadataElement::valid_types)) {
            throw new InvalidValueException("\"$type\" is not a valid metadata element type.");
        }
        $this->type = $type;
    }
}