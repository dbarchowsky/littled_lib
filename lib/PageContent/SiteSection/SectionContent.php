<?php

namespace Littled\PageContent\SiteSection;

use Littled\Exception\CommitException;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\StringInput;
use Exception;


/**
 * Extends SerializedContent by adding properties of the serialized content.
 */
abstract class SectionContent extends SerializedContent
{
    /** @var ContentProperties Site section properties. */
    public ContentProperties $content_properties;

    /**
     * @inheritDoc
     * @param ?int $id Record id to retrieve.
     * @param ?int $content_type_id Record id of the site section where this piece of content belongs.
     */
    public function __construct(int|null $id = null, int|null $content_type_id = null)
    {
        parent::__construct($id);
        if ($content_type_id === null) {
            try {
                $content_type_id = static::getContentTypeId();
            } catch(Exception) {
                /* continue */
            }
        }
        $this->content_properties = (new ContentProperties())
            ->withConnection($this)
            ->setRecordId($content_type_id)
            ->setLabel('Content type')
            ->setAsRequired();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     * @throws ReadException
     */
    protected function _getTableName(): string
    {
        return static::$table_name ?? $this->getContentProperties()->table->value;
    }

    /**
     * @inheritDoc
     */
    public function _getContentTypeId(): int|null
    {
        if (isset($this->content_properties) && ($this->content_properties->getRecordId() ?? 0) > 0) {
            return $this->content_properties->getRecordId();
        }
        try {
            return parent::_getContentTypeId();
        }
        catch (ConfigurationUndefinedException) {
            return null;
        }
    }

    /**
     * Kludge work-around for hosting providers with mod_security
     * enabled. This assumes that JavaScript base-64 encodes the form data
     * before the form data is submitted.
     * @return void
     */
    public function base64DecodeInput(): void
    {
        foreach ($this as $item) {
            if (($item instanceof StringInput) &&
                strlen($item->value) > 0) {
                $item->value = base64_decode(strip_tags($item->value));
            }
        }
    }

    /**
     * Collects the content properties record id from the input data.
     * @param ?array $src (Optional) Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @return void
     */
    protected function collectContentPropertiesRequestData(?array $src = null): void
    {
        if ($this->content_properties->getRecordId() > 1) {
            return;
        }
        $this->content_properties->id->collectRequestData($src);
    }

    /**
     * Fills the object's property values from input variable values, e.g. GET, POST, etc.
     * @param ?array $src (Optional) Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @return $this
     */
    public function collectRequestData(?array $src = null): static
    {
        // skip over the content properties object when retrieving request data
        $this->configureContentPropertyCollection();

        // collect request data applicable to the content record in the database
        parent::collectRequestData($src);

        // collect just the content type record id and load the content type properties if it was specified
        $this->collectContentPropertiesRequestData($src);

        return $this;
    }

    /**
     * Determines if the content properties property should be excluded from request data collection.
     * @return void
     */
    protected function configureContentPropertyCollection(): void
    {
        $this->content_properties->bypassCollectFromInput = true;
    }

    /**
     * Deletes the Site Section record matching the object's internal ID value.
     * @return string
     * @throws FailedQueryException
     * @throws InvalidStateException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     * @throws RecordUnavailableException
     */
    public function delete(): string
    {
        parent::delete();
        return ('Successfully deleted ' . strtolower($this->getContentLabel()) . ' record.');
    }

    /**
     * Alias for retrieveSectionProperties()
     * @return void
     * @throws RecordUnavailableException
     */
    public function fetchProperties(): void
    {
        $this->retrieveSectionProperties();
    }

    /**
     * Implements an abstract method not referenced for unit test purposes.
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }

    /**
     * Returns the content properties object. Retrieves the object property values from the database if they aren't already set.
     * @return ContentProperties
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws ReadException
     * @throws RecordNotFoundException
     */
    public function getContentProperties(): ContentProperties
    {
        $this->testForContentType();
        if (($this->content_properties->name->value ?? '') === '') {
            $this->content_properties->read();
        }
        return $this->content_properties;
    }

    /**
     * @deprecated Use getContentTypeId() instead.
     * Content properties id getter.
     * @return int|null
     * @throws ConfigurationUndefinedException
     */
    public function getContentPropertyId(): int|null
    {
        return $this->_getContentTypeId();
    }

    /**
     * Returns a string representing the type of content of this content record.
     * @return string
     * @throws NotInitializedException
     * @throws RecordUnavailableException
     */
    public function getContentLabel(): string
    {
        if ((($this->content_properties->getRecordId() ?: 0) < 1) &&
            ((static::$content_type_id ?? 0) > 0)) {
            $this->setContentType(static::$content_type_id);
        }
        return $this->content_properties->getContentLabel(true);
    }

    /**
     * Returns the content type slug property value of the individual content record.
     * @return string
     */
    public function getContentTypeSlug(): string
    {
        if (!isset($this->content_properties)) {
            return '';
        }
        return $this->content_properties->slug->value;
    }

    /**
     * Returns the name or label property value of the individual content record.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->name->value ?? $this->label->value ?? '';
    }

    /**
     * Returns the path to the "listings" template for this type of content.
     * The client app will set the value of the $listingsTemplate property.
     * @return string Path to the listing template.
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
     * Retrieves the content record from the database.
     * @return $this
     * @throws RecordUnavailableException
     */
    public function read(): static
    {
        try {
            parent::read();
            $this->retrieveSectionProperties();
        }
        catch (FailedQueryException|ReadException|RecordNotFoundException $ex) {
            throw new RecordUnavailableException($ex->throwMessage('Error retrieving content record'));
        }
        return $this;
    }

    /**
     * Generates markup to use to refresh the listing content after inline edits have been applied to the "listings" data.
     * @param FilterCollection $filters Filters to apply to listings content
     * @return string Updated listings markup.
     * @throws ResourceNotFoundException
     * @throws Exception
     */
    public function refreshContentAfterEdit(FilterCollection &$filters): string
    {
        $template = $this->getListingsTemplatePath();
        if (!$template) {
            throw new ResourceNotFoundException('Listings template not available.');
        }

        $context = array(
            'content' => &$this,
            'filters' => &$filters);
        return (ContentUtils::loadTemplateContent($template, $context));
    }

    /**
     * Retrieves site section properties and stores that data in object properties.
     * @throws RecordUnavailableException
     */
    public function retrieveSectionProperties(): void
    {
        if ($this->content_properties->id->value === null || $this->content_properties->id->value < 1) {
            $this->content_properties->id->value = static::getContentTypeId();
        }
        try {
            $this->content_properties->read();
        }
        catch (
            FailedQueryException|ReadException|RecordNotFoundException $ex) {
            throw new RecordUnavailableException($ex->throwMessage('Error retrieving content properties'));
        }
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws ReadException
     * @throws RecordNotFoundException
     * @throws CommitException
     */
    public function save(): void
    {
        $this->getContentProperties();
        parent::save();
    }

    /**
     * Content type id setter.
     * @param int $id
     * @return $this
     */
    public function setContentType(int $id): static
    {
        $this->content_properties->setRecordId($id);
        return $this;
    }

    /**
     * Tests for a valid content type id. Throws ContentValidationException if the property value isn't current set.
     * @param string $msg (Optional) Message to prepend to an error message.
     * @throws ConfigurationUndefinedException
     */
    protected function testForContentType(string $msg = ''): void
    {
        if (($this->content_properties->id->value ?? 0) < 1) {
            $msg = ($msg) ? ("$msg ") : ('Could not perform operation. ');
            throw new ConfigurationUndefinedException("$msg A content type was not specified.");
        }
    }

    /**
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @inheritDoc
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        $this->content_properties->bypass_validation = true;
        parent::validateInput($exclude_properties, $clear_existing);
    }
}