<?php
namespace Littled\PageContent\SiteSection;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\Serialized\SerializedContent;
use Exception;
use Littled\Request\StringInput;

/**
 * Extends SerializedContent by adding properties of the serialized content.
 */
abstract class SectionContent extends SerializedContent
{
	/** @var ContentProperties Site section properties. */
	public ContentProperties $content_properties;

	/**
	 * SectionContent constructor.
	 * @param ?int $id Record id to retrieve.
	 * @param ?int $content_type_id Record id of site section where this piece of content belongs.
	 * @throws ConfigurationUndefinedException
	 */
	public function __construct(int $id=null, int $content_type_id=null)
	{
		parent::__construct($id);
		$this->content_properties = new ContentProperties($content_type_id ?: static::getContentTypeId());
		$this->content_properties->id->label = "Content type";
		$this->content_properties->id->required = true;
	}

	/**
	 * Kludge work-around for hosting providers with mod_security
	 * enabled. This assumes that JavaScript base-64 encodes the form data
	 * before the form data is submitted.
	 * @return void
	 */
	public function base64DecodeInput()
	{
		foreach($this as $item) {
			if (($item instanceof StringInput) &&
				strlen($item->value) > 0) {
				$item->value = base64_decode(strip_tags($item->value));
			}
		}
	}

	/**
	 * Fills the object's property values from input variable values, e.g. GET, POST, etc.
	 * @param ?array $src (Optional) Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectRequestData(?array $src=null)
	{
		$this->content_properties->bypassCollectFromInput = true;
		parent::collectRequestData($src);
	}

	/**
	 * Deletes the Site Section record matching the object's internal ID value.
	 * @return string
	 * @throws ContentValidationException
     * @throws NotImplementedException
	 */
	public function delete(): string
	{
		parent::delete();
		return ("Successfully deleted ".strtolower($this->content_properties->name->value)." record.");
	}

    /**
     * Implement abstract method not referenced for unit test purposes.
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }

    /**
	 * Content properties id getter.
	 * @return int
	 */
	public function getContentPropertyId(): int
	{
		return $this->content_properties->id->value;
	}

	/**
     * Content label getter.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->content_properties->label->value;
    }

	/**
	 * Content label getter.
	 * @return string
	 */
	public function getContentLabel(): string
	{
		return $this->content_properties->getContentLabel();
	}

	/**
	 * Returns the path to the "listings" template for this type of content.
	 * The client app will set the value of the $listingsTemplate property.
	 * @return string Path to listings template.
     * @throws Exception
	 */
	public function getListingsTemplatePath(): string
	{
        $listings_tokens = array('listings', 'cms-listings');
        foreach ($listings_tokens as $token) {
            $template = $this->content_properties->getContentTemplateByName($token);
            if ($template instanceof ContentTemplate) {
                return $template->formatFullPath();
            }
        }
        return '';
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws NotImplementedException
	 */
	public function read()
	{
		parent::read();
		$this->retrieveSectionProperties();
	}

	/**
	 * Generates markup to use to refresh listings content after inline edits have been applied to the "listings" data.
	 * @param FilterCollection $filters Filters to apply to listings content
	 * @return string Updated listings markup.
	 * @throws ResourceNotFoundException
     * @throws Exception
	 */
	public function refreshContentAfterEdit( FilterCollection &$filters ): string
	{
		$template = $this->getListingsTemplatePath();
		if (!$template) {
			throw new ResourceNotFoundException("Listings template not available.");
		}

		$context = array(
			'content' => &$this,
			'filters' => &$filters);
		return(ContentUtils::loadTemplateContent($template, $context));
	}

	/**
	 * Retrieves site section properties and stores that data in object properties.
=	 * @throws ConfigurationUndefinedException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		if ($this->content_properties->id->value===null || $this->content_properties->id->value < 1) {
			$this->content_properties->id->value = static::getContentTypeId();
		}
		$this->content_properties->read();
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function save()
	{
		if ($this->content_properties->id->value===null || $this->content_properties->id->value < 1) {
			throw new ContentValidationException("A content type was not specified.");
		}
		$this->content_properties->read();
		parent::save();
	}

	/**
	 * Tests for a valid content type id. Throws ContentValidationException if the property value isn't current set.
     * @param string $msg (Optional) Message to prepend to error message.
	 * @throws ContentValidationException
	 */
	protected function testForContentType(string $msg='')
	{
		if (null === $this->content_properties->id->value || 1 > $this->content_properties->id->value) {
            $msg = ($msg)?("$msg "):("Could not perform operation. ");
			throw new ContentValidationException("{$msg}A content type was not specified.");
		}
	}
}