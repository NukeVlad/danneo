<?php
/**
 * File:        /admin/includes/wysiwyg.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
if( ! defined("USEWYS")) exit();

global $WYSFORM, $WYSVALUE, $editor,  $conf, $tm;

/**
 * Language Site
 */
$langcode = $conf['langcode'];
$langs = new GlobIterator(ADMDIR.'/js/editor/tinymce/langs/*.js');

foreach ($langs as $file)
{
	if ($file->isFile()) $larray[] = $file->getBasename('.js');
}

// Default lang
if ( ! isset($larray) AND ! in_array($conf['langcode'], $larray)) $langcode = 'en';

/**
 * Type element
 */
$form_short = isset($form_short) ? $form_short : 'textshort';
$form_more = isset($form_more) ? $form_more : 'textmore';

/**
 * Init TinyMCE
 */
if(empty($editor))
{
	echo '	<script src="'.ADMPATH.'/js/editor/tinymce/tinymce.min.js"></script>
			<script>
			tinymce.init({
				theme : "modern",
				skin : "custom",
				mode : "exact",
				elements : "'.$form_short.'",
				language : "'.$langcode.'",
				height: 100,
				schema: "html5",
				convert_urls: false,
				menubar : false,
				plugins: "link",
				toolbar: "undo redo | bold italic underline | link unlink"
			});
			tinymce.init({
				theme : "modern",
				skin : "custom",
				mode : "exact",
				elements : "'.$form_more.'",
				language : "'.$langcode.'",
				height: 200,
				schema: "html5",
				convert_urls: false,
				plugins: [
					"advlist autolink link image lists charmap preview hr anchor pagebreak spellchecker",
					"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
					"save table contextmenu directionality emoticons template paste textcolor"
				],
				menu : {
					edit   : {title : "Edit"  , items : "undo redo | cut copy paste pastetext | selectall"},
					insert : {title : "Insert", items : "link anchor | image media | hr"},
					view   : {title : "View"  , items : "visualaid preview"},
					format : {title : "Format", items : "formats"},
					table  : {title : "Table" , items : "inserttable tableprops deletetable | cell row column"}
				},
				toolbar: "insertfile undo redo | bold italic | link | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | code fullscreen"
			});
			';
	if (isset($form_info)) 
	{
		echo '	tinymce.init({
				theme : "modern",
				skin : "custom",
				mode : "exact",
				elements : "'.$form_info.'",
				language : "'.$langcode.'",
				height: 150,
				schema: "html5",
				convert_urls: false,
				plugins: [
					"advlist autolink link image lists charmap preview hr anchor pagebreak spellchecker",
					"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
					"save table contextmenu directionality emoticons template paste textcolor"
				],
				menu : {
					edit   : {title : "Edit"  , items : "undo redo | cut copy paste pastetext | selectall"},
					insert : {title : "Insert", items : "link anchor | image media | hr"},
					view   : {title : "View"  , items : "visualaid preview"},
					format : {title : "Format", items : "formats"},
					table  : {title : "Table" , items : "inserttable tableprops deletetable | cell row column"}
				},
				toolbar: "insertfile undo redo | bold italic | link | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | code fullscreen"
			});
			';
	}
	echo '	</script>';
	$editor = 1;
}

/**
 * Init Form
 */
$tm->textarea($WYSFORM, 7, 50, $WYSVALUE, 0);
