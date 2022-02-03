<?php

namespace Littled\PageContent\Navigation;


use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

class NavigationMenuBase
{
	/** @var string Path to template used to display the breadcrumbs. */
	protected static $menu_template_path = "";
	/** @var string Class name of the class used to render the breadcrumb nodes. */
	protected static $node_type = '';

	/** @var BreadcrumbsNode Pointer to first node in the list of breadcrumbs. */
	public $first;
	/** @var BreadcrumbsNode Pointer to last node in the list of breadcrumbs. */
	public $last;
	/** @var string CSS class to apply to the breadcrumb menu parent element */
	public $css_class;

	function __construct()
	{
		$this->first = null;
		$this->last = null;
		$this->css_class = '';
	}

	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label
	 * @param string $url
	 */
	public function addNode(string $label, string $url='')
	{
		/** placeholder for children classes */
	}

	/**
	 * Remove and delete all nodes on the tree.
	 */
	public function clearNodes()
	{
		while(isset($this->last)) {
			$node = null;
			if (isset($this->last->prev_node) && is_object($this->last->prev_node)) {
				$node = &$this->last->prev_node;
			}
			unset($this->last);
			if (is_object($node)) {
				$this->last = &$node;
			}
		}
		unset($this->first);
	}

	/**
	 * removes the last node from the chain
	 */
	function dropLast ( )
	{
		if (!isset($this->last)) {
			return;
		}

		if (isset($this->last->prev_node)) {
			$node = $this->last->prev_node;
			unset($node->next_node);
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
		return (static::$menu_template_path);
	}

	/**
	 * Returns the current menu node count.
	 * @return int
	 */
	public function getNodeCount(): int
	{
		$n = 0;
		$node = $this->first;
		if (isset($node)) {
			while(isset($node)) {
				$n++;
				$node = $node->next_node;
			}
		}
		return $n;
	}

	/**
	 * @return string Returns the type set for the navigation menu nodes.
	 */
	public static function getNodeType(): string
	{
		return static::$node_type;
	}

	/**
	 * Returns true/false depending on whether the menu current contains any nodes.
	 * @return bool True if the menu has nodes, false otherwise
	 */
	public function hasNodes (): bool
	{
		return (isset($this->first));
	}

	/**
	 * Sets initial pointers to child nodes of the menu.
	 * @param NavigationNodeBase $node
	 * @return void
	 */
	protected function initializeChildren(NavigationNodeBase &$node)
	{
		if (isset($this->first)) {
			$this->last->next_node = $node;
			$node->prev_node = $this->last;
		}
		else {
			$this->first = $node;
		}
		$this->last = $node;
	}

	/**
	 * Outputs navigation menu markup.
	 * @throws ResourceNotFoundException
	 */
	function render ()
	{
		ContentUtils::renderTemplate(static::getMenuTemplatePath(), array(
			'menu' => &$this
		));
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public function setCSSClass(string $css_class)
	{
		$this->css_class = $css_class;
	}

	/**
	 * Sets the path to the navigation template.
	 * @param string $path Path to the navigation menu template.
	 */
	public static function setMenuTemplatePath(string $path)
	{
		static::$menu_template_path = $path;
	}

	/**
	 * Sets the type of the breadcrumb nodes.
	 * @param string $type Name of the class to use as breadcrumb nodes.
	 */
	public static function setNodeType(string $type)
	{
		static::$node_type = $type;
	}
}