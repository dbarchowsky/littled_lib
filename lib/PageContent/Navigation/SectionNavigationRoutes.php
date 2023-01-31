<?php
namespace Littled\PageContent\Navigation;


class SectionNavigationRoutes
{
	protected static string $details_page_class='';
	protected static string $details_route='';
	protected static string $edit_page_class='';
	protected static string $listings_page_class='';
	protected static string $listings_route='';
    protected static string $template_dir='';

	/**
	 * Details page class name getter
	 * @return void
	 */
	public static function getDetailsPageClass(): string
	{
		return static::$details_page_class;
	}

	/**
	 * Details route getter
	 * @return void
	 */
	public static function getDetailsRoute(): string
	{
		return static::$details_route;
	}

    /**
     * Returns the first component of the details route.
     * @return string
     */
    public static function getDetailsRouteBase(): string
    {
        $arr = array_values(array_filter(explode('/', static::$details_route)));
        return ((count($arr) > 0)?($arr[0]):(''));
    }

	/**
	 * Edit page class name getter
	 * @return void
	 */
	public static function getEditPageClass(): string
	{
		return static::$edit_page_class;
	}

	/**
	 * Listings page class name getter
	 * @return void
	 */
	public static function getListingsPageClass(): string
	{
		return static::$listings_page_class;
	}

	/**
	 * Listings route getter
	 * @return void
	 */
	public static function getListingsRoute(): string
	{
		return static::$listings_route;
	}

    /**
     * Returns the first component of the listings route.
     * @return string
     */
    public static function getListingsRouteBase(): string
    {
        $arr = array_values(array_filter(explode('/', static::$listings_route)));
        return ((count($arr) > 0)?($arr[0]):(''));
    }

    /**
     * Template path getter.
     * @return void
     */
    public static function getTemplateDir(): string
    {
        return static::$template_dir;
    }

    /**
	 * Details route setter.
	 * @param string $route
	 * @return void
	 */
	public static function setDetailsRoute(string $route)
	{
		static::$details_route = $route;
	}

	/**
	 * Listings route setter.
	 * @param string $route
	 * @return void
	 */
	public static function setListingsRoute(string $route)
	{
		static::$listings_route = $route;
	}

    /**
     * Template path setter.
     * @param string $path
     * @return void
     */
    public static function setTemplateDir(string $path)
    {
        static::$template_dir = $path;
    }
}