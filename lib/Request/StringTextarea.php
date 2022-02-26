<?php
namespace Littled\Request;


class StringTextarea extends StringInput
{
    /** @var string */
    protected static string $input_template_filename = 'textarea-input.php';
    /** @var string */
    protected static string $template_filename = 'string-text-field.php';
}