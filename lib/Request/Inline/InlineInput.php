<?php

namespace Littled\Request\Inline;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidPropertyException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Request\StringInput;

abstract class InlineInput extends SectionContent
{
    public StringInput $operation;
    /** @var string[] Property values to validate after changes are made in an HTML form. */
    public array $validate_properties;

    public const string OPERATION_KEY = 'op';

    protected static string $input_property;

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     * @throws ConfigurationUndefinedException
     */
    function __construct()
    {
        if (!isset(static::$input_property)) {
            throw new ConfigurationUndefinedException('Input property not configured in ' . Log::getClassBaseName(static::class));
        }
        parent::__construct();
        $this->content_properties = (new ContentProperties())
            ->withConnection($this)
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
        $this->validate_properties = ['id', 'content_properties' => ['id'], 'operation', static::$input_property];
    }

    /**
     * @inheritDoc
     * Collect content properties values when collecting request data.
     */
    protected function configureContentPropertyCollection(): void
    {
        $this->content_properties->bypassCollectFromInput = false;
    }

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $property = static::$input_property;
        $query = 'UPDATE `' . $this->getTableName() . "` SET `$property` = ? WHERE `id` = ?";
        $types_str = $this->{$property}::getPreparedStatementTypeIdentifier() . 'i';
        return [$query, $types_str, $this->{$property}->value, $this->id->value];
    }

    /**
     * @inheritDoc
     */
    protected function formatRecordSelectQuery(): array
    {
        $property = static::$input_property;
        $query = "SELECT `$property` FROM `" . $this->getTableName() . '` WHERE `id` = ?';
        return [$query, 'i', $this->id->value];
    }

    /**
     * Returns the value of the property controlled by the object.
     * @return mixed
     */
    public function getValue(): mixed
    {
        $property = static::$input_property;
        return $this->{$property}->value;
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        $property = static::$input_property;
        return $this->{$property}->hasData();
    }

    /**
     * @inheritDoc
     * @return InlineInput
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function read(): static
    {
        $this->hydrateFromQuery(...$this->formatRecordSelectQuery());
        return $this;
    }

    /**
     * @inheritdoc
     * @throws FailedQueryException
     */
    public function save(): void
    {
        $this->query(...$this->formatCommitQuery());
    }

    /**
     * Sets the value of the property controlled by the object.
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): static
    {
        $property = static::$input_property;
        $this->{$property}->setInputValue($value);
        return $this;
    }

    /**
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @inheritDoc
     * @throws InvalidPropertyException
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
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