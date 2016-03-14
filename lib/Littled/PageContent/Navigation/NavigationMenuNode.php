<?php
namespace Littled\PageContent\Navigation;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;


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
	public static $menuNodeTemplate = "";
	
	/**
	 * Class constructor.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $title Title attribute value for the node element.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $target (Optional) Target window for the link. Defaults to the same window.
	 * @param integer $level (Optional) Indentation level of the menu item.
	 * @param string $dom_id  (Optional) Sets the id attribute of the menu item element.
	 * @param string $attributes (Optional) Hook to insert any extra attributes into the menu item element.
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	function __construct ( $label=null, $url=null, $target=null, $title='', $level=0, $dom_id=null, $attributes=null)
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}

		$this::$menuNodeTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/navmenu-node.php";
		if (!file_exists($this::$menuNodeTemplate)) {
			throw new ResourceNotFoundException("Navigation menu template not found at {$this::$menuNodeTemplate}.");
		}

		$this->label = $label;
		$this->url = $url;
		$this->target = $target;
		$this->title = $title;
		$this->level = $level;
		$this->domId = $dom_id;
		$this->attributes = $attributes;
	}

    /**
     * render
     * Outputs markup for the the individual navigation menu node.
     */
    public function render ( )
    {
	    PageContent::render($this::$menuNodeTemplate, array(
		    'node' => &$this
	    ));
    }
}
