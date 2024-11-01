<?php
namespace leau\co\wp_favicons_server;


if (!class_exists("\\leau\\co\\wp_favicons_server\\Log"))
{
	class Log
	{
		// singleton instance
		private static $instance;

		/**
		 * 
		 */
		private function __construct()
		{

		}
		
		/**
		 * 
		 */
		private function __clone()
		{
		}

		/**
		 * 
		 */
		public static function getInstance()
		{
			if(!self::$instance)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * 
		 * @param unknown_type $msg
		 */
		public static function L($msg)
		{
			$instance = self::getInstance();
			$instance->WriteLogLine(current_time("mysql") . " - " . $msg . "\n");
		}

		/**
		 * 
		 */
		public static function LL()
		{
			$instance = self::getInstance();
			$instance->WriteLogLine("----------------------------------------------------\n");
		}

		/**
		 * 
		 * @param unknown_type $msg
		 */
		public static function M($msg)
		{
			$call = debug_backtrace(false);
			// @todo : never hardcode this stuff...
			$class = str_replace('leau\\co\\wp_favicons_server\\', '', $call[1]['class']);
			$function = $call[1]['function'];
			$line = $call[0]['line'];

			$nLine = current_time("mysql") . " - "
			. $class . "::"
			. $function . "("
			. $line . ") - "
			. $msg . "\n";

			// Object of class IXR_Error could not be converted to string in class-log.php
			// on line 74
			
			$instance = self::getInstance();
			$instance->WriteLogLine($nLine, 'REMOTE.LOG');
		}

		/**
		 * 
		 * @param unknown_type $favicon
		 * @param unknown_type $msg
		 */
		public static function F($favicon,$msg='')
		{
			$instance = self::getInstance();
			if (! is_object($favicon)) {
				$instance->WriteLogLine('ERROR - no favicon object - error in server configuration');
				return;
			}
						
			$uri = $favicon->GetUri();
			$call = debug_backtrace(false);
			// @todo : never hardcode this stuff...
			$class = str_replace('leau\\co\\wp_favicons_server\\', '', $call[1]['class']);
			$function = $call[1]['function'];
			$line = $call[0]['line'];

			$nLine = current_time("mysql") . " - " . $uri . " - "
			. $class . "::"
			. $function . "("
			. $line . ") - "
			. $msg . "\n";
			
			$instance->WriteLogLine($nLine);
		}

		/**
		 * 
		 * @param unknown_type $msg_line
		 */
		public static function WriteLogLine($msg_line, $file = 'DEBUG.LOG')
		{
			if (1==Config::GetOptionsArrayValue(Config::GetPluginSlug().'debuglog'))
			{
				$dir=Config::GetOptionsArrayValue(Config::GetPluginSlug()
				. 'upload_dir_cache');
				$file=$dir.$file;
				if (!is_dir($dir)) {
					@mkdir($dir, 0755, true);
				}
				$fh = fopen($file, 'a') or die("can't open file");
				fwrite($fh, $msg_line);
				fclose($fh);
			}
		}

	}
}
