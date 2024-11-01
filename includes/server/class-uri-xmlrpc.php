<?php
/*
 * extends the xmlrpc with our method "wpserver.favicon"
 * takes some content returns content with favicon uris
 * 
 * dependencies: 
 * - /includes/class-xmlrpc.php : the abstract class it is based on
 * 
 * @package WP-Favicons
 * @since 0.030
 * @author Edward de Leau <e@leau.net, http://edward.de.leau.net>
 * @copyright gpl 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\uri_xmlrpc"))
{

class uri_xmlrpc extends MasterXMLRPC {

	public function favicon($data){
				
		if (!isset($data["blog"])) {
			Log::M('missing blog');
			return "Missing 'blog' parameter";
		}	

		if (!isset($data["text"])) {
			Log::M('missing text');
			return "Missing 'text' parameter";
		}	

		$blog = $data["blog"];
		$text = $data["text"];
		
		// check transient for existence otherwise start plugin and look for icons
		$transient_key = md5($text);
		if ( false === ( $return_content = get_transient( $transient_key ) ) ) {		
			// get the icon, status and generate html code
			$wp_favicon_the_content = new MetaData('module','plugin');
			$return_content = apply_filters( Config::GetPluginSlug() . 'xmlrpc_text', $text);
			set_transient($transient_key,$return_content,WP_FAVICON_TRANSIENT_TIMEOUT_SERVER);
		}
			
		return $return_content;
	}
}

}


