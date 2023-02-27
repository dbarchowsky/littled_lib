<?php
namespace Littled\PageContent\Navigation;


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
        $p = new $class();
        return $p->formatRoutePath($record_id);
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
        $p = new $class();
        return $p->formatRoutePath($record_id);
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
        $p = new $class();
        return $p->formatRoutePath();
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