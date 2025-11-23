<?php
namespace Littled\Request;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Utility\LittledUtility;
use Littled\Validation\ContentConversion;
use Littled\Validation\Validation;
use Exception;

trait RenderedInputTrait
{
    /** Name of CSS class to be used when displaying the form input. */
    public string               $container_css_class='form-cell';
    /**
     * Flag to control the insertion of a "placeholder" attribute
     * when rendering the input. If TRUE, a placeholder attribute will be added
     * (to text fields), using the object's "label" property value as its value.
     */
    public bool                 $display_placeholder=false;
    /** Error indicator CSS class. */
    protected static string     $error_class = 'form-error';
    /** Input template filename. */
    protected static string     $hidden_template_filename = 'hidden-input.php';
    /** CSS class identifier. */
    public string               $input_css_class = '';
    protected static string     $input_error_css_class = 'input-error';
    /** Form input element filename. */
    protected static string     $input_template_filename;
    /** Required field indicator string. */
    protected static string     $required_field_indicator = ' (*)';
    /** Path to form input templates. */
    protected static string     $template_base_path;
    protected static string     $template_filename = 'hidden-input.php';

    /**
     * Returns string containing markup containing all attributes and their values stored in the object.
     * @return string
     */
    public function formatAttributesMarkup(): string
    {
        $markup = implode(' ', array_map(
            function($key, $value) {return "$key=\"$value\""; },
            array_keys($this->getAttributes()),
            $this->getAttributes()));
        if($markup) {
            $markup = ' '.$markup;
        }
        return $markup;
    }

    /**
     * Formats the CSS class attribute string to be injected into markup of the input's container element.
     * @param string $css_class (Optional) An additional CSS class to apply to the element in addition to the CSS class stored in the object.
     * @param callable|null $css_callback (Optional) Routine to use to fetch the class from the input object that will be applied to the element. The markup element is either the input element itself or its container. Defaults to applying the input elements CSS class.
     * @return string
     */
    public function formatClassAttributeMarkup(string $css_class='', ?callable $css_callback=null): string
    {
        if ($css_callback===null) {
            $css_callback = [$this, 'getInputCssClass'];
        }
        $base_class = call_user_func($css_callback);
        $error_class = '';
        if ($this->hasValidationErrors()) {
            $error_class = (($css_callback[1]==='getInputCssClass')?(static::getInputErrorClass()):(static::getErrorClass()));
        }
        $classes = trim(implode(' ', array_filter([$base_class, $css_class, $error_class])));
        return (($classes)?(" class=\"$classes\""):(''));
    }

    /**
     * Formats a string that can be inserted into markup to use the $index property value.
     * @return string
     */
    public function formatIndexMarkup(): string
    {
        return ContentConversion::formatIndexMarkup($this->getIndex());
    }

    /**
     * Default routine for rendering the label of the input.
     * @param string $label Text to display as the label for the form input. A null value will cause the internal label value to be used. An empty string will cause the label to not be rendered at all.
     * @return string Label markup to insert into form content.
     * @throws ResourceNotFoundException Template not found.
     */
    public function formatLabelMarkup( string $label ): string
    {
        if (strlen($label) > 0 && $this->display_placeholder===false) {
            return (ContentUtils::loadTemplateContent(static::$template_base_path. 'form-input-label.php', [
                'label' => $label,
                'input' => &$this
            ]));
        }
        return ('');
    }

    /**
     * Returns markup to inject into the input container element to indicate on the front-end that the input form data is required.
     * @return string
     */
    public function formatRequiredIndicatorMarkup(): string
    {
        return (($this->isRequired())?(static::getRequiredIndicator()):(''));
    }

    /**
     * Formats the value of the object in a way where it can be inserted into markup.
     * @return string
     */
    public function formatValueMarkup(): string
    {
        return ('' .$this->getInputValue());
    }

    /**
     * Implemented in \Request\RequestInput class.
     * @return array
     */
    abstract public function getAttributes(): array;

