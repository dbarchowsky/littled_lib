<?php

namespace Littled\PageContent\Images;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Request\StringInput;

/**
 * Class SocialXPostImage
 * @package Littled\PageContent\Images
 */
class SocialXPostImage extends ImageUpload
{
    /** @var StringInput        Flickr post id */
    public StringInput          $flickr_id;
    /** @var StringInput        WordPress post id */
    public StringInput          $wp_id;
    /** @var StringInput        Twitter post id */
    public StringInput          $twitter_id;
    /** @var StringInput        Short URL, e.g. Bit.ly URL */
    public StringInput          $short_url;

    /**
     * @param bool $generic_params (Optional) If set to true then the parameter names of the object's id, parent id,
     * and type id parameters will be set to generic names, ie "id", "pid", and "tid". Defaults to true.
     * @param int $content_type_id (Optional) ID of this collection's site section within the CMS.
     * @param int $parent_id (Optional) ID of the image collection's parent content record.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws InvalidStateException
     * @throws InvalidValueException
     */
    function __construct($generic_params = true, $content_type_id = null, $parent_id = null)
    {
        parent::__construct($content_type_id, $parent_id);
        $this->flickr_id = new StringInput('Flickr ID', 'info', false, '', 50);
        $this->wp_id = new StringInput('WordPress ID', 'fixup', false, null);
        $this->twitter_id = new StringInput('Twitter ID', 'inti', false, '', 64);
        $this->short_url = new StringInput('Short URL', key: 'ixusr', required: false, value: '', size_limit: 128);
        $this->setParameterNames($generic_params);
    }

    /**
     * Resets internal variables to their default value, while saving some values such as parent id and section properties.
     */
    public function clearValues(): void
    {
        parent::clearValues();
        $this->flickr_id->value = '';
        $this->wp_id->value = null;
        $this->twitter_id->value = '';
        $this->short_url->value = '';
        $this->setParameterNames(true);
    }

    /**
     * Retrieve image properties from database.
     * @param bool $read_keywords (Optional) Flag to suppress retrieving keywords linked to the image_link record.
     * Defaults to TRUE.
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws InvalidValueException
     */
    function read($read_keywords = true): void
    {
        parent::read($read_keywords);

        $query = 'SELECT flickr_id, '.
            'wp_id, '.
            'twitter_id, '.
            'short_url, '.
            'FROM image_link '.
            'WHERE id = ?';
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($data) < 1) {
            throw new RecordNotFoundException('Image not found.');
        }

        $this->flickr_id->value = $data[0]->flickr_id;
        $this->wp_id->value = $data[0]->wp_id;
        $this->twitter_id->value = $data[0]->twitter_id;
        $this->short_url->value = $data[0]->short_url;
    }

    /**
     * Upload images attached to the object, and save their properties in the database.
     * @param bool $save_keywords (Optional) Update keywords for the record. Defaults to true.
     * @param bool $randomize_filename
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws OperationAbortedException
     * @throws ResourceNotFoundException
     */
    function save(bool $save_keywords = true, bool $randomize_filename = false): void
    {
        $is_new = ($this->id->value === null || $this->id->value < 1);

        if ($is_new) {
            if ($this->parent_id->value > 0) {
                /* put new pages at the end of the list of existing pages */
                $query = 'SELECT ISNULL(MAX(`slot`),0)+1 AS `slot` '.
                    'FROM `image_link` '.
                    'WHERE `parent_id` = ? '.
                    'AND `type_id` = ?';
                $data = $this->fetchRecords($query, 'ii',
                    $this->parent_id->value, $this->content_properties->id->value);
                $this->slot->value = $data[0]->slot;
            }
            else {
                $this->slot->value = 0;
            }
        }

        parent::save($save_keywords, $this->randomize->value);

        $query = 'UPDATE `image_link` SET ' .
            '`flickr_id` = ?, ' .
            '`wp_id` = ?, ' .
            '`twitter_id` = ?, ' .
            '`short_url` = ? ' .
            'WHERE `id` = ?';
        $this->query($query, 'sass',
            $this->flickr_id->value,
            $this->wp_id->value,
            $this->twitter_id->value,
            $this->short_url->value,
            $this->id->value);

        // Commented out until this section can be refactored. These ContentCache routines are now abstract.
        // if (class_exists("ContentCache") && $this->parent_id->value > 0) {
        /*
         * this is a hook to allow content to be cached after updates
         * this cache_class needs to be included in the script that uses the SocialXPostImage
         * the types of content that are cached, and how they are cached are specific to the local script
         */
        /*
            if ($is_new) {
                ContentCache::setInitialProperties($this);
            }
            ContentCache::updateCache($this->content_properties, $this);
        }
        */
    }
}