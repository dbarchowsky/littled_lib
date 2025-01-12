<?php

namespace Littled\Request;


class BooleanCheckbox extends BooleanInput
{
    /** Form element template filename */
    public static string $template_filename = 'boolean-checkbox-field.php';
    /** Form input element template filename */
    public static string $input_template_filename = 'boolean-checkbox-input.php';
}