<?php
namespace Littled\Filters;

class SocialGalleryFilters extends GalleryFilters
{
	const DEFAULT_PAGE_LEN = 50;
	const FRONTEND_URI = '';
	const LISTINGS_LABEL = '';

	/** @var BooleanContentFilter Control to filter records that have been previously posted on Wordpress. */
	public $onWordpress;
	/** @var BooleanContentFilter Control to filter records that have been previously posted to Twitter. */
	public $onTwitter;
	/** @var BooleanContentFilter Control to filter records that have been assigned a short URL. */
	public $hasShortURL;

	public static function DEFAULT_PAGE_LEN() { return(self::DEFAULT_PAGE_LEN); }
	public static function FRONTEND_URI() { return(self::FRONTEND_URI); }
	public static function LISTINGS_LABEL() { return(self::LISTINGS_LABEL); }

	/**
	 * class constructor
	 * @param int|null[optional] $content_type_id Content type identifier, corresponds to site_section record.
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws \Exception Error establishing database connection.
	 */
	function __construct ( $content_type_id=null, $default_page_len=10 )
	{
		parent::__construct($content_type_id, $default_page_len);
		$this->onWordpress = new BooleanContentFilter("posted on wordpress", "gfwp", null, $this::COOKIE_NAME);
		$this->onTwitter = new BooleanContentFilter("posted on twitter", "gftw", null, $this::COOKIE_NAME);
		$this->hasShortURL = new BooleanContentFilter("has short ulr", "gfsu", null, $this::COOKIE_NAME);
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