<?php
namespace Littled\PageContent\Navigation;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;


/**
 * Class NavigationMenuNode
 * Navigation menu node object used to configure and render individual items on navigation menus.
 * @package Littled\PageContent\Navigation
 */
class NavigationMenuNode
{
	/** @var string Extra attributes to add to the node HTML tag, e.g. "data-id" */
	public $attributes;
	/** @var string CSS class to apply to the node */
	public $cssClass;
	/** @var string Description */
	public $title;
	/** @var string DOM id value */
	public $domId;
	/** @var string Path to image to display as content of the menu node. */
	public $imgPath;
	/** @var string Node label displayed on the page */
	public $label;
	/** @var int Nesting level of the node. */
	public $level;
	/** @var NavigationMenuNode Link to next node in the menu. */
	public $nextNode;
	/** @var NavigationMenuNode Link to previous node in the menu. */
	public $prevNode;
	/** @var string Named browser target. For opening new browser windows, e.g. "_blank" */
	public $target;
	/** @var string URL that the node links to */
	public $url;

	/** @var string Path to template to use to render the node */
	protected static $menuNodeTemplate = '';
	
	/**
	 * Class constructor.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $title Title attribute value for the node element.
	 * @param string $target (Optional) Target window for the link. Defaults to the same window.
	 * @param integer $level (Optional) Indentation level of the menu item.
	 * @param string $dom_id  (Optional) Sets the id attribute of the menu item element.
	 * @param string $attributes (Optional) Hook to insert any extra attributes into the menu item element.
	 */
	function __construct ( string $label='', string $url='', string $title='', string $target='', int $level=0, string $dom_id='', string $attributes='')
	{
		$this->label = $label;
		$this->url = $url;
		$this->target = $target;
		$this->title = $title;
		$this->level = $level;
		$this->domId = $dom_id;
		$this->attributes = $attributes;
	}

	/**
	 * @return string Navigation menu node template path.
	 */
	public static function getNodeTemplatePath(): string
	{
		return (static::$menuNodeTemplate);
	}

    /**
     * render
     * Outputs markup for the individual navigation menu node.
	 * @throws ResourceNotFoundException
	 */
    public function render ( )
    {
	    ContentUtils::renderTemplate($this::getNodeTemplatePath(), array(
		    'node' => &$this
	    ));
    }
	
	/**
	 * Sets the path to an image file to use as the content of the menu node.
	 * @param string $path Path to image.
	 */
	public function setImagePath(string $path)
	{
		$this->imgPath = $path;		
	}

	/**
	 * Sets the path to the breadcrumb nodes template.
	 * @param string $path Template path.
	 */
	public static function setNodeTemplatePath(string $path)
	{
		static::$menuNodeTemplate = $path;
	}
}
