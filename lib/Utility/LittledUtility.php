<?php
namespace Littled\Utility;

class LittledUtility
{
	/**
	 * Joins parts of a filesystem path into a single path.
	 * @param string[] $parts
	 * @return string
	 */
	public static function joinPathParts(array $parts): string
	{
		$last = (DIRECTORY_SEPARATOR===(substr(end($parts), -1))?(DIRECTORY_SEPARATOR):(''));
		reset($parts);
		$parts = array_map(function($i) { return trim($i, DIRECTORY_SEPARATOR); }, array_filter($parts));
		if (0 < count($parts)) {
			return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts).$last;
		}
		return '';
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
        $parts = array_splice($parts, 0, (-1*$levels));
        return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR;
    }
}