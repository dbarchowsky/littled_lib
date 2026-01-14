<?php
namespace Littled\PageContent\Serialized;

use Littled\Database\AppContentBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\ContentUtils;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\Validation\Validation;
use Exception;


/**
 * Class SerializedContentUtils
 *
 * @method getContentTypeId(): int
 * @method static getContentTypeId(): int
 */
class SerializedContentUtils extends AppContentBase
{
    use PropertyEvaluations {
        setRecordsetPrefix as traitSetRecordsetPrefix;
    }
    use SerializedFieldOperations, HydrateFieldOperations {
        applyInputKeyPrefix as traitApplyInputKeyPrefix;
        fill as traitFill;
        setColumnPrefix as traitSetColumnPrefix;
        setInputPrefix as traitSetInputPrefix;
    }

    /** @var string             Path to CMS template dir */
    protected static string     $common_cms_template_path;
    protected static int        $content_type_id;
    /** @var string             Path to cache template. */
    protected static string     $cache_template = '';
    /** @var string             Path to a rendered cache file to use on the site front-end. */
    protected static string     $output_cache_file = '';

    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     * @throws ConfigurationUndefinedException
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'getContentTypeId') {
            return $this->_getContentTypeId();
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     * @throws ConfigurationUndefinedException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'getContentTypeId') {
            return (new static())->_getContentTypeId();
        }
        return null;
    }

    /**
     * Checks if the content type id property exists and returns its value.
     * @return int|null Class's content type id value, if it has been defined.
     * @throws ConfigurationUndefinedException
     */
    protected function _getContentTypeId(): int|null
    {
        if (!isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException('Content type not set in ' . Log::getClassBaseName(static::class));
        }
        return static::$content_type_id;
    }

    /**
     * Add a separator string after a string.
     * @param string $str Source string.
     * @param string $separator (Optional) Character or string to append to the source string. Defaults to a comma.
     * @return string Modified string containing the separator.
     */
    public function appendSeparator(string $str, string $separator = ','): string
    {
        if (strlen(trim($str)) > 0) {
            $str = rtrim($str) . "$separator ";
        }
        return ($str);
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function applyInputKeyPrefix(string $prefix): static
    {
        $this->traitApplyInputKeyPrefix($prefix);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function fill(object|array $src): static
    {
        $this->traitFill($src);
        return $this;
    }

    /**
     * Returns cache template path.
     * @return string Cache template path.
     */
    public static function getCacheTemplatePath(): string
    {
        return (static::$cache_template);
    }

    /**
     * Returns current common cms template path value.
     * @return string Current common cms template path value.
     * @throws ConfigurationUndefinedException
     */
    public static function getCommonCMSTemplatePath(): string
    {
        if (!isset(static::$common_cms_template_path) || Validation::isStringBlank(static::$common_cms_template_path)) {
            throw new ConfigurationUndefinedException('Path to shared content templates not set.');
        }
        return static::$common_cms_template_path;
    }

    /**
     * Assign values contained in an array to object input properties.
     * @param string $query SQL SELECT statement to use to hydrate object property values.
     * @throws RecordNotFoundException
     * @throws FailedQueryException
     */
    protected function hydrateFromQuery(string $query, string $arg_types = '', ...$args): void
    {
        $data = $this->fetchRecords($query, $arg_types, ...$args);
        if (count($data) < 1) {
            $msg = (ucfirst(strtolower(static::getContentLabel())) ?: 'Record') . ' not found.';
            throw new RecordNotFoundException($msg);
        }
        $this->hydrateFromRecordsetRow($data[0]);
    }

    /**
     * Tests if any of the RequestInput properties of the object are marked as required.
     * @return bool
     */
    public function isRequired(): bool
    {
        $properties = $this->getKeyPropertiesList();
        foreach ($properties as $property) {
            if ($this->{$property}->isRequired()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the form data members of the object as a JSON string.
     * @param ?array $exclude_keys Array of property names to exclude from the encoding.
     * @return string JSON-encoded name/value pairs extracted from the object.
     */
    public function jsonEncode(?array $exclude_keys = null): string
    {
        return (json_encode($this->arrayEncode($exclude_keys)));
    }

    /**
     * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
     * @param int $count Number determining if the label is plural or not.
     * @param string $property_name Name of property to make plural.
     * @return string Plural form of the record label if $count is not 1.
     * @throws ConfigurationUndefinedException
     */
    public function pluralLabel(int $count, string $property_name): string
    {
        if (!property_exists($this, $property_name)) {
            throw new ConfigurationUndefinedException(
                "Cannot get plural label for unknown property \"$property_name\" of " . get_class($this)
            );
        }
        if ($this->{$property_name} instanceof StringInput === false) {
            throw new ConfigurationUndefinedException(
                'Cannot get plural label for non-string input ' . get_class($this) . "::$property_name."
            );
        }
        if ($this->{$property_name}->value === null || $this->{$property_name}->value === '') {
            return '';
        }

        $label = $this->{$property_name}->value;
        if ($count === 1) {
            return ($label);
        } else {
            return (static::makePlural($label));
        }
    }

    /**
     * Add a separator string before a string.
     * @param string $str Source string.
     * @param string $separator (Optional) Character or string to prepend to the source string. Defaults to a comma.
     * @return string Modified string containing the separator.
     */
    public function prependSeparator(string $str, string $separator = ','): string
    {
        if (strlen(trim($str)) > 0) {
            $str = "$separator " . ltrim($str);
        }
        return ($str);
    }

    /**
     * Restores column name value to a RequestInput property of the object.
     * @param string $property
     * @param string $column_name
     * @return void
     */
    public function restoreColumnName(string $property, string $column_name): void
    {
        if (property_exists($this, $property) && $this->{$property} instanceof RequestInput) {
            $this->{$property}->setColumnName($column_name);
        }
    }

    /**
     * Sets value of a shared cms templates path.
     * @param string $path Path to shared cms templates.
     */
    public static function setCommonCMSTemplatePath(string $path): void
    {
        static::$common_cms_template_path = $path;
    }

    /**
     * Sets the "not required" flag of any RequestInput properties of the object.
     * @return $this
     */
    public function setAllNotRequired(): static
    {
        // update all RequestInput properties
        $properties = array_merge($this->getInputPropertiesList(), $this->getKeyPropertiesList());
        foreach ($properties as $property) {
            $this->$property->setAsNotRequired();
        }
        $properties = $this->getLinkedContentPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAllNotRequired();
        }
        return $this;
    }

    /**
     * Sets the "required" flag of any RequestInput properties on the object.
     * @return $this
     */
    public function setAllRequired(): static
    {
        // update all RequestInput properties
        $properties = array_merge($this->getInputPropertiesList(), $this->getKeyPropertiesList());
        foreach ($properties as $property) {
            $this->$property->setAsRequired();
        }
        $properties = $this->getLinkedContentPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAllRequired();
        }
        return $this;
    }

    /**
     * Sets the "not required" flag of any properties used to link the object to other objects.
     * @return $this
     */
    public function setAsNotRequired(): static
    {
        // only update key properties
        $properties = $this->getKeyPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAsNotRequired();
        }
        $properties = $this->getLinkedContentPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAsNotRequired();
        }
        return $this;
    }

    /**
     * Alias for setAsNotRequired()
     * @return $this
     */
    public function setAsOptional(): static
    {
        return $this->setAsNotRequired();
    }

    /**
     * Sets the "required" flag of any properties used to link the object to other objects.
     * @return $this
     */
    public function setAsRequired(): static
    {
        // only update key properties
        $properties = $this->getKeyPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAsRequired();
        }
        $properties = $this->getLinkedContentPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAsRequired();
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function setColumnPrefix(string $prefix): static
    {
        $this->traitSetColumnPrefix($prefix);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function setInputPrefix(string $prefix): static
    {
        $this->traitSetInputPrefix($prefix);
        return $this;
    }

    /**
     * Sets the "required" flag of any properties used to link the object to other objects.
     * @param bool $required
     * @return SerializedContentUtils
     */
    public function setIsRequired(bool $required): static
    {
        if ($required) {
            $this->setAsRequired();
        } else {
            $this->setAsNotRequired();
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @return string|string[] $this
     */
    public function setRecordsetPrefix(string|array $prefix): static
    {
        $this->traitSetRecordsetPrefix($prefix);
        return $this;
    }

    /**
     * Remove column name value from property.
     * @param string $property
     * @return string
     */
    public function stashColumnName(string $property): string
    {
        if (property_exists($this, $property) && $this->{$property} instanceof RequestInput) {
            $column_name = $this->{$property}->getColumnName('');
            $this->{$property}->setColumnName('');
            return $column_name;
        }
        return '';
    }

    /**
     * Loads content from a template file. Writes the parsed content to a separate file.
     * @param ?array $context Array containing name/value pairs representing variable names and values to insert into the source template at $src_path;
     * @param ?string $cache_template Path to content template. If not supplied, the internal $cache_template value will be used.
     * @param ?string $output_cache_file Path to a cache file. If not supplied, the internal $output_cache_file value will be used.
     * @throws ResourceNotFoundException Cache template not found.
     * @throws Exception File error.
     */
    function updateCacheFile(
        ?array $context = null,
        ?string $cache_template = null,
        ?string $output_cache_file = null): void
    {
        if ($cache_template === null) {
            $cache_template = static::$cache_template;
            if (!file_exists($cache_template)) {
                $err_msg = "External link cache template not available at \"$cache_template\".";
                throw new ResourceNotFoundException($err_msg);
            }
        }
        if ($output_cache_file === null) {
            $output_cache_file = static::$output_cache_file;
        }
        $cache_content = ContentUtils::loadTemplateContent($cache_template, $context);
        $f = fopen($output_cache_file, 'w');
        fputs($f, $cache_content);
        fclose($f);
    }
}
