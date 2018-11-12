<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
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
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	function __construct()
	{
		parent::__construct();

		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}

		/* override default breadcrumbs template path */
		$this::$menuTemplate = LITTLED_TEMPLATE_DIR."framework/navigation/navmenu.php";
		if (!file_exists($this::$menuTemplate)) {
			throw new ResourceNotFoundException("Navigation menu template not found at {$this::$menuTemplate}.");
		}
		$this::$nodeType = 'Littled\PageContent\Navigation\CMS\CMSNavigationMenuNode';
	}
}