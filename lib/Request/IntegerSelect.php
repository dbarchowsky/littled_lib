<?php
namespace Littled\Request;


class IntegerSelect extends IntegerInput
{
    public static string $input_template_filename = 'string-select-input.php';
    protected static string $template_filename = 'string-select-field.php';
    public bool $allow_multiple = false;
    public ?int $options_length = null;

    /**
     * Allow multiple setter. If set to true, multiple choices can be selected from the drop-down options.
     * @return void
     */
    public function allowMultiple()
    {
        $this->allow_multiple = true;
    }

    /**
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return ((0 < $this->options_length)?(" size=\"$this->options_length\""):(''));
    }

    /**
     * Options length getter.
     * @return int|null
     */
    public function getOptionsLength(): ?int
    {
        return $this->options_length;
    }

    public function render(string $label = '', string $css_class = '', array $context=[])
    {
        if (!array_key_exists('options', $context)) {
            $context['options'] = $context;
        }
        parent::render($label, $css_class, $context);
    }

    /**
     * Options length setter. If this value is set, the number of options displayed will be limited to length value.
     * @param int $len
     * @return void
     */
    public function setOptionsLength(int $len)
    {
        $this->options_length = $len;
    }
}