<?php
namespace Littled\Utility;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\OperationFailedException;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;


class Mailer
{
    public Email            $sender;
    public Email            $recipient;
    public Email            $reply_to;
    protected string        $password;
    public string           $subject;
    public string           $body;
    public bool             $is_html        = true;
    public string           $mail_errors    = '';

    public static string    $host;
    public static ?int      $port;

    /**
     * class constructor
     */
    function __construct ()
    {
        $this->sender = new Email();
        $this->recipient = new Email();
        $this->reply_to = new Email();
    }

    /**
     * Clear any cached error messages captured from the mailing process.
     */
    public function clearErrors(): void
    {
        $this->mail_errors = '';
    }

    /**
     * Format the plain text portion of the email by stripping tags from the HTML body.
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
        static::$host = (static::$host ?? '') ?: ($_ENV['SMTP_HOST'] ?? '');
        return static::$host;
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
        static::$port = (static::$port ?? '') ?: ($_ENV['SMTP_PORT'] ?? '');
        return static::$port;
    }

    public static function hasHost(): bool
    {
        return !empty(static::getHost()) && !empty(static::getPort());
    }

    /**
     * Sends email. Expects properties of the object to be set before calling this routine.
     * @param int $debug_level Sets the PHPMailer debug level. Defaults to 0 for no debugging.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws OperationFailedException
     */
    public function send(int $debug_level=0): void
    {
        if (!$this->sender->hasData() || !$this->recipient->hasData() || !$this->subject || !$this->body) {
            throw new ConfigurationUndefinedException('Email properties not set.');
        }

        $mail = new PHPMailer(true);
        if (static::hasHost() && $this->password) {
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = static::getHost();
            $mail->Port = static::getPort();
            $mail->Username = $this->sender->email;
            $mail->Password = $this->password;
        }

        // secure connection settings
        if (in_array($mail->Port, [465, 587])) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }


        try {
            $mail->setFrom($this->sender->email, $this->sender->name);
            $mail->addAddress($this->recipient->email, $this->recipient->name);
            if ($this->reply_to->hasData()) {
                $mail->addReplyTo($this->reply_to->email, $this->reply_to->name);
            }
        }
        catch (Exception $ex) {
            throw new InvalidValueException(message: 'Could not set email address', previous: $ex);
        }
        $mail->Subject = $this->subject;
        if ($this->is_html) {
            $mail->isHTML();
            $mail->Body = $this->body;
            $mail->AltBody = $this->getAltBody();
        }
        else {
            $mail->IsHTML(false);
            $mail->Body = $this->getAltBody();
        }

        // This sends output to the console.
        $mail->SMTPDebug = $debug_level;

        try {
            $mail->send();
        }
        catch (Exception $ex) {
            throw new OperationFailedException(message: 'An error occurred sending the email.', previous: $ex);
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
     * Email recipient name and email setter.
     * @param string $email
     * @param string $name
     * @return $this
     * @throws InvalidValueException
     */
    public function setRecipient(string $email, string $name=''): Mailer
    {
        $this->recipient->setEmail($email)->setName($name);
        return $this;
    }

    /**
     * Recipient email address setter.
     * @param string $email
     * @return $this
     * @throws InvalidValueException
     */
    public function setRecipientEmail(string $email): Mailer
    {
        $this->recipient->setEmail($email);
        return $this;
    }

    /**
     * Recipient name setter.
     * @param string $name
     * @return $this
     */
    public function setRecipientName(string $name): Mailer
    {
        $this->recipient->setName($name);
        return $this;
    }

    /**
     * Email reply-to name and email setter.
     * @param string $email
     * @param string $name
     * @return $this
     * @throws InvalidValueException
     */
    public function setReplyTo(string $email, string $name=''): Mailer
    {
        $this->reply_to->setEmail($email)->setName($name)->setName($name);
        return $this;
    }

    /**
     * Reply-To setter
     * @param string $email
     * @return $this
     * @throws InvalidValueException
     */
    public function setReplyToEmail(string $email): Mailer
    {
        $this->reply_to->setEmail($email);
        return $this;
    }
    /**
     * Reply-To setter
     * @param string $name
     * @return $this
     */
    public function setReplyToName(string $name): Mailer
    {
        $this->reply_to->setName($name);
        return $this;
    }

    /**
     * Email sender name and email setter.
     * @param string $email
     * @param string $name
     * @return $this
     * @throws InvalidValueException
     */
    public function setSender(string $email, string $name=''): Mailer
    {
        $this->sender->setEmail($email)->setName($name);
        return $this;
    }

    /**
     * Sender email address setter.
     * @param string $email
     * @return $this
     * @throws InvalidValueException
     */
    public function setSenderEmail(string $email): Mailer
    {
        $this->sender->setEmail($email);
        return $this;
    }

    /**
     * Sender name setter.
     * @param string $name
     * @return $this
     */
    public function setSenderName(string $name): Mailer
    {
        $this->sender->setName($name);
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
