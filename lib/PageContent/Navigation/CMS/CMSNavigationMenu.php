<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\Navigation\NavigationMenu;


/**
 * Navigation menu class to be used by CMS pages.
 */
class CMSNavigationMenu extends NavigationMenu
{
	/**
	 * CMSNavigationMenu constructor.
     * @throws ConfigurationUndefinedException
     */
	function __construct()
	{
		parent::__construct();
		static::setMenuTemplatePath(LittledGlobals::getLocalTemplatesPath()."framework/navigation/navigation-menu.php");
		static::setNodeType(CMSNavigationMenuNode::class);
	}
}