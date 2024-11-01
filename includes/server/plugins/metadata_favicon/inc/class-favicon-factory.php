<?php
/**
 * New file yet to be documented
 * Enter description here ...
 * @author cogmios
 *
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\FaviconFactory"))
{
	class FaviconFactory
	{
		private $_favicon;
		private $_active_filter;

		/**
		 * constructor
		 * The factory is started when a new $favicon object is passed and
		 * a filter. A filter determines where the replacement in the text 
		 * will take place.
		 * @param $favicon
		 * @param $active_filter
		 */
		function __construct($favicon, $active_filter)
		{
			$this->_favicon = $favicon;
			$this->_active_filter = $active_filter;

			if (Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'use_cache'))
			{
				$this->SetAdvancedFavicon();
			}
			else
			{
				$this->SetSimpleFavicon();
			}
		}

		/**
		 *
		 * Enter description here ...
		 */
		function SetSimpleFavicon()
		{
			$this->_favicon->SetPublicFaviconUri(
				'http://www.google.com/s2/favicons?domain=' .
					parse_url($this->_favicon->GetUri(), PHP_URL_HOST));
		}
		
		function SetEmptyFavicon()
		{
			$this->_favicon->SetPublicFaviconUri('');
		}
		

		/**
		 *
		 * Enter description here ...
		 */
		function SetAdvancedFavicon()
		{
			// READ
			$this->_favicon->ReadFromDatabase();
			if (!$this->_favicon->ExistsInDatabase())
			{
				/* check time buffer first */
				apply_filters( Config::GetPluginSlug() . 'timebuffer', $this->_favicon);
				if ($this->_favicon->GetState())
				{
					Log::F($this->_favicon,'Does not exist yet in database');
					$this->_favicon->SetNewInDb();
					$this->FaviconLoop();

					if ($this->_favicon->FoundStatus())
					{
						// WRITE
						Log::F($this->_favicon,'Write in database after icon loop:');
						$this->_favicon->WriteInDatabase();
						if (!$this->_favicon->ExistsInDatabase())
						{
							// Somehow it is still not written in the DB
							Log::F($this->_favicon,'Still does not exist yet in database:');
							$this->SetEmptyFavicon();
							return;
						}
					}
					else
					{
						// it is not found and no default has been provided
						// or the iconloop timed out (user has to increase
						// php time-out settings)
						// @todo instead of none found use amount of retries
						Log::F($this->_favicon,'Write NONE FOUND in database after icon loop');
						$this->_favicon->SetFaviconUri('EMPTY');
						$this->_favicon->WriteInDatabase();
						$this->SetEmptyFavicon();
						return;
					}
				}
				else
				{
					$this->SetSimpleFavicon();
					return;
				}
			}
			//
			// Empty is used when the user has set NO defaults and none
			// has been found. To prevent searching the next time
			// we have to tag it with a value so that it is not constantly
			// polled next time. So for all uri's that have no icon
			// and the user has set to show no default then those will
			// get the value EMPTY.
			elseif ($this->_favicon->ExistsInDatabase()==='EMPTY')
			{
				Log::F($this->_favicon,'NONE FOUND in database');
				// @todo if the user config has no defaults set up
				// then do nothing if it however has defaults set up
				// then repeat the request as a background process
				//
				$this->SetEmptyFavicon();
				return;
			}
			// Another reason exists and those are time-outs because
			// the initial timeout value of php or php-fpm etc is too
			// low so that no icons are found whatsoever.
			// Since this will be pretty obvious this will be in 
			// the documentation instead.

			if ($this->_favicon->GetFilters() == 'NOCACHE')
			{
				Log::F($this->_favicon,$this->_favicon->GetFilters());
				Log::F($this->_favicon,'NON CACHED Version: not saved in disk cache');
				$this->_favicon->SetPublicFaviconUri($this->_favicon->GetFaviconUri());
			}
			else
			{
				$favicon_cache = new EdlLinkFaviconCache(
					$this->_favicon->GetUri(), 'favicon.' .
					$this->_favicon->GetType());

				if ($this->_favicon->GetNewInDb())
				{
					Log::F($this->_favicon,'New in database so disk caching');
					if ($data = $this->_favicon->GetData())
					{
						if ($favicon_cache->FileExistInCache())
						{
							Log::F($this->_favicon,'Icon already exists in disk cache, lets overwrite!');
						}

						$favicon_cache->Savedata($data);

						if (!$favicon_cache->FileExistInCache())
						{
							Log::F($this->_favicon,'Error could not be saved in disk cache');
							$this->_favicon->SetPublicFaviconUri('');
						}
						else
						{
							Log::F($this->_favicon,'Saved in disk cache:' . $this->_favicon->GetUri());
							$this->_favicon->SetPublicFaviconUri($favicon_cache->GetRelativeLocation()
								. 'favicon.' . $this->_favicon->GetType());
						}
					}
					else
					{
						Log::F($this->_favicon,'Error HAS NO DATA before save in disk cache');
						$this->_favicon->SetPublicFaviconUri('');
					}
				}
				else
				{
					/* get from cache - assume it is there to save speed */
					$this->_favicon->SetPublicFaviconUri($favicon_cache->GetRelativeLocation()
								. 'favicon.' . $this->_favicon->GetType());

				}
			}
		}
		

		/**
		 *
		 * Enter description here ...
		 */
		function GetAsHtml()
		{
			if ($this->_favicon->GetPublicFaviconUri() == '' )
			{
				return '';
			}
			else
			{
				return '<img '
					. 'src="' . $this->_favicon->GetPublicFaviconUri() . '" '
					. 'alt=" " '
					. 'class="' . Config::GetPluginSlug() . $this->_active_filter . '" '
					. 'style="position: relative; top: 0; left: 0;" '
					. '/>';
			}
		}

		/**
		 * @todo second caller gets empty object....
		 * Enter description here ...
		 */
		function FaviconLoop()
		{
			Log::F($this->_favicon,'Start IconLoop With:' . $this->_favicon->GetUri());
			apply_filters( Config::GetPluginSlug() . 'search', $this->_favicon);
			apply_filters( Config::GetPluginSlug() . 'default', $this->_favicon);
			apply_filters( Config::GetPluginSlug() . 'convert', $this->_favicon);
			apply_filters( Config::GetPluginSlug() . 'default_non_cached', $this->_favicon);
		}

	}
}