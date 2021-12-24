<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\ContentUtils;
use Littled\Exception\ResourceNotFoundException;


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
	/** @var string CSS class to apply to the navigation menu parent element */
	public $cssClass;

	/** @var string Path to template to use to render the menu in markup. */
	protected static $menuTemplate = '';
	/** @var string Class name to use to manage the navigation menu nodes. Default is NavigationMenuNode. */
	protected static $nodeType = 'Littled\PageContent\Navigation\NavigationMenuNode';

	function __construct()
	{
		$this->first = null;
		$this->last = null;
		$this->cssClass = '';
	}

	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
     * @param string $title (Optional) Title attribute value to apply to menu node element.
	 * @param string $target (Optional) Target window for the link. Defaults to the same window.
	 * @param integer $level (Optional) Indentation level of the menu item.
	 * @param string $dom_id  (Optional) Sets the id attribute of the menu item element.
	 * @param string $attributes (Optional) Hook to insert any extra attributes into the menu item element.
	 */
	function addNode ( string $label, string $url='', string $title='', string $target='', int $level=0, string $dom_id='', string $attributes='')
	{
		$node_type = $this::$nodeType;
		/** @var $node NavigationMenuNode */
		$node = new $node_type($label, $url, $title, $target, $level, $dom_id, $attributes);
		if (isset($this->first)) {
			$this->last->nextNode = $node;
			$node->prevNode = $this->last;
		}
		else {
			$this->first = $node;
		}
        $this->last = $node;
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
	 * @return string Navigation menu template path.
	 */
	public static function getMenuTemplatePath(): string
	{
		return (static::$menuTemplate);
	}

	/**
	 * @return string Returns the type set for the navigation menu nodes.
	 */
	public static function getNodeType(): string
	{
		return static::$nodeType;
	}

	/**
	 * Returns true/false depending on whether the menu current contains any nodes.
	 * @return bool True if the menu has nodes, false otherwise
	 */
	function hasNodes (): bool
	{
		return (isset($this->first));
	}

	/**
	 * Outputs navigation menu markup.
	 * @throws ResourceNotFoundException
	 */
	function render ()
	{
		ContentUtils::renderTemplate($this::getMenuTemplatePath(), array(
			'menu' => &$this
		));
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public function setCSSClass(string $css_class)
	{
		$this->cssClass = $css_class;
	}

	/**
	 * Sets the path to the navigation template.
	 * @param string $path Path to the navigation menu template.
	 */
	public static function setMenuTemplatePath(string $path)
	{
		static::$menuTemplate = $path;
	}

	/**
	 * Sets the type of the navigation menu nodes.
	 * @param string $type Name of the class to use as navigation menu nodes.
	 */
	public static function setNodeType(string $type)
	{
		static::$nodeType = $type;
	}
}
