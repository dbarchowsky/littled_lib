<?php
namespace Littled\Filters;


class SocialAlbumFilters extends AlbumFilters
{
	/** @var IntegerContentFilter Filter records based on whether their content has been posted to WordPress. */
	public $posted_to_wordpress;
	/** @var IntegerContentFilter Filter records based on whether their content has been posted to Flickr. */
	public $posted_to_flickr;
	/** @var IntegerContentFilter Filter records based on whether their content has been posted to Twitter. */
	public $posted_to_twitter;
	/** @var IntegerContentFilter Filter records based on whether their content has been posted to Facebook. */
	public $posted_to_facebook;
	/** @var IntegerContentFilter Filter records based on whether their content has been posted to Tumblr. */
	public $posted_to_tumblr;


	/**
	 * SocialAlbumFilters constructor
	 * @param int $content_type_id ID of the section of the site containing the listings. (From the site_section table.)
	 * @param int $page_content_type_id ID of the site_section representing the images within the listings (From the site_section table.)
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function __construct($content_type_id, $page_content_type_id, $default_page_len = 10)
	{
		parent::__construct($content_type_id, $page_content_type_id, $default_page_len);

		$this->posted_to_wordpress = new IntegerContentFilter("posted to wordpress", "fawp", null, null, $this::COOKIE_NAME);
		$this->posted_to_flickr = new IntegerContentFilter("posted to flickr", "fafk", null, null, $this::COOKIE_NAME);
		$this->posted_to_twitter = new IntegerContentFilter("posted to twitter", "fatw", null, null, $this::COOKIE_NAME);
		$this->posted_to_facebook = new IntegerContentFilter("posted to facebook", "fafb", null, null, $this::COOKIE_NAME);
		$this->posted_to_tumblr = new IntegerContentFilter("posted to tumblr", "fatm", null, null, $this::COOKIE_NAME);
	}

	/**
	 * Returns select portion of SQL statement to retrieve album listings.
	 * @return string SQL string used to retrieve album listings
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	protected function formatListingsQuery()
	{
		$this->query_string = parent::formatListingsQuery();
		if ($this->contentProperties->gallery_thumbnail->value===false) {
			$this->query_string = str_replace('albumFilteredListingsSelect', 'albumSocialFilteredListingsSelect', $this->query_string);
		}
		return ($this->query_string);
	}
}