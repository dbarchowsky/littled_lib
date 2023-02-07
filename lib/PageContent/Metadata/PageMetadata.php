<?php
namespace Littled\PageContent\Metadata;

use Littled\Exception\InvalidValueException;

/**
 * Class PageMetadata
 * Site metadata properties.
 * @package Littled\PageContent
 */
class PageMetadata
{
    /** @var string Core name for the site. */
    public string $site_label = "";
    /** @var string Page title suitable for displaying in the page content. */
    public string $title = "";
	/** @var MetadataElement Page description inserted into the page metadata for SEO. */
	protected MetadataElement $description;
	/** @var MetadataElement List of keywords to be inserted into the page metadata for SEO. */
	protected MetadataElement $keywords;
	/** @var MetadataElement $meta_title Metadata title displayed in the browser title bar for SEO. */
	protected MetadataElement $meta_title;
    /** @var array Array of MetadataElement objects used to inject abstract metadata elements into a page */
    public array $extras = array();

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
     * @param string $type
     * @param string $name
     * @param string $value
     * @throws InvalidValueException
     */
    public function addPageMetadata(string $type, string $name, string $value)
    {
        $this->extras[] = new MetadataElement($type, $name, $value);
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

    public function setDescription(string $description)
    {
        $this->description->setContent($description);
    }

    public function setKeywords(array $keywords)
    {
        $this->keywords->setContent(implode(',', $keywords));
    }

    public function setMetaTitle(string $title)
    {
        $this->meta_title->content = $title;
    }
}
