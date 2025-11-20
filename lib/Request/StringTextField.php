<?php

namespace Littled\Request;


class StringTextField extends StringInput
{
    protected static string $input_template_filename = 'string-text-input.php';
    protected static string $template_filename = 'string-text-field.php';
}