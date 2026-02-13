<?php

namespace Littled\API;

class JSONResponseBase
{
    public string $key;

    /**
     * @param string $key
     */
    function __construct(string $key = '')
    {
        $this->key = $key;
    }

    /**
     * Converts keys and values of the object into an object that will be used to generate a JSON object
     * to be transmitted to another page as an AJAX response.
     * @return array
     */
    public function formatJSON(): array
    {
        $arr = [];
        foreach ($this as $tag) {
            if ($tag instanceof JSONField) {
                $tag->formatJSON($arr);
            } elseif ($tag instanceof JSONResponseBase) {
                if ($tag->key) {
                    $arr[$tag->key] = $tag->formatJSON();
                } else {
                    $arr = array_merge($arr, $tag->formatJSON());
                }
            }
        }
        return ($arr);
    }

    /**
     * Sends json data as response to client.
     * @param array $arr JSON data to send as a response to the client
     */
    public static function sendJsonResponse(array $arr): void
    {
        header('Content-type: application/json; charset=utf-8');
        print json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }

    /**
     * Formats JSON string using the instance's current property values and sends it as a response.
     */
    public function sendResponse(): void
    {
        static::sendJsonResponse($this->formatJSON());
    }
}