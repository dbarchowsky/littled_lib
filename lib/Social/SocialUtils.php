<?php
namespace Littled\Social;


use Littled\App\LittledGlobals;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotInitializedException;
use Littled\Utility\LittledUtility;

$error_reporting = error_reporting();
if (SocialUtils::getWordpressInstallPath()) {
	require_once(LittledUtility::joinPaths(
        SocialUtils::getWordpressInstallPath() . 'wp-load.php'));
	require_once(LittledUtility::joinPaths(
        SocialUtils::getWordpressInstallPath() . 'wp-includes/class-IXR.php'));
	require_once(LittledUtility::joinPaths(
        SocialUtils::getWordpressInstallPath() . 'wp-includes/class-wp-http-ixr-client.php'));
}
error_reporting($error_reporting);

class SocialUtils
{
    protected static string $app_domain;
    protected static string $bitly_key;
    protected static string $bitly_username;
    protected static string $flickr_auth_url;
    protected static string $wordpress_install_path;
    protected static string $twitter_pwd;
    protected static string $twitter_username;
    protected static string $wp_pwd;
    protected static string $wp_username;
    protected static string $wp_xml_url;

    /**
     * App domain getter.
     * @return string
     */
    public static function getAppDomain(): string
    {
        return static::$app_domain ?? '';
    }

