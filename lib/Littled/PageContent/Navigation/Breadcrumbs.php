<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\PageContent;


/**
 * Class Breadcrumbs
 * @package Littled\PageContent\Navigation
 */
class Breadcrumbs
{
	/** @var BreadcrumbsNode Pointer to first node in the list of breadcrumbs. */
	public $first;
	/** @var BreadcrumbsNode Pointer to last node in the list of breadcrumbs. */
	public $last;

	/** @var string Path to template used to display the breadcrumbs. */
	public static $breadcrumbsTemplate = "";
	/** @var string Class name of the class used to render the breadcrumb nodes. */
	public static $nodeType = "BreadCrumbsNode";

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		if (defined('LITTLED_TEMPLATE_DIR')) {
			$this::$breadcrumbsTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/breadcrumbs.php";
		}
		/* @todo throw configuration error if LITTLED_TEMPLATE_DIR not defined */
		/* @todo throw resource not found error if template file doesn't exist */
	}
	
	/**
	 * Outputs navigation menu markup.
	 */
	function render () 
	{
		PageContent::render($this::$breadcrumbsTemplate, array(
			'breadcrumbs' => &$this
		));
	}
	
	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $dom_id (Optional) value for the breadcrumb node's id attribute.
	 * @param string $css_class (Optional) value for the breadcrumb node's class attribute.
	 */
	function addNode ($label, $url=null, $dom_id="", $css_class="")
	{
		/** @var $node BreadcrumbsNode */
		$node_type = $this::$nodeType;
		$node = new $node_type($label, $url, $dom_id, $css_class);
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
	 * Remove and delete all nodes on the tree.
	 */
	public function clearNodes()
	{
		while(isset($this->last)) {
			$node = null;
			if (isset($this->last->prevNode) && is_object($this->last->prevNode)) {
				$node = &$this->last->prevNode;
			}
			unset($this->last);
			if (is_object($node)) {
				$this->last = &$node;
			}
		}
		unset($this->first);
	}
	
	/**
	 * Returns true/false depending on whether the menu current contains any nodes.
	 * @return bool true if the menu has nodes, false otherwise
	 */
	function hasNodes ()
	{
		return (isset($this->first));
	}
} 
