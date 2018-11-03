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
	 * @param int $page_section_id (Optional) ID of the site_section representing the images within the listings (From the site_section table.)
	 * @param int[optional] $default_page_len (Optional) Lenth of the pages of listings.
	 * @throws \Exception
	 */
	function __construct($content_type_id, $page_content_type_id, $default_page_len = 10)
	{
		parent::__construct($content_type_id, $page_content_type_id, $default_page_len);

		$this->posted_to_wordpress = new IntegerContentFilter("posted to wordpress", "fawp", null, 0, self::COOKIE_NAME);
		$this->posted_to_flickr = new IntegerContentFilter("posted to flickr", "fafk", null, 0, self::COOKIE_NAME);
		$this->posted_to_twitter = new IntegerContentFilter("posted to twitter", "fatw", null, 0, self::COOKIE_NAME);
		$this->posted_to_facebook = new IntegerContentFilter("posted to facebook", "fafb", null, 0, self::COOKIE_NAME);
		$this->posted_to_tumblr = new IntegerContentFilter("posted to tumblr", "fatm", null, 0, self::COOKIE_NAME);
	}

	/**
	 * Returns select portion of SQL statement to retrieve album listings.
	 * @return string SQL string used to retrieve album listings
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function formatListingsQuery()
	{
		$query = <<<SQL
SELECT a.id 
    , a.title
	, a.slug
    , a.description
    , a.`date` 
    , a.slot
    , (SELECT COUNT(*) FROM image_link pub WHERE (pub.parent_id = a.id) AND (pub.type_id = {$this->gallery->siteSection->id->value})) private_pages
    , (SELECT COUNT(*) FROM image_link pub WHERE (pub.parent_id = a.id) AND (pub.type_id = {$this->gallery->siteSection->id->value}) AND (pub.access LIKE 'public')) public_pages
    , DATE_FORMAT(a.release_date,'%m/%d/%Y') release_date
    , a.`access`
    , a.`layout`
    , IFNULL(mini.path, med.path) tn_path
    , IFNULL(mini.width, med.width) tn_width
    , IFNULL(mini.height, med.height) tn_height
	, mini.path mini_path
	, mini.width mini_width
	, mini.height mini_height
	, med.path med_path
	, med.width med_width
	, med.height med_height
    , full.path full_path
    , full.width full_width
    , full.height full_height
	, IF THEN ELSE END IF
SQL;
		if ($this->siteSection->gallery_thumbnail->value == true) {
			$query .= <<<SQL
    , a.tn_id 
FROM `album` a 
LEFT JOIN 
(
    image_link il 
    INNER JOIN images full ON il.fullres_id = full.id
    LEFT JOIN images med ON il.med_id = med.id
    LEFT JOIN images mini ON il.mini_id = mini.id
) ON (a.tn_id = il.id) 
SQL;
		}
		else {
			$query .= <<<SQL
    , tn.id as tn_id 
FROM `album` a 
LEFT JOIN 
(
    image_link tn  
    INNER JOIN images full ON tn.fullres_id = full.id
    LEFT JOIN images med ON tn.med_id = med.id
    LEFT JOIN images mini ON tn.mini_id = mini.id
) ON (tn.parent_id = a.id and tn.type_id = {$this->siteSection->id->value}) 
SQL;
		}
		return ($query.$this->sqlClause);
	}
}