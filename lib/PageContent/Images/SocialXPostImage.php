<?php
namespace Littled\PageContent\Images;


use Littled\Cache\ContentCache;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\StringInput;

/**
 * Class SocialXPostImage
 * @package Littled\PageContent\Images
 */
class SocialXPostImage extends ImageUpload
{
	/** @var StringInput Flickr post id */
	public $flickr_id;
	/** @var StringInput Wordpress post id */
	public $wp_id;
	/** @var StringInput Twitter post id */
	public $twitter_id;
	/** @var StringInput Short URL, e.g. Bitly URL */
	public $short_url;

	/**
	 * @param bool[optional] $generic_params If set to true then the parameter names of the object's id, parent id, and type id parameters will be set to generic names, ie "id", "pid", and "tid". Defaults to true.
	 * @param int[optional] $section_id ID of this collection's site section within the CMS.
	 * @param int[optional] $parent_id ID of the image collection's parent content record.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	function __construct($_generic_params=true, $contenttype_id=null, $parent_id=null )
	{
		parent::__construct($contenttype_id, $parent_id);
		$this->flickr_id = new StringInput("Flickr ID", "ixfi", false, "", 50);
		$this->wp_id = new StringInput("WordPress ID", "ixwp", false, null);
		$this->twitter_id = new StringInput("Twitter ID", "ixti", false, "", 64);
		$this->short_url = new StringInput("Short URL", "ixsu", false, "", 128);
		$this->setParameterNames($_generic_params);
	}

	/**
	 * Resets internal variables to their default value, while saving some values such as parent id and section properties.
	 */
	public function clearValues( )
	{
		parent::clearValues();
		$this->flickr_id = "";
		$this->wp_id = null;
		$this->twitter_id = "";
		$this->short_url = "";
		$this->setParameterNames(true);
	}

	/**
	 * Retrieve image properties from database.
	 * @param bool[optional] $read_keywords Flag to suppress retrieving keywords linked to the image_link record. Defaults to TRUE.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	function read($read_keywords=true )
	{
		parent::read($read_keywords);

		$query = <<<SQL
SELECT flickr_id
	, wp_id
	, twitter_id
	, short_url 
FROM image_link 
WHERE id = {$this->id->value}
SQL;
		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Image not found.");
		}

		$this->flickr_id->value = $data[0]->flickr_id;
		$this->wp_id->value = $data[0]->wp_id;
		$this->twitter_id->value = $data[0]->twitter_id;
		$this->short_url->value = $data[0]->short_url;
	}

	/**
	 * Upload images attached to the object, and save their properties in the datbase.
	 * @param bool[optional] $save_keywords Update keywords for the record. Defaults to true.
	 * @param bool[optional] $randomize_filename
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\OperationAbortedException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	function save($save_keywords=true, $randomize_filename=false)
	{
		$is_new = ($this->id->value===null || $this->id->value<1);

		if ($is_new) {
			if ($this->parent_id->value > 0) {
				/* put new pages at the end of the list of existing pages */
				$query = <<<SQL
SELECT IFNULL(MAX(`slot`),0)+1 AS `slot`  
FROM `image_link` 
WHERE `parent_id` = {$this->parent_id->value} 
AND `type_id` = {$this->content_properties->id->value}  
SQL;
				$data = $this->fetchRecords($query);
				$this->slot->value = $data[0]->slot;
			}
			else {
				$this->slot->value = 0;
			}
		}

		parent::save($save_keywords, $this->randomize->value);

		$query = "UPDATE `image_link` SET ".
			"`flickr_id` = ".$this->flickr_id->escapeSQL($this->mysqli).", ".
			"`wp_id` = ".$this->wp_id->escapeSQL($this->mysqli).", ".
			"`twitter_id` = ".$this->twitter_id->escapeSQL($this->mysqli).", ".
			"`short_url` = ".$this->short_url->escapeSQL($this->mysqli)." ".
			"WHERE `id` = {$this->id->value}";
		$this->query($query);

		if (class_exists("ContentCache") && $this->parent_id->value>0) {
			/*
			 * this is a hook to allow content to be cached after updates
			 * this cache_class needs to be included in the script that uses the image_xpost_class
			 * the types of content that are cached, and how they are cached are specific to the local script
			 */
			if ($is_new) {
				ContentCache::setInitialProperties($this);
			}
			ContentCache::updateCache($this->content_properties, $this);
		}
	}
}