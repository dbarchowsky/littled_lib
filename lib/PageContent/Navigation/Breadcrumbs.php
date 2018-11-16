<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\PageContent;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;


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
	/** @var string CSS class to apply to the breadcrumbs menu parent element */
	public $cssClass;

	/** @var string Path to template used to display the breadcrumbs. */
	public static $breadcrumbsTemplate = "";
	/** @var string Class name of the class used to render the breadcrumb nodes. */
	public static $nodeType = 'Littled\PageContent\Navigation\BreadcrumbsNode';

	/**
	 * Class constructor.
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	public function __construct()
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}
		$this::$breadcrumbsTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/breadcrumbs.php";
		if (!file_exists($this::$breadcrumbsTemplate)) {
			throw new ResourceNotFoundException("Breadcrumbs template not found at {$this::$breadcrumbsTemplate}.");
		}
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
	 * @return bool true if the menu has nodes, false otherwise
	 */
	function hasNodes ()
	{
		return (isset($this->first));
	}

	/**
	 * Outputs navigation menu markup.
	 * @throws ResourceNotFoundException
	 */
	function render ()
	{
		PageContent::render($this::$breadcrumbsTemplate, array(
			'breadcrumbs' => &$this
		));
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public function setCSSClass($css_class)
	{
		$this->cssClass = $css_class;
	}
}
