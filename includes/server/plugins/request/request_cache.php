<?php
/**
 * Request Cache on/off settings
 * @package WP-Favicons
 * @since 0.5.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\request_request_cache"))
{
   /**
	* Request Cache class - caches outgoing requests to not make too many request to sites 
 	* @since 0.5.0
 	* @author Edward de Leau <e@leau.net>, http://wp.leau.co
	*/
	class request_request_cache extends Plugin
	{

	   /**
		* Adds the filter to the http requests
 		* @since 0.5.0
 		* @author Edward de Leau <e@leau.net>, http://wp.leau.co
		*/
		function AddFilter()
    	{
    		/* parent class arranges that this is not called when the bool is off */
    		Log::M('request cache filters added');
    		add_filter(Config::GetPluginSlug() . 'pre_http', array($this,'PreHttp'), 6, 2);
    		add_action(Config::GetPluginSlug() . 'post_http_full', array($this,'PostHttpFull'), 6, 2);
    		add_action(Config::GetPluginSlug() . 'post_http_head', array($this,'PostHttpHead'), 6, 2);
    		add_filter(Config::GetPluginSlug() . 'timebuffer', array($this,'Timebuffer'), 6, 1);

    		/* add the location based filters */
    		if (Config::GetOptionsArrayValue(Config::GetPluginSlug()
    		 . $this->_plugin . '_show_status'))
			{
   				add_filter(Config::GetPluginSlug() . 'metadata_after_href'
   					, array($this,'DoFilter'), 6, 3);
			}
    	}

	   /**
		* Depending on the cached response return another colored icon
 		* @since 0.5.0
 		* @author Edward de Leau <e@leau.net>, http://wp.leau.co
		* @param $html_string
		* @param $uri
		* @param $filter
		*/
		function DoFilter($html_string, $uri, $filter)
    	{
			$provider = new HttpStatusFactory( new HttpStatus($uri));
			$this->_html_string = $provider->GetAsHtml();
			if ($this->_html_string)
			{
				// we can do a color for each HTTP code (see http://en.wikipedia.org/wiki/HTTP_200#2xx_Success)
				// but for users probably green (ok), yellow (301/302), red( 400/500)
				// and black (error) will probably be the clearest
				//
				// but in next releases this will be refined because a color per
				// http status code is the coolest.
				//
				if (in_array($this->_html_string,array('100','101','102','122','202')))
				{
					$icon_code='100';
				}
				elseif (in_array($this->_html_string,array('200','201','272', '226')))
				{
					$icon_code='200';
				}
				elseif (in_array($this->_html_string,array('300','301','302','303'
					,'304','305','306', '203')))
				{
					$icon_code='300';
				}
				elseif ($this->_html_string=='307') // temporary redirect
				{
					$icon_code='307';
				}
				elseif (in_array($this->_html_string,array('400','401','402','403'
					,'404','405','406','407','408','409','410','411','412','413'
					,'414','415','416','417','422','423','424','425','426','444'
					,'449','450','499')))
				{
					$icon_code='400';
				}
				elseif (in_array($this->_html_string,array('500','501','502','503'
					,'504','505','506','507','509','510','202','204','205','206')))
				{
					$icon_code='100'; // could be temporary
				}
				elseif($this->_html_string=='418')
				{
					$icon_code='418';
				}
				else
				{
					$icon_code='000';
				}

				$txt_before1 = '<span style="position: relative; left: 0; top: 0;">';
				// @todo this is dirty
				$txt_before2 = '<img src="' . WP_PLUGIN_URL . '/wp-favicons/includes/server/img/';
				$txt_after1='.png" style="position: absolute; top: 0; left: 0;"/>';
				$txt_after2='</span>';
				// 	@todo regex list not complete yet
				/* before */
				$found = false;
				$regex = '/<img(.*?)><a(.*?)>(.*?)<\/a>/i';
				preg_match($regex,$html_string,$matches);
				if ($matches)
				{
					$found = true;
					$html_string =
						$txt_before1
						. '<img' . $matches[1] . '>'
						. $txt_before2
						. $icon_code
						. $txt_after1
						. $txt_after2
						. '<a' . $matches[2] . '>'
						. $matches[3] . '</a>';
				}
				/* after */
				$regex = '/<a(.*?)>(.*?)<\/a><img(.*?)>/i';
				preg_match($regex,$html_string,$matches);
				if ($matches && $found==false)
				{
					$html_string =
						'<a' . $matches[1] . '>'
						. $matches[2] . '</a>'
						. $txt_before1
						. '<img' . $matches[3] . '>'
						. $txt_before2
						. $icon_code
						. $txt_after1
						. $txt_after2;
				}
				/* in tag before*/
				$regex = '/<a(.*?)><img(.*?)>(.*?)<\/a>/i';
				preg_match($regex,$html_string,$matches);
				if ($matches && $found==false)
				{
					$html_string =
						'<a' . $matches[1] . '>'
						. $txt_before1
						. '<img' . $matches[2] . '>'
						. $txt_before2
						. $icon_code
						. $txt_after1
						. $txt_after2
						. $matches[3] . '</a>';
				}
				/* in tag after*/
				$regex = '/<a(.*?)>(.*?)<img(.*?)><\/a>/i';
				preg_match($regex,$html_string,$matches);
				if ($matches && $found==false)
				{
					$html_string =
						'<a' . $matches[1] . '>'. $matches[2]
						. $txt_before1
						. '<img' . $matches[3] . '>'
						. $txt_before2
						. $icon_code
						. $txt_after1
						. $txt_after2
						 . '</a>';
				}
			}
			return $html_string;
    	}

    	/**
    	 * Throttling http requests
    	 * To not send out a massive amount of a requests we set the amount of requests
    	 * to go out to every 30 seconds
    	 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
    	 * @param $favicon
    	 */
    	function Timebuffer($favicon)
    	{
    		global $wpdb;
			$table_name_2 = Database::TableName2();
			$state = $favicon->GetState();
			// WP_FAVICON_REQUEST_TIMEOUT seconds between icons retrieval
			//
			// Dreamhost support blocked the  plugin because of the following query
			// SELECT itime FROM arc_http_request_cache WHERE unix_timestamp(itime) >
			// unix_timestamp(now()) - 30;
			//
			// justin@dreamhost.com mails me that performance would improve when the 
			// convert would not be in the query, so convert column timestamp to unix timestamp 
			// and SELECT itime FROM arc_http_request_cache WHERE itime >
			// unix_timestamp(now()) - 30;
			//
			// @todo replace with http://stackoverflow.com/questions/307438/how-can-i-tell-when-a-mysql-table-was-last-updated
			//
										
			try 
			{
				// takes about 0.2435 seconds
				//$row_uri = $wpdb->get_results(	"SELECT MAX(itime), NOW() FROM $table_name_2", ARRAY_A);
				$sql = "SELECT UPDATE_TIME, NOW() FROM information_schema.TABLES WHERE `TABLE_NAME` = '$table_name_2';";
				$row_uri = $wpdb->get_results(	$sql, ARRAY_A);
			} 
			catch  (Exception $e) 
			{
				Log::F($favicon,'Could not Get Response ' . $e->getMessage());
			}
			if (empty($row_uri) || !is_array($row_uri) || $row_uri[0]["UPDATE_TIME"] == NULL)
			{
				Log::M('no request cache entry');
				// no entry so go ahead
				$favicon->SetState(true);
				return $favicon;
			}
			else    
			{
			
				// make sure we have the correct time offset
				$latest_timestamp_in_database =  date_create($row_uri[0]["UPDATE_TIME"]);
				$latest_timestamp_in_database = $latest_timestamp_in_database->getTimeStamp();
				
				$current_date_timestamp_in_database =  date_create($row_uri[0]["NOW()"]);
				$current_date_timestamp = $current_date_timestamp_in_database->getTimestamp();
				
				$difference = $current_date_timestamp - $latest_timestamp_in_database;
				Log::M($current_date_timestamp . " - " . $latest_timestamp_in_database . " = " . $difference);
				
				//$difference = $current_date->getTimestamp() - $latest_timestamp_in_database->getTimeStamp();
				//Log::M($difference);
				//ob_start();
				//var_dump($entry_diff);
				//$result = ob_get_clean();
				//Log::M($result);
				if ($difference > WP_FAVICON_REQUEST_TIMEOUT) 
				{
					$favicon->SetState(true);
					return $favicon;
				}	
			}
			$favicon->SetState(false);
			return $favicon;

			
		}

    	/**
    	 * Check before actual HTTP calls
    	 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
    	 * @param $favicon
    	 * @param $response
    	 */
    	function PreHttp($favicon, $response)
    	{
    		if ($dbrequest=$this->CheckRequest($favicon))
			{
				if ($dbrequest['request_response_code'])
				{
					$request = array();
					$response['response']['code'] = $dbrequest['request_response_code'];
					$response['headers'] = unserialize($dbrequest['request_headers']);
					$response['body'] = $dbrequest['request_body'];
				}
				elseif ($dbrequest['request_response_message'])
				{
					$response = new \WP_Error('http_request_failed',
						sprintf(__('%s'), $dbrequest['request_response_message']));
				}
			}
			return $response;
    	}

    	/**
    	 * Log a request after a http call
    	 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
    	 * @param unknown_type $favicon
    	 * @param unknown_type $response
    	 */
    	function PostHttpFull($favicon, $response)
    	{
    		$this->LogRequest($favicon, $response);
    	}

    	/**
    	 * log a header request after a http request
    	 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
    	 * @param unknown_type $favicon
    	 * @param unknown_type $response
    	 */
		function PostHttpHead($favicon, $response)
    	{
    		if (!( !is_wp_error( $response ) &&
			 (in_array($response['response']['code'], array('200','404','405')))))
			{
				$this->LogRequest($favicon, $response);
			}
    	}

		/**
		 * Log a request to the request cache
		 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $uri
		 * @param $response
		 */
		function LogRequest($favicon, $response)
		{
			global $wpdb;
			$table_name_2 = Database::TableName2();

			$uri = $favicon->LastUriAddedToUriArr();
			$response_message = '';
			$response_body = '';
			$response_headers = '';
			$response_code = '';
			$response_cookies = '';

			if (is_wp_error( $response ))
			{
				$response_message = $response->get_error_message();
			}
			else
			{
				if (array_key_exists('body',$response))
				{
					$response_body = $response['body'];
				}
				if (array_key_exists('headers',$response))
				{
					$response_headers = serialize($response['headers']);
				}
				if (array_key_exists('response',$response))
				{
					$subresponse = $response['response'];
					if (array_key_exists('code',$subresponse))
					{
						$response_code 	= $subresponse['code'];
					}
					if (array_key_exists('message',$subresponse))
					{
						$response_message 	= $subresponse['message'];
					}
				}
				if (array_key_exists('cookies',$response))
				{
					$response_cookies = serialize($response['cookies']);
				}
			}
			
			try {
				$wpdb->insert($table_name_2,array(
				'uri' 						=> $uri,
				'uri_hash' 					=> md5($uri),
				'request_headers'			=> $response_headers,
				'request_cookies'			=> $response_cookies,
				'request_response_code'		=> $response_code,
				'request_response_message' 	=> $response_message,
				'request_body' 				=> $response_body,
				));				
			} catch (Exception $e) {
				Log::F($favicon,'Could not write Response for '.
					$e->getMessage());
			}
			Log::F($favicon,' written in Request Db');
		}


	   /**
		 * Check and returns historical requests from the Request Cache
		 * @since 0.5.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $uri
		 */
		function CheckRequest($favicon)
		{
			global $wpdb;
			$table_name_2 = Database::TableName2();
			$uri = $favicon->LastUriAddedToUriArr();

			try {
				$row_uri = $wpdb->get_results(	"SELECT * " .
					"FROM $table_name_2 ".
					"WHERE `uri_hash` = '" . md5($uri) . "';",
					ARRAY_A);
			} catch  (Exception $e) {
				Log::F($favicon,'Could not Get Response for '.
					$uri . " : " . $e->getMessage());
			}
			if (!empty($row_uri) && is_array($row_uri))
			{
				Log::F($favicon,'retrieved from Request Cache');
				return $row_uri[0];
			}
			else
			{
				Log::F($favicon,'Not yet in Request Cache');
				return false;
			}
		}
	}
}

