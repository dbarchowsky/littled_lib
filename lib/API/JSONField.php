<?php

namespace Littled\API;


/**
 * Class JSONField
 * @package Littled\PageContent\API
 */
class JSONField
{
    const FORMAT_CURRENCY = 'CURRENCY';

    public string $name;
    public mixed $value;
    public string $format;

    /**
     * JSONField constructor.
     * @param string $name JSON field key.
     * @param mixed $value JSON field value.
     * @param string $format Enum indicating format to use when converting object to JSON data.
     * Acceptable values are 'CURRENCY'. Leave blank for no formatting.
     */
    function __construct(string $name = '', mixed $value = '', string $format = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->format = $format;
    }

    /**
     * Escapes HTML characters to avoid JSON parsing errors.
     * @param string $src String to be escaped.
     * @return string Escaped version of the string.
     */
    public static function escapeHTML(string $src): string
    {
        return (str_replace("&", "&amp;", str_replace(">", "&gt;", str_replace("<", "&lt;", mb_convert_encoding($src, 'UTF-8', 'ISO-8859-1')))));
    }

    /**
     * Adds the current key/value pair to the supplied array.
     * @param array $data Array containing full set of JSON key/value pairs to be passed back to the client.
     */
    public function formatJSON(array &$data): void
    {
        $func = function ($i) {
            if ($i instanceof JSONResponseBase) {
                return ((object)$i->formatJson());
            } else {
                return ($i);
            }
        };

        $val = $this->value;
        if (is_array($val)) {
            $val = array_map($func, $val);
        }
        if ($this->format == JSONField::FORMAT_CURRENCY && is_numeric($val)) {
            $val = number_format($val, 2);
        }
        $data[$this->name] = $val;
    }
}