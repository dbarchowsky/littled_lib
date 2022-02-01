<?php
namespace Littled\Validation;


/**
 * Class ContentConversion
 * Static routines for converting blocks of content.
 * @package Littled\Validation
 */
class ContentConversion
{
	/**
	 * do all necessary processing to content entered in form textarea fields to save it in XML
	 * @param string $content Source content to clean.
	 * @return string parsed content
	 */
	public static function cleanTextForXml(string $content): string
	{
		/* remove newline characters */
		$content = preg_replace("/[\n\r]/", "<br />", $content);

		/* convert non-ASCII characters */
		$content = htmlentities($content, ENT_NOQUOTES, "iso-8859-1");

		/* restore HTML tags */
		$content = str_replace("&lt;", "<", $content);
		$content = str_replace("&gt;", ">", $content);
		$content = str_replace("&amp;", "&", $content);

		return ($content);
	}

	/**
	 * Does all necessary processing to content created in TinyMCE editor to save it in XML
	 * @param string $content Text to fix.
	 * @return string Fixed text.
	 */
	public static function cleanTinymceTextForXml( string $content ): string
	{
		/* remove newline characters */
		$content = preg_replace("/[\n\r]/", "", $content);

		/* convert non-ASCII characters */
		$content = htmlentities($content, ENT_NOQUOTES, "iso-8859-1");

		/* restore HTML tags */
		$content = str_replace("&lt;", "<", $content);
		$content = str_replace("&gt;", ">", $content);
		$content = str_replace("&amp;", "&", $content);

		/* swap <b> for <strong> and <i> for <em> */
		$content = str_replace("<strong>", "<b>", $content);
		$content = str_replace("</strong>", "</b>", $content);
		$content = str_replace("<em>", "<i>", $content);
		$content = str_replace("</em>", "</i>", $content);

		$content = self::htmlPTagsToBrTags($content);

		return ($content);
	}

	/**
	 * Formats a string that can be inserted into the name attribute of an input html element to represent the
	 * element's position in an array.
	 * @param mixed $index
	 * @return string
	 */
	public static function formatIndexMarkup($index): string
	{
		if (is_numeric($index)) {
			return "[$index]";
		}
		if (strlen("".$index)>0) {
			return "['$index']";
		}
		return '';
	}

	/**
	 * convert HTML paragraph tags to <br /> tags
	 * @param string $html Source markup to parse.
	 * @return string Markup with <p> tags converted to <br /> tags.
	 */
	public static function htmlPTagsToBrTags( string $html ): string
	{
		$html = preg_replace('/<p[^>]*>/', '', $html); /* Remove the start <p> or <p attr=""> */
		$html = preg_replace('/<\/p>/', '<br /><br />', $html); /* Replace the end */
		return ($html);
	}

	/**
	 * - Remove all newline characters (don't replace them with <br> tags)
	 * - Strip out all problematic html characters
	 * - Do NOT preserve any html tags
	 * @param string $content Source content to parse.
	 * @return string Content stripped of newlines.
	 */
	public static function stripNewlinesForXml(string $content): string
	{
		/* remove newline characters */
		$content = preg_replace("/[\n\r]/", "", $content);

		/* convert non-ASCII characters */
		$content = htmlentities($content, ENT_NOQUOTES, "iso-8859-1");

		return ($content);
	}
}