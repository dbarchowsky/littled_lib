<?php

namespace Littled\PageContent;

use Littled\Exception\ResourceNotFoundException;

/**
 * Class containing static methods for injecting templated content into pages.
 */
class ContentUtils
{
    /**
     * Inserts data into a template file and stores the resulting content in the object's $content property.
     * @param string $template_path Path to content template file.
     * @param array|null $context Array containing data to insert into the template.
     * @return string Markup with content inserted into it.
     * @throws ResourceNotFoundException If the requested template file cannot be located.
     */
    public static function loadTemplateContent(string $template_path, array $context=null ): string
    {
        if (substr($template_path, 0, 1) == '/') {
            if ($_SERVER['DOCUMENT_ROOT']) {
                if (strpos($template_path, $_SERVER['DOCUMENT_ROOT']) !== 0) {
                    $template_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$template_path;
                }
            }
        }
        if (!file_exists($template_path)) {
            if ($template_path) {
                throw new ResourceNotFoundException("Template \"" . basename($template_path) . "\" not found.");
            }
            else {
                throw new ResourceNotFoundException("Template not found.");
            }
        }
        if (is_array($context)) {
            foreach($context as $key => $val) {
                ${$key} = $val;
            }
        }
        ob_start();
		try {
			include($template_path);
			$markup = ob_get_contents();
		} finally {
			ob_end_clean();
		}

        return ($markup);
    }

    /**
     * Inserts error message into DOM.
     * @param string $msg Error message to print out.
     * @param string $fmt Format to use to print out error message. Overrides the default format.
     * @param string $css_class (Optional) CSS class to apply to the element containing the error message. Defaults to
     * "alert alert-error".
     * @param string $encoding Defaults to 'UTF-8'
     */
    public static function printError(string $msg, string $fmt='', string $css_class='', string $encoding="UTF-8")
    {
        $css_class = $css_class ?: 'alert alert-error';
        $fmt = $fmt ?: "<div class=\"$css_class\">%s</div>";
        printf($fmt, htmlspecialchars($msg, ENT_QUOTES, $encoding));
    }

	/**
	 * Redirects to URI.
	 * @param string $uri
	 * @return void
	 */
	public static function redirectToURI(string $uri)
	{
		header("Location: $uri");
	}

    /**
     * Inserts data into a template file and renders the result.
     * @param string $template_path Path to template to render.
     * @param ?array $context Data to insert into the template.
     * @throws ResourceNotFoundException If the requested template file cannot be located.
     */
    public static function renderTemplate( string $template_path, ?array $context=null)
    {
        if (!file_exists($template_path)) {
            if ($template_path) {
                throw new ResourceNotFoundException("Template \"" . basename($template_path) . "\" not found.");
            }
            else {
                throw new ResourceNotFoundException("Template not found.");
            }
        }
        if (is_array($context)) {
            foreach($context as $context_key => $context_value) {
                ${$context_key} = $context_value;
            }
        }
        include ($template_path);
    }

    /**
     * Inserts data into a template file and renders the result. Catches exceptions and prints error messages directly to the DOM.
     * @param string $template_path Path to template to render.
     * @param array|null $context Data to insert into the template.
     * @param string $css_class (Optional) CSS class to apply to the error message container element.
     * @param string $encoding (Optional) Defaults to 'UTF-8'
     */
    public static function renderTemplateWithErrors(string $template_path, array $context=null, string $css_class='', string $encoding='UTF-8')
    {
        try {
            ContentUtils::renderTemplate($template_path, $context);
        }
        catch(ResourceNotFoundException $ex) {
            ContentUtils::printError($ex->getMessage(), '', $css_class, $encoding);
        }
    }
}