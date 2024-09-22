<?php

namespace Littled\PageContent\Navigation;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\ContentUtils;
use Littled\Exception\ResourceNotFoundException;


class BreadcrumbsNode extends NavigationNodeBase
{
    /** @var string */
    protected static string $node_template_path = '';

    /**
     * Class constructor.
     * @param string $label Text to display for this item within the navigation menu.
     * @param ?string $url (Optional) URL where the menu item will link to.
     * @param string $dom_id (Optional) value for the breadcrumb node's id attribute.
     * @param string $css_class (Optional) value for the breadcrumb node's class attribute.
     */
    function __construct(string $label, ?string $url = null, string $dom_id = '', string $css_class = '')
    {
        parent::__construct($label, $url);
        $this->css_class = $css_class;
        $this->dom_id = $dom_id;
    }

    /**
     * Returns the path to the node template path.
     * @return string Template path.
     * @throws ConfigurationUndefinedException
     */
    public static function getNodeTemplatePath(): string
    {
        if (!static::$node_template_path) {
            static::setNodeTemplatePath(LittledGlobals::getSharedTemplatesPath() . 'framework/navigation/breadcrumbs-node.php');
        }
        return static::$node_template_path;
    }

    /**
     * Outputs markup for the individual navigation menu node.
     * @throws ResourceNotFoundException|ConfigurationUndefinedException
     */
    public function render(): void
    {
        ContentUtils::renderTemplate(static::getNodeTemplatePath(), array(
            'node' => &$this
        ));
    }
}
