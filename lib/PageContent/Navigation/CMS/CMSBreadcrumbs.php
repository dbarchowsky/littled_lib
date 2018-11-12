<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\PageContent\Navigation\Breadcrumbs;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;


/**
 * Class CMSBreadcrumbs
 * Breadcrumbs class to use on CMS pages.
 * @package Littled\PageContent\Navigation\CMS
 */
class CMSBreadcrumbs extends Breadcrumbs
{
	/**
	 * CMSBreadcrumbs constructor.
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	public function __construct()
	{
		parent::__construct();

		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}

		/* override default breadcrumbs template path */
		$this::$breadcrumbsTemplate = LITTLED_TEMPLATE_DIR."framework/navigation/breadcrumbs.php";
		if (!file_exists($this::$breadcrumbsTemplate)) {
			throw new ResourceNotFoundException("Breadcrumbs template not found at {$this::$breadcrumbsTemplate}.");
		}
		$this::$node_type = 'Littled\PageContent\Navigation\CMS\BreadcrumbsNode';
	}

}