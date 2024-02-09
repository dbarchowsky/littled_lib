<?php

namespace Littled\Utility;

/**
 * General utility functions.
 */
class LittledUtility
{
    /**
     * @deprecated Use LittledUtility::joinPaths() instead.
     * Joins parts of a filesystem path into a single path.
     * @param string[] $parts
     * @return string
     */
    public static function joinPathParts(array $parts): string
    {
        $last = (DIRECTORY_SEPARATOR === (substr(end($parts), -1)) ? (DIRECTORY_SEPARATOR) : (''));
        reset($parts);
        $parts = array_map(function ($i) {
            return trim($i, DIRECTORY_SEPARATOR);
        }, array_filter($parts));
        if (0 < count($parts)) {
            return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . $last;
        }
        return '';
    }

    /**
     * Joins variable length list of strings into a single filesystem path strong. Unlink LittledUtility::joinPathParts(),
     * it will not add a leading slash to the path if it isn't present in the first string passed to the method.
     * @return string
     */
    public static function joinPaths(): string
    {
        $paths = array();
        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', join('/', $paths));
    }

    public static function overlap(string $a, string $b)
    {
        if (!strlen($b)) {
            return '';
        }

        if (strpos($a, $b) !== false) {
            return $b;
        }

        $left = LittledUtility::overlap($a, substr($b, 1));
        $right = LittledUtility::overlap($a, substr($b, 0, -1));

        return strlen($left) > strlen($right) ? $left : $right;
    }

    /**
     * Replaces the first occurrence of $needle in $haystack with $replace. Returns the original string if no matches
     * are found.
     * @param string $needle
     * @param string $replace
     * @param string $haystack
     * @return array|string|string[]
     */
    public static function replaceFirstOccurrence(string $needle, string $replace, string $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            return substr_replace($haystack, $replace, $pos, strlen($needle));
        }
        return $haystack;
    }

    /**
     * Strips levels off a filesystem path.
     * @param string $path Path to edit.
     * @param int $levels Number of directories to remove from the end of the path.
     * @return string Modified path.
     */
    public static function stripPathLevels(string $path, int $levels): string
    {
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path));
        if (count($parts) < $levels) {
            return '';
        }
        $parts = array_splice($parts, 0, (-1 * $levels));
        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
    }
}