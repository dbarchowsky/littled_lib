<?php

namespace Littled\PageContent\Metadata;

use Littled\Exception\InvalidValueException;

class MetadataElement
{
    protected string    $attribute;
    public string       $value;
    public string       $content;
    /** @var array */
    protected const     valid_attributes = ['name', 'http-equiv', 'charset', 'itemprop', 'property'];

    /**
     * @param string $attribute
     * @param string $value
     * @param string $content
     * @throws InvalidValueException
     */
    function __construct (string $attribute, string $value, string $content='')
    {
        $this->setAttribute($attribute);
        $this->value = $value;
        $this->content = $content;
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
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * Test if the element's property values match provided values.
     * @param string $attribute
     * @param string $value
     * @param string $content
     * @return bool
     */
    public function isSame(string $attribute, string $value, string $content): bool
    {
        return $this->attribute === $attribute && $this->value === $value && $this->content === $content;
    }

    /**
     * Injects metadata properties as page markup.
     * @return void
     */
    public function render(): void
    {
?>
<meta <?=$this->getAttribute()?>="<?=$this->getValue()?>" content="<?=$this->getContent()?>" />
<?php
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @param string $attribute
     * @throws InvalidValueException
     */
    public function setAttribute(string $attribute): void
    {
        if (!in_array($attribute, MetadataElement::valid_attributes)) {
            throw new InvalidValueException("\"$attribute\" is not a valid metadata element attribute.");
        }
        $this->attribute = $attribute;
    }
}