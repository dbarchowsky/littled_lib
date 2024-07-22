<?php

namespace Littled\PageContent;

use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\Metadata\Preload;
use Littled\Validation\Validation;
use Littled\PageContent\Metadata\PageMetadata;
use Littled\PageContent\Navigation\NavigationMenu;
use Littled\PageContent\Navigation\Breadcrumbs;
use Exception;

/**
 * Class PageConfig
 * Site configuration.
 * @package Littled\PageContent
 */
class PageConfig
{
    /** @var string CSS class to apply to the page content. */
    public static string $contentCSSClass = '';
    /** @var string[] List of css includes. */
    public static array $stylesheets = array();
    /** @var string[] List of script includes. */
    public static array $scripts = array();
    /** @var Preload[] List of preload images. */
    public static array $preloads = array();
    /** @var PageMetadata Site metadata */
    protected static PageMetadata $metadata;
    /** @var string Status message passed from one page to another */
    protected static string $status = '';
    /** @var ?NavigationMenu Page utility links list. */
    protected static ?NavigationMenu $utilityLinks;
    /** @var ?Breadcrumbs Page breadcrumb list. */
    protected static ?Breadcrumbs $breadcrumbs;
    /** @var string */
    protected static string $breadcrumbs_class = Breadcrumbs::class;
    /** @var string */
    protected static string $navigation_menu_class = NavigationMenu::class;

