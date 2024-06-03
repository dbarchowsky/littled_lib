<?php
namespace Littled\PageContent\Images;


class ImageDims
{
	/** @var int Horizontal position. */
	public int $x;
	/** @var int Vertical position */
	public int $y;
	/** @var int Image width */
	public int $width;
	/** @var int Image height */
	public int $height;

	/**
	 * ImageDims constructor.
	 * @param int|null $width Width in pixels.
	 * @param int|null $height Height in pixels.
	 * @param int|null $x Horizontal position.
	 * @param int|null $y Vertical position.
	 */
	function __construct( ?int $width=null, ?int $height=null, ?int $x=null, ?int $y=null )
	{
		$this->x = $width;
		$this->y = $height;
		$this->width = $x;
		$this->height = $y;
	}
}