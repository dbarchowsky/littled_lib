<?php
namespace Littled\Filters;

use Littled\SiteContent\ContentOperations;
use Littled\SiteContent\ContentProperties;

/**
 * Class AlbumFilters
 * @package Littled\Filters
 */
class AlbumFilters extends FilterCollection
{
	/** @var StringContentFilter Keyword filter. */
	public $keyword;
	/** @var StringContentFilter Album title filter. */
	public $title;
	/** @var StringContentFilter Name filter. */
	public $name;
	/** @var StringContentFilter Display date filter. */
	public $date;
	/** @var StringContentFilter Access level filter. */
	public $access;
	/** @var StringContentFilter Filters out records with release dates before the value of this property. */
	public $release_after;
	/** @var StringContentFilter Filters out records with release dates after the value of this property. */
	public $release_before;
	/** @var IntegerContentFilter Slot filter. */
	public $slot;
	/** @var ContentProperties Content properties. */
	public $site_section;
	/** @var ContentOperations Extended content properties. */
	public $section_operations;
	/** @var GalleryFilters Gallery filters. */
	public $gallery;
}