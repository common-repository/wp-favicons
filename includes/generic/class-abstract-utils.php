<?php
/**
 * Contains Util Classes.
 *
 * General Utilities
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 * 
 * @todo make this one a generic one used over client and server plugin
 */

namespace leau\co\wp_generic;

if (!class_exists("\\leau\\co\\wp_generic\\AbstractUtils"))
{

	/**
	 * Class: EdlUtils
	 *
	 * @since 0.1
	 * @author Edward de Leau <e@leau.net>
	 * @copyright GPL 2
	 * @static class containing utility functions
	 */
	class AbstractUtils
	{
		CONST LOGLEVEL = 3;

		// singleton instance
		private static $instance;

		// private constructor function
		// to prevent external instantiation
		private function __construct()
		{

		}
		private function __clone()
		{
		}

		// getInstance method
		public static function getInstance()
		{
			if(!self::$instance)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Writes a file to a directory
		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $dir
		 * @param $file
		 * @param $content
		 */
		public static function WriteFile($dir,$file,$content)
		{
			if (!is_dir($dir)) {
				@mkdir($dir, 0755, true);
			}
			$fh = fopen($file, 'w') or die("can't open file");
			fwrite($fh, $content);
			//echo "<!-- " . $file ." written -->";
			fclose($fh);
		}

		// from http://php.net/manual/en/function.serialize.php
		/**
		 * converts an array to string
		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $array
		 * @param $level
		 */
		public static function array2str($array,$level=1)
		{
			$str = array();
			foreach($array as $key=>$value)
			{
				$nkey = base64_encode($key);
				$nvalue = is_array($value)?'$'.base64_encode(array2str($value)) : (string)base64_encode($value);
				$str[] = $nkey.'&'.$nvalue;
				//	printf("Encoded %s,%s to %s,%s\n",$key,$value,$nkey,$nvalue);
			}
			return implode('|',$str);
		}

		/**
		 * converts a string to an array
		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $str
		 */
		public static function str2array($str)
		{
			$rest = array();
			if(strpos($str,'|')>0)
			{
				$array = explode('|',$str);
			}
			else
			{
				$array=array($str);
			}
			foreach($array as $token)
			{
				list($key,$value) = explode('&',$token);
				$nkey=base64_decode($key);
				$nvalue = (substr($value,0,1) != '$' ? base64_decode($value) : str2array(base64_decode(substr($value,1))) );
				$rest[$nkey] = $nvalue;
				//printf("Decoded %s,%s to %s,%s\n",$key,$value,$nkey,$nvalue);
			}
			return $rest;
		}

		/**
		 * Some admin fields we use currently
		 * Will add some more in later releases
		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param $field
		 * @param $fieldname
		 * @param $fieldvalue
		 * @uses checked as defined in \wp-includes\general-template.php
		 *
		 * @todo 0.5.0 refine e.g. esc and extend these helpers with more fields
		 */
		public static function AdminForm($field,$fieldname,$fieldvalue)
		{
			switch ($field['type']) {
				case 'radio_bool':
					echo '<input ';
					echo 'type="radio" ';
					echo 'name="' . $fieldname . '" ';
					echo 'id="' . $fieldname . '" ';
					echo ' value="1" ';
					checked( '1', $fieldvalue );
					echo ' /> yes <br />';

					echo '<input ';
					echo 'type="radio" ';
					echo 'name="' . $fieldname . '" ';
					echo 'id="' . $fieldname . '" ';
					echo ' value="0" ';
					checked( '0', $fieldvalue );
					echo " /> no";
					break;
				case 'text':
					echo '<input ';
					echo 'type="text" ';
					echo 'name="' . $fieldname . '" ';
					echo 'id="' . $fieldname . '" ';
					echo 'value="' . $fieldvalue . '" ';
					echo 'size="150" ';
					echo " />";
					break;
				case 'text_no_change':
					echo '<input ';
					echo 'type="text" ';
					echo 'name="' . $fieldname . '" ';
					echo 'id="' . $fieldname . '" ';
					echo 'value="' . $fieldvalue . '" ';
					echo 'size="150" disabled';
					echo " />";
					break;
				case 'textarea':
					echo '<textarea ';
					echo 'name="' . $fieldname . '" ';
					echo 'rows="5" ';
					echo 'cols="80" ';
					echo '>';
					echo $fieldvalue;
					echo '</textarea>';
					break;
				case 'freetext':
					echo $fieldvalue;
					break;
			}

		}

		/**
		 * Writes a DEBUG log in the wp favicon cache dir if WP_DEBUG is on
		 * Handy for debugging
		 * @since 0.4.0
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @param unknown_type $url
		 * @param unknown_type $msg
		 */
		public static function favicon_page_msg($url,$msg) {
			Log::L($url.$msg);
		}

		/**
		 * Gets a file extension
		 * Just to put a default in, the verifier will afterwards place
		 * the correct extension in, so the result of this method is never
		 * shown to the user only to possible 3rd party plugins
 		 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
		 * @since 4.4.6
		 */
		public static function GetExtension($imagePath)
		{
			$ext = end(explode(".",$imagePath));
			if ($ext != "jpeg" || $ext='wbmp')
			{
				$ext = substr($ext,0,3);
			}
			return $ext;

		}

		/**
		 * get a URI extension based on a the last letters after a dot
		 * @since 4.4.6
		 * @todo merge with previous one
		 */
		public static  function GetUriExtension($strHayStack, $strNeedle,
			$boolLeftInclusive = 0, $boolRightInclusive = 0)
		{
			// overview of all variables used in this method
			$str_uri='';
			$str_extension='';

			if (strrpos($strHayStack, $strNeedle) !== false)
			{
				$str_uri =  substr($strHayStack, 0, strrpos($strHayStack, $strNeedle) + $boolLeftInclusive);
				$boolRightInclusive = ($boolRightInclusive == 0) ? 1 : 0;
				$str_extension = substr(strrchr($strHayStack, $strNeedle), $boolRightInclusive);
				return $str_extension;
			}
			else
			{
				return false;
			}
		}
		/**
		 * Get all directories recursively
		 * @param unknown_type $dir
		 * @return Ambigous <multitype:, string>
		 */
		function GetDirectories($dir)
		{
			$dirs = array();
			if(substr($dir,-1) !== '/'){$dir .= '/';}
			if ($handle = opendir($dir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if (filetype($dir.$file) === 'dir' && $file != "." && $file != "..")
					{
						clearstatcache();
						$dirs[] .= $file;
						EdlUtils::GetDirectories($dir . $file);
					}
				}
				closedir($handle);
			}
			return $dirs;
		}

		/**
		 * Reads a directory for files
		 * @param $dir
		 * @param $recursive
		 */
		function process_dir($dir)
		{
			if (is_dir($dir))
			{
				for ($list = array(),$handle = opendir($dir); (FALSE !== ($file = readdir($handle)));)
				{
					if (	(	$file != '.' && $file != '..')
							&& (file_exists($path = $dir.'/'.$file))
							&& (strtolower(end(explode(".",$file))) == 'url')
						)
					{
						if (is_dir($path))
						{
							$list = array_merge($list, EdlUtils::process_dir($path));
						}
						else
						{
							$entry = array('filename' => $file, 'dirpath' => $dir);
							$entry['modtime'] = filemtime($path);
							$list[] = $entry;
						}
					}
				}
				closedir($handle);
				return $list;
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * Compare a filename
		 * @param $a
		 * @param $b
		 */
		function cmp_filename($a, $b)
		{
			return strnatcmp($a['filename'], $b['filename']);
		}

		/**
		 * Read an .ini file
		 * @param $filename
		 * @param $commentchar
		 */
		function readINIfile ($filename, $commentchar)
		{
			$array1 = file($filename);
			$array2 = '';
			$section = '';
			foreach ($array1 as $filedata)
			{
				$dataline = trim($filedata);
				$firstchar = substr($dataline, 0, 1);
				if ($firstchar!=$commentchar && $dataline!='')
				{
					//It's an entry (not a comment and not a blank line)
					if ($firstchar == '[' && substr($dataline, -1, 1) == ']')
					{
						//It's a section
						$section = strtolower(substr($dataline, 1, -1));
					}
					else
					{
						//It's a key...
						$delimiter = strpos($dataline, '=');
						if ($delimiter > 0)
						{
							//...with a value
							$key = strtolower(trim(substr($dataline, 0, $delimiter)));
							$value = trim(substr($dataline, $delimiter + 1));
							if (substr($value, 1, 1) == '"' && substr($value, -1, 1) == '"') 
							{ 
								$value = substr($value, 1, -1); 
							}
							$array2[$section][$key] = stripcslashes($value);
						}
						else
						{
							//...without a value
							$array2[$section][strtolower(trim($dataline))]='';
						}
					}
				}
				else
				{
					//It's a comment or blank line.  Ignore.
				}
			}
			return $array2;
		}

		/**
		 * Strip Weird stuff from filenames
		 * @param $filename
		 */
		function StripTagsFromFileName($filename)
		{
			$title = preg_replace('/\#([^\*]+)\# /', '', $filename);
			$title = preg_replace('/\{([^\*]+)\} /', '', $title);
			$title = preg_replace('/\{([^\*]+)\}/', '', $title);
			$title = basename($title , '.url');
			$title = basename($title , '.URL');
			return $title;
		}

	}
}
		
