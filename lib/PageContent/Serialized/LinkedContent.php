<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Validation\Validation;
use Littled\Log\Log;


abstract class LinkedContent extends SerializedContent
{
    use HydrateFieldOperations, InputOperations {
        applyInputKeyPrefix as traitApplyInputKeyPrefix;
    }

    /**
     * @inheritDoc
     * @param string $prefix
     * @return $this
     */
    public function applyInputKeyPrefix(string $prefix): LinkedContent
    {
        $this->traitApplyInputKeyPrefix($prefix);
        return $this;
    }

    /**
     * Returns a list of the names of the object properties that represent content objects, i.e. derived from
     * SerializedContent.
     * @param string[] $exclude
     * @return array
     */
    protected function extractContentPropertiesList(array $exclude = []): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubClass($property, SerializedContent::class) &&
                !in_array($key, $exclude)) {
                $properties[] = $key;
            }
        }
        return $properties;
    }

    /**
     * Returns only the fields that map to the table managed by this class. The parent routine returns all RequestInput
     * properties of the object. This routine overrides that to subtract the fields from linked content object.
     * It also tests for any properties that may be pointers to child properties.
     * @param array $used_keys
     * @return QueryField[]
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $fields = parent::extractPreparedStmtArgs($used_keys);

        // remove any properties that map to child content objects
        $content = $this->extractContentPropertiesList();
        for($i = count($fields)-1; $i >= 0; $i--) {
            if (in_array($fields[$i]->key, $content)) {
                unset($fields[$i]);
            }
            // re-index array to avoid missing elements in subsequent loops
            $fields = array_values($fields);
        }

        // remove any properties that are pointers to child content object properties
        foreach($content as $property) {
            $cp = $this->$property->getInputPropertiesList();
            for($i = count($fields)-1; $i >= 0; $i--) {
                if (in_array($fields[$i]->key, $cp)) {
                    unset($fields[$i]);
                }
            }
            // re-index array to avoid missing elements in subsequent loops
            $fields = array_values($fields);
        }

        return $fields;
    }

    /**
     * Returns the value of the id of the record that this record is linked to.
     * @return int|null
     */
    abstract public function getLinkedId(): int| null;

    /**
     * Returns the record id value of the parent record.
     * @return int|null
     */
    abstract public function getParentId(): int|null;

    /**
     * Returns the key value for primary key input.
     * @return string
     */
    abstract public function getPrimaryKey(): string;

    /**
     * Combines two prepared statement argument lists.
     * @param array $base
     * @param ?array $args
     * @return array
     */
    protected static function mergeArgLists(array $base, ?array $args): array
    {
        $args ??= [];
        return array_merge($base, $args);
    }

    /**
     * Combines two prepared statement argument type strings.
     * @param string $base
     * @param ?string $arg_types
     * @return string
     * @todo confirm this method is needed
     */
    protected static function mergeArgTypeStrings(string $base, ?string $arg_types): string
    {
        return $base.$arg_types;
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function read(): static
    {
        if ($this->id->hasData() && $this->id->isDatabaseField()) {
            parent::read();
        }

        try {
            $this->hydrateFromQuery(...$this->formatRecordSelectPreparedStmt());
        } catch (RecordNotFoundException) {
            $table = '[ERR:TABLE NAME NOT CONFIGURED IN CLASS ' . Log::getClassBasename($this::class). ']';
            try {
                $table = $this::getTableName();
            }
            catch(ConfigurationUndefinedException) { /* skip */ }
            throw new RecordNotFoundException("The requested $table record was not found.");
        }

        $linked = $this->getContentPropertiesList();
        foreach($linked as $property) {

            if (!$this->{$property}->isReadyToRead()) {
                continue;
            }

            // Use a new instance to retrieve data from the database to avoid any overrides made to the instance that is a property of the parent.
            $class = get_class($this->{$property});
            /** @var SerializedContent $o */
            $o = (new $class())
                ->shareConnection($this)
                ->setRecordId($this->{$property}->getRecordId())
                ->read();
            $this->{$property}->copy($o);
        }
        return $this;
    }

    /**
     * Sets the value of the id of the record that this object is linked to.
     * @param int|null $record_id
     * @return $this
     */
    abstract public function setLinkedId(int|null $record_id): static;

    /**
     * Assigns the record id value of the parent record.
     * @param int|null $record_id
     * @return $this
     */
    abstract public function setParentId(int|null $record_id): static;
}