<?php

namespace Littled\Log;

use Littled\PageContent\PageContent;
use Littled\PageContent\Templates\ErrorTemplate;
use Throwable;

class Log
{
    /**
     * Display an error in response to catching an exception. If the "verbose" flag is TRUE, the error message and a
     * stack trace will be displayed. Otherwise, a custom error message will be displayed.
     * @param Throwable $e The caught exception object.
     * @param bool $verbose Flag determining whether to show the detailed exception output or a custom message.
     * @param string $msg A custom message to display in response to the error.
     * @param bool $redirect Flag determining if the errors are displayed inline or on a dedicated error page.
     * @return void
     */
    public static function displayExceptionMessage(
        Throwable $e,
        bool $verbose = false,
        string $msg = '',
        bool $redirect = false): void
    {
        $format = "<div class=\"%s\"><pre>%s</pre></div>\n";
        $msg = $msg ?: $e->getMessage();
        $msg .= $verbose ? "\n".$e->getTraceAsString() : '';
        if ($redirect) {
            PageContent::redirectToErrorPage($msg);
        }
        new ErrorTemplate()
            ->setFormat($format)
            ->renderError($msg);
    }

    /**
     * Returns the base name of a class within a fully qualified class name.
     * @param string $class Fully qualified class name.
     * @return string Base name of class.
     */
    public static function getClassBaseName(string $class): string
    {
        $pos = strrpos($class, '\\');
        if ($pos===false) {
            return $class;
        }
        return substr($class, $pos+1);
    }

    /**
     * Returns the current method name with its class without the path.
     * @return string
     */
    public static function getShortMethodName(): string
    {
        $debug = debug_backtrace();
        $class_path = explode('\\', $debug[1]['class']);
        return end($class_path). '::' .$debug[1]['function'];
    }
}