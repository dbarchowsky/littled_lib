<?php

namespace Littled\PageContent\Navigation;


use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

class NavigationNodeBase
{
	/** @var string */
	protected static string $node_template_path = '';

	/** @var string CSS class to apply to the node. */
	public string $css_class='';
	/** @var string DOM id for the node element */
	public string $dom_id='';
	/** @var string Node label to display on the page. */
	public string $label='';
	/** @var NavigationNodeBase Link to next node in the menu. */
	public NavigationNodeBase $next_node;
	/** @var NavigationNodeBase Link to previous node in the menu. */
	public NavigationNodeBase $prev_node;
	/** @var string URL that the node links to */
	public string $url='';
	/** @var string Extra attributes to add to the node HTML tag, e.g. "data-id" */
	public string $attributes='';

	/**
	 * Class constructor
	 * @param string $label
	 * @param string $url
	 */
	function __construct ( string $label='', string $url='')
	{
		$this->label = $label;
		$this->url = $url;
	}

	/**
	 * Returns the path to the node template path.
	 * @return string Template path.
	 */
	public static function getNodeTemplatePath(): string
	{
		return static::$node_template_path;
	}

	/**
	 * Outputs markup for the individual navigation menu node.
	 * @throws ResourceNotFoundException
	 */
	public function render ( ): void
    {
		ContentUtils::renderTemplate(static::getNodeTemplatePath(), array(
			'node' => &$this
		));
	}

	/**
	 * Sets the path to the breadcrumb nodes template.
	 * @param string $path Template path.
	 */
	public static function setNodeTemplatePath(string $path): void
    {
		static::$node_template_path = $path;
	}
}