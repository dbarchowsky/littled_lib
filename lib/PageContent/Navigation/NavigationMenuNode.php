<?php

namespace Littled\PageContent\Navigation;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;


class NavigationMenuNode extends NavigationNodeBase
{
    /** @var string */
    protected static string $node_template_path = '';
    /** @var string Description */
    public string $title = '';
    /** @var string Path to image to display as content of the menu node. */
    public string $image_path = '';
    /** @var int Nesting level of the node. */
    public int $level = 0;
    /** @var string Named browser target. For opening new browser windows, e.g. "_blank" */
    public string $target = '';

    /**
     * Class constructor.
     * @param string $label Text to display for this item within the navigation menu.
     * @param string $url (Optional) URL where the menu item will link to.
     * @param string $title Title attribute value for the node element.
     * @param string $target (Optional) Target window for the link. Defaults to the same window.
     * @param integer $level (Optional) Indentation level of the menu item.
     * @param string $dom_id (Optional) Sets the id attribute of the menu item element.
     * @param string $attributes (Optional) Hook to insert any extra attributes into the menu item element.
     */
    function __construct(string $label = '', string $url = '', string $title = '', string $target = '', int $level = 0, string $dom_id = '', string $attributes = '')
    {
        parent::__construct($label, $url);
        $this->target = $target;
        $this->title = $title;
        $this->level = $level;
        $this->dom_id = $dom_id;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritDoc}
     * @throws ConfigurationUndefinedException
     */
    public static function getNodeTemplatePath(): string
    {
        if (!static::$node_template_path) {
            static::$node_template_path = LittledGlobals::getSharedTemplatesPath() . 'framework/navigation/navigation-menu-node.php';
        }
        return (static::$node_template_path);
    }

    /**
     * Sets the path to an image file to use as the content of the menu node.
     * @param string $path Path to image.
     */
    public function setImagePath(string $path): void
    {
        $this->image_path = $path;
    }
}
