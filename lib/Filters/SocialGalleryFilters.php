<?php

namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;

class SocialGalleryFilters extends GalleryFilters
{
    protected static int $default_listings_length = 50;
    protected static string $listings_label = '';
    protected static string $frontend_uri = '';
    /** @var BooleanContentFilter Control to filter records that have been previously posted on WordPress. */
    public BooleanContentFilter $onWordpress;
    /** @var BooleanContentFilter Control to filter records that have been previously posted to Twitter. */
    public BooleanContentFilter $onTwitter;
    /** @var BooleanContentFilter Control to filter records that have been assigned a short URL. */
    public BooleanContentFilter $hasShortURL;

    /**
     * class constructor
     * @throws ConfigurationUndefinedException
     */
    function __construct()
    {
        parent::__construct();
        $this->onWordpress = new BooleanContentFilter("posted on wordpress", "gfwp", null, null, $this::getCookieKey());
        $this->onTwitter = new BooleanContentFilter("posted on twitter", "gftw", null, null, $this::getCookieKey());
        $this->hasShortURL = new BooleanContentFilter("has short ulr", "gfsu", null, null, $this::getCookieKey());
    }

    /**
     * Frontend URI getter.
     * @return string
     * @throws NotImplementedException
     */
    public function getFrontendURI(): string
    {
        if (!static::$frontend_uri) {
            throw new NotImplementedException('Frontend URI value not set in ' . __CLASS__ . '.');
        }
        return static::$frontend_uri;
    }

    /**
     * @inheritDoc
     */
    public function formatListingsQuery(bool $calculate_offset = true): array
    {
        parent::formatListingsQuery($calculate_offset);
        $content_id = $this::getContentTypeId();
        return array('CALL socialGalleryFilteredSelect(?,?,?,?,?,?,?,?,?,?,?,?,?,@total_matches)',
            'iiiissssisiii',
            &$this->listings_offset,
            &$this->listings_length->value,
            &$content_id,
            &$this->albumId->value,
            &$this->title->value,
            &$this->releaseAfter->value,
            &$this->releaseBefore->value,
            &$this->access->value,
            &$this->slot->value,
            &$this->keyword->value,
            &$this->onWordpress->value,
            &$this->onTwitter->value,
            &$this->hasShortURL->value);
    }

    /**
     * Frontend URI setter.
     * @param string $uri
     * @return void
     */
    public static function setFrontendURI(string $uri): void
    {
        static::$frontend_uri = $uri;
    }
}