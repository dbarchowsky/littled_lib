<?php
namespace Littled\Ajax;

use stdClass;

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
     * @return stdClass
     */
    public function formatJson(): stdClass
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
	 * Sends json data as response to client.
	 * @param stdClass $arr json data to send as response to client
	 */
	public static function sendJsonResponse(stdClass $arr)
	{
		header('Content-type: application/json; charset=utf-8');
		print json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);
	}

    /**
     * Formats JSON string using instance's current property values and sends it as a response.
     */
    public function sendResponse()
    {
		self::sendJsonResponse($this->formatJson());
    }
}