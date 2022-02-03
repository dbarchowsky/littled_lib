<?php

namespace Littled\PageContent\Navigation;


use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

class NavigationNodeBase
{
	/** @var string */
	protected static $node_template_path = '';

	/** @var string CSS class to apply to the node. */
	public $css_class;
	/** @var string DOM id for the node element */
	public $dom_id;
	/** @var string Node label to display on the page. */
	public $label;
	/** @var NavigationMenuNode Link to next node in the menu. */
	public $next_node;
	/** @var NavigationMenuNode Link to previous node in the menu. */
	public $prev_node;
	/** @var string URL that the node links to */
	public $url;

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
	public function render ( )
	{
		ContentUtils::renderTemplate(static::getNodeTemplatePath(), array(
			'node' => &$this
		));
	}

	/**
	 * Sets the path to the breadcrumb nodes template.
	 * @param string $path Template path.
	 */
	public static function setNodeTemplatePath(string $path)
	{
		static::$node_template_path = $path;
	}
}