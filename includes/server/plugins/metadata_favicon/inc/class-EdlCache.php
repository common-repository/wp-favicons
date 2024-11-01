<?php
/**
 * Contains Classes for managing the an Eternal Cache based on a  reversed url structure.
 *
 * @package WP-Favicons
 * @since 0.030
 * @author Edward de Leau <e@leau.net>
 * @copyright Edward de Leau, http://edward.de.leau.net
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\EdlLinkFaviconCache")) {

	/**
 	 * Class: EdlLinkFaviconCache
     * Caching of favicons based on a domain e.g. \com\delicious\www\[cache location]
     * so always a url is passed to determine the cache location on. Remnants of
     * a more larger generic class
     * @package WP-Favicons
   	 * @since 0.4.0
 	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 	 * @copyright GPL 2
     */
	class EdlLinkFaviconCache {

		public $url;
		public $physical_location;
		public $physical_location_and_file;
		public $relative_location;
		public $file_name;
		public $file_content;

		/*
		 * constructor
		 */
		public function __construct($url, $fileName)
		{
			/* calculate the relative reverse domain dir */
			$this->SetUrl($url);

			if ($fileName)
			{
				$this->SetFileName($fileName);
			}

			$this->physical_location_and_file = $this->physical_location .
				$this->file_name;
		}

		/**
		 * URL based dir calculation
		 * Based on the url given the physical / relatieve domain based
		 * directories are calculated
		 * @param string $url the url passed
		 */
		function SetUrl($url) {
			$write_loc = '';
			if ($url)
			{
				$url_array = Array();
				$this->url = $url;

				// SCHEME

				$url_scheme = parse_url($url,PHP_URL_SCHEME);
				$write_loc .= '/' . $url_scheme;

				// HOST

				$url_host = parse_url($url, PHP_URL_HOST);
				if (Http::valid_ip($url_host))
				{
					$write_loc .= '/' . $url_host;
				}
				else
				{
					$segments = array_reverse(explode('.',$url_host));
					foreach($segments as $segment) {
						$write_loc .= '/' . $segment;
					}
				}

				// PORTS

				$url_port = parse_url($url, PHP_URL_PORT);
				if ($url_port)
				{
					$write_loc .= '/' . $url_port;
				}

				// @todo we now store all image paths however
				// for sites that do not have a favicon it will retrieve
				// a default e.g. identicon which is unique for each url
				// meaning a certain path could have many generated default
				// the solution is more or less to advice to not cache
				// the default icons which solves it. A broader solution
				// is that we somehow detect which group of urls belong
				// to each other but that is quite difficul and requires
				// parent child relationships between uris.

				// PATH

				$uncleanpath = parse_url($url, PHP_URL_PATH);
				$cleanpath = preg_replace("/[^a-z0-9\/\.]/i", "-", $uncleanpath);
				$write_loc .= $cleanpath;

				// PHP_URL_QUERY

				// @todo if someone requests this as an RFC we can add this
				// We DO need this in a future version when we are going to store
				// 3rd party requests in these directories

				// PHP_URL_FRAGMENT

				// @todo if someone requests this as an RFC we can add this

			}
			$write_loc = substr($write_loc, 1);
			$this->relative_location = Config::GetOptionsArrayValue(Config::GetPluginSlug() .
						'upload_url_cache') . $write_loc;
			//
			if (substr($this->relative_location,-1) != '/')
			{
				$this->relative_location = $this->relative_location . '/';
			}

			$this->physical_location = Config::GetOptionsArrayValue(Config::GetPluginSlug() .
						'upload_dir_cache') . $write_loc . '/';
			return true;
		}
			

		/*
		 * the filename of the file we want to cache
		 */
		function SetFileName($filename)
		{
			$this->file_name = $filename;
			$this->physical_location_and_file = $this->physical_location . '/' . $this->file_name;
			return true;
		}

		/*
		 *
		 */
		function SetContent($filecontent)
		{
			$this->file_content = $filecontent;
			return true;
		}

		/*
		 *
		 */
		function GetRelativeLocation() {
			return $this->relative_location;
		}

		/*
		 *
		 */
		function GetPhysicalPathAndFile() {
			return $this->physical_location_and_file;
		}

		/*
		 * check if the file exists we want to cache
		 * if it does not write it
		 * always return true unless error
		 */
		function FileExistInCache()
		{
			if (file_exists($this->physical_location_and_file))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/*
		 * write the file to cache
		 */
		function WriteFileToCache()
		{
			EdlUtils::WriteFile($this->physical_location,
				$this->physical_location_and_file,
				$this->file_content);
		}

		/*
		 * read cache
		 */
		function ReadFileFromCache()
		{
			$data = @file_get_contents($this->physical_location_and_file);
			return $data;
		}

		/**
		 * direct save to cache
		 * @since 0.4.0
		 * @param $data
		 */
		function Savedata($data)
		{
			//if (!$this->FileExistInCache())
			//{
				$this->SetContent($data);
				$this->WriteFileToCache();
			//}
		}

	}
}