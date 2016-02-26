<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\PageContent\Navigation\BreadcrumbsNode;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;

class CMSBreadcrumbsNode extends BreadcrumbsNode
{
	/**
	 * CMSBreadcrumbsNode constructor.
	 * @param string $label Breadcrumb label.
	 * @param string|null $url Url that the breadcrumb links to.
	 * @param string $dom_id DOM id to apply to the breadcrumb element.
	 * @param string $css_class CSS class to apply to the breadcrumb element.
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	public function __construct($label, $url=null, $dom_id="", $css_class="")
	{
		parent::__construct( $label, $url, $dom_id, $css_class );

		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
		}

		/* override default breadcrumbs template path */
		$this::$breadcrumbsNodeTemplate = LITTLED_TEMPLATE_DIR."framework/navigation/breadcrumbs-node.php";
		if (!file_exists($this::$breadcrumbsNodeTemplate)) {
			throw new ResourceNotFoundException("Breadcrumbs template not found at {$this::$breadcrumbsNodeTemplate}.");
		}
	}
}