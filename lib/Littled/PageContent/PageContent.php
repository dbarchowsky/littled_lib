<?php
namespace Littled\PageContent;

use Littled\Exception\ResourceNotFoundException;

/**
 * Class PageContent
 * Static utility routines for rendering page content.
 * @package Littled\PageContent
 */
class PageContent
{
	/**
	 * Inserts data into a template file and stores the resulting content in the object's $content property.
	 * @param string $template_path Path to content template file.
	 * @param array $context Array containing data to insert into the template.
	 * @return string Markup with content inserted into it.
	 * @throws ResourceNotFoundException If the requested template file cannot be located.
	 */
	public static function loadTemplateContent( $template_path, $context=null )
	{
		if (!file_exists($template_path)) {
			throw new ResourceNotFoundException("Template \"".basename($template_path)."\" not found.");
		}
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		ob_start();
		include($template_path);
		$markup = ob_get_contents();
		ob_end_clean();

		return ($markup);
	}

	/**
	 * Inserts data into a template file and renders the result.
	 * @param string $template_path Path to template to render.
	 * @param array $context Data to insert into the template.
	 * @throws ResourceNotFoundException If the requested template file cannot be located.
	 */
	public static function render( $template_path, $context=null )
	{
		if (!file_exists($template_path)) {
			throw new ResourceNotFoundException("Template \"".basename($template_path)."\" not found.");
		}
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		include ($template_path);
	}
}
