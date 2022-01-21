<?php
namespace Littled\PageContent\SiteSection;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Ajax\ContentAjaxProperties;
use Exception;

class SectionContent extends SerializedContent
{
	/** @var ContentProperties Site section properties. */
	public $content_properties;

	/**
	 * SectionContent constructor.
	 * @param ?int $id Record id to retrieve.
	 * @param ?int $content_type_id Record id of site section where this piece of content belongs.
	 */
	public function __construct(int $id=null, int $content_type_id=null)
	{
		parent::__construct($id);
		$this->content_properties = new ContentProperties($content_type_id);
		$this->content_properties->id->label = "Content type";
		$this->content_properties->id->required = true;
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
	 * Content properties id getter.
	 * @return int
	 */
	public function getContentPropertyId(): int
	{
		return $this->content_properties->id->value;
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
        if ($this->content_properties instanceof ContentProperties) {
            foreach ($listings_tokens as $token) {
                $template = $this->content_properties->getContentTemplateByName($token);
                if ($template instanceof ContentTemplate) {
                    return $template->formatFullPath();
                }
            }
        }
        return '';
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
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
	 * @throws RecordNotFoundException
     * @throws Exception
	 */
	public function refreshContentAfterEdit( FilterCollection &$filters ): string
	{
		$template = $this->getListingsTemplatePath();
		if (strlen($template) < 1) {
			$ao = new ContentAjaxProperties();
			$ao->section_id->value = $this->content_properties->id->value;
			$ao->retrieveContentProperties();
			$template = $ao->listings_template->value;
		}
		if (strlen($template) < 1) {
			throw new ResourceNotFoundException("Listings template not available.");
		}

		$context = array(
			'content' => &$this,
			'filters' => &$filters);
		return(ContentUtils::loadTemplateContent($template, $context));
	}

	/**
	 * Retrieves site section properties and stores that data in object properties.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		if ($this->content_properties->id->value===null || $this->content_properties->id->value < 1) {
			throw new ContentValidationException("Cannot retrieve section properties. Content site section not specified within ".get_class($this).".");
		}
		$this->content_properties->read();
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
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