    /**
     * Returns the value of a configuration property, first checking the object's own property, then the static property, then the static property of the parent class.
     * @param $property
     * @return string
     */
    protected static function getConfigurationValue($property): string
    {
        if (isset(static::${$property}) && !empty(static::${$property})) {
            return static::${$property};
        }
        return self::${$property} ?? '';
    }

    /**
     * Container CSS class getter.
     * @return string
     */
    public function getContainerCssClass(): string
    {
        return $this->container_css_class;
    }

    /**
     * Error CSS class getter.
     * @return string Current error css class value.
     */
    public static function getErrorClass(): string
    {
        return static::getCOnfigurationValue('error_class');
    }

    /**
     * Hidden template filename getter.
     * @return string
     */
    public static function getHiddenTemplateFilename(): string
    {
        return static::getConfigurationValue('hidden_template_filename');
    }

    /**
     * Get a full path to the hidden form input element template file.
     * @return string Full path to form input element template file.
     */
    public static function getHiddenTemplatePath(): string
    {
        return (LittledUtility::joinPaths(static::getTemplateBasePath(), static::getHiddenTemplateFilename()));
    }

    /**
     * Implemented in \Request\RequestInput class.
     * @return int|string|null
     */
    abstract public function getIndex(): int|string|null;

    /**
     * Error CSS class getter for the container element.
     * @return string
     */
    public function getInputCssClass(): string
    {
        return $this->input_css_class;
    }

    /**
     * Error CSS class getter for the input element.
     * @return string Current error css class value.
     */
    public static function getInputErrorClass(): string
    {
        return (static::$input_error_css_class ?? self::$input_error_css_class);
    }

    /**
     * Returns the filename of the template used to render just the input element.
     * @return string Form input template filename.
     */
    public static function getInputTemplateFilename(): string
    {
        return static::getConfigurationValue('input_template_filename');
    }

    /**
     * Returns full path to input element template file.
     * @return string Path to input element template.
     */
    public static function getInputTemplatePath(): string
    {
        return(LittledUtility::joinPaths(static::getTemplateBasePath(), static::getInputTemplateFilename()));
    }

    /**
     * Implemented in \Request\RequestInput class.
     * @return mixed
     */
    abstract public function getInputValue(): mixed;

    /**
     * Implemented in \Request\RequestInput class.
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Implemented in \Request\RequestInput class.
     * @return string
     */
    abstract public function getLabel(): string;

    /**
     * Returns string to insert into front-end templates that will indicate that a field is required to submit form data.
     * @return string Content to insert into template.
     */
    public static function getRequiredIndicator(): string
    {
        return static::getConfigurationValue('required_field_indicator');
    }

    /**
     * Template path getter.
     * @return string Current internal template path value.
     */
    public static function getTemplateBasePath(): string
    {
        $path = static::getConfigurationValue('template_base_path');
        if (empty($path)) {
            // If the class isn't a descendant of \Request\RenderedInput, then attempt to get the template path value
            // from the RenderedInput class.
            $path = RenderedInput::getTemplateBasePath();
            static::setTemplateBasePath($path);
        }
        return $path;
    }

    /**
     * Template filename getter.
     * @return string Current internal template filename.
     */
    public static function getTemplateFilename(): string
    {
        return static::getConfigurationValue('template_filename');
    }

    /**
     * Get full path to form input element template file.
     * @return string Full path to form input element template file.
     */
    public static function getTemplatePath(): string
    {
        return (LittledUtility::joinPaths(static::getTemplateBasePath(), static::getTemplateFilename()));
    }

    /**
     * Implemented in \Request\RequestInput class.
     * @return bool
     */
    abstract public function hasValidationErrors(): bool;

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isInputTemplateBasePathDefined(): bool
    {
        return (Validation::isStringWithContent($this::getTemplateBasePath()));
    }

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isInputTemplateDefined(): bool
    {
        return (Validation::isStringWithContent($this::getInputTemplateFilename()));
    }

    /**
     * Implemented in \Request\RequestInput class.
     * @return bool
     */
    abstract public function isRequired(): bool;

    /**
     * Tests if the inherited class has defined a template to use to render the input element group.
     * @return bool TRUE if
     */
    public function isTemplateDefined(): bool
    {
        return (Validation::isStringWithContent($this::getTemplateFilename()));
    }

