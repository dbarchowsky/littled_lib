<?php
namespace Littled\PageContent;


use Littled\PageContent\Metadata\MetadataElement;

/**
 * Class PageMetadata
 * Site metadata properties.
 * @package Littled\PageContent
 */
class PageMetadata
{
    /** @var string Core name for the site. */
    public $site_label = "";
    /** @var string Page title suitable for displaying in the page content. */
    public $title = "";
	/** @var MetadataElement Page description inserted into the page metadata for SEO. */
	protected $description;
	/** @var MetadataElement List of keywords to be inserted into the page metadata for SEO. */
	protected $keywords = array();
	/** @var MetadataElement Metadata title displayed in the browser title bar for SEO. */
	protected $meta_title = "";
    /** @var array Array of MetadataElement objects used to inject abstract metadata elements into a page */
    public $extras = array();

    /**
     *
     */
    function __construct()
    {
        $this->description = new MetadataElement('name', 'description');
        $this->keywords = new MetadataElement('name', 'keywords');
        $this->meta_title = new MetadataElement('name', 'title');
    }

    public function addPageMetadata(string $type, string $name, string $value)
    {
        array_push($this->extras, new MetadataElement($type, $name, $value));
    }

    public function getDescription(): string
    {
        return ($this->description->value);
    }

    public function getKeywords(): string
    {
        return (explode(',', $this->keywords->value));
    }

    public function getMetaTitle(): string
    {
            return ($this->meta_title->value);
    }

    public function getPageMetadata(): array
    {
        return $this->extras;
    }

    public function setDescription(string $description)
    {
        $this->description->value = $description;
    }

    public function setKeywords(array $keywords)
    {
        $this->keywords->value = implode(',', $keywords);
    }

    public function setMetaTitle(string $title)
    {
        $this->meta_title->value = $title;
    }
}
