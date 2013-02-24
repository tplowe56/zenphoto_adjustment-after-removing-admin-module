<?php
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once(SERVERPATH.'/'.ZENFOLDER.'/functions-config.php');

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());
$htpath = SERVERPATH.'/.htaccess';
$ht = @file_get_contents($htpath);

preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed\.php|',$ht,$matches);
switch (@$_GET['siteState']) {
	case 'closed':
		require_once(SERVERPATH.'/'.ZENFOLDER.'/class-rss.php');
		class setupRSS extends RSS {
			public function getRSSitems() {
				$this->feedtype = 'setup';
				$items = array();
				$items[] = array(	'title'=>gettext('RSS suspended'),
						'link'=>'',
						'enclosure'=>'',
						'category'=>'',
						'media_content'=>'',
						'media_thumbnail'=>'',
						'pubdate'=>date("r",time()),
						'desc'=>gettext('The RSS feed is currently not available.'));
				return $items;
			}
			protected function startRSSCache() {
			}
			protected function endRSSCache() {
			}
		}
		$rss = new setupRSS();
		ob_start();
		$rss->printRSSFeed();
		$xml = ob_get_contents();
		ob_end_clean();
		file_put_contents(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/rss-closed.xml', $xml);

		$report = gettext('Site is now marked in upgrade.');
		setSiteState('closed');
		break;
	case 'open':
		$report = gettext('Site is viewable.');
		setSiteState('open');
		break;
	case 'closed_for_test':
		if ($matches && $matches[0]) {
			if (strpos($matches[0][1],'#')!==0) {
				foreach ($matches[0] as $match) {
					$ht = str_replace($match, preg_replace('/^ /','# ',$match), $ht);
				}
				@chmod($htpath, 0777);
				file_put_contents($htpath, $ht);
				@chmod($htpath, 0444);
			}
		}
		$report = gettext('Site is avaiable for testing only.');
		setSiteState('closed_for_test');
		break;
}

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report='.$report);
exitZP();

/**
 * updates the site status
 * @param string $state
 */
function setSiteState($state) {
	$zp_cfg = @file_get_contents(CONFIGFILE);
	$zp_cfg = updateConfigItem('site_upgrade_state', $state, $zp_cfg);
	file_put_contents(CONFIGFILE, $zp_cfg);
	setOption('site_upgrade_state', $state);
}
?>