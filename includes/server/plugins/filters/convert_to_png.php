<?php
/**
 * Filter to convert a favicon to PNG.
 *
 * This file contains the wp_favicons_convert filter implementation
 * to convert a favicon to PNG.
 *
 * Note: feel free to add your own filters!
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 * @copyright GPL 2
 */
namespace leau\co\wp_favicons_server;

/*********************************************/
/* Fonction: ImageCreateFromBMP              */
/* Author:   DHKold                          */
/* Contact:  admin@dhkold.com                */
/* Date:     The 15th of June 2005           */
/* Version:  2.0B                            */
/*********************************************/



if (!class_exists("\\leau\\co\\wp_favicons_server\\filters_convert_to_png"))
{
	/**
	 * Converts an image to PNG
 	 * @package WP-Favicons
 	 * @since 0.4.0
 	 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 	 * @copyright GPL 2
 	 */
	class filters_convert_to_png extends Plugin
	{
    	/**
    	 * This Method is called by default by the master Plugin object
    	 * @see plugins/EdlLinkFaviconPlugin::AddFilter()
	     * @since 0.4.0
     	 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
    	 */
		function AddFilter()
		{
			add_filter(Config::GetPluginSlug() . 'convert', array($this,'ExecuteFilter'), 6, 1);
		}

		/**
		 * Converts an ico to png
		 * @param $data
		 */
		function ConvertIcoToImage($favicon)
		{
			// imagecreatefromstring did not work for ico
			// found class on http://www.tom-reitz.com/2009/02/09/ico-images-in-facebook-profile-boxes/
			$data = $favicon->GetData();
			$image='';
			$icon = new Ico();
			if(!($icon->LoadData($data)))
			{
				Log::F($favicon,'[PNG_CONVERSION] could not load the ico...');
			}
			else
			{
				Log::F($favicon,'[PNG_CONVERSION] loaded the ico...');
			}
			if(!($image=$icon->GetIcon(0)))
			{
				Log::F($favicon,'[PNG_CONVERSION] could not get ico data from ico...');
			}
			else
			{
				Log::F($favicon,'[PNG_CONVERSION] got image from ico...');
			}
			return $image;
		}

		/**
		 * Make an PNG from an image
		 * lots of nice info on http://php.net/manual/en/function.imagepng.php
		 * Enter description here ...
		 * @param $image
		 */
		function GetConvertedPNGImage($image)
		{
			ob_start();
			imagealphablending($image, true); // setting alpha blending on
			imagesavealpha($image, true); // save alphablending setting (important)
			imagepng($image, NULL, 0, NULL);
			$image_png = ob_get_contents();
			ob_end_clean();
			return $image_png;
		}

		/**
		 * Executes the functionality of the png converter
		 * @param $favicon_array
		 */
		function ExecuteFilter( $favicon )
		{
			if (!$favicon->FoundStatus())
			{
				return $favicon;
			}

			/* png is easy */
			if ('png' == $favicon->GetType())
			{
				Log::F($favicon,'[PNG_CONVERSION] already png');
			}
			/* ico needs a seperate lib */
			elseif ('ico' == $favicon->GetType())
			{
				Log::F($favicon,'[PNG_CONVERSION] ico to png');
				$image = '';
				$image = $this->ConvertIcoToImage($favicon);
				$image = $this->GetConvertedPNGImage($image);
				$favicon->SetData($image);
				$favicon->SetType('png');
				$favicon->SetFilters($favicon->GetFilters() . '|png');
			}
			/* everything else should be possible */
			else
			{
				$image = '';
				$image = @imagecreatefromstring($favicon->GetData());
				if ( !is_resource( $image ) )
				{
					// This is a (Costly) beta double check!
					Log::F($favicon,'[PNG_CONVERSION] not an image!');
					$favicon->BlockFoundIcon();
				}
				else
				{
					$image = $this->GetConvertedPNGImage($image);
					$favicon->SetData($image);
					$favicon->SetType('png');
					$favicon->SetFilters($favicon->GetFilters() . '|png');
				}
			}
			return $favicon;
		}
	}
}