<?php

namespace Littled\API;

use JetBrains\PhpStorm\NoReturn;

/**
 * Standardized container for JSON responses to api requests.
 */
class JSONResponse extends JSONResponseBase
{
    /** @var JSONField Operation results message. */
    public JSONField $status;
    /** @var JSONField Error message. */
    public JSONField $error;

    /**
     * Class constructor.
     * @param string $key
     */
    public function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->status = new JSONField('status');
        $this->error = new JSONField('error');
    }

    /**
     * Hook for inherited classes. Add any necessary cleanup after sending response to client.
     * @return void
     */
    public function cleanup()
    {
        // Hook for inherited classes.
    }

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
                    E_USER_ERROR |
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
        while (ob_get_level()) {
            ob_end_clean();
        }

        $error_str = match ($err_num) {
            E_ERROR, E_USER_ERROR => "PHP FATAL ERROR: $err_str $err_file at line $err_line. ",
            E_WARNING, E_USER_WARNING => "PHP WARNING: $err_str $err_file at line $err_line. ",
            E_NOTICE, E_USER_NOTICE => "PHP NOTICE: $err_str $err_file at line $err_line. ",
            E_PARSE => "PHP PARSE ERROR: $err_str $err_file at line $err_line. ",
            default => "PHP UNKNOWN ERROR TYPE ($err_num): ",
        };

        if ($error = error_get_last()) {
            if (isset($error["message"])) {
                $error_str .= " " . $error["message"];
            }
            if (isset($error["file"])) {
                $error_str .= " at " . $error["file"];
            }
            if (isset($error["line"])) {
                $error_str .= " line " . $error["line"];
            }
        }

        $response_data = array("error" => $error_str);

        if (isset($tags)) {
            if (property_exists($tags, "id")) {
                $response_data["id"] = $tags->id->value;
            }
            if (property_exists($tags, "containerID")) {
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
    #[NoReturn] public function returnError(string $error_msg): void
    {
        $this->error->value = $error_msg;
        $this->sendResponse();
        $this->cleanup();
        exit;
    }

    /**
     * Sets the error message to be returned to the client.
     * @param string $err
     * @return $this
     */
    public function setErrorMessage(string $err): JSONResponse
    {
        $this->error->value = $err;
        return $this;
    }
}