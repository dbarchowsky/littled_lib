<?php
namespace Littled\PageContent;

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

	public static function clearStylesheets( ) {
		self::$stylesheets = array();
	}

	public static function clearScripts( ) {
		self::$scripts = array();
	}

	public static function registerStylesheet($src ) {
		array_push(self::$stylesheets, $src);
	}
	
	public static function registerScript($src ) {
		array_push(self::$scripts, $src);
	}

	public static function registerPreload($src ) {
		array_push(self::$preloads, $src);
	}

	public static function metadata() {
		if (!is_object(self::$metadata)) {
			self::$metadata = new PageMetadata();
		}
	}
	
	public static function setSiteLabel($site_label ) {
		self::metadata();
		self::$metadata->site_label = $site_label;
	}
	
	public static function getSiteLabel() {
		self::metadata();
		return(self::$metadata->site_label);
	}

	public static function setPageTitle($title ) {
		self::metadata();
		self::$metadata->title = $title;
	}
	
	public static function getPageTitle() {
		self::metadata();
		return(self::$metadata->title);
	}

	public static function setMetaTitle($meta_title ) {
		self::metadata();
		self::$metadata->meta_title = $meta_title;
	}
	
	public static function getMetaTitle() {
		self::metadata();
		return(self::$metadata->meta_title);
	}

	public static function setDescription($description ) {
		self::metadata();
		self::$metadata->description = $description;
	}
	
	public static function getDescription() {
		self::metadata();
		return(self::$metadata->description);
	}

	/** 
	 * Sets the list of keywords to be inserted into the page headers for SEO.
	 * @param array $keywords List of keywords to be inserted into the page headers for SEO.
	 * @throws \Exception
	 */
	public static function setKeywords($keywords ) {
		if (!is_array($keywords)) {
			throw new \Exception("[".__METHOD__."] \$keywords parameter expects array.");
		}
		self::metadata();
		self::$metadata->keywords = $keywords;
	}
	
	/**
	 * Returns keywords to be inserted into the page headers for SEO.
	 * @return array List of keywords to be inserted into the page headers for SEO.
	 */
	public static function getKeywords() {
		self::metadata();
		return(self::$metadata->keywords);
	}

	public static function collectPageStatus( ) {
		if (!defined('P_MESSAGE')) {
			/* @todo throw configuration error */
			return;
		}
		self::$status = Validation::collectStringInput(P_MESSAGE);
		if(isset($_SESSION[P_MESSAGE])) {
			unset($_SESSION[P_MESSAGE]);
		}
	}
	
	public static function getPageStatus() {
		return(self::$status);
	}
	
	public static function addBreadcrumb($label, $url='', $dom_id='', $css_class='') {
		if (!is_object(self::$breadcrumbs)) {
			self::$breadcrumbs = new Breadcrumbs();
		}
		self::$breadcrumbs->addNode($label, $url, $dom_id, $css_class);
	}

	public static function setUtilityLinksCssClass($css_class )
	{
		self::$utilityLinks = new $css_class;
	}

	public static function setBreadcrumbsCssClass($css_class )
	{
		self::$breadcrumbs = new $css_class;
	}
	
	public static function addUtilityLink($label, $url='', $target='', $level='', $dom_id='', $attributes='') {
		if (!is_object(self::$utilityLinks)) {
			self::$utilityLinks = new NavigationMenu();
		}
		self::$utilityLinks->addNode($label, $url, $target, $level, $dom_id, $attributes);
	}
	
	public static function renderBreadcrumbs() {
		if (!is_object(self::$breadcrumbs)) {
			return;
		}
		self::$breadcrumbs->render();
	}
	
	public static function clearBreadcrumbs()
	{
		if (isset(self::$breadcrumbs)) {
			self::$breadcrumbs->clearNodes();
		}
	}
	
	public static function renderUtilityLinks() {
		if (!is_object(self::$utilityLinks)) {
			return;
		}
		self::$utilityLinks->render();
	}
	
	public static function setLinkAttributes( $attributes ) {
		if (!is_object(self::$utilityLinks)) {
			return;
		}
		self::$utilityLinks->last->attributes = $attributes;
	}
}