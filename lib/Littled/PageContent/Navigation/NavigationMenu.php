<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\PageContent;

/**
 * Class NavigationMenu
 * @package Littled\PageContent\Navigation
 */
class NavigationMenu
{
	/** @var NavigationMenuNode Pointer to first node in the menu. */
	public $first;
	/** @var NavigationMenuNode Pointer to last node in the menu. */
	public $last;

	public static $menuTemplate = "";
	public static $nodeType = 'Littled\PageContent\Navigation\NavigationMenuNode';

	public function __construct()
	{
		if (defined('LITTLED_TEMPLATE_DIR')) {
			$this::$menuTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/navmenu.php";
		}
		/* @todo throw configuration error if LITTLED_TEMPLATE_DIR is not defined */
		/* @todo throw resource not found error if template file doesn't exist */
	}
	
	/**
	 * Outputs navigation menu markup.
	 */
	function render () 
	{
		PageContent::render($this::$menuTemplate, array(
			'menu' => &$this
		));
	}
	
	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $target (Optional) Target window for the link. Defaults to the same window.
	 * @param integer $level (Optional) Indentation level of the menu item.
	 * @param string $dom_id  (Optional) Sets the id attribute of the menu item element.
	 * @param string $attributes (Optional) Hook to insert any extra attributes into the menu item element.
	 */
	function addNode ( $label, $url=null, $target=null, $level=0, $dom_id=null, $attributes=null)
	{
		$node_type = $this::$nodeType;
		/** @var $node NavigationMenuNode */
		$node = new $node_type($label, $url, $target, $level, $dom_id, $attributes);
		if (isset($this->first)) {
			$this->last->nextNode = $node;
			$node->prevNode = $this->last;
			$this->last = $node;
		} 
		else {
			$this->first = $node;
			$this->last = $node;
		}
	}
	
	/**
	 * removes the last node from the chain
	 */
	function dropLast ( )
	{
		if (!isset($this->last)) {
			return;
		}
		
		if (isset($this->last->prevNode)) {
			$node = $this->last->prevNode;
			unset($node->nextNode);
			$this->last = $node;
		} 
		else {
			unset($this->last);
		} 
	}
	
	/**
	 * Returns true/false depending on whether the menu current contains any nodes.
	 * @return bool True if the menu has nodes, false otherwise
	 */
	function hasNodes ()
	{
		return (isset($this->first));
	}
} 
