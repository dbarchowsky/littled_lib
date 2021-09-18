<?php

namespace Littled\Ajax;

class JSONResponseBase
{
    /** @var string */
    public $key;

    /**
     * @param string $key
     */
    function __construct(string $key='')
    {
        $this->key = $key;
    }

    /**
     * Converts keys and values of the object into an object that will be used to generate a JSON object
     * to be transmitted to another page as an AJAX response.
     * @return array
     */
    public function formatJson(): array
    {
        $arr = array();
        foreach($this as $tag) {
            if ($tag instanceof JSONField) {
                $tag->formatJSON($arr);
            }
            elseif ($tag instanceof JSONResponseBase) {
                if ($tag->key) {
                    $arr[$tag->key] = $tag->formatJson();
                }
                else {
                    $arr = array_merge($arr, $tag->formatJson());
                }
            }
        }
        return ($arr);
    }

    /**
     * Formats JSON string using instance's current property values and sends it as a response.
     */
    public function sendResponse()
    {
        $arr = $this->formatJson();
        header('Content-type: application/json; charset=utf-8');
        print json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);
    }
}