<?php

namespace Littled\Filters;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Request\RenderedInput;
use Littled\Validation\RequestValidation;
use Littled\Validation\Validation;

/**
 * Class for collecting request data used to filter listings content.
 */
class ContentFilter
{
    protected static string     $preserve_value_template = 'filter-preserved-input.php';
    /** @var string Key of the cookie element holding the filter value. */
    public string               $cookieKey;
    /** @var string Variable name used to pass along filter values. */
    public string               $key;
    /** @var string Label to display on filter form inputs. */
    public string               $label;
    /** @var ?int Size limit of the filter value. */
    public ?int                 $size;
    /** @var mixed|string Filter value. */
    public mixed                $value;

    /**
     * ContentFilter constructor.
     * @param string $label Label to display on filter form inputs.
     * @param string $key Variable name used to pass along filter values.
     * @param ?mixed $value Filter value.
     * @param int|null $size Size limit of the filter value.
     * @param ?mixed $cookieKey Key of the cookie element holding the filter value.
     */
    function __construct(string $label='', string $key='', mixed $value = null, int|null $size = 0, mixed $cookieKey = '')
    {
        $this->label = $label;
        $this->key = $key;
        $this->value = $value;
        $this->size = $size;
        $this->cookieKey = $cookieKey;
    }

    /**
     * Clears the filter value from cookies.
     */
    protected function clearCookie(): void
    {
        if (isset($this->cookieKey)) {
            if (isset($_COOKIE[$this->cookieKey])) {
                $expires = time() + 3600 * 24 * 90;
                $ar = explode('|', $_COOKIE[$this->cookieKey]);
                if (array_key_exists($this->key, $ar)) unset($ar[$this->key]);
                setcookie($this->cookieKey, implode('|', $ar), $expires);
            } else {
                setcookie($this->cookieKey, '', time() - 1);
            }
        } else {
            setcookie($this->key, '', time() - 1);
        }
    }

    /**
     * Assign value property using value stored in a cookie.
     * @return void
     */
    public function collectValueFromCookie(): void
    {
        if ($this->cookieKey && isset($_COOKIE[$this->cookieKey])) {
            $ar = explode('|', $_COOKIE[$this->cookieKey]);
            if (array_key_exists($this->key, $ar)) {
                $this->value = $ar[$this->key];
            }
        } elseif (isset($_COOKIE[$this->key])) {
            $this->value = $_COOKIE[$this->key];
        }
    }

    /**
     * Collects filter value from GET or POST request variables by default. Or using $src data, if supplied.
     * @param ?array $src Request data that will override GET or POST data.
     */
    protected function collectRequestValue(?array $src = null): void
    {
        $value = Validation::collectRequestVar($this->key, RequestValidation::DEFAULT_REQUEST_FILTER, $src);
        $this->value = $value ?: $this->value;
    }

    /**
     * Collects the filter value from request variables, session variables, or cookie variables, in that order.
     * @param bool $read_cookies Flag indicating that the cookie collection should be included in the search for a filter value.
     * @param ?array $src Optional array containing request variables to use to override GET and POST data.
     * @return void
     */
    public function collectValue(bool $read_cookies = true, ?array $src = null): void
    {
        $this->collectRequestValue($src);
        if ($this->value) {
            return;
        }

        $this->collectValueFromSession();
        if ($this->value) {
            return;
        }

        if ($read_cookies) {
            $this->collectValueFromCookie();
        }
    }

    /**
     * Assigns value property using value stored in the session variable.
     * @return void
     */
    public function collectValueFromSession(): void
    {
        if (isset($_SESSION[$this->key])) {
            $this->value = trim('' . $_SESSION[$this->key]);
        }
    }

    /**
     * Returns name/value pair from within a query string representing the
     * current "param" and "value" property values of the object.
     * @return string Name/value pair formatted for insertion into a query string.
     */
    function formatQueryString(): string
    {
        if ($this->value === null) {
            return '';
        }
        if ($this->value === true) {
            return $this->key . '=1';
        }
        if ($this->value === false) {
            return $this->key . '=0';
        }
        if (strlen($this->value) < 1) {
            return '';
        }
        return $this->key . '=' . urlencode($this->value);
    }

    /**
     * Value preservation form getter.
     * @return string
     */
    public function getPreserveValueTemplate(): string
    {
        return static::$preserve_value_template;
    }

    /**
     * Value preservation form getter.
     * @return string
     */
    public function getPreserveValueTemplatePath(): string
    {
        return RenderedInput::getTemplateBasePath() . static::$preserve_value_template;
    }

    /**
     * Tests if the filter has been assigned a value.
     * @return bool
     */
    public function hasData(): bool
    {
        return ($this->value ?? '') !== '';
    }

    /**
     * Returns string safe from XSS attacks that can be embedded in HTML.
     * @param ?int $options Combination of tokens to pass along, e.g., FILTER_SANITIZE_FULL_SPECIAL_CHARS
     * Same values as the 3rd argument to PHP's filter_var() routine.
     * @return string XSS-safe string.
     */
    public function safeValue(?int $options = ENT_NOQUOTES): string
    {
        return htmlspecialchars(strip_tags('' . $this->value), $options);
    }

    /**
     * Saves the filter value as a cookie variable.
     */
    protected function saveCookie(): void
    {
        $expires = time() + 3600 * 24 * 90;
        if (isset($this->cookieKey)) {
            if (isset($_COOKIE[$this->cookieKey])) {
                $ar = explode('|', $_COOKIE[$this->cookieKey]);
                $ar[$this->key] = $this->value;
                setcookie($this->cookieKey, implode('|', $ar), $expires);
            } else {
                setcookie($this->cookieKey, $this->key . '|' . $this->value, $expires);
            }
        } else {
            setcookie($this->key, $this->value, $expires);
        }
    }

    /**
     * Output markup that will preserve the filter's value in an HTML form.
     * @throws ResourceNotFoundException
     */
    public function saveInForm(): void
    {
        ContentUtils::renderTemplate(static::getPreserveValueTemplatePath(), array(
            'key' => $this->key,
            'index' => '',
            'value' => $this->value
        ));
    }

    public function setCookieKey(string $cookieKey): static
    {
        $this->cookieKey = $cookieKey;
        return $this;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Value preservation form setter.
     * @param string $filename
     * @return void
     */
    public function setPreserveValueTemplate(string $filename): void
    {
        static::$preserve_value_template = $filename;
    }

    public function setSizeLimit(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }
}
