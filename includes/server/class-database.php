<?php

/**
 * Database functions for wp-favicons-server in a static class
 * This php file has not been optimized yet
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 * @copyright GPL 2
 *
 * dependencies:
 * - /includes/class-config.php : because it reads some config variables
 * - /includes/class-log.php : because it uses the package log methods
 *
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\Database"))
{
	/**
	 * Database functions for wp-favicons-server
	 * @package WP-Favicons
	 * @since 0.4.0
	 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
	 * @copyright GPL 2
	 */
	class Database
	{
		/**
		 * generic: singleton
		 * @var unknown_type
		 */ 
		private static $instance;

		/**
		 * generic: to prevent external instantiation
		 */
		private function __construct() { }
		
		/**
		 * generic: prevents cloning
		 */
		private function __clone() { }

		/**
		 * generic: Makes it possible to instantiate itself to call its own methods
		 * @return \leau\co\wp_favicons_server\Database
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
		 * Enter description here ...
		 * @param string $call
		 */
		public static function Config($call)
		{
			
		}
		
		/**
		* old table name : only for backwards compatibility
		* @return string
		*/
		public static function TableNameUri()
		{
			global $wpdb;
			return $wpdb->prefix . "wpfavicons_uri";
		}
		
		/**
		 * old table name : only for backwards compatibility
		 * @return string
		 */
		public static function TableNameFavicon()
		{
			global $wpdb;
			return $wpdb->prefix . "wpfavicons_icon";
		}

		/**
		* old table name : only for backwards compatibility
		* @return string
		*/
		public static function old_TableName2()
		{
			global $wpdb;
			return $wpdb->prefix . "wpfavicons_2";
		}
		
		/**
		 * returns main table name (which contains all uris and favicon hashes
		 * @return string
		 */
		public static function TableName1()
		{
			global $wpdb;
			return $wpdb->prefix . "wpfavicons_1";
		}
		

		
		/**
		 * returns cache table name (which contains all http requests)
		 * Enter description here ...
		 * @return string
		 */
		public static function TableName2()
		{
			global $table_prefix;
			return $table_prefix . "http_request_cache";
		}
		
		
		/**
		 * Installs the databases
		 * update: Justin K from Dreamhost Support mailed me that the timestamp
		 * conversion is blowing up servers and suggested that i use a timestamp
		 * in the first place + no conversion + an index on the time, however,
		 * i might just shift this to the client side now since i can throttle the 
		 * amount of client requests.  
		 */
		public static function Install()
		{
			global $wpdb;
			$instance = self::getInstance();
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$table_name_1 = $instance->TableName1();
			$table_name_2 = $instance->TableName2();

			/* delete tables we had in schema 0.0.1 if the user has that */
			$db_version = Config::GetOptionsArrayValue('wpfavicons_icon_db_version');
			if (version_compare($db_version, "0.5.6", "<"))
			{
				$table_name_uri = $instance->TableNameUri();
				$table_name_icon = $instance->TableNameFavicon();
				$old_requests = $instance->old_TableName2();

				if( $wpdb->get_var("SHOW TABLES LIKE '$table_name_icon'")
					== $table_name_icon) {
					$sql = "DROP TABLE IF EXISTS `" . str_replace('`', '', $table_name_icon) . "`;";
					$wpdb->query( $sql );
				}

				if( $wpdb->get_var("SHOW TABLES LIKE '$table_name_uri'")
					== $table_name_uri) {
					$sql = "DROP TABLE IF EXISTS `" . str_replace('`', '', $table_name_uri) . "`;";
					$wpdb->query( $sql );
				}

				if( $wpdb->get_var("SHOW TABLES LIKE '$old_requests'")
					== $old_requests) {
					$sql = "DROP TABLE IF EXISTS `" . str_replace('`', '', $old_requests) . "`;";
					$wpdb->query( $sql );
				}
			}
			

			//
			// the creation of the SQL databases has been split because
			// they have been incrementally introduced
			//

			/* Icon Table
			 * id: unique id (UNIQUE)
			 * uri: the full uri
			 * uri_hash: the hashed uri (UNIQUE)
			 * favicon_uri: the full uri of the icon
			 * favicon_uri_hash: the hashed favicon uri
			 * favicon_type: the type e.g. png we always verify
			 * favicon_filters: list of filters applied to the favicon
			 * favicon_source: information where we got the icon from
			 * favicon_default: boolean is it a default or not
			 * favicon_parent: the holder of the same icon
			 * comment: for comments
			 */
			$sql1 = "
CREATE TABLE `" . str_replace('`', '', $table_name_1) . "` (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  uri varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  uri_hash varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  favicon_uri varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  favicon_uri_hash varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  favicon_type varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  favicon_filters varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  favicon_source varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  favicon_default tinyint(1) NOT NULL,
  favicon_parent bigint(20) NOT NULL,
  comment varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  itime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY uri_hash (uri_hash)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			";

			/*
			 * Request Table
			 * Used for caching requests. Especially useful during testing
			 * since we do not want to bother sites with countless requests
			 * time and time again.
			 *
			 *
			 */
			$sql2 = "
CREATE TABLE `" . str_replace('`', '', $table_name_2) . "` (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  uri varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  uri_hash varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  request_headers varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  request_cookies varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  request_response_code varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  request_response_message varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  request_body mediumblob NOT NULL,
  itime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY uri_hash (uri_hash)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			";

			if( $wpdb->get_var("SHOW TABLES LIKE '$table_name_1'") != $table_name_1)
			{
				$wpdb->query( $sql1 );
			}

			if( $wpdb->get_var("SHOW TABLES LIKE '$table_name_2'") != $table_name_2)
			{
				$wpdb->query( $sql2 );
			}

			dbDelta($sql1.$sql2);
			Config::AddOptions(Array('wpfavicons_uri_db_version' => '0.5.6'));

		}

		


		/**
		 * Empties the database cache
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		public static function EmptyIconCache()
		{
			global $wpdb;
			$instance = self::getInstance();
			$table_name_1 = $instance->TableName1();
			$wpdb->query("DELETE FROM $table_name_1;");
			return;
		}

		/**
		 * Empties the Request cache
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		public static function EmptyRequestCache()
		{
			global $wpdb;
			$instance = self::getInstance();
			$table_name_2 = $instance->TableName2();
			$wpdb->query("DELETE FROM $table_name_2;");
			return;
		}

		/**
		 * Gets the latest icons
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param $amount
		 * @param $default
		 */
		public static function getLatestIcons($amount=25,$default=false)
		{
			global $wpdb;
			$instance = self::getInstance();

			$sql = "SELECT uri " .
				" FROM " . $instance->TableName1() .
				" WHERE favicon_parent = 0 ";
			if (false==$default) {
				$sql .= " AND favicon_default = 0 ";
			}
			$sql .= " ORDER BY id DESC LIMIT 0 , $amount;";

			$row_icons = $wpdb->get_results($sql,
				ARRAY_A);
			if (is_array($row_icons) && !empty($row_icons))
			{
				return $row_icons;
			}
			return false;
		}


		/**
		 * 
		 * Enter description here ...
		 * @return unknown|boolean
		 */
		public static function SourcesStats()
		{
			global $wpdb;
			$instance = self::getInstance();
			$table_name_1 = $instance->TableName1();
			try {
				$row_sources = $wpdb->get_results(
					"SELECT favicon_source, COUNT( favicon_source ) AS NumOccurrences " .
					"FROM " . $table_name_1 . " GROUP BY favicon_source " .
					"HAVING (COUNT( favicon_source ) >1);", ARRAY_A);
			} catch (Exception $e) {
				Log::M('READ - Sources Stats' . $e->getMessage());
			}
			if (is_array($row_sources) && !empty($row_sources))
			{
				return $row_sources;
			}
			else
			{
				return false;
			}
		}
	}
}

