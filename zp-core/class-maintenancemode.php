<?php

/**
 * Maintenance mode utility class based on the former site_upgrade plugin 
 * 
 * @since ZenphotoCMS 1.6
 * 
 * @author Stephen Billard (sbillard), adapted by Malte Müller (acrylian)
 * @package admin
 */
class maintenanceMode {

	/**
	 * Loads the placeholder page if the site is in test mode
	 * 
	 * @global type $_zp_conf_vars
	 */
	static function loadPlaceholderPage() {
		global $_zp_conf_vars;
		if (OFFSET_PATH == 0) {
			//$state = @$_zp_conf_vars['site_upgrade_state'];
			$state = maintenanceMode::getState();
			if ((!zp_loggedin(ADMIN_RIGHTS) && $state == 'closed_for_test') || $state == 'closed') {
				if (isset($_zp_conf_vars['special_pages']['page']['rewrite'])) {
					$page = $_zp_conf_vars['special_pages']['page']['rewrite'];
				} else {
					$page = 'page';
				}
				if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
					header("HTTP/1.1 503 Service Unavailable");
					header("Status: 503 Service Unavailable");
					header('Pragma: no-cache');
					header('Retry-After: 3600');
					header('Cache-Control: no-cache, must-revalidate, max-age=0');
					include SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.php';
					exitZP();
				}
			}
		}
	}

	/**
	 * Updates the site state
	 * 
	 * @param string $state
	 */
	static function setState($state) {
		global $_zp_config_mutex;
		if (in_array($state, array('open', 'closed', 'closed_for_test'))) {
			$_zp_config_mutex->lock();
			$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
			$zp_cfg = updateConfigItem('site_upgrade_state', $state, $zp_cfg);
			storeConfig($zp_cfg);
			$_zp_config_mutex->unlock();
		}
	}

	/**
	 * Gets the site state
	 * 
	 * @global type $_zp_conf_vars
	 * @return string
	 */
	static function getState() {
		global $_zp_conf_vars;
		$state = '';
		$ht = @file_get_contents(SERVERPATH . '/.htaccess');
		preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|', $ht, $matches);
		if (!$matches || strpos($matches[0], '#') === 0) {
			$state = @$_zp_conf_vars['site_upgrade_state'];
		} else {
			$state = 'closed';
		}
		switch ($state) {
			default:
			case 'open':
				return 'open';
			case 'closed':
				return $state;
			case 'closed_for_test':
				return $state;
		}
	}

	/**
	 * Gets the site state note 
	 * 
	 * @param string $which Default null to  get the note to the current status, 'open", 'closed" or "closed_for_test' to get the note on demand
	 * @return string
	 */
	static function getStateNote($which = null) {
		if(is_null($which)) {
			$status = maintenanceMode::getState();
		} else {
			$status = $which;
		}
		switch ($status) {
			case 'open':
			default:
				return gettext('The site is opened');
			case 'closed':
				return gettext('<strong>Maintenance Mode:</strong> The site is closed!');
			case 'closed_for_test':
				return gettext('<strong>Maintenance Mode:</strong> The site is in test mode!');
		}
	}

	/**
	 * Prins the site state notice on the backend
	 */
	static function printStateNotice() {
		$status = maintenanceMode::getState();
		if ($status != 'open') {
			echo '<p class="warningbox" style="margin: 0">' . maintenanceMode::getStateNote() . '</p>';
		}
	}

	/**
	 * Restores the placeholder files
	 * 
	 * @global obj $_zp_gallery
	 */
	static function restorePlaceholderFiles() {
		global $_zp_gallery;
		mkdir_recursive(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/', FOLDER_MOD);
		copy(SERVERPATH . '/' . ZENFOLDER . '/site_upgrade/closed.php', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.php');
		if (isset($_POST['maintenance_mode_restorefiles']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.htm')) {
			$html = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/site_upgrade/closed.htm');
			$site_title = sprintf(gettext('%s upgrade'), $_zp_gallery->getTitle());
			$default_logo = FULLWEBPATH . '/' . ZENFOLDER . '/images/zen-logo.png';
			$site_title2 = sprintf(gettext('<strong><em>%s</em></strong> is undergoing an upgrade'), $_zp_gallery->getTitle());
			$link = '<a href="' . FULLWEBPATH . '/index.php">' . gettext('Please return later') . '</a>';
			$html_final = sprintf($html, $site_title, $default_logo, $site_title2, $link);
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.htm', $html_final);
		}
		if (isset($_POST['maintenance_mode_restorefiles']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.css')) {
			copy(SERVERPATH . '/' . ZENFOLDER . '/site_upgrade/closed.css', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.css');
		}
		if (isset($_POST['maintenance_mode_restorefiles']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss_closed.xml')) {
			$xml = '<?xml version="1.0" encoding="utf-8"?>
				<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
					<channel>
						<title>' . html_encode(gettext('RSS temprarily suspended for maintenance')) . '</title>
						<link>' . FULLWEBPATH . '</link>
						<description></description>
						<item>
							<title>' . html_encode(gettext('Closed for maintenance')) . '</title>
							<description>' . html_encode(gettext('The site is currently undergoing an upgrade')) . '</description>
						</item>
					</channel>
				</rss>';
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss-closed.xml', $xml);
		}
	}

}
