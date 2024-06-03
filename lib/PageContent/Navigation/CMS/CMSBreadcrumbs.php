<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\Navigation\Breadcrumbs;


class CMSBreadcrumbs extends Breadcrumbs
{
	/**
	 * CMSBreadcrumbs constructor.
     * @throws ConfigurationUndefinedException
     */
	public function __construct()
	{
		parent::__construct();

		/* override default breadcrumbs template path */
		static::setMenuTemplatePath(LittledGlobals::getLocalTemplatesPath(). 'framework/navigation/breadcrumbs-menu.php');
		static::setNodeType('Littled\PageContent\Navigation\CMS\BreadcrumbsNode');
	}

}