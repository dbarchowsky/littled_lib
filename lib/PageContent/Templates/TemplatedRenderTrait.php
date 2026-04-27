<?php

namespace Littled\PageContent\Templates;

use Littled\Exception\ResourceNotFoundException;
use Littled\Request\RenderedInput;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;

trait TemplatedRenderTrait
{
    protected static string     $template_base_path;
    protected static string     $template_filename = '';

    /**
     * Returns the value of a configuration property, first checking the object's own property, then the static property, then the static property of the parent class.
     * @param $property
     * @return string
     */
    protected static function getConfigurationValue($property): string
    {
        if (isset(static::${$property}) && !empty(static::${$property})) {
            return static::${$property};
        }
        return self::${$property} ?? '';
    }

    /**
     * Template path getter.
     * @return string Current internal template path value.
     */
    public static function getTemplateBasePath(): string
    {
        $path = static::getConfigurationValue('template_base_path');
        if (empty($path) && !Validation::isSubclass(static::class, RenderedInput::class)) {
            // If the class isn't a descendant of \Request\RenderedInput, then attempt to get the template path value
            // from the RenderedInput class.
            $path = RenderedInput::getTemplateBasePath();
            if (!empty($path)) {
                static::setTemplateBasePath($path);
            }
        }
        return $path;
    }

    /**
     * Template filename getter.
     * @return string Current internal template filename.
     */
    public static function getTemplateFilename(): string
    {
        return static::getConfigurationValue('template_filename');
    }

    /**
     * Get a full path to form an input element template file.
     * @return string Full path to form an input element template file.
     */
    public static function getTemplatePath(): string
    {
        return (LittledUtility::joinPaths(static::getTemplateBasePath(), static::getTemplateFilename()));
    }

    /**
     * Returns string containing HTML to render the input elements in a form.
     * @param array $context Contextual data to be injected into the template.
     * @throws ResourceNotFoundException
     */
    public function render(array $context = []): void
    {
        TemplateRenderer::create()
            ->withTemplate(static::getTemplatePath())
            ->withContext($context)
            ->render();
    }

    /**
     * Sets the internal template path value.
     * @param string $path Path to the template directory.
     */
    public static function setTemplateBasePath( string $path ): void
    {
        static::$template_base_path = $path;
    }

    /**
     * Template filename setter.
     * @param string $filename template filename
     */
    public static function setTemplateFilename( string $filename ): void
    {
        static::$template_filename = $filename;
    }
}