<?php
namespace LittledTests\DataProvider\Request;

use Littled\Request\RenderedInput;


class SelectTestData
{
    /** @var string */
    public const TEST_LABEL = 'test select';
    /** @var string */
    public const TEST_KEY = 'p_key';
    /** @var string Expected test output, typically a fragment of a processed template */
    public string $expected;
    /** @var string Value to use in place of the default input label. */
    public string $override_label;
    /** @var string CSS class to apply to the input element */
    public string $css_class;
    /** @var array Additional options to apply to the input element */
    public array $options;

    function __construct(
        string $expected,
        RenderedInput $o,
        array $options=[],
        string $override_label='',
        string $css_class='',
        bool $allow_multiple=false,
        ?int $options_size=null)
    {
        $this->expected = $expected;
        $this->options = $options;
        $this->override_label = $override_label;
        $this->css_class = $css_class;
    }
}