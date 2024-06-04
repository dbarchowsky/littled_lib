<?php
namespace Littled\PageContent;

use DOMElement;
use DOMException;
use DOMDocument;
use DOMText;
use DOMXPath;
use JetBrains\PhpStorm\NoReturn;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Validation\Validation;

/**
 * Class containing collection of static page manipulation methods.
 */
class PageUtils
{
	/**
	 * Calculates the number of rows needed to accommodate a set number of items in a set number of columns.
	 * @param int $total total number of items
	 * @param int $cols total number of columns
	 * @return int number of rows needed to fit the items in the columns
	 */
	public static function calculateRowCount ( int $total, int $cols ): int
	{
		if ($cols==0) {
			return (0);
		}
		$rows = $total/$cols;
		if (($rows - ((int)($rows))) > 0.0) {
			$rows = ((int)($rows)) + 1;
		}
		return ((int)$rows);
	}


	/**
	 * Handles redirects to other pages. If page argument "ref" has a value,
	 * that will be used as the url for the redirect, overriding the $sURI argument passed to the script.
	 * @param string $target_uri URI to redirect to.
	 * @param ?string $msg Optional message to pass along to the next page.
	 */
	#[NoReturn] public static function doRedirect(string $target_uri='', ?string $msg=null): void
    {
		$_SESSION[LittledGlobals::INFO_MESSAGE_KEY] = $msg;

		$uri = Validation::collectStringRequestVar(LittledGlobals::REFERER_KEY, FILTER_SANITIZE_URL);
		if (!$uri) {
			$uri = $target_uri;
		}
		$iPos = strpos($uri, '/');
		if (is_numeric($iPos) && ($iPos==0)) {
			/* NB INPUT_SERVER is unreliable with filter_input() */
			$uri = 'http://' .$_SERVER['HTTP_HOST'].$uri;
		}

		if (function_exists('cleanup')) {
			cleanup();
		}
		header("Location: $uri\n\n");
		exit();
	}

	/**
	 * Formats a date stored in a string. Similar to PHP built-in routine strftime, but date passed in as a string instead of as a timestamp.
	 * @param string $date Date value.
	 * @param string $format See PHP built-in function strftime.
	 * @return string Formatted date.
	 */
	public static function formatDate( string $date, string $format ): string
	{
		$date = @strtotime($date);
		if (!$date) {
			return ($date);
		}
		return (date($format, $date));
	}

	/**
	 * Returns string with date value formatted as mm/dd/yyyy.
	 * @param string $date Date value.
	 * @return string Formatted date.
	 */
	public static function formatDateMMDDYY( string $date ): string
    {
		return(PageUtils::formatDate($date, '%m/%d/%Y'));
	}

	/**
	 * Returns string with date value formatted as Mon dd, YYYY.
	 * @param string $date Date value.
	 * @return string Formatted date.
	 */
	public static function formatDateMonDDYYYY( string $date ): string
    {
		return(PageUtils::formatDate($date, '%b %d, %Y'));
	}

	/**
	 * Given a string, formats that string so that it can be used as the name of a file on disk.
	 * @param string $src Source string to convert to filename.
	 * @return string Source string converted to filename-friendly format.
	 */
	public static function formatFilename( string $src ): string
	{
		$sBase = preg_replace('/[^a-z0-9 ]/', '', strtolower($src));
		$sBase = ucwords($sBase);
		$sBase = preg_replace('/\W/', '_', $sBase);
		return ($sBase);
	}

	/**
	 * Takes $key and $value and formats them into a string that can then be inserted into a query string to represent
	 * a variable and its value in the query string.
	 * @param string $key
	 * @param mixed $val
	 * @param string $delim
	 * @return string
	 */
	public static function formatQuerystringNameValuePair(string $key, mixed $val, string $delim='&'): string
	{
		$s = '';
		if (is_array($val)) {
			for ($j=0; $j<count($val); $j++) {
				$s = $delim.$key. '[' .$j. ']=' .$val[$j];
			}
		} 
		else {
			/* @todo urlencode the value? */
			$s = $delim.$key. '=' .$val;
		}
		return ($s);
	}

	/**
	 * Generates a randomized filenames of varying lengths.
	 * @param int $size Size of the file name (excluding the extension).
	 * @param string $file_extension Extension to add to the filename
	 * @return string Randomized filename.
	 */
	public static function generateRandomFilename(int $size, string $file_extension ): string
	{
		$filename = '';
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		for ($i=0; $i<$size; $i++) {
			$idx = rand(0,61);
			$filename .= $chars[$idx];
		}
		return (date('ymd').$filename. '.' .$file_extension);
	}

	/**
	 * Returns a string of random characters.
	 * @param int $size Length of the string to create.
	 * @param bool $alphanumeric_only (Optional) flag to indicate that only alphanumeric characters should be used in the string. Defaults to true.
	 * @return string String of random characters.
	 */
	public static function generateRandomString(int $size, bool $alphanumeric_only=true ): string
	{
		$rand_str = '';
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if (!$alphanumeric_only) {
			$chars .= '@#!*^|:;%';
		}
		$iSize = strlen($chars)-1;
		for ($i=0; $i<$size; $i++) {
			$idx = rand(0,$iSize);
			$rand_str .= $chars[$idx];
		}
		return ($rand_str);
	}

