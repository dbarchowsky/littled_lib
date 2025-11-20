<?php

namespace Littled\Request;


class StringTextarea extends StringInput
{
    protected static string $input_template_filename = 'textarea-input.php';
    protected static string $template_filename = 'string-text-field.php';
}