<?php
/** 
 * Performs an XML RPC call
 * 
 * goes out and does an XMLRPC call with a piece of html containing hrefs and comes 
 * back with the same piece of html containing hrefs and image tags pointing to the
 * favicons
 * 
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 * @copyright GPL 2
 */
namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\XMLRPCCall")) {

	class XMLRPCCall {

		public  $_filter = 'the_content';
		
		/**
		 * 
		 * @param unknown_type $moduleID
		 * @param unknown_type $pluginID
		 */
		public function __construct($moduleID, $pluginID)
		{
			/* set the new filter in the object for reference */
			$this->_filter = Config::GetModulePluginVar($moduleID, $pluginID,
					'filter_context_name');
			
			/* add a filter to the content type (which calls the server for the transformation */
			add_filter($this->_filter, array($this,'AddIcons'), 99);				
		}
		
		/**
		 * 
		 * @param unknown_type $content
		 */
		public function AddIcons($content)
		{
			$r_content = $content;			
			if (is_string($content) && isset($content) && trim($content)!='')
			{
				$r_content = $this->CallServerWith($content);
			}
			// $md5_content = md5($content);
			return $r_content;				
		}
				
		/**
		 * 
		 * @param string $content
		 */
		public function CallServerWith($content)
		{
			//$content = '<a href="http://www.google.com">google</a>';
			
			$uri = Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'server_uri');
			$usr = Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'server_usr');
			$pwd = Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'server_pwd');		
			$blogid = Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'blog_id');		
			$namespace = 'wpserver';
			$method = 'favicon';
			$charset = get_option('blog_charset');
						
			// @todo perform a pre-check that the content actually contains 
			// hrefs
			
			// see: http://php.net/manual/en/function.xmlrpc-encode-request.php
			// note that this has to be installed! ( --with-xmlrpc )
			$request = \xmlrpc_encode_request(
				$namespace . '.' . $method
				, array($blogid, $usr, $pwd,
					array(
						'blog' => site_url(),
						'text' => $content
					)
				)
				, array('encoding' => $charset, 'escaping' => 'markup', 'output_type' => 'xml')
			);
			
			
			//  cache these requests using the transients http://codex.wordpress.org/Transients_API
			$transient_key = md5($content);		
			
			// while debugging uncomment...
			//delete_transient( $transient_key );
			
			
			if ( false === ( $return_content = get_transient( $transient_key ) ) ) {
				// It wasn't there, so regenerate the data and save the transient
				//
				// @todo a timeout of 5 seconds is for some large chunks of text
				// too slow, make it configurable
				$response = wp_remote_get( $uri
					, array (
					'method' => 'POST', // should be post
					'timeout' => 5,
					'redirection' => 5,
					'httpversion' => '1.0',
					'user-agent' => "Favcollector/1.0 (info@favcollector.com http://www.favcollector.com/)/node " . get_bloginfo( 'url'),
					'blocking' => true,
					'headers' => array(),
					'cookies' => array(),
					'body' => $request,
					'compress' => false,
					'decompress' => true,
					'sslverify' => false
    				)
				);				
				$return_content = $content;
				
				// DEBUG
				//echo "<!-- \n"; 
				//print_r($response);
				//echo " --> \n";
				
				
				if ( is_wp_error( $response ) ) {
					//Log ERROR				
				} 
				elseif (array_key_exists('response',$response))
				{
					$response_response = $response['response'];
					if (array_key_exists('code',$response_response))
					{
						if ($response_response['code'] != 200)
						{
							// log ERROR		
						}
						else 
						{
							if (array_key_exists('body',$response))
							{
								$return_content = xmlrpc_decode($response['body'], $charset);
								if(is_array(xmlrpc_decode($response['body'], $charset)))
								{
									echo "Error:"; 									
									print_r($return_content);
									$return_content = $content;
									// only set the transient once we get something
									set_transient($transient_key,$return_content,WP_FAVICON_TRANSIENT_TIMEOUT);
								}
								elseif(trim($return_content) == '')
								{
									// @todo do trick: insert google refs here
									// probably the server took too long
									
									$return_content = $content;
								}								
							}
							else
							{
								// log ERROR
							}
						}
					}
					else
					{
						// log ERROR
					}
				}			
				// set the timeout out transients on 1 hour 
				
			}
							
			return $return_content;
		}		
		
	}

}