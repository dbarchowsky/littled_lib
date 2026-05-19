<?php

namespace Littled\API;


class JSONField
{
    const string        FORMAT_CURRENCY = 'CURRENCY';

    public string       $name;
    public mixed        $value;
    public string       $format;
    public bool         $send_when_empty = true;

    /**
     * JSONField constructor.
     * @param string $name JSON field key.
     * @param mixed $value JSON field value.
     * @param string $format Enum indicating the format to use when converting an object to JSON data.
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
        return htmlspecialchars(mb_convert_encoding($src, 'UTF-8', 'ISO-8859-1'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Adds the current key/value pair to the supplied array.
     * @param array $data Array containing a full set of JSON key/value pairs to be passed back to the client.
     */
    public function formatJSON(array &$data): void
    {
        $func = function ($i) {
            if ($i instanceof JSONResponseBase) {
                return ((object)$i->formatJSON());
            } else {
                return ($i);
            }
        };

        $val = $this->value;
        if (($val ?? '') === '' && !$this->sendWhenEmpty()) {
            return;
        }

        if (is_array($val)) {
            $val = array_map($func, $val);
        }
        if ($this->format == JSONField::FORMAT_CURRENCY && is_numeric($val)) {
            $val = number_format($val, 2);
        }
        $data[$this->name] = $val;
    }

    /**
     * "Send when empty" property getter.
     * @return bool
     */
    public function sendWhenEmpty(): bool
    {
        return $this->send_when_empty;
    }

    /**
     * Format property setter.
     * @param string $format
     * @return $this
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Name property setter.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * "Send when empty" property setter.
     * @param bool $send_when_empty
     * @return $this
     */
    public function setSendWhenEmpty(bool $send_when_empty): static
    {
        $this->send_when_empty = $send_when_empty;
        return $this;
    }

    /**
     * Value property setter.
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }
}