<?php
/* Server XMLRPC class extensison allowing more dispatchers
 * 
 * via http://wordpress.stackexchange.com/questions/39473/custom-xmlrpc-method-plus-authentication-of-user-woocommerce-order
 * 
 * dependencies: none
 * 
 * @package WP-Favicons
 * @since 0.030
 * @author Edward de Leau <e@leau.net, http://edward.de.leau.net>
 * @copyright gpl 2
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\MasterXMLRPC"))
{
/**
 * 
 * @author cogmios
 *
 */
abstract class MasterXMLRPC {
	protected $calls = Array();
	protected $namespace = "wpserver";

	/**
	 * 
	 * @param unknown_type $namespace
	 */
	function __construct($namespace){
		$this->namespace = $namespace;
		$reflector = new \ReflectionClass($this);
		foreach ( $reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if ($method->isUserDefined() && $method->getDeclaringClass()->name != get_class()){
				$this->calls[] = $method->name;
			}
		}
		add_filter('xmlrpc_methods', array($this, 'xmlrpc_methods'));
	}

	/**
	 * 
	 * @param unknown_type $methods
	 * @return multitype:\leau\co\wp_favicons_server\MasterXMLRPC string
	 */
	public function xmlrpc_methods($methods)
	{
		foreach ($this->calls as $call){
			$methods[$this->namespace . "." . $call] = array($this, "dispatch");
		}
		return $methods;
	}

	/**
	 * 
	 * @param unknown_type $args
	 * @return unknown|string
	 */
	public function dispatch($args){
		global $wp_xmlrpc_server;
		
		$blogid 	= $args[0];		
		$username   = $args[1];
		$password   = $args[2];
		$data 		= $args[3];
		

		if ( !$wp_xmlrpc_server->login($username, $password) ) {
			//Log::M($wp_xmlrpc_server->error);
			//return $password;
			return $wp_xmlrpc_server->error;
		}	

		$call = $this->get_called_method();

		if (method_exists($this, $call)){
			$status = call_user_func_array(array($this, $call), array($data));
			//Log::M('status ' . $status);
			return $status;
		}else{
			Log::M("XMLRpc Method not allowed");
			return "Method not allowed";
		}

	}

	/**
	 * 
	 * @return unknown
	 */
	private function get_called_method(){
		global $wp_xmlrpc_server;
		$call = $wp_xmlrpc_server->message->methodName;
		$pieces = explode(".", $call);
		return $pieces[1];
	}

}

}
