<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\PageContent;


/**
 * Class breadcrumbs_node_class
 * Class used to render individual nodes in breadcrumbs.
 * @package Littled\PageContent\Navigation
 */
class BreadcrumbsNode
{
	/** @var string CSS class to apply to the node. */
	public $cssClass;
	/** @var string DOM id for the node element */
	public $domId;
	/** @var string Node label to display on the page. */
    public $label;
	/** @var BreadcrumbsNode Pointer to next node in the list. */
    public $nextNode;
	/** @var BreadcrumbsNode Pointer to previous node in the list. */
	public $prevNode;
	/** @var null|string URL that the node links to */
	public $url;

	/** @var string Path to template to use to render individual breadcrumb nodes. */
	public static $breadcrumbsNodeTemplate = "";
	
    /**
     * Class constructor.
     * @param string $label Text to display for this item within the navigation menu.
     * @param string $url (Optional) URL where the menu item will link to.
     * @param string $dom_id (Optional) value for the breadcrumb node's id attribute.
     * @param string $css_class (Optional) value for the breadcrumb node's class attribute.
     */
    function __construct ( $label, $url=null, $dom_id="", $css_class="")
    {
	    if (defined('LITTLED_TEMPLATE_DIR')) {
		    $this::$breadcrumbsNodeTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/breadcrumbs-node.php";
	    }
	    /* @todo throw configuration error if LITTLED_TEMPLATE_DIR not defined */
	    /* @todo throw resource not found error if template file doesn't exist */

		$this->label = $label;
		$this->url = $url;
		$this->cssClass = $css_class;
		$this->domId = $dom_id;
    }

    /**
     * Outputs markup for the the individual navigation menu node.
     */
    public function render ( )
    {
	    PageContent::render($this::$breadcrumbsNodeTemplate, array(
		    'node' => &$this
	    ));
    } 
}
