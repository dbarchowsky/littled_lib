<?php
namespace Littled\Utility;

use Littled\Exception\ConfigurationUndefinedException;
use Exception;
use Throwable;


class Mailer
{
    /** @var string Sender's name */
    public string $from;
    /** @var string Sender's email address */
    public string $from_address;
    /** @var string Recipient's name */
    public string $to;
    /** @var string Recipient's email address */
    public string $to_address;
    /** @var string Reply-to address */
    public string $reply_to='';
    /** @var string Email subject */
    public string $subject;
    /** @var string Body of email */
    public string $body;
    /** @var bool Flag indicating if the email is to be sent as HTML. */
    public bool $is_html;
    /** @var string Error messages related to sending out the mail. */
    public string $mail_errors='';

    /**
     * class constructor
     * @param string $from (Optional) initial sender's name value. Defaults to "".
     * @param string $from_address (Optional) initial sender's email value. Defaults to "".
     * @param string $to (Optional) initial recipient's name value. Defaults to "".
     * @param string $to_address (Optional) recipient's email value. Defaults to "".
     * @param string $subject (Optional) initial subject value. Defaults to "".
     * @param string $body (Optional) initial email body value. Defaults to "".
     * @param bool $is_html (Optional) initial HTML flag value. Defaults to FALSE.
     */
    function __construct (
        string $from= '',
        string $from_address= '',
        string $to= '',
        string $to_address= '',
        string $subject= '',
        string $body= '',
        bool $is_html=false )
    {
        $this->from = $from;
        $this->from_address = $from_address;
        $this->to = $to;
        $this->to_address = $to_address;
        $this->subject = $subject;
        $this->body = $body;
        $this->is_html = $is_html;
    }

    /**
     * Clear any cached error messages captured from the mailing process.
     */
    public function clearErrors(): void
    {
        $this->mail_errors = '';
    }

    /**
     * Sends email. Expects properties of the object to be set before calling this routine.
     * @return void
     * @throws Exception
     */
    public function send(): void
    {
        if (!$this->from_address || !$this->to_address || !$this->subject || !$this->body) {
            throw new ConfigurationUndefinedException('Email properties not set.');
        }

        $headers = '';
        if ($this->to) {
            $headers .= "To: $this->to <$this->to_address>\r\n";
        }
        else {
            $headers .= "To: $this->to_address\r\n";
        }
        if ($this->from) {
            $headers .= "From: $this->from <$this->from_address>\r\n";
        }
        else {
            $headers .= "From: $this->from_address\r\n";
        }
        if ($this->reply_to)
        {
            $headers .= "Reply-To: $this->reply_to\r\n";
        }
        else {
            $headers .= "Reply-To: $this->from <$this->from_address>\r\n";
        }
        if ($this->is_html) {
            /* add headers for html email */
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $sMsg = &$this->body;
        }
        else {
            /* strip any html tags out for plain text emails */
            $sText = preg_replace('/<tr>/', "\r\n", $this->body);
            $sText = preg_replace('/<td>/', "\t", $sText);
            $sMsg = strip_tags($sText)."\r\n";
        }
        $to = $this->to_address;

        /* send the email */
        $this->clearErrors();
        try {
            mail($to, $this->subject, $sMsg, $headers);
        }
        catch (Throwable $e) {
            $this->mail_errors .= $e->getMessage();
        }

        /* send along any errors captured during the mailing process */
        if ($this->mail_errors) {
            throw new Exception($this->mail_errors);
        }
    }

    /**
     * Email body setter.
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): Mailer
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Is HTML flag setter.
     * @param bool $is_html
     * @return $this
     */
    public function setIsHTML(bool $is_html): Mailer
    {
        $this->is_html = $is_html;
        return $this;
    }

    /**
     * Recipient email address setter.
     * @param string $email
     * @return $this
     */
    public function setRecipientEmail(string $email): Mailer
    {
        $this->to_address = $email;
        return $this;
    }

    /**
     * Recipient name setter.
     * @param string $name
     * @return $this
     */
    public function setRecipientName(string $name): Mailer
    {
        $this->to = $name;
        return $this;
    }

    /**
     * Sender email address setter.
     * @param string $email
     * @return $this
     */
    public function setSenderEmail(string $email): Mailer
    {
        $this->from_address = $email;
        return $this;
    }

    /**
     * Sender name setter.
     * @param string $name
     * @return $this
     */
    public function setSenderName(string $name): Mailer
    {
        $this->from = $name;
        return $this;
    }

    /**
     * Subject line setter.
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): Mailer
    {
        $this->subject = $subject;
        return $this;
    }
}
