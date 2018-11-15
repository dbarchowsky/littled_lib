<?php
/**
 * Created by PhpStorm.
 * User: damien
 * Date: 3/9/2016
 * Time: 1:10 PM
 */

namespace Littled\Cache;

use Littled\Exception\ContentValidationException;
use Littled\Exception\ResourceNotFoundException;


/**
 * Class CacheControl
 * @package BFHHand\PageContent
 */
class CacheControl
{
	/** @var string URI of the original request. */
	public $sourceURI;
	/** @var string Path to root directory for assets. */
	public $rootDir;
	/** @var string URI of the original request. */
	public $fullpath;
	/** @var CacheControlType Object representing the cache controls for the requested file type. */
	public $cacheType;
	/** @var array|null List of allowed file extensions (as keys) assosiated with their mime types (as values). */
	public static $allowedTypes = null;

	const  REFERRER_PARAM = 'ref';

	function __construct()
	{
		$this::$allowedTypes = array(
			new CacheControlType('css', 'text/css', false, CacheControlType::MAX_AGE_3_HOUR, true),
			new CacheControlType('js', 'text/css', false, CacheControlType::MAX_AGE_3_HOUR, true),
			new CacheControlType('png', 'image/png', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('jpg', 'image/jpeg', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('jpeg', 'image/jpeg', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('gif', 'image/gif', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('eot', 'application/vnd.ms-fontobject', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('woff', 'application/font-woff', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('woff2', 'application/font-woff2', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('ttf', 'application/font-sfnt', true, CacheControlType::MAX_AGE_1_WEEK, false),
			new CacheControlType('svg', 'image/svg+xml', true, CacheControlType::MAX_AGE_1_WEEK, false)
		);
		$this->sourceURI     = '';
		$this->rootDir       = '';
		$this->fullpath      = '';
		$this->cacheType     = null;
	}

	/**
	 * Collects variables and their values passed in the request.
	 * @throws ContentValidationException
	 * @throws ResourceNotFoundException
	 */
	public function collectInput()
	{
		$this->sourceURI = filter_input(INPUT_GET, $this::REFERRER_PARAM, FILTER_SANITIZE_STRING);
		if (!$this->sourceURI) {
			throw new ContentValidationException("Source URI is unavailable.");
		}
		$this->validateFileType();
		$this->validateAsset();
	}

	/**
	 * Sends 400 http response.
	 * @param string $msg Message to include in body of response.
	 */
	public static function exitWith400($msg)
	{
		header( "HTTP/1.1 400 Invalid Request" );
		print($msg);
		exit;
	}

	/**
	 * Sends 404 http response.
	 */
	public static function exitWith404()
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	/**
	 * Sets cache control in headers & before serving asset.
	 */
	public function sendResponse()
	{
		if ($this->cacheType->gzip) {
			header('Accept-Encoding: gzip, deflate');
		}
		header("Cache-Control: max-age={$this->cacheType->maxAge}".(($this->cacheType->isPublic)?(', public'):('')));
		header("Last-Modified: ".gmdate("D, j M Y G:i:s e", filemtime($this->fullpath)));
		header("Content-Type: {$this->cacheType->mimeType}");
		readfile($this->fullpath);
	}

	/**
	 * Sets the root path under which assets are located, e.g. the site's root directory.
	 * @param string $path Path of the assets' base directory.
	 */
	public function setRootDir($path)
	{
		$this->rootDir = $path;
	}

	/**
	 * Validates the requested asset's file.
	 * @throws ResourceNotFoundException
	 */
	public function validateAsset()
	{
		$this->fullpath = $this->rootDir.$this->sourceURI;
		if (!file_exists($this->fullpath)) {
			throw new ResourceNotFoundException("{$this->sourceURI} not found.");
		}
	}

	/**
	 * Collects and validates the file type by its extension. The extension must be
	 * included in the object's $allowed_types property.
	 * @throws ContentValidationException
	 */
	public function validateFileType()
	{
		$p = strrpos($this->sourceURI, '.');
		if ($p===false) {
			throw new ContentValidationException("Couldn't determine file type.");
		}
		$ext = substr($this->sourceURI, ( $p + 1));
		if (!$ext) {
			throw new ContentValidationException("Invalid file type.");
		}
		$this->cacheType = null;
		foreach($this::$allowedTypes as $c) {
			/** @var CacheControlType $c */
			if ($c->extension == $ext) {
				$this->cacheType = $c;
			}
		}
		if ($this->cacheType==null) {
			throw new ContentValidationException("Invalid file type.");
		}
	}
}
