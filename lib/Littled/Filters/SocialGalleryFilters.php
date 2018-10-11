<?php
namespace Littled\Filters;

class SocialGalleryFilters extends GalleryFilters
{
	/** @var BooleanContentFilter Control to filter records that have been previously posted on Wordpress. */
	public $onWordpress;
	/** @var BooleanContentFilter Control to filter records that have been previously posted to Twitter. */
	public $onTwitter;
	/** @var BooleanContentFilter Control to filter records that have been assigned a short URL. */
	public $hasShortURL;

	/**
	 * class constructor
	 * @param integer[optional] $default_page_len (Optional) Length of the pages of listings.
	 * @throws \Exception Error establishing database connection.
	 */
	function __construct ( $default_page_len=10 )
	{
		parent::__construct($default_page_len);
		$this->onWordpress = new BooleanContentFilter("posted on wordpress", "gfwp", null, self::COOKIE_NAME);
		$this->onTwitter = new BooleanContentFilter("posted on twitter", "gftw", null, self::COOKIE_NAME);
		$this->hasShortURL = new BooleanContentFilter("has short ulr", "gfsu", null, self::COOKIE_NAME);
	}

	/**
	 * Format SQL string containing conditions used to filter down image listings.
	 * @throws \Exception Error establishing connection to database.
	 */
	function formatListingsQuery()
	{
		parent::formatListingsQuery();

		/* insert new procedure name into query */
		$this->queryString = preg_replace('/CALL .*?\(/', "CALL socialGalleryFilteredSelect (", $this->queryString);

		/* break query to insert extra input parameters before the @total_matches output parameter */
		$this->queryString = preg_replace('/,(?:(?!,).)*@total_matches.*;$/', '', $this->queryString);

		$this->queryString .=
			','.$this->onWordpress->escapeSQL($this->mysqli).
			','.$this->onTwitter->escapeSQL($this->mysqli).
			','.$this->hasShortURL->escapeSQL($this->mysqli).
			',@total_matches);';
	}
}