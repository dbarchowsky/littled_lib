<?php

namespace Littled\PageContent\SiteSection;


use Littled\PageContent\Serialized\SerializedContent;

/**
 * Class SiteSection
 * @package Littled\PageContent\SiteSection
 */
class SiteSection extends SerializedContent
{
	/**
	 * SiteSection constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Delete this record from the database.
	 * @return string Message indicating result of the deletion.
	 * @throws \Littled\Exception\ContentValidationException Record id not provided.
	 * @throws \Littled\Exception\InvalidQueryException Table name not set in inherited class.
	 * @throws \Littled\Exception\NotImplementedException SQL error raised running deletion query.
	 */
	public function delete()
	{
		$status = parent::delete();

		/** @todo move this logic down into the appropriate inherited class that actually has the "site_section" property */
		if (property_exists($this, "site_section") &&
			($this->site_section->label || $this->site_section->name->value)) {
			$status = "The " . strtolower(($this->site_section->label) ? ($this->site_section->label) : ($this->site_section->name->value)) . " record has been deleted. \n";
		}
		return ($status);
	}
}