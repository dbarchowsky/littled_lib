<?php
namespace Littled\PageContent\Albums;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
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
	 * @param ?int $content_type_id Optional record id of the content's site section. (dbo: site_section.id)
	 * @param ?int $images_content_type_id Optional record id of the gallery's site section (dbo: site_section.id)
	 * @param ?int $id Optional record id of the content record. (dbo: [content_table].id)
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	function __construct (?int $content_type_id=null, ?int $images_content_type_id=null, ?int $id=null )
	{
		parent::__construct($content_type_id, $images_content_type_id, $id);
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
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	function read( $read_images=true, $read_image_keywords=false )
	{
		$this->create_date->is_database_field = true;
		$this->mod_date->is_database_field = true;
		$this->flickr_id->is_database_field = true;
		$this->wp_id->is_database_field = true;
		$this->twitter_id->is_database_field = true;
		$this->short_url->is_database_field = true;

		parent::read($read_images, $read_image_keywords);
	}


	/**
	 * Saves content record, along with gallery images and keywords.
	 * @param boolean $save_thumbnail (Optional) Flag to additionally save a thumbnail record (dbo: image_link) as opposed to linking to an existing image in the gallery list as this content item's thumbnail. Default TRUE.
	 * @param boolean $update_cache (Optional) Flag to additionally update content cache. Default TRUE.
	 * @return string Message to display to user indicating the result of the operation.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws OperationAbortedException
	 * @throws RecordNotFoundException
	 * @throws ResourceNotFoundException
	 */
	function save ( $save_thumbnail=true, $update_cache=true )
	{
		$this->create_date->is_database_field = false;
		$this->mod_date->is_database_field = false;
		$this->flickr_id->is_database_field = false;
		$this->wp_id->is_database_field = false;
		$this->twitter_id->is_database_field = false;
		$this->short_url->is_database_field = false;

		return(parent::save($save_thumbnail, $update_cache));
	}
}