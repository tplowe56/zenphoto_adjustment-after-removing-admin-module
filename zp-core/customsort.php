<?php

/*
 * A demo plugin for an admin cusom sort order
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_name = "custom_sortorder";
$plugin_description = gettext('Plugin for adding custom sortorders to various admin dropdown selectors.');
$plugin_author = "acrylian";
$plugin_siteurl = 'https://maltem.de';


zp_register_filter('admin_sortbyoptions', 'AdminCustomSortorder::defineCustomSortorder');

class AdminCustomSortorder {

	/**
	* You can add ow or modify or delete existing orders here. 
	* 
  * @param array $orders The original array with the sort orders. A one dimensional array wiht key => value pairs (selector entry => column to sort by)
	* @type string $type The item type $order belongs to.
	* @return array
	*/
	static function defineCustomSortorder($orders, $type) {
		//the switch contains all available types
		switch ($type) {
			case 'albums': // albums
				break;
			case 'albums-dynamic': //albums within dynamic albums
				break;
			case 'albums-search': // albums search results
				break;
			case 'images': // images
				// Left: Is the selector entry. This can be gettext but of course may not be translated
				// Right: the db column to sort by, this is directly used with ORDER BY sql syntax, so also supports more than one column or other syntax
            $orders['IPTCSubLocation'] = 'IPTCSubLocation'; 			
    		break;
			case 'images-search': // image search results
				break;
			case 'images-edit': // images backend list order
				break;
			case 'pages': // pages (note pages are normally sorted manually and also nested!)
				break;
			case 'pages-search': // pages search results (front end)
				break;
			case 'news': // News articles search results, since otherwise always sorted by date descending
				break;
		}
  	return $orders;
	}

}