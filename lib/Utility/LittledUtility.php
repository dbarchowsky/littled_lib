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
}