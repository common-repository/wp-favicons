<?php
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\HttpStatus"))
{
	class HttpStatus
	{
		private $_uri;
		private $_response_code;

		function __construct($uri)
		{
			$this->_uri = $uri;
			$this->SetStatusCode();
		}

		function SetStatusCode()
		{
			$response = $this->CheckHTTPStatusRequest();
			$this->_response_code = $response['request_response_code'];

		}

		function GetStatusCode()
		{
			return $this->_response_code;
		}


		/**
		 * Check and returns historical requests from the Request Cache
		 * @param $uri
		 */
		function CheckHTTPStatusRequest()
		{
			global $wpdb;
			$table_name_2 = Database::TableName2();
			$uri = $this->_uri;

			try {
				$row_uri = $wpdb->get_results(	"SELECT request_response_code, request_response_message " .
					"FROM $table_name_2 ".
					"WHERE `uri_hash` = '" . md5($uri) . "';",
					ARRAY_A);
			} catch  (Exception $e) {
			}
			if (!empty($row_uri) && is_array($row_uri))
			{
				return $row_uri[0];
			}
			else
			{
				// @todo put on uri queue
				// Not yet in Request Cache
				return false;
			}
		}

	}
}