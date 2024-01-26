<?php
namespace Littled\PageContent\Navigation;

use Littled\Exception\InvalidTypeException;
use Littled\Validation\Validation;


abstract class SectionNavigationRoutes
{
	protected static string $details_page_class='';
	protected static string $edit_page_class='';
	protected static string $listings_page_class='';
    protected static string $template_dir='';

	/**
	 * Details page class name getter. Returns empty string if the details route class has not been specified in the
     * client app.
	 * @return string
	 */
	public static function getDetailsPageClass(): string
	{
        return (static::$details_page_class ?? '');
	}

	/**
	 * Details route getter. Returns empty string if the details route class has not been specified in the client app.
     * @param ?int $record_id
	 * @return string
	 */
	public static function getDetailsRoute(?int $record_id=null): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getDetailsPageClass();
        return (Validation::isSubclass($class, RoutedPageContent::class) ?
            $class::formatRoutePath($record_id) : '');
    }

    /**
     * Returns the first component of the details route. Returns empty string if this value has not been set in the
     * client app.
     * @return string
     */
     public static function getDetailsRouteBase(): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getDetailsPageClass();
        return (Validation::isSubclass($class, RoutedPageContent::class) ?
            $class::getBaseRoute() : '');
    }

	/**
	 * Edit page class name getter. Returns empty string if the editing route class has not been set in the client app.
	 * @return void
	 */
	public static function getEditPageClass(): string
	{
		return (static::$edit_page_class ?? '');
	}

    /**
     * Edit route getter. Returns empty string if a route has not be defined.
     * @param ?int $record_id Record id of the record being edited.
     * @return string
     */
    public static function getEditRoute(?int $record_id=null): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getEditPageClass();
        return (Validation::isSubclass($class, RoutedPageContent::class) ?
            $class::formatRoutePath($record_id) : '');
    }

	/**
	 * Listings page class name getter. Returns empty string if routed page class is undefined.
	 * @return string
	 */
	public static function getListingsPageClass(): string
	{
		return (static::$listings_page_class ?? '');
	}

	/**
	 * Listings route getter. Returns empty string if routed page class is undefined.
	 * @return string
	 */
	public static function getListingsRoute(): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getListingsPageClass();
        return (Validation::isSubclass($class, RoutedPageContent::class) ?
            $class::formatRoutePath() : '');
    }

    /**
     * Returns the first component of the listings route. Returns empty string if listings route class has not been
     * specified in the client app.
     * @return string
     */
    public static function getListingsRouteBase(): string
    {
        /** @var RoutedPageContent $class */
        $class = static::getListingsPageClass();
        return (Validation::isSubclass($class, RoutedPageContent::class) ?
            $class::getBaseRoute() : '');
    }

	/**
	 * Return page route for a specified class.
	 * @param string $class Class name
	 * @param int|null $record_id Optional record id to be incorporated into the route
	 * Default value is TRUE.
	 * @return string
	 * @throws InvalidTypeException
	 */
	public static function getPageRoute(string $class, ?int $record_id=null): string
	{
		if(!class_exists($class)) {
			throw new InvalidTypeException("Bad class: \"$class\".");
		}
		if (!Validation::isSubclass($class, RoutedPageContent::class)) {
			throw new InvalidTypeException("\"$class\" is not a routed page type.");
		}
		/** @var RoutedPageContent $class */
		if ($record_id) {
			return $class::formatRoutePath($record_id);
		}
		return $class::formatRoutePath();
	}

    /**
     * Template path getter. Returns empty string if the template directory path has not been specified.
     * @return string
     */
    public static function getTemplateDir(): string
    {
        return (static::$template_dir ?? '');
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