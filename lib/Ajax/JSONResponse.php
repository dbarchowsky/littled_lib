<?php
namespace Littled\Ajax;


use Littled\PageContent\PageContent;

/**
 * Class JSONResponse
 * @package Littled\PageContent\Ajax
 */
class JSONResponse
{
	/** @var JSONField Record id. */
	public $id;
	/** @var JSONField Name of the element in the DOM to update. */
	public $containerID;
	/** @var JSONField Page content to be inserted into the DOM. */
	public $content;
	/** @var JSONField Error message. */
	public $error;
	/** @var JSONField Element label value. */
	public $label;
	/** @var JSONField Operation results message. */
	public $status;

	/**
	 * Class constructor.
	 */
	function __construct ( )
	{
		$this->id = new JSONField('id');
		$this->content = new JSONField('content');
		$this->label = new JSONField('label');
		$this->containerID = new JSONField('container_id');
		$this->status = new JSONField('status');
		$this->error = new JSONField('error');
	}

	/**
	 * Inserts data into a template file and stores the resulting content in the object's $content property.
	 * @param string $template_path Path to content template file.
	 * @param array|null[optional] $context Array containing data to insert into the template.
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function loadContentFromTemplate( $template_path, $context=null )
	{
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		$this->content->value = PageContent::loadTemplateContent($template_path, $context);
	}

	/**
	 * Handler for PHP exceptions. Will attempt to interpret the error and send it as a response to the calling script.
	 * @global array $tags
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 * @return mixed
	 */
	public static function PHPError($errno, $errstr, $errfile, $errline)
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
				$errno) != $errno) {
			return;
			/* print "PHP ERROR {$errno}: {$errstr} {$errfile} at line {$errline}. "; exit; */
		}

		error_reporting(0);
		while(ob_get_level()) { ob_end_clean(); }

		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
				$error_str = "PHP FATAL ERROR: {$errstr} {$errfile} at line {$errline}. ";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$error_str = "PHP WARNING: {$errstr} {$errfile} at line {$errline}. ";
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$error_str = "PHP NOTICE: {$errstr} {$errfile} at line {$errline}. ";
				break;
			case E_PARSE:
				$error_str = "PHP PARSE ERROR: {$errstr} {$errfile} at line {$errline}. ";
				break;
			default:
				$error_str = "PHP UNKNOWN ERROR TYPE ({$errno}): ";
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
	function returnError($error_msg)
	{
		$this->error->value = $error_msg;
		$this->sendResponse();
		if (function_exists("cleanup")) {
			cleanup();
		}
		exit;
	}

	/**
	 * Formats JSON string using instance's current property values and sends
	 * it as a response.
	 */
	function sendResponse()
	{
		$arr = array();
		foreach($this as $key => $tag) {
			if (is_object($tag) && $tag instanceof JSONField) {
				$tag->formatJSON($arr);
			}
		}
		header('Content-type: application/json; charset=utf-8');
		print json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);
	}
}