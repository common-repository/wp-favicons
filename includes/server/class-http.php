<?php
/**
 * Contains All methods related to doing http work in this plugin
 *
 * General HTTP
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\Http"))
{

	/**
	 * Contains All methods doing http work and related utils
	 * The core Method is " GetIconFromUri" which tries to get an icon from
	 * a certain URI.
	 *
	 * @since 0.1
	 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 * @copyright GPL 2
	 * @static class containing http functions
	 */
	class Http
	{
		const FLAG_NOK = 'FLAG_NOK';
		/**
		 * Enter description here ...
		 * @var unknown_type
		 */
		const FLAG_TOO_MANY_COUNTS = 'TOO_MANY_COUNTS';
		/**
		 * Enter description here ...
		 * @var unknown_type
		 */
		const FLAG_DATA_FOUND = 'DATA_FOUND';
		/**
		 * Enter description here ...
		 * @var unknown_type
		 */
		const FLAG_ICON = 'ICON';
		const FLAG_PAGE = 'PAGE';
		const MAX_REDIRECTS = 7;

		/**
		 * @var singleton instance
		 */
		private static $instance;

		/** private constructor function
		 *  to prevent external instantiation
		 */
		private function __construct()
		{
		}

        /**
		 * clone method
		 */
		private function __clone()
		{
		}

		/**
		 * getInstance method
		 */
		public static function getInstance()
		{
			if(!self::$instance)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		/* ------------------------------------------------------------ */

		/**
		 * Returns max redirection count
		 * Important that this is 0 we do our redirection ourself
		 * Has to be public to act in callback function
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 	 * @copyright GPL 2
	 	 * @return int maximum amount of redirections
		 */
		public function SetDirectionCount()
		{
			return 0;
		}

		/**
		 * Returns the default useragent
		 * Has to be public to act in callback function
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 	 * @copyright GPL 2
	 	 * @return string User Agent String
		 */
		public static  function SetHeaderUserAgent()
		{
			$version = Config::GetPluginVersion();
			return "Favcollector/$version (info@favcollector.com http://www.favcollector.com/ " 
			  . get_bloginfo( 'url' ) . ")";
		}

    	/* ------------------------------------------------------------ */

		/**
		 * Parses an embedded icon or icon href from a rel href string
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 	 * @copyright GPL 2
	 	 * @param object $xpath xpath object needed to find base href if needed
		 * @param string $XpathExpr expression used merely for statistics logging
		 * @param string $hrefstring the string we are parsing
		 * @param string $uri the original uri request for logging
		 * @returns array $favicon_array as used all over the place
		 */
		public static function GetIconFromHrefString( $xpath, $XpathExpr, $hrefstring, $favicon)
		{
			$instance = self::getInstance();

			/* it can contain 2 things either an embedded base64 encoded string
			 * or a href */

			/* option 1: embedded */
			if (strtolower(substr(trim($hrefstring),0,4))=='data')
			{
				Log::F($favicon,'[PAGE] embedded image found');

				/* 1 explode the parts of the href */

				list($type,$rest) = explode(":", $hrefstring);
				list($mimetype,$rest) = explode(";",$rest);
				list($encoding,$imagedata) = explode(",", $rest);
				$type = trim($type);
				$mimetype = trim($mimetype);
				$encoding = trim($encoding);
				$imagedata = trim($imagedata);

				/* 2 check if base 64 */

				if (!$encoding=='base64') { return false; }

				/* 3 verify and store (we get filters and type) */
				// if ($return_array = filters_verify_image::VerifyImage($return_array))
				// takes place in page class

				$favicon->SetFaviconUri($favicon->GetUri(). "[" . $imagedata . "]");
				$favicon->SetSource('sources_page' .
						':' . $XpathExpr . ':' . 'embedded');
				$favicon->SetData(base64_decode($imagedata));
				$favicon->AddUriToUriArr($favicon->GetFaviconUri());
				return $instance::FLAG_DATA_FOUND;
			}
			/* option 2: not embedded */
			else
			{
				Log::F($favicon,'[PAGE] href found');
				$favicon->SetSource('sources_page'
				.':' . $XpathExpr . ':' . 'href');
				return $instance->CallAgain($favicon,
					Http::getFullFaviconPath($favicon->LastUriAddedToUriArr(), $hrefstring, $xpath));
			}
		}

		/* ------------------------------------------------------------ */

		/**
		 * gets a REL ICON href string from a HTML page
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 	 * @copyright GPL 2
	 	 * @param string $data the HTML page in a string
	 	 * @param string $uri the uri we are requesting
		 */
		public static function GetHrefStringFromHTMLPage($favicon)
		{
			/*
			 * these could be merged for speed but we use them for statistics on usage
			 * see: http://bit.ly/hBdhJV kudos http://stackoverflow.com/users/459897/dr-molle
			 */
			$instance = self::getInstance();
			Log::F($favicon,'FINDICON process (in page)');
			/* quick shortcut */
			if (!( strpos("-".strtolower($favicon->GetData()),strtolower('<head')) &&
				   strpos("-".strtolower($favicon->GetData()),strtolower('<body'))
				 )
			   )
			{
				Log::F($favicon,'icon with wrong mime (probably)');
				return $instance::FLAG_DATA_FOUND;
			}

			$dom = new \DOMDocument();
			libxml_use_internal_errors(true);
			$dom->strictErrorChecking = false;

			if (!$dom->loadHTML($favicon->GetData()))
			{
				foreach (libxml_get_errors() as $error)
				{
				}
				libxml_clear_errors();
				Log::F($favicon,'DOM could not be parsed');
				return $instance::FLAG_NOK;
			}
			$xpath = new \DOMXPath($dom);

			/* Check page content for image tags */
			$_xPathExpressions = array(
				"//head/link[@rel='shortcut icon']",
				"//head/link[@rel='icon']",
				"//link[@rel='shortcut icon']",
				"//link[@rel='icon']",
				"//link[translate(@rel,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz') = 'shortcut icon']",
				"//link[translate(@rel,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz') = 'icon']",
			);
			foreach($_xPathExpressions as $xPathExpression)
			{
				if ($hrefstring=$instance->XpathHrefCheck($xpath, $xPathExpression, 'href'))
				{
					Log::F($favicon,'image rel tag found in page : ' . $hrefstring );
					return $instance->GetIconFromHrefString($xpath, $xPathExpression,
						$hrefstring, $favicon);
				}
				else
				{
					Log::F($favicon,'image rel tag NOT found in page with : ' . $xPathExpression);
				}
			}

			/* check for javascript redirections */
			// temp. disabled need more advanced javascript parser 
			/*
			if ($hrefstring=$instance->XpathHrefCheck($xpath, "//script", 'location.href'))
			{
				Log::F($favicon,'[PAGE] javascript redirection found to ' . $hrefstring);
				return $instance->CallAgain($favicon
				, Http::getFullFaviconPath($favicon->LastUriAddedToUriArr()
				, $hrefstring, $xpath));
			}
			*/

			/* check for meta http refresh tags */
			if ($hrefstring=$instance->XpathHrefCheck($xpath
			, "//head/meta[translate(@http-equiv],'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz') = 'refresh']"
			, 'url'))
			{
				Log::F($favicon,'[PAGE] meta redirection tag found to ' . $hrefstring);
				return $instance->CallAgain($favicon
				, Http::getFullFaviconPath($favicon->LastUriAddedToUriArr()
				, $hrefstring, $xpath));
			}



			/* Check Root and/or /favicon.ico */
			Log::F($favicon,'not found in page try /');
			if ($instance->CallAgain($favicon,'/') == $instance::FLAG_DATA_FOUND)
			{
				return $instance::FLAG_DATA_FOUND;
			}
			else
			{
				$favicon->SetSource('sources_root');
				Log::F($favicon,'not found in page try /favicon.ico');
				return $instance->CallAgain($favicon,'/favicon.ico');
			}
		}

		/* ------------------------------------------------------------ */


		/**
		 * This function return an icon inside an array based on a uri request
		 * @param array $r_arr
		 * @returns array $r_arr
		 */
		public static function GetIconFromUri($favicon, $state='')
		{
			Log::F($favicon,'!');
			$instance = self::getInstance();
			if ($favicon->LastUriAddedToUriArr() == '')
			{
				return $instance::FLAG_NOK;
			}
			if ( $favicon->CountUriArr() > $instance::MAX_REDIRECTS)
			{
				Log::F($favicon,'TOO MANY REDIRECTS');
				return $instance::FLAG_TOO_MANY_COUNTS;
			}

			/* --------------------------------------------------------------
			 * HTTP
			 * --------------------------------------------------------------
			 */

			/* 4.1 HEAD : first try only the head to see if we get a redirect or error
			 * */

			$response = $instance->HttpRequest($favicon, 'HEAD');
			Log::F($favicon,'HEAD GET ' . $favicon->LastUriAddedToUriArr());
			if ( is_wp_error( $response ))
			{
				Log::F($favicon,'HEAD ERROR ' . $response->get_error_message());
				return $instance::FLAG_NOK;
			}

			/* HEAD : Returns 200 : do full request (do not log the HEAD
			 * request to keep unique uri
			 * HEAD : Returns 404 : do full request (since even most 404
			 * pages give many nice pages to scan for favicons)
			 * HEAD : Returns 405 : do full request (since in most cases the head
			 * request is not supported)
			 * */
			elseif (	$response['response']['code'] == 200
					||  $response['response']['code'] == 404
					||	$response['response']['code'] == 405
					)
			{
				if (isset($response['headers']['content-type'])) {
					Log::F($favicon,'HEAD 4.3 200/404/405 MIMETYPE : '
					. $response['headers']['content-type']);
				}
				else
				{
					Log::F($favicon,'HEAD 4.3 200/404/405 No mimetype ');
				}

				$response = $instance->HttpRequest($favicon,'FULL');
				Log::F($favicon,'GET ' . $favicon->LastUriAddedToUriArr());
				
				/* GET : ERROR */
				if ( is_wp_error( $response ))
				{
					Log::F($favicon,'GET ERROR : '. $favicon->LastUriAddedToUriArr() . ',' . $response->get_error_message());
					return $instance::FLAG_NOK;
				}
				/* GET : 200 */
				elseif ($response['response']['code'] == 200)
				{
					if ($data = $response['body'])
					{
						$favicon->SetData($data);
						if (array_key_exists('content-type', $response['headers']))
						{
							if ($instance->CheckMimeType($response['headers']['content-type']))
							{
								Log::F($favicon,'GOT ICON : ' . $favicon->LastUriAddedToUriArr());
								return $instance::FLAG_DATA_FOUND;
							}
							else
							{
								Log::F($favicon,'Try find icon in PAGE : ' . $favicon->LastUriAddedToUriArr());
								return $instance->GetHrefStringFromHTMLPage($favicon);
							}
						}
						else
						{
							Log::F($favicon,'no content type found lets try page');
							return $instance->GetHrefStringFromHTMLPage($favicon);
						}
						
					}
					else
					{
						Log::F($favicon,'GET 200 but no BODY : ' . $favicon->LastUriAddedToUriArr());
						return $instance::FLAG_NOK;
					}
				}
				/* GET 301/302
				 * Solved this one due to https://core.trac.wordpress.org/ticket/16855
				 * (Hakre for the fix, TheDeadMedic, dd32) so by the time this comes out...
				 * it should be in a WP Release.
				 */
				elseif (in_array($response['response']['code'], array('301', '302')))
				{
					Log::F($favicon,'GET 301/302 - moved - try redirect');
					if (is_array($response['headers'])) {
						if (array_key_exists('location',$response['headers']))
						{
							return $instance->CallAgain($favicon,$response['headers']['location']);
						}
						else
						{
							Log::F($favicon,'NO REDIRECT URL IN REDIRECT REQUEST');
							return $instance::FLAG_NOK;
						}
					}
					else
					{
						Log::F($favicon,'NO REDIRECT HEADERS IN REDIRECT REQUEST');
						return $instance::FLAG_NOK;
					}
				}
				/* GET : 403 - forbidden - try root */
				elseif (in_array($response['response']['code'], array('403')))
				{
					Log::F($favicon,'HEAD 403 - Forbidden - try root');
					return $instance->CallAgain($favicon,'/');
				}
				/* 4.2.2 GET : 404 (could contain useful stuff) */
				elseif ($response['response']['code'] == 404)
				{
					$data = $response['body'];
					if ($data)
					{
						/* there might be an image link in the HTML */
						if ( strpos(strtolower("-".$data),strtolower('<html')) &&
							 strpos(strtolower("-".$data),strtolower('<body')) )
						{
							/* its HTML */
							Log::F($favicon,'GET 404 : Try find icon in PAGE : '
							. $favicon->LastUriAddedToUriArr());
							$favicon->SetData($data);
							return $instance->GetHrefStringFromHTMLPage($favicon);
						}
						else
						{

							Log::F($favicon,'FULL GET 404 : body but no html try root '
							. $favicon->LastUriAddedToUriArr());
							return $instance->CallAgain($favicon,'/');
						}
					}
					else
					{
						Log::F($favicon,'FULL GET 404 : no body try root' . $favicon->LastUriAddedToUriArr());
						return $instance->CallAgain($favicon,'/');
					}
				}
				/* GET : 405 - Method not allowed*/
				elseif (in_array($response['response']['code'], array('405')))
				{
					Log::F($favicon,'GET 405 - Method not allowed - try root favicon last shot');
					return $instance->CallAgain($favicon,'/favicon.ico');
				}
				/* GET: other than 200 / 404 : log it to analyze assume no redirect */
//				else
//				{
//					/* first check the mime type returned : page = do again */
//					Log::F($favicon,'GET NON 200/404/405/301/302 : '
//					. $response['response']['code']);
//					return $instance::FLAG_NOK;
//				}
				/* GET : Other */
				else
				{
					Log::F($favicon,'HEAD NNN - OTHER RETURN CODE:' , $response['response']['code']);
					return $instance::FLAG_NOK;
				}
			}
			//@ todo what is http://www.ibmpressbooks.com/series/ doing?
			elseif (in_array($response['response']['code'], array('301', '302')))
			{
				Log::F($favicon,'HEAD 301/302 - moved - try redirect');
				if (is_array($response['headers'])) {
					if (array_key_exists('location',$response['headers']))
					{
						return $instance->CallAgain($favicon,$response['headers']['location']);
					}
					else
					{
						Log::F($favicon,'NO REDIRECT URL IN REDIRECT REQUEST');
						return $instance::FLAG_NOK;
					}
				}
				else
				{
					Log::F($favicon,'NO REDIRECT HEADERS IN REDIRECT REQUEST');
					return $instance::FLAG_NOK;
				}
			}
			elseif (in_array($response['response']['code'], array('403')))
			{
				Log::F($favicon,'HEAD 403 - Forbidden - try root');
				return $instance->CallAgain($favicon,'/');
			}
			else
			{
				Log::F($favicon,'HEAD NNN - OTHER RETURN CODE');
				return $instance::FLAG_NOK;
			}
		}

		/* ------------------------------------------------------------ */

		/**
		 *
		 * Enter description here ...
		 * @param Favicon $favicon the current favicon object
		 * @param string $dataUri the data uri to get
		 * @param bool $icon_or_page if it is an icon or page request for logging
		 * @param string $source the source module calling this
		 * @param bool $default is used as default icon or not
		 * @todo icon or page parameter is not needed
		 */
		public static function GetFavicon($favicon)
		{
			$instance = self::getInstance();
			$favicon->AddUriToUriArr($favicon->GetFaviconUri());
			if (!$favicon->FoundStatus() && ($instance::FLAG_DATA_FOUND ==
				$instance->GetIconFromUri($favicon, '')))
			{
				$favicon->Found();
			}
			else
			{
				$favicon->EmptyUriArr();
			}
		}

		/* ------------------------------------------------------------ */

		/**
		 *
		 * Enter description here ...
		 * @param $favicon
		 * @param $msg1
		 * @param $msg2
		 * @param $uri2
		 */
		public static function CallAgain($favicon,$uri2)
		{
			$instance = self::getInstance();
			if ($new_uri
					= $instance->BuildRedirectedUri($favicon
						,$instance->getFullFaviconPath($favicon->LastUriAddedToUriArr(),$uri2)))
			{
				$favicon->AddUriToUriArr($new_uri);
				return $instance->GetIconFromUri($favicon, '');
			}
			else
			{
				Log::F($favicon,'Already Tried or Invalid');
				return $instance::FLAG_NOK;
			}
		}

		/**
		 *
		 * Enter description here ...
		 * @param $favicon
		 * @param $new_uri
		 */
		public static function WentThereBefore($favicon,$new_uri)
		{
			$went_there_before = false;
			foreach($favicon->GetUriArr() as $uri)
			{
				if ($uri == $new_uri)
				{
					$went_there_before = true;
				}
			}
			return $went_there_before;
		}

		/**
		 * Builds a new uri to go to after redirect
		 * @param string $old_uri
		 * @param string $new_uri
		 * @return string $new_uri
		 * @todo check for condition that old url is the same as the previous one
		 */
		public static function BuildRedirectedUri($favicon,$new_uri)
		{
			$instance = self::getInstance();
			$old_uri = $favicon->LastUriAddedToUriArr();
			if (in_array(parse_url($new_uri,PHP_URL_SCHEME), array('http','https','file'))) {
				if ($instance->WentThereBefore($favicon,$new_uri))
				{
					return false;
				}
				else
				{
					return $new_uri;
				}
			}
			$old_url_scheme = parse_url($old_uri,PHP_URL_SCHEME) . '://';
			$old_url_host = parse_url($old_uri, PHP_URL_HOST);
			$old_url_port = parse_url($old_uri, PHP_URL_PORT);
			if ($old_url_port)
			{
				$old_url_port = ':' . $old_url_port;
			}
			$old_uri = $old_url_scheme . $old_url_host . $old_url_port;

			if (substr($new_uri,1) == '/')
			{

				$new_uri = $old_uri . $new_uri;
			}
			else
			{
				$new_uri = $old_uri . '/' . $new_uri;
			}
			/* check that we did not go there before */
			$went_there_before = false;
			foreach($favicon->GetUriArr() as $uri)
			{
				if ($uri == $new_uri)
				{
					$went_there_before = true;
				}
			}
			if ($instance->WentThereBefore($favicon,$new_uri))
			{
				return false;
			}
			else
			{
				return $new_uri;
			}
		}

	   /**
		 * Checks if a returned header content type is image
		 * @param $content_type
		 * @return bool $return_type
		 */
		public static function CheckMimeType($content_type)
		{
			$return_type = false;
			if ($content_type)
			{
				if (strpos($content_type,'text') !== false)
				{
					$return_type = false;
				}
				elseif(strpos($content_type,'image') !== false)
				{
					$return_type = true;
				}
			}
			return $return_type;
		}

		/**
		 * check if a uri host is an IP address
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
	 	 * @copyright GPL 2
		 * @param $ip
		 */
		public static function valid_ip($ip) {
			return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" .
				"(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip);
		}

		/**
		 * deliver an image url from a page string
		 * @todo rename to href finder
		 * @since 0.4.0
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param object $xpath
		 * @param string $XpathExpr
		 * @param string $url
		 */
		public static function XpathHrefCheck($xpath, /*string*/ $XpathExpr, $attribute)
		{
			$elements = $xpath->query($XpathExpr);
			if (!is_null($elements) && $elements!=false)
			{
				foreach ($elements as $element)
				{
					// @todo window.location.replace('') and
					// these window location scripts since they all detect e.g.
					// "http"+(window.location.protocol.indexOf('https:')==0?'s':''
					// probably also the location href should be checked for more
					// since it could well be in e.g. example text on a page
					// or inside script tags
					if ($attribute=='location.href')
					{
						$found = false;
						$search = $element->textContent;
						$regexlist = array(	'/location.href(.*?)=(.*?)[\'"](.*?)[\'"]/i'
										//,'/window.location(.*?)=(.*?)[\'"](.*?)[\'"]/i'
										//,'/window.location(.*?)\((.*?)[\'"](.*?)[\'"]\)/i'
									);
						foreach($regexlist as $regex) {
							preg_match($regex,$search,$matches);
							if ($matches) {
								$found = $matches[3];
							}
						}
						if ($found)
						{
							return $found;
						}
					}

					if ($element->getAttribute($attribute))
					{
						$href = $element->getAttribute($attribute);
						return $href;
					}
				}
			}
			return false;
		}

		/**
 		 * Gets the full image path for a found image in a link href
 		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 	 	 * @param $url
 		 * @param $image
 		 */
		public static function getFullFaviconPath($url, $image='favicon.ico', $xpath='')
		{
			$instance = self::getInstance();

			/* image paths with scheme are easy: */

			if ($url_scheme = parse_url($image,PHP_URL_SCHEME)) {
				Log::M(' [FROMPAGE] full path: ' . $image);
				return $image;
			}
			/* especially for google: <link rel="shortcut icon" href="//www.google.com/favicon.ico"> */
			if (substr($image,0,2)=='//')
			{
				Log::M(' [FROMPAGE] full path: ' . 'http:' . $image);
				return 'http:' . $image;
			}
			$base_href = false;
			/* for relative paths we need to check first if the base href is set */
			if ($xpath)
			{
				$XpathExpr = "//base";
				$base_href = $instance->XpathHrefCheck($xpath, $XpathExpr, 'href');
				// now some sites such as www.shoutmouth.com have:
				// http://www.shoutmouth.com/index.php...
			}
			if ($base_href)
			{
				/* is the base href relative/ */
				if (!parse_url($base_href,PHP_URL_SCHEME))
				{
					$base_href_scheme_temp = parse_url($url,PHP_URL_SCHEME) . "://";
					$base_href__host_temp = parse_url($url, PHP_URL_HOST);
					$base_href__port_temp = parse_url($url, PHP_URL_PORT);
					if ($base_href__port_temp) {
						$base_href__port_temp = ':' . $base_href__port_temp;
					}
					$base_href_temp=$base_href_scheme_temp . $base_href__host_temp .
						$base_href__port_temp . '/';
					$base_href = $base_href_temp . $base_href;
				}
				/* is it the file protocol? */
				if ($base_href=='file')
				{
					if (!substr($base_href,-2)=='\\\\')
					{
						$base_href = $base_href . '\\\\';
					}
				}
				else
				{
					if (!substr($base_href,-1)=='/')
					{
						$base_href = $base_href . '/';
					}
				}
			}
			else
			{
					$base_href_scheme = parse_url($url,PHP_URL_SCHEME) . "://";
					$base_href__host = parse_url($url, PHP_URL_HOST);
					$base_href__port = parse_url($url, PHP_URL_PORT);
					if ($base_href__port) {
						$base_href__port= ':' . $base_href__port;
					}
					$base_href=$base_href_scheme . $base_href__host . $base_href__port . '/';
			}
			if (substr($image,0,1)=='/')
			{
				$image = substr($image,1);
			}
			$image_string = $base_href . $image;

			Log::M('TRYING image string:' . $image_string);

			return $image_string;
		}

		/**
		 * @param $uri
		 * @param $timeout
		 * @param $requesttype
		 */
		public static function HttpRequest($favicon, $requesttype)
		{
			if( !ini_get('safe_mode') ){
				ini_set('max_execution_time',1000);
				set_time_limit(1000);
			}
			$response='';
			$instance = self::getInstance();
			$version = Config::GetPluginVersion();
			$http_args_get = array (
				'method' => 'GET',
				'timeout' => 300,
				'redirection' => 0,
				'httpversion' => '1.0',
				'user-agent' => "Favcollector/$version (info@favcollector.com http://www.favcollector.com/) node " . get_bloginfo( 'url'),
				'blocking' => true,
				'headers' => array(),
				'cookies' => array(),
				'body' => null,
				'compress' => false,
				'decompress' => true,
				'sslverify' => false,
			    'followlocation' => false
			);
			$http_args_head = array (
				'method' => 'HEAD',
				'timeout' => 300,
				'redirection' => 0,
				'httpversion' => '1.0',
				'user-agent' => "Favcollector/$version (info@favcollector.com http://www.favcollector.com/) node " . get_bloginfo( 'url'),
				'blocking' => true,
				'headers' => array(),
				'cookies' => array(),
				'body' => null,
				'compress' => false,
				'decompress' => true,
				'sslverify' => false,
			    'followlocation' => false
			);
	
			//add_action( 'http_api_curl', array($instance,'http_curl_settings'));
			//add_action( 'http_api_curl_info', array($instance,'http_curl_info'));
			//add_action( 'http_api_debug', array($instance,'wphttp_api_debug'), 10 , 3  );

			$response = false;
			$response = apply_filters( Config::GetPluginSlug() . 'pre_http', $favicon, $response);

			// includign new calls for fix for #16855
			if (!$response)
			{
				
				if ($requesttype=='FULL')
				{
					//$response = wp_remote_request_WPFAVICONS($favicon->LastUriAddedToUriArr(), $http_args_get);
					$response = wp_remote_request($favicon->LastUriAddedToUriArr(), $http_args_get);
					do_action(Config::GetPluginSlug() . 'post_http_full', $favicon, $response);
				}
				elseif ($requesttype = 'HEAD')
				{
					//$response = wp_remote_request_WPFAVICONS($favicon->LastUriAddedToUriArr(), $http_args_head);
					$response = wp_remote_request($favicon->LastUriAddedToUriArr(), $http_args_get);
					do_action(Config::GetPluginSlug() . 'post_http_head', $favicon, $response);
				}
			}			
			return $response;
		}

		/**
		 * see /ext/curl/streams.c to read how the php implementation of curl works
		 * @param resource $handle Curl Handle
		 */
		public static function http_curl_settings(&$handle)
		{
			// might be handy
			// see 16855:
			//curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, false );

			/* maximum redirects we put to 0 to handle redirects ourself */
			curl_setopt( $handle, CURLOPT_MAXREDIRS, 0 );

			/* return the transfer to let the http class handle it */
			curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );

			/* we set this one because we want the header info afterwards in the
			 * info. we can then log what our request was in case of problems.
			 */
			curl_setopt($handle, CURLINFO_HEADER_OUT, true);
		}

		/**
		 *
		 * Enter description here ...
		 * @param $handle
		 */
		public static function http_curl_info($handle)
		{
			//$a = curl_getinfo($handle);
			//echo '<pre>';
			//print_r($a);
			//echo '</pre>';
		}

		/**
		 * @param $response
		 * @param $type
		 * @param $r
		 */
		public static function wphttp_api_debug($response='', $type='', $r='')
		{
			//print_r($response);
			//print_r($r);
			//echo $url;
			//wp_die('');

			//Log::M("{$r['method']} {$url} HTTP/{$r['httpversion']}");
			//Log::M("{$response['response']['code']} {$response['response']['message']}");

			if (is_object($response))
			{
				Log::M($r . ':' . $type . ':' . get_class($response));
			}
			elseif (is_array($response))
			{
				Log::M($r . ':' . $type . ':' . $response['response']['code'] . ':'
				. $response['response']['message']);
			}


		}

	}
}


