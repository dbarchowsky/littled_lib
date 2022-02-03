<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\App\LittledGlobals;
use Littled\PageContent\Navigation\BreadcrumbsNode;

class CMSBreadcrumbsNode extends BreadcrumbsNode
{
	/**
	 * CMSBreadcrumbsNode constructor.
	 * @param string $label Breadcrumb label.
	 * @param string $url Url that the breadcrumb links to.
	 * @param string $dom_id DOM id to apply to the breadcrumb element.
	 * @param string $css_class CSS class to apply to the breadcrumb element.
	 */
	public function __construct(string $label, string $url='', string $dom_id='', string $css_class='')
	{
		parent::__construct( $label, $url, $dom_id, $css_class );
		static::setNodeTemplatePath(LittledGlobals::getTemplatePath().'framework/navigation/breadcrumbs-node.php');
	}
}