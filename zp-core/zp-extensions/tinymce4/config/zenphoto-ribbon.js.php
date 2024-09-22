<?php
/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
/**
 * Filter used by "file manager" plugins to attach themselves to tinyMCE.
 *
 * @package filters
 * @subpackage zenpage
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
global $_zp_rtl_css;
?>
<script src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script>
	tinymce.init({
		selector: "textarea.texteditor",
		language: "<?php echo $locale; ?>",
		entity_encoding: '<?php echo getOption('tinymce4_entityencoding'); ?>',
		<?php if(!empty(trim(strval(getOption('tinymce4_entities'))))) { ?>
			entities: '<?php echo getOption('tinymce4_entities'); ?>',
		<?php } ?>
		<?php if (getOption('tinymce4_textfield-height')) { ?>
		min_height: <?php echo getOption('tinymce4_textfield-height'); ?>,
		<?php } ?>
		<?php if (getOption('tinymce4_browser-spellcheck')) { ?>
		browser_spellcheck: true,
		<?php } ?>
		<?php if (getOption('tinymce4_browser-menu')) { ?>
		contextmenu: false,
		<?php } ?>
		directionality: "<?php echo $_zp_rtl_css ? 'rtl' : 'ltr'; ?>",
		relative_urls: false,
		content_css: "<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tinymce4/config/content.css",
<?php
if ($filehandler) {
	?>
		elements : "<?php echo $filehandler; ?>",
		file_browser_callback : <?php echo $filehandler; ?>,
	<?php
}
?>
<?php if (getOption('tinymce4_browser-menu')) { ?>
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table directionality",
			"emoticons template paste textpattern imagetools tinyzenpage"
		],
<?php } else { ?>
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"emoticons template paste textpattern imagetools tinyzenpage"
		],
<?php } ?>
		toolbar: false,
		setup: function(ed) {
			ed.on('change', function(e) {
				$('.dirty-check').addClass('dirty');
			});
		}
	});
</script>