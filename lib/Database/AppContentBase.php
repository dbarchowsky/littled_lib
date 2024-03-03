<?php


namespace Littled\Database;


use Littled\App\AppBase;

/**
 * Class AppContentBase
 * @package Littled\Database
 */
class AppContentBase extends MySQLConnection
{
    /**
     * Returns the current class base name and method name.
     * @return string Class and method description.
     */
    public static function getMethodDescriptor(): string
    {
        return (basename(str_replace('\\', '/', get_called_class())) . "::" . debug_backtrace()[1]['function'] . "()");
    }

    /**
     * Returns a plural version of a string.
     * @todo test for list of words that have non-standard plural versions, e.g. goose, deer
     * @param string $str
     * @return string
     */
    public static function makePlural(string $str): string
    {
        if ('' === $str) {
            return $str;
        }
        if ('s' === strtolower(substr($str, -1))) {
            return ($str);
        }
        if ('y' === strtolower(substr($str, -1))) {
            return (substr($str, 0, -1) . 'ies');
        }
        if ('x' === strtolower(substr($str, -1))) {
            return ($str . 'es');
        }
        return ($str . 's');
    }
}