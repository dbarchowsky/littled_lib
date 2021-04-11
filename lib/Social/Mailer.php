<?php
namespace Littled\Social;

use \Exception;

/**
 * Class Mailer
 * @package Littled\Social
 * Class containing some basic email utility functions.
 */
class Mailer
{
	/** @var string Sender's name */
	public $from;
	/** @var string Sender's email address */
	public $from_addr;
	/** @var string Recipient's name */
	public $to;
	/** @var string Recipient's email address */
	public $to_addr;
	/** @var string Reply-to address */
	public $reply_to;
	/** @var string Email subject */
	public $subject;
	/** @var string Body of email */
	public $body;
	/** @var boolean Flag indicating if the email is to be sent as HTML. */
	public $is_html;
	/** @var string Error messages related to sending out the mail. */
	public $mail_errors;

	/**
	 * class constructor 
	 * @param string $from (Optional) initial sender's name value. Defaults to "".
	 * @param string $from_addr (Optional) initial sender's email value. Defaults to "".
	 * @param string $to (Optional) initial recipient's name value. Defaults to "".
	 * @param string $to_addr (Optional) recipient's email value. Defaults to "".
	 * @param string $subject (Optional) initial subject value. Defaults to "".
	 * @param string $body (Optional) initial email body value. Defaults to "".
	 * @param boolean $is_html (Optional) initial HTML flag value. Defaults to FALSE.
	 */
	function __construct ( $from="", $from_addr="", $to="", $to_addr="", $subject="", $body="", $is_html=false) 
	{
		$this->from = $from;
		$this->from_addr = $from_addr;
		$this->to = $to;
		$this->to_addr = $to_addr;
		$this->reply_to = "";
		$this->subject = $subject;
		$this->body = $body;
		$this->is_html = $is_html;
		$this->mail_errors = "";
	}

	
	/**
	 * Sends email. Expects properties of the object to be set before calling this routine.
	 * @return void 
	 * @throws Exception
	 */
	public function send()
	{
		try
		{
			if (!$this->from_addr || !$this->to_addr || !$this->subject || !$this->body)
			{
				throw new Exception("Email properties not set.");
			}

			$sHead = "";
			if ($this->to) {
				$sHead .= "To: {$this->to} <{$this->to_addr}>\r\n";
			} 
			else {
				$sHead .= "To: {$this->to_addr}\r\n";
			}
			if ($this->from) {
				$sHead .= "From: {$this->from} <{$this->from_addr}>\r\n";
			} 
			else {
				$sHead .= "From: {$this->from_addr}\r\n";
			}
			if ($this->reply_to) 
			{
				$sHead .= "Reply-To: {$this->reply_to}\r\n";
			} 
			else {
				$sHead .= "Reply-To: {$this->from} <{$this->from_addr}>\r\n";
			}
			if ($this->is_html) 
			{
				/* add headers for html email */
				$sHead .= "MIME-Version: 1.0\r\n";
				$sHead .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				$sMsg = &$this->body;
			} 
			else 
			{
				/* strip any html tags out for plain text emails */
				$sText = preg_replace("/<tr>/", "\r\n", $this->body);
				$sText = preg_replace("/<td>/", "\t", $sText);
				$sMsg = strip_tags($sText)."\r\n";
			}

			$to = $this->to_addr;

			/* suppress PHP errors and warnings (if the SMTP server can't send the message) */
			set_error_handler(array($this, "mail_error"));
			
			/* send the email */
			$this->clear_errors();
			mail($to, $this->subject, $sMsg, $sHead);
			
			/* restore PHP error and warning functionality to its previous state */
			restore_error_handler();
			
			/* send along any errors captured during the mailing process */
			if ($this->mail_errors)
			{
				throw new Exception($this->mail_errors);
			}
		}
		catch(Exception $ex) {
			throw ($ex);
		}
	}
	
	/**
	 * Override of the default PHP error reporting. Normally if there is a problem
	 * with the server PHP will print out a warning to the browser revealing the 
	 * path to this mail class. This routine is a callback for PHP's built-in
	 * set_error_handler() routine. This routine captures the mail error for later 
	 * use in the class's $mail_errors property.
	 * @param integer $errno Expected callback argument. Not used.
	 * @param string $errstr Expected callback argument. Error message.
	 * @param string $errfile Expected callback argument. Not used.
	 * @param integer $errline Expected callback argument. Not used.
	 */
	public function mail_error($errno, string $errstr, $errfile, $errline)
	{
		$this->mail_errors = $errstr;
	}
	
	/**
	 * Clear any cached error messages captured from the mailing process.
	 */
	public function clear_errors() 
	{
		$this->mail_errors = "";
	}
}
