<?php
namespace Littled\Social;


$error_reporting = error_reporting();
if (defined("WPRESS_INSTALL_DIR"))
{
	require_once(WPRESS_INSTALL_DIR."wp-load.php");
	require_once(WPRESS_INSTALL_DIR."wp-includes/class-IXR.php");
	require_once(WPRESS_INSTALL_DIR."wp-includes/class-wp-http-ixr-client.php");
}
error_reporting($error_reporting);


/**
 * Class SocialUtils
 * @package Littled\Social
 * TODO Get rid of all the references to global constants.
 */
class SocialUtils
{
	/**
	 * Fetch a bit.ly short url for the supplied full url.
	 * @param string $longURL Original full-length url.
	 * @return string Shortened url.
	 */
	public static function getShortURL( $longURL )
	{
		/* get shortened url to sketchbook page */
		$bitlyURL = "http://api.bit.ly/shorten?version=2.0.1".
			"&longUrl=".urlencode($longURL).
			"&login=".BITLY_USERNAME.
			"&apiKey=".BITLY_API_KEY.
			"&format=json".
			"&history=1";

		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL, $bitlyURL);
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($curl);
		curl_close( $curl );

		$obj = json_decode($result, true);
		return ($obj["results"]["$longURL"]["shortUrl"]);
	}


	/**
	 * Post a tweet on Twitter.
	 * @param string $msg Tweet text to post.
	 * @param string $format (Optional) Format to use to connect to the Twitter API. Defaults to JSON.
	 * @return integer	Twitter API ID of the new tweet.
	 */
	public static function postToTwitter($msg, $format="json")
	{
		// The twitter API address
		switch ($format) {
			case "json":
				$url = 'http://twitter.com/statuses/update.json'; /* json */
				break;
			case "xml":
				$url = 'http://twitter.com/statuses/update.xml'; /* xml */
				break;
			default:
				/* unhandled type */
				return ("Supplied format is unhandled.");
				break;
		}

		// Set up and execute the curl process
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$msg");
		curl_setopt($curl_handle, CURLOPT_USERPWD, TWITTER_USERNAME.":".TWITTER_PASSWORD);
		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		// check for failure here
		$twitter_id = false;

		if ($result)
		{
			$obj = json_decode($result, true);
			$twitter_id = $obj["id"];
		}
		return ($twitter_id);
	}


	/**
	 * Create or update a post on a WordPress blog
	 * @param int $post_id ID of the post to edit.
	 * @param string $title Title of the post.
	 * @param string $body Body of the post.
	 * @param array $categories Array containing the names of the categories to assign to the post.
	 * @param array $tags Array containing the tags to assign to the post.
	 * @param string $publish Flag to control publishing the post immediately or to save it as a draft for later.
	 * @return string WordPress response code.
	 * @throws \Exception
	 */
	public static function postWordpressXMLRPC($post_id, $title, $body, $categories, $tags, $publish='publish' )
	{
		$client = new WP_HTTP_IXR_CLIENT(WP_XMLRPC_URL);
		// $client->debug = 1;

		/** TODO Determine if the "post_status" value should be a string or boolean */
		$post = array(
			'post_status' => $publish,
			'post_title' => $title,
			'post_content' => $body,
			'terms_names' => array(
			'category' => $categories,
			'post_tag' => $tags
			));

		$stripped_body = trim(strip_tags($body));
		if (strlen($stripped_body) < 1) {
			/* insures that item summary is not blank in rss feed, which causes validation warnings */
			$post['post_excerpt'] = $title;
		}

		if ($post_id>0) {
			/* editing an existing WP post */
			$status = $client->query("wp.editPost", 0, WP_USERNAME, WP_PASSWORD, $post_id, $post);
		}
		else {
			/* create a new WP post */
			$status = $client->query("wp.newPost", 0, WP_USERNAME, WP_PASSWORD, $post);
		}

		if ($status) {
			$post_id = $client->getResponse();
		}
		else {
			throw new \Exception("Error in RPC request: {$client->error->message}");
		}
		return ($post_id);
	}


	/**
	 * Do authorization transaction with Flickr API.
	 * Requires that the following tokens have been defined:
	 * - APP_DOMAIN - Domain of the site registered with the Flickr API
	 * - FLICKR_AUTH_SCRIPT - Local script that is registered with the Flickr API as the callback URL for authorization.
	 * - P_REFERER - Parameter name for passing the current script's url to the authorization script. If the authorization is successful this is where we should end up after it's all finished.
	 */
	public static function getFLickrAuthorization()
	{
		if (empty($_SESSION['phpFlickr_auth_token']))
		{
			$sURL = "http://".APP_DOMAIN.$_SERVER["REQUEST_URI"];
			if (isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"])>0) {
				$sURL .= "?".$_SERVER["QUERY_STRING"];
			}
			$sURL = FLICKR_AUTH_SCRIPT."?".P_REFERER."=".urlencode($sURL);
			header("Location: {$sURL}\n\n");
		}
	}
}