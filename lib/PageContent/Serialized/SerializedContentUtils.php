<?php

namespace Littled\PageContent\Serialized;

use Littled\Database\AppContentBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Request\StringInput;
use Exception;
use Littled\Validation\Validation;


/**
 * Class SerializedContentUtils
 * @package Littled\PageContent\Serialized
 */
class SerializedContentUtils extends AppContentBase
{
    use PropertyEvaluations {
        setRecordsetPrefix as traitSetRecordsetPrefix;
    }
    use SerializedFieldOperations, HydrateFieldOperations {
        applyInputKeyPrefix as traitApplyInputKeyPrefix;
        fill as traitFill;
    }


    /** @var string             Path to CMS template dir */
    protected static string     $common_cms_template_path;
    protected static int        $content_type_id;
    /** @var string             Path to cache template. */
    protected static string     $cache_template = '';
    /** @var string             Path to rendered cache file to use on site front-end. */
    protected static string     $output_cache_file = '';

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
    public function applyInputKeyPrefix(string $prefix): SerializedContentUtils
    {
        $this->traitApplyInputKeyPrefix($prefix);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function fill($src): SerializedContentUtils
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
            throw new ConfigurationUndefinedException("Path to shared content templates not set.");
        }
        return static::$common_cms_template_path;
    }

    /**
     * Checks if content type id property exists and returns its value.
     * @return ?int Class's content type id value, if it has been defined.
     * @throws InvalidStateException
     */
    public static function getContentTypeId(): ?int
    {
        if (!isset(static::$content_type_id)) {
            throw new InvalidStateException('Content type not set.');
        }
        return static::$content_type_id;
    }

    /**
     * Assign values contained in array to object input properties.
     * @param string $query SQL SELECT statement to use to hydrate object property values.
     * @throws RecordNotFoundException
     * @throws ConnectionException|ConfigurationUndefinedException|InvalidQueryException
     */
    protected function hydrateFromQuery(string $query, string $arg_types = '', &...$args)
    {
        if ($arg_types) {
            array_unshift($args, $query, $arg_types);
            $data = $this->fetchRecords(...$args);
        } else {
            $data = $this->fetchRecords($query);
        }
        if (count($data) < 1) {
            throw new RecordNotFoundException("Record not found.");
        }
        $this->hydrateFromRecordsetRow($data[0]);
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
                "Cannot get plural label for non-string input " . get_class($this) . "::$property_name."
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
     * Sets value of shared cms templates path.
     * @param string $path Path to shared cms templates.
     */
    public static function setCommonCMSTemplatePath(string $path)
    {
        static::$common_cms_template_path = $path;
    }

    /**
     * Sets the "not required" flag of all RequestInput properties of the object to FALSE.
     * @return $this
     */
    public function setAsNotRequired(): SerializedContentUtils
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->setAsNotRequired();
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function setRecordsetPrefix($prefix): SerializedContentUtils
    {
        $this->traitSetRecordsetPrefix($prefix);
        return $this;
    }

    /**
     * Loads content from a template file. Writes the parsed content to a separate file.
     * @param ?array $context Array containing name/value pairs representing variable names and values to insert into the source template at $src_path;
     * @param ?string $cache_template Path to content template. If not supplied, the internal $cache_template value will be used.
     * @param ?string $output_cache_file Path to cache file. If not supplied, the internal $output_cache_file value will be used.
     * @throws ResourceNotFoundException Cache template not found.
     * @throws Exception File error.
     */
    function updateCacheFile(?array $context = null, ?string $cache_template = null, ?string $output_cache_file = null)
    {
        if ($cache_template === null) {
            $cache_template = static::$cache_template;
            if (!file_exists($cache_template)) {
                throw new ResourceNotFoundException("External link cache template not available at \"$cache_template\".");
            }
        }
        if ($output_cache_file === null) {
            $output_cache_file = static::$output_cache_file;
        }
        $cache_content = ContentUtils::loadTemplateContent($cache_template, $context);
        $f = fopen($output_cache_file, "w");
        fputs($f, $cache_content);
        fclose($f);
    }
}
