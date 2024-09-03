<?php
namespace Littled\Utility;

use Littled\Exception\ConfigurationUndefinedException;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;


class Mailer
{
    /** @var string Sender's name */
    public string       $from;
    /** @var string Sender's email address */
    public string       $from_address;
    protected string    $password;
    /** @var string Recipient's name */
    public string       $to;
    /** @var string Recipient's email address */
    public string       $to_address;
    /** @var string Reply-to address */
    public string       $reply_to='';
    /** @var string Email subject */
    public string       $subject;
    /** @var string Body of email */
    public string       $body;
    /** @var bool Flag indicating if the email is to be sent as HTML. */
    public bool         $is_html;
    /** @var string Error messages related to sending out the mail. */
    public string $mail_errors='';

    public static string    $host = '';
    public static ?int      $port = 25;

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
     * Format plain text portion of email by stripping tags from HTML body.
     * @return string
     */
    public function getAltBody(): string
    {
        $txt = preg_replace('/<tr>/', "\r\n", $this->body);
        $txt = preg_replace('/<td>/', "\t", $txt);
        return strip_tags($txt)."\r\n";
    }

    /**
     * SMTP host getter
     * @return string
     */
    public static function getHost(): string
    {
        return static::$host ?? '';
    }

    /**
     * Password getter.
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * SMTP port getter
     * @return ?int
     */
    public static function getPort(): ?int
    {
        return static::$port ?? '';
    }

    public static function hasHost(): bool
    {
        return isset(static::$host) && static::$host && isset(static::$port) && static::$port > 0;
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

        $mail = new PHPMailer(true);
        if (static::hasHost() && $this->password) {
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = static::$host;
            $mail->Port = static::$port;
            $mail->Username = $this->from_address;
            $mail->Password = $this->password;
        }
        $mail->setFrom($this->from_address, $this->from);
        $mail->addAddress($this->to_address, $this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $this->body;
        $mail->AltBody = $this->getAltBody();

        $mail->send();
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
     * SMTP host setter
     * @param string $host
     * @return void
     */
    public static function setHost(string $host): void
    {
        static::$host = $host;
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
     * Password setter
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): Mailer
    {
        $this->password = $password;
        return $this;
    }

    /**
     * SMTP port setter
     * @param ?int $port
     * @return void
     */
    public static function setPort(?int $port): void
    {
        static::$port = $port;
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
