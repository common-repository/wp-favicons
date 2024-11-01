<?php
/**
 * Contains Classes for the Href operations.
 *
 * @package WP-Favicons
 * @since 0.030
 * @author Edward de Leau <e@leau.net, http://edward.de.leau.net>
 * @copyright gpl2
 */
namespace leau\co\wp_favicons_server;
/**
 * abstract class for WordPress Href operations
 *
 * The following abstract class provides methods to work with HTML Hrefs
 * such as putting strings in front or after a href
 *
 * @package WP-Favicons
 * @since 0.1
 * @author Edward de Leau <e@leau.net>
 * @copyright Edward de Leau, http://edward.de.leau.net
 */
if (!class_exists("\\leau\\co\\wp_favicons_server\\MetaData")) {

	class MetaData {

		const HTML_REF_REGEX2 = '/<a(.*?)href=[\'"](.*?)[\'"](.*?)>(.*?)<\\/a>/i';
		public $mStrNotAllowedExtensions = '';
		public $_filter = 'the_content';

		/**
		 * Method: constructor
		 * @since 0.1
		 */
		public function __construct($moduleID, $pluginID)
		{
			/* set the new filter in the object for reference */
			//$this->_filter = Config::GetModulePluginVar($moduleID, $pluginID,
			//	'filter_context_name');

						
			add_filter(Config::GetPluginSlug() . 'xmlrpc_text', array($this,'ReplaceAll'), 9);
			
			$admin_options = Config::GetOptionsAsArray();
			if (isset($admin_options[Config::GetPluginSlug() . 'exclude_filetypes'])) {
				$this->SetNotAllowedExtension($admin_options[Config::GetPluginSlug() .
				'exclude_filetypes']);
			}
		}

		/**
		 * Not Allowed Extensions
		 * Makes sure we do not parse choosen extensions
		 * @param $strNotAllowedExtensions
		 */
		public function SetNotAllowedExtension( /*string*/ $strNotAllowedExtensions)
		{
			if(!is_string($strNotAllowedExtensions)){
				throw new Exception('Invalid parameter type!');
			}
			$this->mStrNotAllowedExtensions = $strNotAllowedExtensions;
		}



		/**
		 * Validates the Href
		 * Pretty important to determine what we want to support,
		 * @param unknown_type $arrUrlMatches
		 */
		private function ValidateHTMLHref($arrUrlMatches)
		{
			// let's start to suppose it is valid (be positive :))
			$bool_href_valid=true;

			// give the matches some decent names again
			$str_original_uri_attributes_before = $arrUrlMatches[1];
			$str_original_uri_uri = $arrUrlMatches[2];
			$str_original_uri_attributes_after = $arrUrlMatches[3];
			$str_original_uri_display_text = trim($arrUrlMatches[4]);

			/*
			 * (!) protocols supported:
			 * - http://
			 * - https://
			 * - file://
			 * no refinement on placing other content within it at this stage
			 * STRICT! In next release some DOM within these tags support
			 */
			if (!((substr($str_original_uri_uri,0,7) == 'http://' ) ||
				  (substr($str_original_uri_uri,0,8) == 'https://') ||
				  (substr($str_original_uri_uri,0,7) == 'file://' ) )
			    )
			{
				return false;
			}

			/* allow no tags in href strict */
			if (strstr(trim($str_original_uri_display_text), '<'))
			{
				return false;
			}


			/* user defined extensions will also not be replaced */
			$str_original_uri_type_extension
				= EdlUtils::GetUriExtension($str_original_uri_uri, '.', 1, 1);
			if ($str_original_uri_type_extension != false)
			{
				if (stristr($this->mStrNotAllowedExtensions,
					$str_original_uri_type_extension) != false)
				{
					$bool_href_valid=false;
				}
			}

			return $bool_href_valid;
		}

	   /**
		* Called for each Href found by ReplaceAll
		* @param array $arrUrlMatches
		* @return string
		*/
		private function AddMetaDataToUri($arrUrlMatches)
		{
			$full_return_string='';
			$uri = $arrUrlMatches[2];
			$found = strstr($arrUrlMatches[2], '#', true);
			if ($found)
			{
				$uri = $found;
			}
			// @todo add stylesheet icon option placement in a tag
			/* if the href is valid add things otherwise dont */
			if ($this->ValidateHTMLHref($arrUrlMatches) &&  $arrUrlMatches[2] != '')
			{
				$full_return_string = apply_filters( Config::GetPluginSlug() . 'metadata_before_href'
				, $full_return_string, $uri, $this->_filter);

				$full_return_string = $full_return_string .= '<a' . $arrUrlMatches[1] . 'href="'
				. $arrUrlMatches[2] . '"' . $arrUrlMatches[3] .'>';

				$full_return_string = apply_filters( Config::GetPluginSlug() . 'metadata_inside_before_href'
				, $full_return_string, $uri, $this->_filter);

				$full_return_string .= $arrUrlMatches[4];

				$full_return_string = apply_filters( Config::GetPluginSlug() . 'metadata_inside_after_href'
				, $full_return_string, $uri, $this->_filter);

				$full_return_string .= 	'</a>';

				$full_return_string = apply_filters( Config::GetPluginSlug() . 'metadata_after_href'
				, $full_return_string, $uri, $this->_filter);

				return $full_return_string;
			}
			else
			{
				return '<a' . $arrUrlMatches[1] . 'href="' . $arrUrlMatches[2]
					. '"' . $arrUrlMatches[3] .'>' . $arrUrlMatches[4] . '</a>';
			}
		}
		

		/**
		 * this public method is the entry point for classes
		 * to replace hrefs in body text
		 *
		 * @param string $content
		 * @return string $changedContent
		 */
		public function ReplaceAll($content)
		{
			$r_content = '';
			
			if (is_string($content) )
			{
				Log::L($r_content);
				$r_content = preg_replace_callback(self::HTML_REF_REGEX2,
					array($this,'AddMetaDataToUri'), $content);
			}
			return $r_content;
		}
	}
}
