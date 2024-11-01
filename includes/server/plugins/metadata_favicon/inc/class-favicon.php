<?php
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\Favicon"))
{
	class Favicon
	{
		private $_uri = '';
		private $_favicon_uri = '';
		private $_type = 'png';
		private $_filters = '';
		private $_source = '';
		private $_default = 0;
		private $_comment = '';
		private $_data = '';

		private $_db_found = false;
		private $_db_error = '';
		private $_new_in_db = false;

		private $_public_favicon_uri;

		private $_diskcache_physical_dir;
		private $_diskcache_relative_dir;
		private $_state;

		private $_http_uri_arr = array();
		private $_found = false;

		const FLAG_DB_READ = 'READ';
		const FLAG_DB_WRITE = 'WRITE';
		const FLAG_NO_ICON = 'EMPTY';
		

		function __construct($uri='')
		{
			$this->_uri = $uri;
		}

		function SetFaviconUri 	($uri) 		{$this->_favicon_uri	= $uri;		}
		function SetType		($type) 	{$this->_type		= $type;		}
		function SetFilters		($filters) 	{$this->_filters		= $filters;	}
		function SetSource		($source) 	{$this->_source		= $source;		}
		function SetDefault		($default) 	{
			if ($default==false || $default==0) {
				$this->_default = 0;
			}	
			else 
			{
				$this->_default = 1;
			}	
		}
		function SetComment		($comment) 	{$this->_comment		= $comment;	}
		function SetData		($data) 	{$this->_data		= $data;		}
		function SetPublicFaviconUri ($uri)	{$this->_public_favicon_uri = $uri; }

		function SetNewInDb() { $this->_new_in_db = true; }
		function GetNewInDb() { return $this->_new_in_db;  }

		function GetUri 		() 	{return $this->_uri;		}
		function GetFaviconUri 	() 	{return $this->_favicon_uri;}
		function GetType		() 	{return $this->_type;		}
		function GetFilters		() 	{return $this->_filters;	}
		function GetSource		() 	{return $this->_source;		}
		function GetDefault		() 	{return $this->_default;	}
		function GetComment		() 	{return $this->_comment;	}
		function GetData		() 	{return $this->_data;		}
		function GetPublicFaviconUri ()	{return $this->_public_favicon_uri;}

		function GetUriArr()          { return $this->_http_uri_arr; }
		function SetUriArr($uri)      { $this->_http_uri_arr = array($uri);}
		function EmptyUriArr()		  { $this->_http_uri_arr = array();}
	    function AddUriToUriArr($uri) { $this->_http_uri_arr[] = $uri;}
	    function LastUriAddedToUriArr(){ return end($this->_http_uri_arr); }
		function CountUriArr()		  { return count($this->_http_uri_arr); }

		function Found() { $this->_found = true;}
		function FoundStatus() {return $this->_found;}
		function BlockFoundIcon() {$this->_found = false;}

		function GetState() {return $this->_state;}
		function SetState($state) {$this->_state = $state;}

		/*************************************************************/

	   /**
		 * Checks the filetype of a data string
		 * @param string $data the image data
		 * @return string $filetype the image type
		 */
		function GetFileType()
		{
			/* we need a physical file to determine file type */
			//$admin_options = get_option('wp_favicons_options');
			$data = $this->GetData();
			$tempfilename = 'tempfavicon';
			$tempdir = Config::GetOptionsArrayValue(Config::GetPluginSlug() .
				'upload_dir_cache');
			$tempfile = $tempdir . $tempfilename;
			EdlUtils::WriteFile($tempdir, $tempfile,  $data);
			Log::F($this,'[VERIFY] '. $tempdir . $tempfilename . ' written');
			$filetype='';

			/* check type via exif_imagetype */

			if ( !function_exists( 'exif_imagetype' ))
			{
				if ( ( list($width, $height, $type, $attr) = getimagesize( $tempfile ) ) !== false )
				{
					$type_to_check = $type;
				}
				else
				{
					Log::F($this,'[VERIFY] no PHP EXIF and no PHP IMAGETYPE');
					$type_to_check = '';
				}
			}
			else
			{
				$type_to_check = exif_imagetype($tempfile);
			}

			switch($type_to_check)
			{
				case IMAGETYPE_ICO:
					$filetype = 'ico';
					break;
				case IMAGETYPE_GIF:
					$filetype = 'gif';
					break;
				case IMAGETYPE_JPEG:
					$filetype = 'jpg';
					break;
				case IMAGETYPE_PNG:
					$filetype = 'png';
					break;
				case IMAGETYPE_ICO:
					$filetype = 'png';
					break;
				case IMAGETYPE_BMP:
					$filetype = 'bmp';
					break;
				case IMAGETYPE_XBM:
					$filetype = 'xbm';
					break;
				case IMAGETYPE_WBMP:
					$filetype = 'wbmp';
					break;
				default:
					Log::F($this,'[VERIFY] '. $type_to_check . ' found however: not supported');
					$filetype = '';
					break;
			}
			return $filetype;
		}

		/**
		 *
		 * Enter description here ...
		 * @param $favicon
		 */
		function VerifyImage()
		{
			/*   check that GD is installed */
			if ( ! function_exists('imagecreatefromstring') )
			echo '<!-- The GD image library is not installed. -->';

			/*   check if it contains html stuff
			 *   handier than a binary check to rescan redirects for
			 *   favicon content tags
			 * */
			if (strpos(strtolower("-".$this->GetData()),strtolower('<html')) &&
				strpos(strtolower("-".$this->GetData()),strtolower('<body'))) {
					$this->BlockFoundIcon();
			}

			/*   check the filetype */
			Log::F($this,'[VERIFY] check image type');
			$filetype = $this->GetFileType();
			if ('' != $filetype)
			{
				$this->SetType($filetype);
				$this->SetFilters($this->GetFilters() . '|' . $filetype);
			}
			else
			{
				$this->BlockFoundIcon();
			}
		}

		/**
		 *
		 * Enter description here ...
		 * @param $imagePath
		 */
		function GetExtension($imagePath)
		{
			$ext = end(explode(".",$imagePath));
			if ($ext != "jpeg" || $ext='wbmp')
			{
				$ext = substr($ext,0,3);
			}
			return $ext;

		}

		/*************************************************************/
		// Database: representation of the favicon in the database
		/*************************************************************/

		/**
		 * 
		 * Enter description here ...
		 */
		function WriteInDatabase()
		{			
			$this->IconDb(self::FLAG_DB_WRITE);
			$this->CheckDBErrors();
		}

		/**
		 * 
		 * Enter description here ...
		 */
		function ReadFromDatabase()
		{
			$this->CheckDBErrors();
			return $this->IconDb(self::FLAG_DB_READ);
		}

		/**
		 * 
		 * Enter description here ...
		 */
		function ExistsInDatabase() {
			if (self::FLAG_NO_ICON == $this->GetFaviconUri())
			{
				return self::FLAG_NO_ICON;
			}
			else

			{
				return $this->_db_found;

			}
		}

		function CheckDBErrors()
		{
			if ($this->_db_error) {
				Log::F($this->_db_error);
			}
		}
		


		/**
		 * Logs icon metadata in the database or reads icon metadata from the database
		 * This single-method-that-does-db-interaction is only called twice:
		 * - once for a read action
		 * - once for a write action (if read determined a write is needed)
		 * both from class EdlLinkFaviconContent
		 * @since 0.4.9
		 * @param array $favicon_array
		 */
		function IconDb($mode)
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();
			if ($mode=='') {
				$mode = self::FLAG_DB_READ;
			}

			/* get the uri record(s) */
			try {
				$row_uri = $wpdb->get_results(	"SELECT * " .
								"FROM " . $table_name_1 .
								" WHERE uri_hash = '" .
								md5($this->_uri) . "';",
								ARRAY_A);
			} catch (Exception $e) {
				Log::F($this,'READ - getting uri record from ' . $this->_uri);
				Log::F($this,$e->getMessage());
			}

			/* for write modus we already have the favicon uri */
			if ($mode == self::FLAG_DB_WRITE)
			{
				Log::F($this,'WRITE MODUS: ' . $mode);
				/* get the favicon_uri records to check if it already exists
				 * */
				try {
					$row_furi = $wpdb->get_results(	"SELECT * " .
						"FROM $table_name_1 ".
						"WHERE `favicon_uri_hash` = '" . md5($this->_favicon_uri) .
						"';",
						ARRAY_A);
				} catch (Exception $e) {
					Log::F($this,'READ - getting favicon uri record from' . $this->_favicon_uri);
					Log::F($this,$e->getMessage());
				}
			}
			/* check the read modus furi relations */
			else
			{
				//Log::F($this,'READ MODUS: ' . $mode);
				if (!is_array($row_uri) || empty($row_uri))
				{
					$row_furi = '';
				}
				elseif (count($row_uri) == 1)
				{
					/* get the parent favicon_uri records  */
					if ($row_uri[0]['favicon_parent'] > 0)
					{
						/* get the favicon_uri records */
						try {
							$row_furi = $wpdb->get_results(	"SELECT * " .
								"FROM $table_name_1 ".
								"WHERE `id` = '" . $row_uri[0]['favicon_parent'] . "';",
								ARRAY_A);
						} catch (exception $e) {
							Log::F($this,'READ - getting favicon parent record from' .
								$row_uri[0]['favicon_parent']);
							Log::F($this,$e->getMessage());
						}
					}
					/* else doublecheck amount of favicon uris */
					elseif ($row_uri[0]['favicon_uri'] != '')
					{
						/* get the favicon_uri records */
						$row_furi = $wpdb->get_results(	"SELECT * " .
							"FROM $table_name_1 ".
							"WHERE `favicon_uri_hash` = '" . md5($row_uri[0]['favicon_uri']) . "';",
							ARRAY_A);
					}
					else
					{
						$row_furi = $row_uri;
					}
				}
				else
				{
					// multiple uris, there could even be multiple furis
					// but lets keep it simple
					$row_furi = '';
				}
			}



			/*
			 * The following table shows what should happen for URI/FURI relation:
			 *
			 * WRITE
			 *
			 *      | URI | FURI |
			 * --------------------------------------------------------------------------------
			 *      |  0  |  0   | full insert
			 * --------------------------------------------------------------------------------
			 *      |  1  |  0   | update uri (new icon found for this uri)
			 * --------------------------------------------------------------------------------
			 *      |  0  |  1   | insert with link to FURI id (favicon already in)
			 * --------------------------------------------------------------------------------
			 * 1    |  1  |  1   | same record: update features (plugin update)
			 * --------------------------------------------------------------------------------
			 * 0    |  1  |  1   | another record: update URI with parent FURI (
			 * --------------------------------------------------------------------------------
			 *      |  1  |  N   | ERROR - duplicate FURI's parent-child relations corrupted
			 * -------------------------------------------------------------------------------
			 *      |  N  |  1   | ERROR - duplicate URI's should be unique
			 * --------------------------------------------------------------------------------
			 *      |  N  |  0   | ERROR - duplicate URI's should be unique
			 * --------------------------------------------------------------------------------
			 *      |  0  |  N   | ERROR - duplicate FURI's parent-child relations corrupted
			 * --------------------------------------------------------------------------------
			 *      |  N  |  N   | MEGA ERROR - database completely corrupted wp_die^28
			 * --------------------------------------------------------------------------------
			 *
			 * READ
			 *      | URI | FURI |
			 * --------------------------------------------------------------------------------
			 *      |  0  |  0   | FALSE: a full insert is needed cant find furi
			 * --------------------------------------------------------------------------------
			 *      |  1  |  0   | FALSE: either the parent id is bad or the icon data is missing
			 * --------------------------------------------------------------------------------
			 *      |  0  |  1   | x
			 * --------------------------------------------------------------------------------
			 * 1    |  1  |  1   | TRUE: same record: return record
			 * --------------------------------------------------------------------------------
			 * 0    |  1  |  1   | TRUE: another record: combine records and return
			 * --------------------------------------------------------------------------------
			 *      |  1  |  N   | ERROR - duplicate FURI's parent-child relations corrupted
			 * -------------------------------------------------------------------------------
			 *      |  N  |  1   | ERROR - duplicate URI's should be unique
			 * --------------------------------------------------------------------------------
			 *      |  N  |  0   | ERROR - duplicate URI's should be unique
			 * --------------------------------------------------------------------------------
			 *      |  0  |  N   | x
			 * --------------------------------------------------------------------------------
			 *      |  N  |  N   | MEGA ERROR - database completely corrupted wp_die^28
			 * --------------------------------------------------------------------------------
			 *
  			 * the IF code beneath is DELIBERATELY not optimized to read more clear
			 * and reference the table above especially since there are still double
			 * occurences happening
			 *
			 */

			if (!is_array($row_uri) || empty($row_uri))
			{
				if (!is_array($row_furi)  || empty($row_furi))
				{
					if ($mode == self::FLAG_DB_READ)
					{
						//Log::F($this,'00 - ' . $mode . ' - Uri not found so run faviconloop with: '.
						//	$this->_uri);
						$this->_db_found = false;
					}
					else
					{
						Log::F($this,'00 - ' . $mode . ' - Uri not found so inserting with: '.
							$this->_uri);
						$this->icoFullInsert();
						$this->_db_found = true;

					}
				}
				elseif (count($row_furi)==1)
				{
					Log::F($this,'01 - ' . $mode . ' - New Uri Record inserted with link to existing favicon: '.
						$this->_uri . ' and ' . $row_furi[0]['id']);
					$this->icoInsertWithLinkToFuriId($row_furi[0]['id']);
					$this->_db_found = true;
				}
				else
				{
					$this->_db_error = '0N - ' . $mode . ' - ERROR - duplicate favicon id: '.
						$this->_uri;
					Log::M($this->_db_error);
					$this->_db_found = false;
				}
			}
			elseif (count($row_uri)==1)
			{
				if (!is_array($row_furi) || empty($row_furi))
				{
					if ($mode == self::FLAG_DB_READ)
					{
						Log::F($this,'10 - ' . $mode . ' - Uri found but no favicon entry, run imageloop with: '.
							$this->_uri);
						$this->_db_found = false;
					}
					else
					{
						Log::F($this,'10 - ' . $mode . ' - Existing Uri updated with new favicon: '.
							$this->_uri);
						$this->updateUri($row_uri[0]['comment'],$row_uri[0]['id']);
						$this->_db_found = true;
					}
				}
				elseif (count($row_furi)==1)
				{
					if ($row_uri[0]['id']==$row_furi[0]['id'])
					{
						if ($mode == self::FLAG_DB_READ)
						{
							Log::F($this,'111 - ' . $mode . ' - Uri found incl favicon');
							$this->SetFaviconUri	($row_furi[0]['favicon_uri']);
							$this->SetType			($row_furi[0]['favicon_type']);
							$this->SetFilters		($row_furi[0]['favicon_filters']);
							$this->SetSource		($row_furi[0]['favicon_source']);
							$this->SetDefault		($row_furi[0]['favicon_default']);
							$this->SetComment		($row_furi[0]['comment']);
							$this->_db_found = true;
						}
						else
						{
							Log::F($this,'111 - ' . $mode . ' - Existing Uri updated with new features: '.
								$row_uri[0]['id']);
							$this->updateFeatures($row_uri[0],$row_furi[0]);
							$this->_db_found = true;
						}
					}
					else
					{
						if ($mode == self::FLAG_DB_READ)
						{
							Log::F($this,'011 - ' . $mode . ' - Uri found with favicon reference using : '.
								$this->_uri);
							$this->SetFaviconUri	($row_furi[0]['favicon_uri']);
							$this->SetType			($row_furi[0]['favicon_type']);
							$this->SetFilters		($row_furi[0]['favicon_filters']);
							$this->SetSource		($row_furi[0]['favicon_source']);
							$this->SetDefault		($row_furi[0]['favicon_default']);
							$this->SetComment		($row_furi[0]['comment']);
							$this->_db_found = true;
						}
						else
						{
							Log::F($this,'011 - ' . $mode . ' - Existing Uri updated with new features: '.
								$row_furi[0]['id']);
							$this->updateUriWithParentFuri($row_uri[0]['id'],
								$row_furi[0]['id'],$row_uri[0]['comment']);
							$this->_db_found = true;
						}
					}
				}
				else
				{
					$this->_db_error = '1N - ' . $mode . ' - ERROR - duplicate favicon id: '.
						$this->_favicon_uri;
					Log::F($this,$this->_db_error);
					$this->_db_found = false;
				}
			}
			else
			{
				if (!is_array($row_furi)  || empty($row_furi))
				{
					$this->_db_error = 'N0 - ' . $mode . ' - ERROR - duplicate uri: '.
						$this->_uri;
					Log::F($this,$this->_db_error);
					$this->_db_found = false;
				}
				elseif (count($row_furi)==1)
				{
					$this->_db_error = 'N1 - ' . $mode . ' - ERROR - duplicate uri: '.
						$this->_uri;
					Log::F($this,$this->_db_error);
					$this->_db_found = false;
				}
				else
				{
					$this->_db_error = 'NN - ' . $mode . ' - GIANT ERROR - Your Favicon Db is corrupt: duplicate '.
						$this->_favicon_uri . ' and duplicate ' . $this->_uri;
					Log::F($this,$this->_db_error);
					$this->_db_found = false;
				}
			}
			return;
		}

		/**
		 * see LogIconInDb (0:0) full insert
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		function icoFullInsert()
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();
			try {
				$wpdb->insert($table_name_1,array(
				'uri' 				=> $this->_uri,
				'uri_hash' 			=> md5($this->_uri),
				'favicon_uri_hash' 	=> md5($this->_favicon_uri),
    			'favicon_uri' 		=> $this->_favicon_uri,
				'favicon_type' 		=> $this->_type,
				'favicon_filters'	=> $this->_filters,
				'favicon_source' 	=> $this->_source,
				'favicon_default' 	=> $this->_default,
				'favicon_parent' 	=> 0,
				'comment' 			=> $this->_comment,
				));
				
			} catch (Exception $e) {
				Log::F($this,'00 - TRIED WRITE - NOTICE - duplicate uri catch '.
						$this->_uri);
				Log::F($this,$e->getMessage());
			}
			$this->_db_error= $wpdb->last_error;
			return;
		}

		/**
		 * @see LogIconInDb (0:1) new uri with existing favicon
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		function icoInsertWithLinkToFuriId($furlId)
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();

			try {
				$wpdb->insert($table_name_1,array(
				'uri' 				=> $this->_uri,
    			'favicon_uri' 		=> '',
				'uri_hash' 			=> md5($this->_uri),
				'favicon_uri_hash' 	=> '',
				'favicon_type' 		=> '',
				'favicon_filters'	=> '',
				'favicon_source' 	=> '',
				'favicon_default' 	=> '',
				'favicon_parent' 	=> $furlId,
				'comment' 			=> $this->_comment,
				));
			} catch (Exception $e) {
				Log::F($this,'01 - TRIED WRITE - NOTICE - duplicate uri catch '.
						$this->_uri);
				Log::F($this,$e->getMessage());
			}
			$this->_db_error	= $wpdb->last_error;
			return;
		}

		/**
		 * @see LogIconInDb (1:0) update URI with new FURI
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		function updateUri($existing_uri_comment,$id)
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();

			try {
				$row = $wpdb->update($table_name_1,
				array(	'favicon_uri' => $this->_favicon_uri,
						'favicon_uri_hash' 	=> md5($this->_favicon_uri),
						'favicon_type' => $this->_type,
						'favicon_filters' => $this->_filters,
						'favicon_source' => $this->_source,
						'favicon_default' => $this->_default,
						'favicon_parent' => 0,
						'comment' => $existing_uri_comment . $this->_comment
				),
				array( 'uri' => $this->_uri));
			} catch (Exception $e) {
				Log::F($this,'10 - TRIED WRITE - NOTICE - update failed '.
						$this->_uri);
				Log::F($this,$e->getMessage());
			}
			$this->_db_error	= $wpdb->last_error;
			return;
		}

		/**
		 * @see LogIconInDb (1:1:1) update URI with existing FURI
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		function updateFeatures($uri,$furi)
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();

			try {
				$row = $wpdb->update($table_name_1,
				array(	'favicon_type' => $this->_type,
						'favicon_filters' => $this->_filters,
						'favicon_source' => $this->_source,
						'favicon_default' => $this->_default,
						'favicon_parent' => 0,
						'comment' => $uri['comment'] . $this->_comment
				),
				array( 'id' => $uri['id'] )
				);
			} catch (Exception $e) {
				Log::F($this,'111 - TRIED WRITE - NOTICE - update failed'.
						$this->_uri);
				Log::F($e->getMessage());
			}
			$this->_db_error	= $wpdb->last_error;
			return;
		}

		/**
		 * @see LogIconInDb (0:1:1) update URI with new parent FURI
		 * @since 0.4.9
		 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
		function updateUriWithParentFuri($uriID,$furiID,$uriComment)
		{
			global $wpdb;
			$table_name_1 = Database::TableName1();

			try {
				$row = $wpdb->update($table_name_1,
				array(	'favicon_type' => '',
						'favicon_filters' => '',
						'favicon_source' => '',
						'favicon_default' => '',
						'favicon_parent' => $furiID,
						'comment' => $uriComment . $this->_comment
				),
				array( 'id' => $uriID )
				);
			} catch (Exception $e) {
				Log::F($this,'011 - TRIED WRITE - NOTICE - update failed'.
						$this->_uri);
				Log::F($e->getMessage());
			}
			$this->_db_error	= $wpdb->last_error;
			return;
		}
	}
}