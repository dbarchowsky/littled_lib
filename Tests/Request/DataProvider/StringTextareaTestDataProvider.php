<?php

namespace Littled\Tests\Request\DataProvider;

use Littled\Request\StringTextarea;

class StringTextareaTestDataProvider
{
    const TEST_INPUT_LABEL = 'Textarea test';
    const TEST_INPUT_KEY = 'ttKey';
    public array $expected=[];
    public string $message='';
    public StringTextarea $field;

    public function __construct(array $expected, string $label, string $key, ?string $value, bool $is_required=false, string $container_class='', string $input_class='', string $error='', string $message='')
    {
        $this->expected = $expected;
        $this->message = $message;
        $this->field = new StringTextarea($label, $key, $is_required, '', 500);
        $this->field->value = $value;
        $this->field->setContainerCSSClass($container_class);
        $this->field->setInputCSSClass($input_class);
        if ($error) {
            $this->field->error = $error;
            $this->field->has_errors = true;
        }
    }

    public static function renderInputTestProvider(): array
    {
        return array(
            [new StringTextareaTestDataProvider(
                array('/^<textarea name=\"'.self::TEST_INPUT_KEY.'\" id=\"'.self::TEST_INPUT_KEY.'\"><\/textarea>$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, '', '', '',
                'Empty value'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<textarea.*>Hello, World!<\/textarea>$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                'Hello, World!', false, '', '', '',
                'input value set'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<textarea name=\"'.self::TEST_INPUT_KEY.'\" id=\"'.self::TEST_INPUT_KEY.'\"><\/textarea>$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, 'my-test-class', '', '',
                'input class not set; container class set'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<textarea class=\"my-test-class\" name=\"'.self::TEST_INPUT_KEY.'\" id=\"'.self::TEST_INPUT_KEY.'\"><\/textarea>$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, '', 'my-test-class', '',
                'input class set'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<textarea class=\"input-error\" name=\"'.self::TEST_INPUT_KEY.'\" id=\"'.self::TEST_INPUT_KEY.'\"><\/textarea>$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, '', '', 'Test error message.',
                'input has error'
            )],
        );
    }

    public static function renderTestProvider(): array
    {
        return array(
            [new StringTextareaTestDataProvider(
                array(
                    '/^<div>\s*<label for=\"'.self::TEST_INPUT_KEY.'\".*>'.self::TEST_INPUT_LABEL.'<\/label>/',
                    '/\s*<div><textarea.*><\/textarea><\/div>\s*<\/div>\s*$/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, '', '', '',
                'Empty value'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<div class=\"form-cell\">\s*<label.*>'.self::TEST_INPUT_LABEL.'<\/label>/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, 'form-cell', '', '',
                'container class set'
            )],
            [new StringTextareaTestDataProvider(
                array('/^<div>\s*<label.*>'.self::TEST_INPUT_LABEL.' \(\*\)<\/label>/'),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', true, '', '', '',
                'required input indicator'
            )],
            [new StringTextareaTestDataProvider(
                array(
                    '/^<div>\s*<label.*>'.self::TEST_INPUT_LABEL.'<\/label>/',
                    '/<div><textarea class=\"my-input-class\" .*>/'
                ),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, '', 'my-input-class', '',
                'input class set'
            )],
            [new StringTextareaTestDataProvider(
                array(
                    '/^<div class=\"my-container-class form-error\">\s*/',
                    '/<label.*>.*<\/label>\s*<div><textarea/',
                    '/<div><textarea class=\"my-input-class input-error\" .*>.*<\/textarea><\/div>\s*<div class=\"input-error-msg\">/',
                    '/<div class=\"input-error-msg\">my error<\/div>/'
                ),
                self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY,
                '', false, 'my-container-class', 'my-input-class', 'my error',
                'error class with input & container classes set'
            )],
        );
    }
}