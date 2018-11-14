<?php
namespace Littled\PageContent\Albums;


use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class SocialXPostAlbum extends Album
{
	/** @var IntegerInput ID of Flicker article */
	public $flickr_id;
	/** @var IntegerInput ID of WordPress article */
	public $wp_id;
	/** @var StringInput ID of Twitter post */
	public $twitter_id;
	/** @var StringInput Short URL, e.g. Bitly URL */
	public $short_url;
	/** @var BooleanCheckbox Flag to update the blog with this content. */
	public $update_blog;

	/**
	 * SocialXPostAlbum constructor.
	 * @param int|null[optional] $section_id Id of the content's site section. (dbo: site_section.id)
	 * @param int|null[optional] $images_section_id ID of the gallery's site section (dbo: site_section.id)
	 * @param int|null[optional] $id Id of the content record. (dbo: [content_table].id)
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function __construct ($content_type_id=null, $images_contenttype_id=null, $id=null )
	{
		parent::__construct($content_type_id, $images_contenttype_id, $id);
		$this->update_blog = new BooleanCheckbox("Update blog", "axub", false, false);
		$this->flickr_id = new IntegerInput("Flickr ID", "axfi", false, null);
		$this->wp_id = new IntegerInput("WordPress ID", "axwp", false, null);
		$this->twitter_id = new StringInput("Twitter ID", "axti", false, "", 64);
		$this->short_url = new StringInput("Short URL", "axsu", false, "", 128);
	}

	/**
	 * Read the content record, along with it's site section properties, gallery images, and keywords.
	 * @param boolean $read_images Flag to additionally read all the images attached to the record. Defaults to true.
	 * @param boolean $read_image_keywords Flag to additionally read the keywords for all of the images in the gallery. Defaults to false.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function read( $read_images=true, $read_image_keywords=false )
	{
		$this->create_date->isDatabaseField = true;
		$this->mod_date->isDatabaseField = true;
		$this->flickr_id->isDatabaseField = true;
		$this->wp_id->isDatabaseField = true;
		$this->twitter_id->isDatabaseField = true;
		$this->short_url->isDatabaseField = true;

		parent::read($read_images, $read_image_keywords);
	}


	/**
	 * Saves content record, along with gallery images and keywords.
	 * @param boolean $save_thumbnail (Optional) Flag to additionally save a thumbnail record (dbo: image_link) as opposed to linking to an existing image in the gallery list as this content item's thumbnail. Default TRUE.
	 * @param boolean $update_cache (Optional) Flag to additionally update content cache. Default TRUE.
	 * @return string Message to display to user indicating the result of the operation.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	function save ( $save_thumbnail=true, $update_cache=true )
	{
		$this->create_date->isDatabaseField = false;
		$this->mod_date->isDatabaseField = false;
		$this->flickr_id->isDatabaseField = false;
		$this->wp_id->isDatabaseField = false;
		$this->twitter_id->isDatabaseField = false;
		$this->short_url->isDatabaseField = false;

		return(parent::save($save_thumbnail, $update_cache));
	}
}