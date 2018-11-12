<?php
namespace Littled\Cache;


class CacheControlType
{
	/** @var string File extension. */
	public $extension;
	/** @var string Mime type. */
	public $mimeType;
	/** @var bool Flag indicating public cache. */
	public $isPublic;
	/** @var int Maximum age of the file type's cache in seconds. */
	public $maxAge;
	/** @var bool Flag controlling gzip compression headers. */
	public $gzip;

	const MAX_AGE_1_HOUR = 3600;
	const MAX_AGE_3_HOUR = 10800;
	const MAX_AGE_1_DAY = 86400;
	const MAX_AGE_1_WEEK = 604800;

	function __construct($extension, $mime_type, $is_public, $max_age, $gzip)
	{
		$this->extension = $extension;
		$this->mimeType = $mime_type;
		$this->isPublic = $is_public;
		$this->maxAge = $max_age;
		$this->gzip = $gzip;
	}
}