    /**
     * Returns string containing HTML to render the input elements in a form.
     * @param string $label (Optional) Text to display as the label for the form input.
     * A null value will cause the internal label value to be used. An empty
     * string will cause the label to not be rendered at all.
     * @param string $css_class (Optional) CSS class name(s) to apply to the input container.
     */
    public function render(string $label = '', string $css_class = '', array $context = []): void
    {
        $context = array_merge($context, [
            'input' => &$this,
            'label' => $label ?: $this->getLabel(),
            'css_class' => $css_class
        ]);
        ContentUtils::renderTemplateWithErrors(static::getTemplatePath(), $context);
    }

    /**
     * @param ?int $value_override Value to insert into the element instead of the object's stored value.
     * @param array $context Optional array of variables to insert into the element template.
     * @return void
     */
    public function renderHidden(?int $value_override = null, array $context = []): void
    {
        $context = array_merge($context, [
            'input' => &$this
        ]);
        if ($value_override !== null) {
            $context['value_override'] = $value_override;
        }
        ContentUtils::renderTemplateWithErrors(static::getHiddenTemplatePath(), $context);
    }

    /**
     * Renders the corresponding form field with a label to collect the input data.
     * @param ?string $label
     */
    public function renderInput(?string $label = null): void
    {
        ContentUtils::renderTemplateWithErrors(static::getInputTemplatePath(), [
            'input' => &$this,
            'label' => $label ?? $this->getLabel()
        ]);
    }
    /**
     * Wrapper for render() method that prints an error message if an exception is thrown rendering the form input element.
     * @param ?string $label Optional label that will override the object's internal property value.
     * @param ?string $css_class Optional CSS class name that will override the object's internal property value.
     */
    public function renderWithErrors(?string $label=null, ?string $css_class=null): void
    {
        try {
            $this->render($label, $css_class);
        }
        catch(Exception $ex) {
            ContentUtils::printError($ex->getMessage());
        }
    }

    /**
     * Prints out markup to save the input value in a hidden form input element.
     * @param string|null $template Path to template to use to override the current template path stored in the object.
     * @param string|null $key Key to use to override the default key value for the variable.
     */
    public function saveInForm( string $template=null, string|null $key=null ): void
    {
        $key = $key ?? $this->getKey();
        $template = $template ?? static::getHiddenTemplatePath();
        ContentUtils::renderTemplateWithErrors($template, [
            'key' => $key,
            'input' => $this
        ]);
    }

    /**
     * Container CSS class setter.
     * @param string $class
     * @return $this
     */
    public function setContainerCSSClass(string $class): static
    {
        $this->container_css_class = $class;
        return $this;
    }

    /**
     * Error CSS class setter.
     * @param string $css_class CSS class name.
     */
    public function setErrorClass( string $css_class ): void
    {
        static::$error_class = $css_class;
    }

    /**
     * Hidden template filename setter.
     * @param string $filename
     * @return void
     */
    public static function setHiddenTemplateFilename(string $filename): void
    {
        static::$hidden_template_filename = $filename;
    }

    /**
     * Input CSS class setter.
     * @param string $class
     * @return $this
     */
    public function setInputCSSClass(string $class): static
    {
        $this->input_css_class = $class;
        return $this;
    }

    /**
     * Form input element template filename setter.
     * @param string $filename Template filename.
     * @return void
     */
    public static function setInputTemplateFilename( string $filename ): void
    {
        static::$input_template_filename = $filename;
    }

    /**
     * Required field indicator string setter.
     * @param string $str Required field indicator string.
     */
    public static function setRequiredIndicator( string $str ): void
    {
        static::$required_field_indicator = $str;
    }

    /**
     * Sets the internal template path value.
     * @param string $path Path to the template directory.
     */
    public static function setTemplateBasePath( string $path ): void
    {
        static::$template_base_path = $path;
    }

    /**
     * Template filename setter.
     * @param string $filename template filename
     */
    public static function setTemplateFilename( string $filename ): void
    {
        static::$template_filename = $filename;
    }
}