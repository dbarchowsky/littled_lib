<?php
namespace Littled\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Validation\Validation;
use Littled\PageContent\Navigation\NavigationMenu;
use Littled\PageContent\Navigation\Breadcrumbs;


/**
 * Class PageConfig
 * Site configuration.
 * @package Littled\PageContent
 */
class PageConfig {

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
	public static function addBreadcrumb($label, $url='', $dom_id='', $css_class='')
	{
		if (!is_object(self::$breadcrumbs)) {
			self::$breadcrumbs = new Breadcrumbs();
		}
		self::$breadcrumbs->addNode($label, $url, $dom_id, $css_class);
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
	public static function addUtilityLink($label, $url='', $target='', $level='', $dom_id='', $attributes='')
	{
		if (!is_object(self::$utilityLinks)) {
			self::$utilityLinks = new NavigationMenu();
		}
		self::$utilityLinks->addNode($label, $url, $target, $level, $dom_id, $attributes);
	}

	/**
	 * Removes all breadcrumb nodes.
	 */
	public static function clearBreadcrumbs()
	{
		if (isset(self::$breadcrumbs)) {
			self::$breadcrumbs->clearNodes();
		}
	}

	/**
	 * Clears all previously registered stylesheets.
	 */
	public static function clearStylesheets( )
	{
		self::$stylesheets = array();
	}

	/**
	 * Clears all previously registered scripts.
	 */
	public static function clearScripts( )
	{
		self::$scripts = array();
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
		self::$status = Validation::collectStringInput(P_MESSAGE);
		if(isset($_SESSION[P_MESSAGE])) {
			unset($_SESSION[P_MESSAGE]);
		}
	}

	/**
	 * Gets the current description value.
	 * @return string
	 */
	public static function getDescription()
	{
		self::metadata();
		return(self::$metadata->description);
	}

	/**
	 * Returns keywords to be inserted into the page headers for SEO.
	 * @return array List of keywords to be inserted into the page headers for SEO.
	 */
	public static function getKeywords()
	{
		self::metadata();
		return(self::$metadata->keywords);
	}

	/**
	 * Gets the current metadata title value.
	 * @return string
	 */
	public static function getMetaTitle()
	{
		self::metadata();
		return(self::$metadata->meta_title);
	}

	/**
	 * Get the current page status
	 * @return string
	 */
	public static function getPageStatus()
	{
		return(self::$status);
	}

	/**
	 * Get the current page title value
	 * @return string
	 */
	public static function getPageTitle()
	{
		self::metadata();
		return(self::$metadata->title);
	}

	/**
	 * Gets the current site label value
	 * @return string
	 */
	public static function getSiteLabel()
	{
		self::metadata();
		return(self::$metadata->site_label);
	}

	/**
	 * Initializes the metadata collection. Uses the current metadata collection if it has already been instantiated.
	 */
	public static function metadata()
	{
		if (!is_object(self::$metadata)) {
			self::$metadata = new PageMetadata();
		}
	}

	/**
	 * Pushes the URL of a resource that will be preloaded on the page.
	 * @param string $src
	 */
	public static function registerPreload($src)
	{
		array_push(self::$preloads, $src);
	}

	/**
	 * Pushes the URL of a script, typically a JavaScript file, to load with the page.
	 * @param string $src
	 */
	public static function registerScript($src)
	{
		array_push(self::$scripts, $src);
	}

	/**
	 * Pushes the URL of a stylesheet to load with the page.
	 * @param string $src
	 */
	public static function registerStylesheet($src)
	{
		array_push(self::$stylesheets, $src);
	}

	/**
	 * Generates and outputs markup that will render the breadcrumbs that have been added to the page.
	 */
	public static function renderBreadcrumbs()
	{
		if (!is_object(self::$breadcrumbs)) {
			return;
		}
		self::$breadcrumbs->render();
	}

	/**
	 * Generates and outputs markup that will render the navigation menu nodes that have been added to the page.
	 */
	public static function renderUtilityLinks()
	{
		if (!is_object(self::$utilityLinks)) {
			return;
		}
		self::$utilityLinks->render();
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public static function setBreadcrumbsCssClass($css_class)
	{
		self::$breadcrumbs->setCSSClass($css_class);
	}

	/**
	 * Sets the metadata page description value.
	 * @param string $description
	 */
	public static function setDescription($description )
	{
		self::metadata();
		self::$metadata->description = $description;
	}

	/**
	 * Sets element attributes for the last node of the navigation menu.
	 * @param string $attributes
	 */
	public static function setLinkAttributes($attributes)
	{
		if (!is_object(self::$utilityLinks)) {
			return;
		}
		self::$utilityLinks->last->attributes = $attributes;
	}

	/**
	 * Sets the list of keywords to be inserted into the page headers for SEO.
	 * @param array $keywords List of keywords to be inserted into the page headers for SEO.
	 * @throws \Exception
	 */
	public static function setKeywords($keywords )
	{
		if (!is_array($keywords)) {
			throw new \Exception("[".__METHOD__."] \$keywords parameter expects array.");
		}
		self::metadata();
		self::$metadata->keywords = $keywords;
	}

	/**
	 * Sets the page metadata title.
	 * @param string $meta_title
	 */
	public static function setMetaTitle($meta_title)
	{
		self::metadata();
		self::$metadata->meta_title = $meta_title;
	}

	/**
	 * Sets the page title value.
	 * @param string $title
	 */
	public static function setPageTitle($title)
	{
		self::metadata();
		self::$metadata->title = $title;
	}

	/**
	 * Sets the site label value, e.g. the string that will represent the site in the title bar of the browser.
	 * @param string $site_label
	 */
	public static function setSiteLabel($site_label)
	{
		self::metadata();
		self::$metadata->site_label = $site_label;
	}

	/**
	 * Sets the CSS class for the navigation menu parent element.
	 * @param string $css_class
	 */
	public static function setUtilityLinksCssClass($css_class)
	{
		self::$utilityLinks->setCSSClass($css_class);
	}
}
