<?php

namespace Littled\Ajax;

class JSONResponseBase
{
    /** @var JSONField Operation results message. */
    public $status;
    /** @var JSONField Error message. */
    public $error;

    /**
     * Handler for PHP exceptions. Will attempt to interpret the error and send it as a response to the calling script.
     * @param int $err_num
     * @param string $err_str
     * @param string $err_file
     * @param int $err_line
     * @return void
     * @global array $tags
     */
    public static function PHPError(int $err_num, string $err_str, string $err_file, int $err_line): void
    {
        global $tags;

        if (((
                    E_ERROR |
                    E_USER_ERROR  |
                    E_WARNING |
                    E_USER_WARNING |
                    E_NOTICE |
                    E_USER_NOTICE |
                    /* E_STRICT | */
                    E_PARSE) &
                $err_num) != $err_num) {
            return;
        }

        error_reporting(0);
        while(ob_get_level()) { ob_end_clean(); }

        switch ($err_num) {
            case E_ERROR:
            case E_USER_ERROR:
                $error_str = "PHP FATAL ERROR: $err_str $err_file at line $err_line. ";
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $error_str = "PHP WARNING: $err_str $err_file at line $err_line. ";
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error_str = "PHP NOTICE: $err_str $err_file at line $err_line. ";
                break;
            case E_PARSE:
                $error_str = "PHP PARSE ERROR: $err_str $err_file at line $err_line. ";
                break;
            default:
                $error_str = "PHP UNKNOWN ERROR TYPE ($err_num): ";
                break;
        }

        if ($error = error_get_last()) {
            if (isset($error["message"])) { $error_str.= " ".$error["message"]; }
            if (isset($error["file"])) { $error_str.= " at ".$error["file"]; }
            if (isset($error["line"])) { $error_str.= " line ".$error["line"]; }
        }

        $response_data = array("error" => $error_str);

        if (isset($tags)) {
            if (property_exists($tags,"id")) {
                $response_data["id"] = $tags->id->value;
            }
            if (property_exists($tags,"containerID")) {
                $response_data["container_id"] = $tags->containerID->value;
            }
        }

        print json_encode($response_data);
        exit;
    }

    /**
     * Inserts the error string into the object's error property and sends
     * the object's current properties as JSON string response.
     * @param string $error_msg Error message.
     */
    function returnError(string $error_msg)
    {
        $this->error->value = $error_msg;
        $this->sendResponse();
        if (function_exists("cleanup")) {
            cleanup();
        }
        exit;
    }

    /**
     * Converts keys and values of the object into an object that will be use to generate a JSON object
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
                $arr = array_merge($arr, $this->formatJson());
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