    /**
     * Adds breadcrumb node.
     * @param string $label Breadcrumb label
     * @param string $url Breadcrumb URL
     * @param string $dom_id Breadcrumb element selector.
     * @param string $css_class CSS class to assign to the breadcrumb node.
     */
    public static function addBreadcrumb(string $label, string $url = '', string $dom_id = '', string $css_class = ''): void
    {
        if (!isset(static::$breadcrumbs)) {
            static::$breadcrumbs = new static::$breadcrumbs_class();
        }
        static::$breadcrumbs->addNode($label, $url, $dom_id, $css_class);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $value
     * @throws InvalidValueException
     */
    public static function addPageMetadata(string $type, string $name, string $value): void
    {
        self::metadata();
        static::$metadata->addPageMetadata($type, $name, $value);
    }

    /**
     * Adds a navigation menu node.
     * @param string $label Menu node link
     * @param string $url (Optional) Menu node target URL
     * @param string $title (Optional) title for the utility link
     * @param string $target (Optional) Target window identifier for the node URL
     * @param int $level (Optional) Menu node level
     * @param string $dom_id (Optional) Menu node element selector
     * @param string $attributes (Optional) String containing any additional attributes to assign to the node element.
     */
    public static function addUtilityLink(
        string $label,
        string $url = '',
        string $title = '',
        string $target = '',
        int    $level = 0,
        string $dom_id = '',
        string $attributes = ''): void
    {
        if (!isset(static::$utilityLinks)) {
            static::$utilityLinks = new static::$navigation_menu_class();
        }
        static::$utilityLinks->addNode($label, $url, $title, $target, $level, $dom_id, $attributes);
    }

    /**
     * Removes all breadcrumb nodes.
     */
    public static function clearBreadcrumbs(): void
    {
        if (isset(static::$breadcrumbs)) {
            static::$breadcrumbs->clearNodes();
        }
    }

    /**
     * Remove all links from all navigation menus.
     * @return void
     */
    public static function clearNavigation(): void
    {
        static::clearBreadcrumbs();
        static::clearUtilityLinks();
    }

    /**
     * Clears all previously registered stylesheets.
     */
    public static function clearPreloads(): void
    {
        static::$preloads = array();
    }

    /**
     * Clears all previously registered stylesheets.
     */
    public static function clearStylesheets(): void
    {
        static::$stylesheets = array();
    }

    /**
     * Clears all previously registered scripts.
     */
    public static function clearScripts(): void
    {
        static::$scripts = array();
    }

    /**
     * Removes all utility link nodes.
     */
    public static function clearUtilityLinks(): void
    {
        if (isset(static::$utilityLinks)) {
            static::$utilityLinks->clearNodes();
        }
    }

    /**
     * Collects page status value as defined in request variables (e.g. GET, POST, session)
     */
    public static function collectPageStatus(): void
    {
        static::$status = '' . Validation::collectStringRequestVar(LittledGlobals::INFO_MESSAGE_KEY);
        if (isset($_SESSION[LittledGlobals::INFO_MESSAGE_KEY])) {
            unset($_SESSION[LittledGlobals::INFO_MESSAGE_KEY]);
        }
    }

    /**
     * Returns current breadcrumbs list
     * @return Breadcrumbs|null
     */
    public static function getBreadcrumbs(): ?Breadcrumbs
    {
        return (isset(static::$breadcrumbs) ? (static::$breadcrumbs) : (null));
    }

    public static function destroyBreadcrumbs(): void
    {
        if (isset(static::$breadcrumbs)) {
            static::$breadcrumbs->clearNodes();
        }
        static::$breadcrumbs = null;
    }

    public static function destroyUtilityLinks(): void
    {
        if (isset(static::$utilityLinks)) {
            static::$utilityLinks->clearNodes();
        }
        static::$utilityLinks = null;
    }

    /**
     * Gets the current content CSS class value.
     * @return string
     */
    public static function getContentCSSClass(): string
    {
        return (isset(static::$contentCSSClass) ? (static::$contentCSSClass) : (''));
    }

    /**
     * Gets the current description value.
     * @return string
     */
    public static function getDescription(): string
    {
        self::metadata();
        return (static::$metadata->getDescription());
    }

    /**
     * Returns keywords to be inserted into the page headers for SEO.
     * @return array List of keywords to be inserted into the page headers for SEO.
     */
    public static function getKeywords(): array
    {
        self::metadata();
        return (static::$metadata->getKeywords());
    }

    /**
     * Gets the current metadata title value.
     * @return string
     */
    public static function getMetaTitle(): string
    {
        self::metadata();
        return (static::$metadata->getMetaTitle());
    }

    /**
     * @return array
     */
    public static function getPageMetadata(): array
    {
        self::metadata();
        return static::$metadata->getPageMetadata();
    }

    /**
     * Get the current page status
     * @return string
     */
    public static function getPageStatus(): string
    {
        return static::$status;
    }

    /**
     * Get the current page title value
     * @return string
     */
    public static function getPageTitle(): string
    {
        self::metadata();
        return (static::$metadata->title);
    }

    /**
     * Preloads getter.
     * @return array
     */
    public static function getPreloads(): array
    {
        return static::$preloads;
    }

    /**
     * Gets the current site label value
     * @return string
     */
    public static function getSiteLabel(): string
    {
        self::metadata();
        return (static::$metadata->site_label);
    }

    /**
     * Gets the current list of utility links.
     * @return ?NavigationMenu
     */
    public static function getUtilityLinks(): ?NavigationMenu
    {
        return (isset(static::$utilityLinks) ? (static::$utilityLinks) : (null));
    }

    /**
     * Initializes the metadata collection. Uses the current metadata collection if it has already been instantiated.
     */
    public static function metadata(): void
    {
        if (!isset(static::$metadata)) {
            static::$metadata = new PageMetadata();
        }
    }

    public static function removeBreadcrumb(string $label = ''): void
    {
        if (isset(static::$breadcrumbs)) {
            if ($label) {
                // remove breadcrumb with matching label
                static::$breadcrumbs->removeByLabel($label);
            } else {
                // remove the last breadcrumb
                static::$breadcrumbs->popNode();
            }
        }
    }

    /**
     * Pushes the URL of a resource that will be preloaded on the page.
     * @param Preload $preload
     */
    public static function registerPreload(Preload $preload): void
    {
        static::$preloads[] = $preload;
    }

    /**
     * Pushes the URL of a script, typically a JavaScript file, to load with the page.
     * @param string $src
     */
    public static function registerScript(string $src): void
    {
        if (!in_array($src, static::$scripts)) {
            static::$scripts[] = $src;
        }
    }

    /**
     * Pushes the URL of a stylesheet to load with the page.
     * @param string $src
     */
    public static function registerStylesheet(string $src): void
    {
        if (!in_array($src, static::$stylesheets)) {
            static::$stylesheets[] = $src;
        }
    }

    /**
     * Generates and outputs markup that will render the breadcrumbs that have been added to the page.
     * @throws ResourceNotFoundException
     */
    public static function renderBreadcrumbs(): void
    {
        if (!isset(static::$breadcrumbs)) {
            return;
        }
        static::$breadcrumbs->render();
    }

    /**
     * Generates and outputs markup that will render the navigation menu nodes that have been added to the page.
     * @throws ResourceNotFoundException
     */
    public static function renderUtilityLinks(): void
    {
        if (!isset(static::$utilityLinks)) {
            return;
        }
        static::$utilityLinks->render();
    }

    /**
     * Sets current breadcrumb links list.
     * @param $breadcrumbs Breadcrumbs
     */
    public static function setBreadcrumbs(Breadcrumbs $breadcrumbs): void
    {
        static::$breadcrumbs = $breadcrumbs;
    }

    /**
     * Breadcrumbs class name setter.
     * @param string $class
     * @return void
     * @throws InvalidTypeException
     */
    public static function setBreadcrumbsClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidTypeException(Log::getShortMethodName() . " \"$class\" is an invalid type.");
        }
        static::$breadcrumbs_class = $class;
    }

    /**
     * Sets the CSS class of the breadcrumbs parent element.
     * @param string $css_class
     */
    public static function setBreadcrumbsCssClass(string $css_class): void
    {
        if (static::$breadcrumbs === null) {
            return;
        }
        static::$breadcrumbs->setCSSClass($css_class);
    }

    /**
     * Sets a css class to assign to the page content element.
     * @param string $css_class
     */
    public static function setContentCSSClass(string $css_class): void
    {
        static::$contentCSSClass = $css_class;
    }

    /**
     * Sets the metadata page description value.
     * @param string $description
     */
    public static function setDescription(string $description): void
    {
        self::metadata();
        static::$metadata->setDescription($description);
    }

    /**
     * Sets element attributes for the last node of the navigation menu.
     * @param string $attributes
     */
    public static function setLinkAttributes(string $attributes): void
    {
        if (!isset(static::$utilityLinks)) {
            return;
        }
        static::$utilityLinks->last->attributes = $attributes;
    }

    /**
     * Sets the list of keywords to be inserted into the page headers for SEO.
     * @param array $keywords List of keywords to be inserted into the page headers for SEO.
     * @throws Exception
     */
    public static function setKeywords(array $keywords): void
    {
        self::metadata();
        static::$metadata->setKeywords($keywords);
    }

    /**
     * Sets the page metadata title.
     * @param string $meta_title
     */
    public static function setMetaTitle(string $meta_title): void
    {
        self::metadata();
        static::$metadata->setMetaTitle($meta_title);
    }

    /**
     * Navigation menu class name setter.
     * @param string $class
     * @return void
     * @throws InvalidTypeException
     */
    public static function setNavigationMenuClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidTypeException(Log::getShortMethodName() . " \"$class\" is an invalid type.");
        }
        static::$navigation_menu_class = $class;
    }

    /**
     * Sets the page title value.
     * @param string $title
     */
    public static function setPageTitle(string $title): void
    {
        self::metadata();
        static::$metadata->title = $title;
    }

    /**
     * Sets the site label value, e.g. the string that will represent the site in the title bar of the browser.
     * @param string $site_label
     */
    public static function setSiteLabel(string $site_label): void
    {
        self::metadata();
        static::$metadata->site_label = $site_label;
    }

    /**
     * Sets current breadcrumb links list.
     * @param NavigationMenu $links
     */
    public static function setUtilityLinks(NavigationMenu $links): void
    {
        static::$utilityLinks = $links;
    }

    /**
     * Sets the CSS class for the navigation menu parent element.
     * @param string $css_class
     */
    public static function setUtilityLinksCssClass(string $css_class): void
    {
        if (!isset(static::$utilityLinks)) {
            return;
        }
        static::$utilityLinks->setCSSClass($css_class);
    }

    /**
     * Updates the url of a breadcrumb node. Label is used to look up the breadcrumb node to update within the list of nodes.
     * @param string $label
     * @param string $url
     * @return void
     */
    public static function updateBreadcrumb(string $label, string $url): void
    {
        if (!isset(static::$breadcrumbs)) {
            return;
        }
        $node = static::$breadcrumbs->find($label);
        if ($node) {
            $node->url = $url;
        }
    }

    /**
     * Pushes the URL of a script, typically a JavaScript file, to load with the page.
     * @param string $src
     */
    public static function unregisterScript(string $src): void
    {
        for ($i = 0; $i < count(static::$scripts); $i++) {
            if (static::$scripts[$i] == $src) {
                unset(static::$scripts[$i]);
            }
        }
        static::$scripts = array_values(static::$scripts);
    }

    /**
     * Pushes the URL of a stylesheet to load with the page.
     * @param string $src
     */
    public static function unregisterStylesheet(string $src): void
    {
        for ($i = 0; $i < count(static::$stylesheets); $i++) {
            if (static::$stylesheets[$i] == $src) {
                unset(static::$stylesheets[$i]);
            }
        }
        static::$stylesheets = array_values(static::$stylesheets);
    }
}
