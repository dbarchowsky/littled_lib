<?php

namespace Littled\PageContent;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Templates\ErrorTemplate;
use Littled\PageContent\Templates\TemplateRenderer;

/**
 * Class containing static methods for injecting templated content into pages.
 */
class ContentUtils
{
    /**
     * Formats a phone number string into the format (xxx) xxx-xxxx.
     * @param string $phone_number Phone number string to format.
     * @return string Formatted phone number string.
     */
    public static function formatPhoneNumber(string $phone_number): string
    {
        return match (true) {
            preg_match('/^[0-9]{10}$/', $phone_number) === 1
            => preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $phone_number),
            default => $phone_number
        };
    }

    /**
     * @deprecated Use TemplateRenderer->renderAsMarkup() instead.
     * Inserts data into a template file and stores the resulting content in the object's $content property.
     * @param string $template_path Path to a content template file.
     * @param ?array $context Array containing data to insert into the template.
     * @return string Markup with content inserted into it.
     * @throws ResourceNotFoundException If the requested template file cannot be located.
     */
    public static function loadTemplateContent(string $template_path, ?array $context = []): string
    {
        return TemplateRenderer::create()
            ->withTemplate($template_path)
            ->withContext($context ?? [])
            ->renderAsMarkup();
    }

    /**
     * @deprecated Use ErrorTemplate->renderError() instead.
     * Inserts error message into DOM.
     * @param string $msg Error message to print out.
     * @param string $fmt Format to use to print out an error message. Overrides the default format.
     * @param string $css_class (Optional) CSS class to apply to the element containing the error message. Defaults to
     * "alert alert-error".
     * @param string $encoding Defaults to 'UTF-8'
     */
    public static function printError(
        string $msg,
        string $fmt = '<div class="%s">%s</div>',
        string $css_class = 'alert alert-error',
        string $encoding = 'UTF-8'): void
    {
        (new ErrorTemplate())
            ->setCSSClass($css_class)
            ->setFormat($fmt)
            ->setEncoding($encoding)
            ->renderError($msg);
    }

    /**
     * Redirects to URI.
     * @param string $uri
     * @return void
     */
    public static function redirectToURI(string $uri): void
    {
        header("Location: $uri");
    }

    /**
     * @deprecated Use TemplateRenderer instead.
     * Inserts data into a template file and renders the result.
     * @param string $template_path Path to template to render.
     * @param ?array $context Data to insert into the template.
     * @throws ResourceNotFoundException If the requested template file cannot be located.
     */
    public static function renderTemplate(string $template_path, ?array $context = []): void
    {
        TemplateRenderer::create()
            ->withTemplate($template_path)
            ->withContext($context ?? [])
            ->render();
    }

    /**
     * @deprecated Use TemplateRenderer->renderWithErrors() instead.
     * Inserts data into a template file and renders the result. Catches exceptions and prints error messages directly to the DOM.
     * @param string $template_path Path to template to render.
     * @param array|null $context Data to insert into the template.
     */
    public static function renderTemplateWithErrors(
        string $template_path,
        ?array $context = []): void
    {
        TemplateRenderer::create()
            ->withTemplate($template_path)
            ->withContext($context ?? [])
            ->renderWithErrors();
    }
}