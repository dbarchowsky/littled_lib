<?php

namespace Littled\Request;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Serialized\LinkedContent;
use Littled\Validation\Validation;

/**
 * Input class designed to collect and commit foreign key input.
 */
class ForeignKeyInput extends IntegerSelect
{
    protected string $content_class;

    public function __construct(
        string  $label,
        string  $key,
        bool    $required = false,
                $value = null,
        int     $size_limit = 0,
        ?int    $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->setIsDatabaseField(false);
    }

    /**
     * Content class getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getContentClass():string
    {
        if (!isset($this->content_class)) {
            throw new ConfigurationUndefinedException(
                'The content class of the foreign key input has not been assigned.');
        }
        return $this->content_class;
    }

    /**
     * Content class setter.
     * @param string $class
     * @return void
     * @throws InvalidTypeException
     */
    public function setContentClass(string $class): ForeignKeyInput
    {
        if (!Validation::isSubclass($class, LinkedContent::class)) {
            throw new InvalidTypeException(
                'Content class for foreign keys must be of type '.LinkedContent::class.'.');
        }
        $this->content_class = $class;
        return $this;
    }
}