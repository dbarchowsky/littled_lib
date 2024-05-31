<?php

namespace Littled\Filters;


use Littled\Exception\ConfigurationUndefinedException;

class SocialAlbumFilters extends AlbumFilters
{
    /** @var IntegerContentFilter Filter records based on whether their content has been posted to WordPress. */
    public IntegerContentFilter $posted_to_wordpress;
    /** @var IntegerContentFilter Filter records based on whether their content has been posted to Flickr. */
    public IntegerContentFilter $posted_to_flickr;
    /** @var IntegerContentFilter Filter records based on whether their content has been posted to Twitter. */
    public IntegerContentFilter $posted_to_twitter;
    /** @var IntegerContentFilter Filter records based on whether their content has been posted to Facebook. */
    public IntegerContentFilter $posted_to_facebook;
    /** @var IntegerContentFilter Filter records based on whether their content has been posted to Tumblr. */
    public IntegerContentFilter $posted_to_tumblr;

    /**
     * SocialAlbumFilters constructor
     * @param int $content_type_id ID of the section of the site containing the listings. (From the site_section table.)
     * @param int $page_content_type_id ID of the site_section representing the images within the listings (From the site_section table.)
     * @param int $default_page_len Length of the pages of listings.
     * @throws ConfigurationUndefinedException
     */
    function __construct(int $content_type_id, int $page_content_type_id, int $default_page_len = 10)
    {
        parent::__construct($content_type_id, $page_content_type_id, $default_page_len);

        $this->posted_to_wordpress = new IntegerContentFilter("posted to wordpress", "fawp", null, null, static::$cookie_key);
        $this->posted_to_flickr = new IntegerContentFilter("posted to flickr", "fafk", null, null, static::$cookie_key);
        $this->posted_to_twitter = new IntegerContentFilter("posted to twitter", "fatw", null, null, static::$cookie_key);
        $this->posted_to_facebook = new IntegerContentFilter("posted to facebook", "fafb", null, null, static::$cookie_key);
        $this->posted_to_tumblr = new IntegerContentFilter("posted to tumblr", "fatm", null, null, static::$cookie_key);
    }

    /**
     * @inheritDoc
     */
    protected function formatListingsQuery(bool $calculate_offset = true): array
    {
        $query = parent::formatListingsQuery($calculate_offset);
        if ($this->content_properties->gallery_thumbnail->value === false) {
            $this->query_string = str_replace('albumFilteredListingsSelect', 'albumSocialFilteredListingsSelect', $query[0]);
        }
        return $query;
    }
}