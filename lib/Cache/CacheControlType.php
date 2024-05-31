<?php

namespace Littled\Cache;


class CacheControlType
{
    /** @var string File extension. */
    public string $extension;
    /** @var string Mime type. */
    public string $mimeType;
    /** @var bool Flag indicating public cache. */
    public bool $isPublic;
    /** @var int Maximum age of the file type's cache in seconds. */
    public int $maxAge;
    /** @var bool Flag controlling gzip compression headers. */
    public bool $gzip;
    public const MAX_AGE_1_HOUR = 3600;
    public const MAX_AGE_3_HOUR = 10800;
    public const MAX_AGE_1_DAY = 86400;
    public const MAX_AGE_1_WEEK = 604800;

    function __construct($extension, $mime_type, $is_public, $max_age, $gzip)
    {
        $this->extension = $extension;
        $this->mimeType = $mime_type;
        $this->isPublic = $is_public;
        $this->maxAge = $max_age;
        $this->gzip = $gzip;
    }
}
