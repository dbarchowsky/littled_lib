<?php
namespace Littled\Request;


class StringTextarea extends StringInput
{
    /** @var string */
    protected static $input_template_filename = 'textarea-input.php';
    /** @var string */
    protected static $template_filename = 'string-text-field.php';

}