<?php
namespace Littled\PageContent\Navigation;


/**
 * Class Breadcrumbs
 * @package Littled\PageContent\Navigation
 */
class Breadcrumbs extends NavigationMenuBase
{
	/** @var string */
	protected static string $menu_template_path = '';
	/** @var string */
	protected static string $node_type = 'Littled\PageContent\Navigation\BreadcrumbsNode';

	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $dom_id (Optional) value for the breadcrumb node's id attribute.
	 * @param string $css_class (Optional) value for the breadcrumb node's class attribute.
	 */
	function addNode (string $label, string $url='', string $dom_id='', string $css_class='')
	{
		/** @var $node BreadcrumbsNode */
		$node_type = static::getNodeType();
		$node = new $node_type($label, $url, $dom_id, $css_class);
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
	 * @param string $label
	 * @return BreadcrumbsNode|null
	 */
	public function find(string $label): ?BreadcrumbsNode
	{
		/** @var BreadcrumbsNode $node */
		if (isset($this->first)) {
			$node = $this->first;
			while ($node) {
				if ($label === $node->label) {
					return $node;
				}
				$node = ((isset($node->next_node))?($node->next_node):(null));
			}
		}
		return null;
	}

	/**
	 * @return string Breadcrumbs template path.
	 */
	public static function getBreadcrumbsTemplatePath(): string
	{
		return static::getMenuTemplatePath();
	}

	public static function setBreadcrumbsTemplatePath(string $path)
	{
		static::setMenuTemplatePath($path);
	}
}
