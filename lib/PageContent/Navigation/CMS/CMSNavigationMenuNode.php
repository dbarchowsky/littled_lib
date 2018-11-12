<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\PageContent\Navigation\NavigationMenuNode;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;


class CMSNavigationMenuNode extends NavigationMenuNode
{
	/**
	 * CMSNavigationMenuNode constructor.
	 * @param string|null $label Menu item label
	 * @param string|null $url URL that the menu items links to.
	 * @param string|null $target Browser window to target for the link
	 * @param int $level Nesting level of the menu item.
	 * @param string|null $dom_id DOM id to assign to the menu item element
	 * @param string|null $attributes Additional attributes to assign to the menu item element
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	public function __construct( $label=null, $url=null, $target=null, $level=0, $dom_id=null, $attributes=null )
	{
		parent::__construct( $label, $url, $target, $level, $dom_id, $attributes );

		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}

		/* override default breadcrumbs template path */
		$this::$menuNodeTemplate = LITTLED_TEMPLATE_DIR."framework/navigation/navmenu-node.php";
		if (!file_exists($this::$menuNodeTemplate)) {
			throw new ResourceNotFoundException("Navigation menu template not found at {$this::$menuNodeTemplate}.");
		}
	}

}