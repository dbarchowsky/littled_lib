<?php
namespace Littled\PageContent\Albums;


use Littled\Database\MySQLConnection;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\SiteSection;
use Littled\Request\IntegerInput;
use phpDocumentor\Reflection\Types\Integer;

/**
 * Class Gallery
 * @package Littled\PageContent\Albums
 */
class Gallery extends MySQLConnection
{
	/** @var SiteSection Section properties. */
	public $site_section;
	/** @var integer Parent record id. */
	public $parent_id;
	/** @var string Label for inserting into page content. */
	public $label;
	/** @var array List of image_link_class objects representing the images in the gallery */
	public $list;
	/** @var ImageLink Thumbnail record. */
	public $tn;
	/** @var IntegerInput Pointer to the thumbnail id object for convenience. */
	public $tn_id;
	/** @var IntegerInput to the content type id object for convenience. */
	public $type_id;
	/** @var integer Current number of images in the gallery. */
	public $image_count;

	/**
	 * Gallery constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns the form data members of the objects as series of nested associative arrays.
	 * @param array $exclude_keys (Optional) array of parameter names to exclude from the returned array.
	 * @return array Associative array containing the object's form data members as name/value pairs.
	 */
	public function arrayEncode($exclude_keys=null )
	{
		$ar = array();
		foreach ($this as $key => $item)
		{
			if (is_object($item))
			{
				if (!is_array($exclude_keys) || !in_array($key, $exclude_keys))
				{
					if (is_subclass_of($item, "RequestInput"))
					{
						$ar[$key] = $item->value;
					}
					elseif (is_subclass_of($item, "SerializedContent"))
					{
						/** @var SerializedContent $item */
						$ar[$key] = $item->arrayEncode();
					}
				}
			}
			elseif ($key=="list")
			{
				$ar[$key] = array();
				if (is_array($item))
				{
					foreach($item as &$img_lnk)
					{
						$ar[$key][count($ar[$key])] = $img_lnk->arrayEncode(array("site_section"));
					}
				}
			}
		}
		return ($ar);
	}
}