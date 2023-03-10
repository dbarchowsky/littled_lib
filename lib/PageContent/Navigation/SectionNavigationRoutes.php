<?php
namespace Littled\PageContent\Navigation;

use Littled\Exception\InvalidTypeException;


abstract class SectionNavigationRoutes
{
	protected static string $details_page_class='';
	protected static string $edit_page_class='';
	protected static string $listings_page_class='';
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
     * @param ?int $record_id
	 * @return void
	 */
	public static function getDetailsRoute(?int $record_id=null): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getDetailsPageClass();
        return $class::formatRoutePath($record_id);
    }

    /**
     * Returns the first component of the details route.
     * @return string
     */
     public static function getDetailsRouteBase(): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getDetailsPageClass();
        return $class::getBaseRoute();
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
     * Edit route getter.
     * @param ?int $record_id Record id of the record being edited.
     * @return string
     */
    public static function getEditRoute(?int $record_id=null): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getEditPageClass();
        return $class::formatRoutePath($record_id);
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
        /** @var RoutedPageContent $class */
        $class = static::getListingsPageClass();
        return $class::formatRoutePath();
    }

    /**
     * Returns the first component of the listings route.
     * @return string
     */
    public static function getListingsRouteBase(): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getListingsPageClass();
        $p = new $class();
        return $p::getBaseRoute();
    }

	/**
	 * Return page route for a specified class.
	 * @param string $class Class name
	 * @param int|null $record_id Optional record id to be incorporated into the route
	 * @param bool $include_query_string Option to include current page filters into the route as a query string.
	 * Default value is TRUE.
	 * @return string
	 * @throws InvalidTypeException
	 */
	abstract public static function getPageRoute(string $class, ?int $record_id=null, bool $include_query_string=true): string;

    /**
     * Template path getter.
     * @return void
     */
    public static function getTemplateDir(): string
    {
        return static::$template_dir;
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