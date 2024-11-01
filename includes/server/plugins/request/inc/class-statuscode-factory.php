<?php
namespace leau\co\wp_favicons_server;
if (!class_exists("\\leau\\co\\wp_favicons_server\\HttpStatusFactory"))
{
	class HttpStatusFactory
	{
		private $_statuscode;

		function __construct($statuscode)
		{
			$this->_statuscode = $statuscode;
		}

		/**
		 * Enter description here ...
		 */
		function GetAsHtml()
		{
			if ($this->_statuscode->GetStatusCode() == '' )
			{
				return '';
			}
			else
			{
				return $this->_statuscode->GetStatusCode();
				//return '<img src="' . .
				//	'" alt="' . Config::GetPluginSlug() . '" class="' .
				//	Config::GetPluginSlug() . $this->_active_filter . '" />';
			}
		}

	}
}