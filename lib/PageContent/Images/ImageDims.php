<?php
namespace Littled\PageContent\Images;


class ImageDims
{
	/** @var int Horizontal position. */
	public $x;
	/** @var int Vertical position */
	public $y;
	/** @var int Image width */
	public $width;
	/** @var int Image height */
	public $height;

	/**
	 * ImageDims constructor.
	 * @param int|null[optional] $width Width in pixels.
	 * @param int|null[optional] $height Height in pixels.
	 * @param int|null[optional] $x Horizontal position.
	 * @param int|null[optional] $y Vertical position.
	 */
	function __construct( $width=null, $height=null, $x=null, $y=null )
	{
		$this->x = $width;
		$this->y = $height;
		$this->width = $x;
		$this->height = $y;
	}
}