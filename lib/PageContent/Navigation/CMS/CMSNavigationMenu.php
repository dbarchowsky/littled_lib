<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\App\LittledGlobals;
use Littled\PageContent\Navigation\NavigationMenu;


/**
 * Class NavigationMenu
 * Navigation menu class to be used by CMS pages.
 * @package Littled\PageContent\Navigation\CMS
 */
class CMSNavigationMenu extends NavigationMenu
{
	/**
	 * CMSNavigationMenu constructor.
	 */
	function __construct()
	{
		parent::__construct();
		static::setMenuTemplatePath(LittledGlobals::getTemplatePath()."framework/navigation/navigation-menu.php");
		static::setNodeType('Littled\PageContent\Navigation\CMS\CMSNavigationMenuNode');
	}
}