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

    protected string $linked_lisings_cb;

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
     * Linked listings callback getter
     * @return false|string
     */
    public function getLinkedListingsCB()
    {
        if (isset($this->linked_lisings_cb)) {
            return $this->linked_lisings_cb;
        }
        return false;
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

    /**
     * Linked listings callback setter
     * @param string $procedure Name of database procedure to run to retrieve list of records linked to the parent.
     * @return void
     */
    public function setLinkedListingsCB(string $procedure)
    {
        $this->linked_lisings_cb = $procedure;
    }
}