<?php

namespace Littled\Utility;


use Littled\Exception\InvalidValueException;
use Littled\Validation\Validation;

class Email
{
    public string $name;
    public string $email;

    /**
     * Tests if an email address value is present.
     * @return bool
     */
    public function hasData(): bool
    {
        return (isset($this->email) && $this->email !== '');
    }

    /**
     * Email address setter
     * @param string $email
     * @return $this
     * @throws InvalidValueException
     */
    public function setEmail(string $email): Email
    {
        if (!Validation::validateEmailAddress($email)) {
            throw new InvalidValueException('"' .  $email . '" is not a valid email address.');
        }
        $this->email = $email;
        return $this;
    }

    /**
     * Email name setter
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Email
    {
        $this->name = $name;
        return $this;
    }
}