    /**
     * Bitly key getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getBitlyKey(): string
    {
        if (!isset(static::$bitly_key)) {
            throw new NotInitializedException('Bitly key value has not been configured.');
        }
        return static::$bitly_key;
    }

    /**
     * Bitly username getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getBitlyUsername(): string
    {
        if (!isset(static::$bitly_username)) {
            throw new NotInitializedException('Bitly username has not been configured.');
        }
        return static::$bitly_username;
    }

    /**
     * Do authorization transaction with Flickr API.
     * Requires that the following tokens have been defined:
     * - APP_DOMAIN - Domain of the site registered with the Flickr API
     * - FLICKR_AUTH_SCRIPT - Local script that is registered with the Flickr API as the callback URL for authorization.
     * - P_REFERER - Parameter name for passing the current script's url to the authorization script. If the
     * authorization is successful this is where we should end up after it's all finished.
     * @throws NotInitializedException
     */
    public static function getFlickrAuthorization(): void
    {
        if (empty($_SESSION['phpFlickr_auth_token']))
        {
            if (
                static::getAppDomain() === '' ||
                static::getFlickrAuthURL() === '') {
                $err = 'Either the social utility app domain or Flickr authorization URL have not been configured.';
                throw new NotInitializedException($err);
            }
            $url = 'https://' . static::$app_domain . $_SERVER['REQUEST_URI'];
            if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])>0) {
                $url .= '?' . $_SERVER['QUERY_STRING'];
            }
            $url = static::$flickr_auth_url . '?' . LittledGlobals::REFERER_KEY . '=' . urlencode($url);
            header("Location: $url\n\n");
        }
    }

    /**
     * Flickr authorization url getter.
     * @return string
     */
    public static function getFlickrAuthURL(): string
    {
        return static::$flickr_auth_url ?? '';
    }

	/**
	 * Fetch a bit.ly short url for the supplied full url.
	 * @param string $long_url Original full-length url.
	 * @return string Shortened url.
     * @throws NotInitializedException
	 */
	public static function getShortURL( string $long_url ): string
    {
		/* get shortened url to sketchbook page */
		$bitly_url = 'https://api.bit.ly/shorten?version=2.0.1' .
            '&longUrl=' .urlencode($long_url).
            '&login=' .static::getBitlyUsername().
            '&apiKey=' .static::getBitlyKey().
            '&format=json' .
            '&history=1';

		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL, $bitly_url);
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($curl);
		curl_close( $curl );

		$obj = json_decode($result, true);
		return ($obj['results']["$long_url"]['shortUrl']);
	}

    /**
     * Twitter token getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getTwitterPassword(): string
    {
        if (!isset(static::$twitter_pwd)) {
            throw new NotInitializedException('Twitter password has not been configured.');
        }
        return static::$twitter_pwd;
    }

    /**
     * Twitter username getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getTwitterUsername(): string
    {
        if (!isset(static::$twitter_username)) {
            throw new NotInitializedException('Twitter username has not been configured.');
        }
        return static::$twitter_username;
    }

    /**
     * WordPress password getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getWordpressPassword(): string
    {
        if (!isset(static::$wp_pwd)) {
            throw new NotInitializedException('WordPress password has not been configured.');
        }
        return static::$wp_pwd;
    }

    /**
     * WordPress username getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getWordPressUsername(): string
    {
        if (!isset(static::$wp_username)) {
            throw new NotInitializedException('WordPress username has not been configured.');
        }
        return static::$wp_username;
    }

    /**
     * WordPress XML RPC URL getter.
     * @return string
     * @throws NotInitializedException
     */
    public static function getWordPressXMLURL(): string
    {
        if (!isset(static::$wp_xml_url)) {
            throw new NotInitializedException('WordPress XML RPC URL has not been configured.');
        }
        return static::$wp_xml_url;
    }

    /**
     * Wordpress install path getter.
     * @return string
     */
    public static function getWordpressInstallPath(): string
    {
        return static::$wordpress_install_path ?? '';
    }

    /**
     * Post a tweet on Twitter.
     * @param string $msg Tweet text to post.
     * @param string $format (Optional) Format to use to connect to the Twitter API. Defaults to JSON.
     * @return bool|int|string Twitter API ID of the new tweet.
     * @throws InvalidValueException
     * @throws NotInitializedException
     */
	public static function postToTwitter(string $msg, string $format= 'json'): bool|int|string
    {
		// The Twitter API address
        $url = match ($format) {
            'json' => 'https://twitter.com/statuses/update.json',
            'xml' => 'https://twitter.com/statuses/update.xml',
            default => throw new InvalidValueException('Unrecognized format token'),
        };

		// Set up and execute the curl process
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$msg");
        $auth = static::getTwitterUsername(). ':' .static::getTwitterUsername();
		curl_setopt($curl_handle, CURLOPT_USERPWD, $auth);
		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		// check for failure here
		$twitter_id = false;

		if ($result) {
			$obj = json_decode($result, true);
			$twitter_id = $obj['id'];
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
	 * @throws InvalidRequestException
     * @throws NotInitializedException
	 */
	public static function postWordpressXMLRPC(
        int    $post_id,
        string $title,
        string $body,
        array  $categories,
        array  $tags,
        string $publish = 'publish' ): string
    {
		$client = new WP_HTTP_IXR_CLIENT(static::getWordPressXMLURL());
		// $client->debug = 1;

		/** TODO Determine if the "post_status" value should be a string or boolean */
		$post = [
			'post_status' => $publish,
			'post_title' => $title,
			'post_content' => $body,
			'terms_names' => [
			'category' => $categories,
			'post_tag' => $tags
            ]];

		$stripped_body = trim(strip_tags($body));
		if (strlen($stripped_body) < 1) {
			/* insures that item summary is not blank in rss feed, which causes validation warnings */
			$post['post_excerpt'] = $title;
		}

		if ($post_id>0) {
			/* editing an existing WP post */
			$status = $client->query('wp.editPost', 0, static::getWordPressUsername(), static::getWordpressPassword(), $post_id, $post);
		}
		else {
			/* create a new WP post */
			$status = $client->query('wp.newPost', 0, static::getWordPressUsername(), static::getTwitterPassword(), $post);
		}

		if ($status) {
			$post_id = $client->getResponse();
		}
		else {
			throw new InvalidRequestException("Error in RPC request: {$client->error->message}");
		}
		return ($post_id);
	}
}