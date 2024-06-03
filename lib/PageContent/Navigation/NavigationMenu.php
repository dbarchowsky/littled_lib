<?php
namespace Littled\PageContent\Navigation;


class NavigationMenu extends NavigationMenuBase
{
	/** @var string */
	protected static string $menu_template_path = '';
	/** @var string */
	protected static string $node_type = 'Littled\PageContent\Navigation\NavigationMenuNode';

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
	public function addNode (
        string $label,
        string $url = '',
        string $title = '',
        string $target = '',
        int    $level = 0,
        string $dom_id = '',
        string $attributes = ''): void
    {
		parent::addNode($label, $url);
		$node_type = static::getNodeType();
		/** @var $node NavigationMenuNode */
		$node = new $node_type($label, $url, $title, $target, $level, $dom_id, $attributes);
		$this->initializeChildren($node);
	}
}
