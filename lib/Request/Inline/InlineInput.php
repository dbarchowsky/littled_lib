<?php

namespace Littled\Request\Inline;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidPropertyException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Request\StringInput;

abstract class InlineInput extends SectionContent
{
    public StringInput $operation;
    /** @var string[] Property values to validate after changes are made in an HTML form. */
    public array $validate_properties;

    public const OPERATION_KEY = 'op';

    /**
     * @inheritdoc
     */
    function __construct()
    {
        try {
            parent::__construct();
        } catch (ConfigurationUndefinedException $ex) {
            /** ignore unset content type */
            if (!preg_match('/^content type/i', $ex->getMessage())) {
                throw $ex;
            }
        }
        $this->content_properties = (new ContentProperties())
            ->shareConnection($this)
            ->setLabel('Content type')
            ->setAsRequired();
        $this->id
            ->setLabel('Record id')
            ->setAsRequired();
        $this->operation = (new StringInput())
            ->setLabel('Operation')
            ->setKey(self::OPERATION_KEY)
            ->setAsRequired()
            ->setSizeLimit(20);
        $this->validate_properties = ['id', 'content_properties' => ['id'], 'operation'];
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     */
    public function save(): void
    {
        $this->query(...$this->formatCommitQuery());
    }

    /**
     * @inheritDoc
     * @throws InvalidPropertyException
     */
    public function validateInput(array $exclude_properties = []): void
    {
        foreach ($this->validate_properties as $key => $value) {
            if (is_numeric($key) || is_string($value)) {
                $this->validatePropertyValue($value);
            }
            elseif (is_array($value)) {
                foreach ($value as $subvalue) {
                    $this->validatePropertyValue($subvalue, $key);
                }
            }
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException('There were problems found in the information that was entered.');
        }
    }

    /**
     * Validates individual property values based on the property name.
     * @param string $property
     * @param string $obj_property
     * @return void
     * @throws InvalidPropertyException
     */
    protected function validatePropertyValue(string $property, string $obj_property=''): void
    {
        if (!empty($obj_property)) {
            if (!property_exists($this, $obj_property)) {
                throw new InvalidPropertyException("Invalid property '$obj_property' in " . Log::getShortMethodName() . '().');
            }
            if (!property_exists($this->{$obj_property}, $property)) {
                throw new InvalidPropertyException("Invalid property '$property' on '$obj_property' in " . Log::getShortMethodName() . '().');
            }
            $p = $this->{$obj_property}->{$property};
        }
        else {
            if (!property_exists($this, $property)) {
                throw new InvalidPropertyException("Invalid property '$property' in " . Log::getShortMethodName() . '().');
            }
            $p = $this->{$property};
        }
        if (property_exists($this, $property) && method_exists($this->{$property}, 'validate')) {
            try {
                $p->validate();
            } catch (ContentValidationException $ex) {
                $this->addValidationError($ex->getMessage());
            }
        }
    }
}