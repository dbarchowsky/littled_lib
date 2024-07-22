<?php

namespace Littled\PageContent\Metadata;

use Littled\Exception\InvalidValueException;

class PageMetadata
{
    /** @var string Core name for the site. */
    public string $site_label = '';
    /** @var string Page title suitable for displaying in the page content. */
    public string $title = '';
    /** @var MetadataElement Page description inserted into the page metadata for SEO. */
    protected MetadataElement $description;
    /** @var MetadataElement List of keywords to be inserted into the page metadata for SEO. */
    protected MetadataElement $keywords;
    /** @var MetadataElement $meta_title Metadata title displayed in the browser title bar for SEO. */
    protected MetadataElement $meta_title;
    /** @var array Array of MetadataElement objects used to inject abstract metadata elements into a page */
    public array $extras = [];

    /**
     *
     */
    function __construct()
    {
        $this->description = new MetadataElement('name', 'description');
        $this->keywords = new MetadataElement('name', 'keywords');
        $this->meta_title = new MetadataElement('name', 'title');
    }

    /**
     * @param string $attribute
     * @param string $value
     * @param string $content
     * @throws InvalidValueException
     */
    public function addPageMetadata(string $attribute, string $value, string $content): void
    {
        $this->extras[] = new MetadataElement($attribute, $value, $content);
    }

    /**
     * Remove all extra page metadata.
     * @return void
     */
    public function clearMetadataExtras(): void
    {
        $this->extras = [];
    }

    public function getDescription(): string
    {
        return ($this->description->getContent());
    }

    public function getKeywords(): array
    {
        return (explode(',', $this->keywords->getContent()));
    }

    public function getMetaTitle(): string
    {
        return ($this->meta_title->content);
    }

    public function getPageMetadata(): array
    {
        return $this->extras;
    }

    /**
     * Remove metadata property from stack if it matches attribute, value, and content
     * @param string $attribute
     * @param string $value
     * @param string $content
     * @return void
     */
    public function removePageMetadata(string $attribute, string $value, string $content): void
    {
        for($i=0; $i<count($this->extras); $i++) {
            if ($this->extras[$i]->isSame($attribute, $value, $content)) {
                unset($this->extras[$i]);
                $this->extras = array_values($this->extras);
            }
        }
    }

    public function setDescription(string $description): void
    {
        $this->description->setContent($description);
    }

    public function setKeywords(array $keywords): void
    {
        $this->keywords->setContent(implode(',', $keywords));
    }

    public function setMetaTitle(string $title): void
    {
        $this->meta_title->content = $title;
    }
}