	/**
	 * Fetches remote content when curl is not available.
	 * @param string $hostname Host name
	 * @param string $url URL of the remote content
	 * @return string Content read from remote source.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getRemoteContent( string $hostname, string $url ): string
	{
		if (!defined('NON_SECURE_SERVER')) {
			throw new ConfigurationUndefinedException('NON_SECURE_SERVER not defined in app settings.');
		}
		$crlf = "\r\n";
		$f = fsockopen($hostname, 80, $errno, $errstr, 12);

		fputs($f, "GET $url HTTP/1.0\r\n");
		fputs($f, "Host: $hostname\r\n");
		fputs($f, 'Referer: ' . NON_SECURE_SERVER . "\r\n");
		fputs($f, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n\r\n");

		$sContent = '';
		while(!feof($f)) {
			$sContent .= fgets($f, 1024);
		}
		fclose($f);

		// split header and body
		$iPos = strpos($sContent, $crlf . $crlf);
		if($iPos!==false) {
			$sContent = substr($sContent, $iPos + 2 * strlen($crlf));
		}
		return ($sContent);
	}

    /**
     * Highlights a keyword within a larger string. Won't insert the tags around the keyword if it's found within HTML tags.
     * see: http://stackoverflow.com/questions/4081372/highlight-keywords-in-a-paragraph
     * @param string $src String to search for the keyword text.
     * @param string $keyword String to search for and highlight.
     * @return string The original text with SPAN tags inserted around the keyword with a class attribute of 'highlight'.
     * @throws DOMException
     */
	public static function highlightKeyword( string $src, string $keyword ): string
    {
		$keyword = str_replace('*', '', $keyword);
		$src = "<div>$src</div>";
		$dom = new DOMDocument();
		$dom->recover = true;
		@$dom->loadHtml($src);
		$xpath = new DOMXPath($dom);
		$elements = $xpath->query('//*[contains(.,"'.$keyword.'")]');
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			foreach ($element->childNodes as $child) {
				if (!$child instanceof DOMText) {
					continue;
				}
				$fragment = $dom->createDocumentFragment();
				$text = $child->textContent;
				while (($pos = stripos($text, $keyword)) !== false) {
					$fragment->appendChild(new DOMText(substr($text, 0, $pos)));
					$word = substr($text, $pos, strlen($keyword));
					$highlight = $dom->createElement('span');
					$highlight->appendChild(new DOMText($word));
					$highlight->setAttribute('class', 'searchterm');
					$fragment->appendChild($highlight);
					$text = substr($text, $pos + strlen($keyword));
				}
				if (!empty($text)) {
					$fragment->appendChild(new DOMText($text));
				}
				$element->replaceChild($fragment, $child);
			}
		}
		$str = $dom->saveXml($dom->getElementsByTagName('body')->item(0)->firstChild);
		return($str);
	}

	/**
	 * Returns the filesystem path even on systems where php's realpath() is disabled.
	 * @param string $path web server path
	 * @return string filesystem path corresponding to $path
	 */
	public static function realpath(string $path): string
	{
		// check if path begins with "/" i.e. is absolute
		// if it isn't concat with script path
		if (!str_starts_with($path, '/')) {
			$base=dirname($_SERVER['SCRIPT_FILENAME']);
			$path=$base. '/' .$path;
		}

		// canonicalize
		$path=explode('/', $path);
		$new_path=array();
		for ($i=0; $i<sizeof($path); $i++) {
			if ($path[$i]==='' || $path[$i]==='.') {
				continue;
			}
			if ($path[$i]==='..') {
				array_pop($new_path);
				continue;
			}
			$new_path[] = $path[$i];
		}
		$final_path='/'.implode('/', $new_path).'/';

		// check then return valid path or filename
		if (file_exists($final_path)) {
			return ($final_path);
		}
		else {
			return (false);
		}
	}

	/**
	 * saves form data, query string data, and optional additional name/value pairs in a string formatted as a new query string
	 * @param ?array $excludes array of parameters to skip over if they exist in form data or the current querystring
	 * @param ?array $adds (optional) associative array of name/value pairs to add to the new querystring
	 * @return string combined name/value pairs formatted as a new query string
	 *
	 */
	public static function serializePageData( ?array $excludes=null, ?array $adds=null ): string
	{
		$data = array_merge($_GET, $_POST);
		if (is_array($adds)) {
			$data = array_merge($data, $adds);
		}
		foreach ($data as $key => $val) {
			if ($excludes===null || !in_array($key,$excludes)) {
				$data[$key] = $key . '=' . urlencode($val);
			}
		}
		$data = ((is_array($data))?('?' .implode('&', $data)):(''));
		return ($data);
	}

	/**
	 * Displays an error message in the browser.
     * @deprecated Use ContentUtils::printError() instead.
	 * @param string $error Error message to display
	 * @param string $css_class (Optional) CSS class to apply to the error message container element.
	 * @param string $encoding (Optional) Defaults to 'UTF-8'
	 */
	public static function showError(string $error, string $css_class='', string $encoding='UTF-8'): void
    {
		if ($css_class==='') {
			$css_class = 'alert alert-error';
		}
		print ("<div class=\"$css_class\">".htmlspecialchars($error, ENT_QUOTES, $encoding). '</div>');
	}

	/**
	 * Appends a trailing slash.
	 * Will remove trailing forward and backslashes if it exists already before adding
	 * a trailing forward slash. This prevents double slashing a string or path.
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 * @param string $string What to add the trailing slash to.
	 * @return string String with trailing slash added.
	 */
	public static function trailingSlashIt( string $string ): string
    {
		return PageUtils::untrailingSlashIt( $string ) . '/';
	}

	/** 
	 * Removes trailing forward slashes and backslashes if they exist.
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 * @param string $string What to remove the trailing slashes from.
	 * @return string String without the trailing slashes.
	 */
	public static function untrailingSlashIt( string $string ): string
    {
		return rtrim( $string, '/\\' );
	}
}

