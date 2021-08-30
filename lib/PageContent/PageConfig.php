<?php
namespace Littled\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
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
	public static $contentCSSClass;
	/** @var array List of css includes. */
	public static $stylesheets = array();
	/** @var array List of script includes. */
	public static $scripts = array();
	/** @var array List of preload images. */
	public static $preloads = array();
	/** @var object Site metadata */
	protected static $metadata;
	/** @var string Status message passed from one page to another */
	protected static $status;
	/** @var NavigationMenu Page utility links list. */
	protected static $utilityLinks;
	/** @var Breadcrumbs Page breadcrumb list. */
	protected static $breadcrumbs;

	/**
	 * Adds breadcrumb node.
	 * @param string $label Breadcrumb label
	 * @param string $url Breadcrumb URL
	 * @param string $dom_id Breadcrumb element selector.
	 * @param string $css_class CSS class to assign to the breadcrumb node.
	 */
	public static function addBreadcrumb(string $label, string $url='', string $dom_id='', string $css_class='')
	{
		if (!is_object(static::$breadcrumbs)) {
			static::$breadcrumbs = new Breadcrumbs();
		}
		static::$breadcrumbs->addNode($label, $url, $dom_id, $css_class);
	}

    /**
     * @param string $type
     * @param string $name
     * @param string $value
     */
    public static function addPageMetadata(string $type, string $name, string $value)
    {
        self::metadata();
        static::$metadata->addPageMetadata($type, $name, $value);
    }

	/**
	 * Adds a navigation menu node.
	 * @param string $label Menu node link
	 * @param string $url Menu node target URL
	 * @param string $target Target window identifier for the node URL
	 * @param string $level Menu node level
	 * @param string $dom_id Menu node element selector
	 * @param string $attributes String containing any additional attributes to assign to the node element.
	 */
	public static function addUtilityLink(
        string $label,
        string $url='',
        string $target='',
        string $level='',
        string $dom_id='',
        string $attributes='')
	{
		if (!is_object(static::$utilityLinks)) {
			static::$utilityLinks = new NavigationMenu();
		}
		static::$utilityLinks->addNode($label, $url, $target, $level, $dom_id, $attributes);
	}

	/**
	 * Removes all breadcrumb nodes.
	 */
	public static function clearBreadcrumbs()
	{
		if (isset(static::$breadcrumbs)) {
			static::$breadcrumbs->clearNodes();
		}
	}

	/**
	 * Clears all previously registered stylesheets.
	 */
	public static function clearStylesheets( )
	{
		static::$stylesheets = array();
	}

	/**
	 * Clears all previously registered scripts.
	 */
	public static function clearScripts( )
	{
		static::$scripts = array();
	}

	/**
	 * Collects page status value as defined in request variables (e.g. GET, POST, session)
	 * @throws ConfigurationUndefinedException
	 */
	public static function collectPageStatus( )
	{
		if (!defined('P_MESSAGE')) {
			throw new ConfigurationUndefinedException("P_MESSAGE not defined in app settings.");
		}
		static::$status = Validation::collectStringInput(P_MESSAGE);
		if(isset($_SESSION[P_MESSAGE])) {
			unset($_SESSION[P_MESSAGE]);
		}
	}

	/**
	 * Returns current breadcrumbs list
	 * @return Breadcrumbs
	 */
	public static function getBreadcrumbs(): Breadcrumbs
	{
		return(static::$breadcrumbs);
	}

	/**
	 * Gets the current content CSS class value.
	 * @return string
	 */
	public static function getContentCSSClass(): string
	{
		return(static::$contentCSSClass);
	}

	/**
	 * Gets the current description value.
	 * @return string
	 */
	public static function getDescription(): string
	{
		self::metadata();
		return(static::$metadata->getDescription());
	}

	/**
	 * Returns keywords to be inserted into the page headers for SEO.
	 * @return array List of keywords to be inserted into the page headers for SEO.
	 */
	public static function getKeywords(): array
	{
		self::metadata();
		return(static::$metadata->getKeywords());
	}

	/**
	 * Gets the current metadata title value.
	 * @return string
	 */
	public static function getMetaTitle(): string
	{
		self::metadata();
		return(static::$metadata->getMetaTitle());
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
		return(static::$status);
	}

	/**
	 * Get the current page title value
	 * @return string
	 */
	public static function getPageTitle(): string
	{
		self::metadata();
		return(static::$metadata->title);
	}

	/**
	 * Gets the current site label value
	 * @return string
	 */
	public static function getSiteLabel(): string
	{
		self::metadata();
		return(static::$metadata->site_label);
	}

	/**
	 * Gets the current list of utility links.
	 * @return NavigationMenu
	 */
	public static function getUtilityLinks(): NavigationMenu
	{
		return(static::$utilityLinks);
	}

	/**
	 * Initializes the metadata collection. Uses the current metadata collection if it has already been instantiated.
	 */
	public static function metadata()
	{
		if (!is_object(static::$metadata)) {
			static::$metadata = new PageMetadata();
		}
	}

	/**
	 * Pushes the URL of a resource that will be preloaded on the page.
	 * @param string $src
	 */
	public static function registerPreload(string $src)
	{
		array_push(static::$preloads, $src);
	}

	/**
	 * Pushes the URL of a script, typically a JavaScript file, to load with the page.
	 * @param string $src
	 */
	public static function registerScript(string $src)
	{
		array_push(static::$scripts, $src);
	}

	/**
	 * Pushes the URL of a stylesheet to load with the page.
	 * @param string $src
	 */
	public static function registerStylesheet(string $src)
	{
		array_push(static::$stylesheets, $src);
	}

	/**
	 * Generates and outputs markup that will render the breadcrumbs that have been added to the page.
	 * @throws ResourceNotFoundException
	 */
	public static function renderBreadcrumbs()
	{
		if (!is_object(static::$breadcrumbs)) {
			return;
		}
		static::$breadcrumbs->render();
	}

	/**
	 * Generates and outputs markup that will render the navigation menu nodes that have been added to the page.
	 * @throws ResourceNotFoundException
	 */
	public static function renderUtilityLinks()
	{
		if (!is_object(static::$utilityLinks)) {
			return;
		}
		static::$utilityLinks->render();
	}

	/**
	 * Sets current breadcrumb links list.
	 * @param $breadcrumbs Breadcrumbs
	 */
	public static function setBreadcrumbs(Breadcrumbs $breadcrumbs)
	{
		static::$breadcrumbs = $breadcrumbs;
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public static function setBreadcrumbsCssClass(string $css_class)
	{
		if (static::$breadcrumbs===null) {
			return;
		}
		static::$breadcrumbs->setCSSClass($css_class);
	}

	/**
	 * Sets a css class to assign to the page content element.
	 * @param string $css_class
	 */
	public static function setContentCSSClass(string $css_class)
	{
		static::$contentCSSClass = $css_class;
	}

	/**
	 * Sets the metadata page description value.
	 * @param string $description
	 */
	public static function setDescription(string $description)
	{
		self::metadata();
		static::$metadata->setDescription($description);
	}

	/**
	 * Sets element attributes for the last node of the navigation menu.
	 * @param string $attributes
	 */
	public static function setLinkAttributes(string $attributes)
	{
		if (!is_object(static::$utilityLinks)) {
			return;
		}
		static::$utilityLinks->last->attributes = $attributes;
	}

	/**
	 * Sets the list of keywords to be inserted into the page headers for SEO.
	 * @param array $keywords List of keywords to be inserted into the page headers for SEO.
	 * @throws Exception
	 */
	public static function setKeywords(array $keywords)
	{
		if (!is_array($keywords)) {
			throw new Exception("[".__METHOD__."] \$keywords parameter expects array.");
		}
		self::metadata();
		static::$metadata->setKeywords($keywords);
	}

	/**
	 * Sets the page metadata title.
	 * @param string $meta_title
	 */
	public static function setMetaTitle(string $meta_title)
	{
		self::metadata();
		static::$metadata->setMetaTitle($meta_title);
	}

	/**
	 * Sets the page title value.
	 * @param string $title
	 */
	public static function setPageTitle(string $title)
	{
		self::metadata();
		static::$metadata->title = $title;
	}

	/**
	 * Sets the site label value, e.g. the string that will represent the site in the title bar of the browser.
	 * @param string $site_label
	 */
	public static function setSiteLabel(string $site_label)
	{
		self::metadata();
		static::$metadata->site_label = $site_label;
	}

	/**
	 * Sets current breadcrumb links list.
	 * @param NavigationMenu $links
	 */
	public static function setUtilityLinks(NavigationMenu $links)
	{
		static::$utilityLinks = $links;
	}

	/**
	 * Sets the CSS class for the navigation menu parent element.
	 * @param string $css_class
	 */
	public static function setUtilityLinksCssClass(string $css_class)
	{
		if (static::$utilityLinks===null) {
			return;
		}
		static::$utilityLinks->setCSSClass($css_class);
	}

	/**
	 * Pushes the URL of a script, typically a JavaScript file, to load with the page.
	 * @param string $src
	 */
	public static function unregisterScript(string $src)
	{
		for($i=0; $i < count(static::$scripts); $i++) {
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
	public static function unregisterStylesheet(string $src)
	{
		for($i=0; $i < count(static::$stylesheets); $i++) {
			if (static::$stylesheets[$i] == $src) {
				unset(static::$stylesheets[$i]);
			}
		}
		static::$stylesheets = array_values(static::$stylesheets);
	}
}
