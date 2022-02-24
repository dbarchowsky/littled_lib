<?php
namespace Littled\PageContent\SiteSection;

use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Littled\Request\URLTextField;

class ContentRoute extends SerializedContent
{
	/** @var int Value of this record in the site section table. */
	protected static int $content_type_id = 34;
	/** @var string */
	protected static $table_name = "content_route";

	/** @var IntegerSelect */
	public IntegerSelect $site_section_id;
	/** @var StringTextField */
	public StringTextField $operation;
	/** @var URLTextField */
	public URLTextField $url;

	/**
	 * Class constructor
	 * @param int|null $id
	 * @param int|null $route_content_type_id
	 * @param string $operation
	 * @param string $url
	 */
	public function __construct(?int $id = null, ?int $route_content_type_id=null, string $operation='', string $url='')
	{
		parent::__construct($id);

		$this->id->label = "Content route id";
		$this->id->key = 'routeId';
		$this->id->required = false;
		$this->site_section_id = new IntegerSelect('Site Section', 'routeSectionId', true, $route_content_type_id);
		$this->operation = new StringTextField('Name', 'routeName', true, $operation, 45);
		$this->url = new URLTextField('URL', 'routeURL', true, $url, 256);
	}

	/**
	 * @inheritDoc
	 */
	public function generateUpdateQuery(): ?array
	{
		return array('CALL contentRouteUpdate(@record_id,?,?,?)',
			'iss',
			&$this->site_section_id->value,
			&$this->operation->value,
			&$this->url->value);
	}

	/**
	 * @inheritDoc
	 */
	public function hasData(): bool
	{
		return ($this->id->value > 0 || $this->url->value || $this->operation->value);
	}
}