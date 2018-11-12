<?php
namespace Littled\PageContent\Navigation;

use Littled\PageContent\PageContent;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;


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
     * @param string $dom_dom_id (Optional) value for the breadcrumb node's id attribute.
     * @param string $css_css_class (Optional) value for the breadcrumb node's class attribute.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    function __construct ($label, $url=null, $dom_dom_id="", $css_css_class="")
    {
	    if (!defined('LITTLED_TEMPLATE_DIR')) {
		    throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not defined in app settings.");
	    }

	    $this::$breadcrumbsNodeTemplate = LITTLED_TEMPLATE_DIR . "framework/navigation/breadcrumbs-node.php";
	    if (!file_exists($this::$breadcrumbsNodeTemplate)) {
		    throw new ResourceNotFoundException("Breadcrumbs template not found at {$this::$breadcrumbsNodeTemplate}.");
	    }

		$this->label = $label;
		$this->url = $url;
		$this->cssClass = $css_css_class;
		$this->domId = $dom_dom_id;
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
