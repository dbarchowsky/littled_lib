<?php

namespace Littled\PageContent\Metadata;


class Preload
{
	/** @var string Tag type of the preload element */
	public $tag;
	/** @var string Value to insert into the "rel" attribute of the element */
	public $rel;
	/** @var string Value to insert into the "href" attribute of the element */
	public $url;
	/** @var string Extra attributes to attach to the element */
	public $extra_attribute;

	/**
	 * @param string $tag
	 * @param string $rel
	 * @param string $url
	 * @param string $extra_attribute
	 */
	function __construct(string $tag, string $rel='', string $url='', string $extra_attribute='')
	{
		$this->tag = $tag;
		$this->rel = $rel;
		$this->url = $url;
		$this->extra_attribute = $extra_attribute;
	}
}