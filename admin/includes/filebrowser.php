<?php
/**
 * File:        /admin/includes/filebrowser.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Базовые константы
 */
define('READCALL', 1);
define('PERMISS', 'filebrowser');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $objdir, $obj, $ims, $sess, $conf, $lang;

	/**
	 *  Список разрешенных админов
	 */
	if($ADMIN_PERM==1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
		(
			'index', 'folder', 'files',
			'newdir', 'newdirsave', 'upload',
			'realupload', 'realdel', 'del', 'realmassdel', 'move', 'realmove',
			'cmods', 'cmodssave',
			'openzip', 'unzip',
			'rename', 'resave', 'thumb', 'viewflv', 'folderdel', 'folderdelreal',
			'imagine', 'imaginesave',
			'personal', 'realpersonal',
			'quickupload', 'moreupload',
			'allupload', 'alluploadsave'
		);
		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Атрибуты
		 */
		function this_selcmod($str, $num, $lock = true)
		{
			global $lang;

			$r = '<option value="0"'.((substr($str, $num, 1) == 0) ? " selected" : "").'> 0 » '.$lang['cmod_0'].'</option>';
			$r.= '<option value="1"'.((substr($str, $num, 1) == 1) ? " selected" : "").'> 1 » '.$lang['cmod_1'].'</option>';
			$r.= '<option value="2"'.((substr($str, $num, 1) == 2) ? " selected" : "").'> 2 » '.$lang['cmod_2'].'</option>';
			$r.= '<option value="3"'.((substr($str, $num, 1) == 3) ? " selected" : "").'> 3 » '.$lang['cmod_3'].'</option>';
			$r.= '<option value="4"'.((substr($str, $num, 1) == 4) ? " selected" : "").'> 4 » '.$lang['cmod_4'].'</option>';
			$r.= '<option value="5"'.((substr($str, $num, 1) == 5) ? " selected" : "").'> 5 » '.$lang['cmod_5'].'</option>';
			$r.= '<option value="6"'.((substr($str, $num, 1) == 6) ? " selected" : "").'> 6 » '.$lang['cmod_6'].'</option>';
			$r.= '<option value="7"'.((substr($str, $num, 1) == 7) ? " selected" : "").'> 7 » '.$lang['cmod_7'].'</option>';

			return $r;
		}

		/**
		 * Список директорий для перехода
		 */
		function this_selfolder($current, $objdir)
		{
			static $dir;
			$open = opendir(WORKDIR.'/up'.$current);
			while ($fitem = readdir($open))
			{
				$newcurrent = $current.'/'.$fitem;
				if ($fitem != '.' AND $fitem != '..' AND is_dir(WORKDIR.'/up'.$newcurrent))
				{
					$dir.= '<option value="'.$newcurrent.'/"'.(($newcurrent.'/' == $objdir) ? ' selected' : '').'>'.$newcurrent.'/</option>';
					this_selfolder($newcurrent, $objdir);
				}
			}
			return $dir;
		}

		/**
		 * Список директорий массивом
		 */
		function this_arrfolder($current = '')
		{
			static $dir;
			if ($open = opendir(WORKDIR.'/up'.$current)) {
			while ($fitem = readdir($open))
			{
				$newcurrent = $current.'/'.$fitem;
				if ($fitem != '.' AND $fitem != '..' AND is_dir(WORKDIR.'/up'.$newcurrent))
				{
					$v = $newcurrent.'/';
					$dir[$v] = $v;
					this_arrfolder($newcurrent);
				}
			}
			}
			return $dir;
		}

		/**
		 * $objdir
		 */
		$objdir = str_replace(array('up/', '../', './', '..', '%2F'), '', addslashes($objdir));
		$obj = str_replace(array('up/', '../', './', '..', '%2F'), '', basename($obj));
		$objcheck = this_arrfolder();
		$objdir = in_array($objdir, $objcheck) ? $objdir : '/';

		if ($ims == 1) {
			$ims = 1;
		} elseif ($ims == 2) {
			$ims = 2;
		} else {
			$ims = 0;
		}

		/**
		 * Удаление директорий
		 */
		function remote($dir)
		{
			$tempdir = opendir($dir);
			while ($file = readdir($tempdir))
			{
				$file = basename($file);
				if(is_file($dir.'/'.$file))
				{
					unlink($dir.'/'.$file);
				}
				elseif (is_dir($dir.'/'.$file) AND $file != '.' AND $file != '..')
				{
					remote($dir.'/'.$file);
				}
			}
			closedir($tempdir);
			return rmdir($dir);
		}

		function back($dir)
		{
			$path = '';
			$sumpath = explode('/', $dir);
			for ($i = 0; $i < sizeof($sumpath) - 2; $i ++)
			{
				if ( ! empty($sumpath[$i]))
				{
					$path.='/'.$sumpath[$i];
				}
			}
			return $path;
		}

		/**
		 * Список директорий
		-----------------------*/
		if ($_REQUEST['dn'] == 'folder')
		{
			global $sess, $ims;

			$path = '';

			if (empty($objdir)) {
				$objdir = '/';
				$ltop = '';
			} else {
				$path = back($objdir);
				$lpath = $path.'/';
				$ltop = $lpath.'/';
			}

			if ($objdir == '/') {
				$ltop = '';
			}

			$d = this_selfolder('', $objdir);

			echo '	<table style="width:100%">
						<tr>
							<th class="al" colspan="4">'.$lang['dir_name'].'</th>
						</tr>
						<tr>
							<td colspan="4">
								<form>
									<select onchange="$.filebrowserupdate(\''.$sess['hash'].'\',this.options[this.selectedIndex].value'.(($ims) ? ' + \'&ims='.$ims.'\'' : '').');" style="width:100%;">
										<option value="/">/</option>
										'.$d.'
									</select>
								</form>
							</td>
						</tr>';
			if ( ! empty($ltop))
			{
				echo '	<tr>
							<td width="5%">
								<a href="javascript:$.filebrowserupdate(\''.$sess['hash'].'\',\''.$lpath.(($ims) ? '&ims='.$ims : '').'\');"><img src="'.ADMPATH.'/template/library/folder1.gif" alt="'.$lang['dir_up'].'" /></a>
							</td>
							<td colspan="2">
								<a href="javascript:$.filebrowserupdate(\''.$sess['hash'].'\',\''.$lpath.(($ims) ? '&ims='.$ims : '').'\');">'.$lang['dir_up'].'</a>
							</td>
						</tr>';
			}
			$folder_dir = opendir(WORKDIR.'/up'.$objdir);
			$dirs = array();
			while ($cont = readdir($folder_dir))
			{
				if ($cont != '.' AND $cont != '..')
				{
					if (is_dir(WORKDIR.'/up'.$objdir.$cont))
					{
						$perms = sprintf("%o", (fileperms(WORKDIR.'/up'.$objdir.$cont)) & 0777);
						$dirs[]= '	<tr>
										<td width="5%">
											<a href="javascript:$.filebrowserupdate(\''.$sess['hash'].'\',\''.$objdir.$cont.'/'.(($ims) ? '&ims='.$ims : '').'\');"><img src="'.ADMPATH.'/template/library/folder.gif"></a>
										</td>
										<td>
											<a href="javascript:$.filebrowserupdate(\''.$sess['hash'].'\',\''.$objdir.$cont.'/'.(($ims) ? '&ims='.$ims : '').'\');">'.$cont.'</a>
										</td>
										<td>
											<a href="javascript:$.filebrowserfolders(\'dn=cmods&ops='.$sess['hash'].'&atr='.$perms.'&objdir='.$objdir.$cont.'/&js=folder'.(($ims) ? '&ims='.$ims : '').'\');"><img src="'.ADMPATH.'/template/images/edit.gif"></a>&nbsp;
											<a href="javascript:$.filebrowserfolders(\'dn=folderdel&ops='.$sess['hash'].'&objdir='.$objdir.$cont.'/\');"><img src="'.ADMPATH.'/template/images/del.gif"></a>
										</td>
									</tr>';
					}
				}
			}
			closedir($folder_dir);
			natsort($dirs);
			if ($dirs) {
				foreach ($dirs as $value) {
					echo $value;
				}
			}
			echo '	</table>';
		}

		/**
		 * Функция сортировки файлов
		 */
		function compare_files($temp, $sotemp)
		{
			global $in, $so;

			$a = $temp[$so];
			$b = $sotemp[$so];
			if (is_string($a) OR is_string($b)) {
				$result = strcoll($a, $b);
			} else {
				$result = $a-$b;
			}
			if ($in == 'desc') {
				return -$result;
			} else {
				return $result;
			}
		}

		/**
		 * Иконки файлов
		 */
		function compare_icon($cont)
		{
			global $sess;

			if (preg_match("#^(gif|jpg|jpeg|png|webp)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/image.gif" alt=".gif .jpg .png" />';
			}
			elseif (preg_match("#^(txt)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/text.gif" alt="Txt" />';
			}
			elseif (preg_match("#^(doc|dot|wiz|wzs)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/msword.gif" alt="MS-Word" />';
			}
			elseif (preg_match("#^(xls|xl|xla|xlb|xlc|xld|xlk|xll|xlm|xlt|xlv|xlw)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/msex.gif" alt="MS-Excel" />';
			}
			elseif (preg_match("#^(flv|wmv|avi|asf|mpeg|nsv|mov|qt)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/move.gif" alt=".flv .wmv .avi .asf .mpeg .nsv .mov .qt" />';
			}
			elseif (preg_match("#^(wav|mp2|mp3|mp4|vqf|midi|mid|mmf)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/audio.gif" alt=".wav .mp2 .mp3 .mp4 .vqf .midi .mid .mmf" />';
			}
			elseif (preg_match("#^(phps|php|php2|php3|php4|asp|asa|cgi|shtml|js|jsp|pl|phtml)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/webscript.gif" alt="web-script" />';
			}
			elseif (preg_match("#^(htaccess)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/security.gif" alt=".htaccess" />';
			}
			elseif (preg_match("#^(sql)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/sql.gif" alt="sql" />';
			}
			elseif (preg_match("#^(html|htm)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/webpage.gif" alt="Html" />';
			}
			elseif (preg_match("#^(zip|rar|cab|ace|gzip|tar|tgz|jar)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/zip.gif" alt=".zip .rar .cab .ace .gzip .tar .tgz .jar" />';
			}
			elseif (preg_match("#^(pdf)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/pdf.gif" alt="Pdf" />';
			}
			elseif (preg_match("#^(psd|ai)$#i",$cont)) {
				return '<img src="'.ADMPATH.'/template/library/psd.gif" alt="Adobe" />';
			}
			else {
				return '<img src="'.ADMPATH.'/template/library/unknown.gif" alt="Unknown type" />';
			}
		}

		/**
		 * Список файлов
		 -------------------*/
		if ($_REQUEST['dn'] == 'files')
		{
			global $sess, $objdir, $conf, $lang, $in, $so, $ims;

			$in = ( ! isset($in)) ? 'desc' : preparse($in, THIS_TRIM, 0, 4);
			$so = ( ! isset($so)) ? 'last' : preparse($so, THIS_TRIM, 0, 4);
			if ($in != 'asc' AND $in != 'desc') $in = 'asc';
			if ($so != 'name' AND $so != 'size' AND $so != 'last' AND $so != 'perm') $so = 'name';
			$folder_dir = opendir(WORKDIR.'/up'.$objdir);
			$filarr = array();
			while ($cont = readdir($folder_dir))
			{
				if ($cont != '.' AND $cont != '..')
				{
					$localobjdir = WORKDIR.'/up'.$objdir;
					$workobjdir = 'up'.$objdir;
					if ( ! is_dir(WORKDIR.'/up'.$objdir.$cont))
					{
						$filarr['up'.$objdir.$cont]['name'] = $cont;
						$filarr['up'.$objdir.$cont]['size'] = filesize($localobjdir.$cont);
						$filarr['up'.$objdir.$cont]['psize'] = outfilesize($localobjdir.$cont);
						$filarr['up'.$objdir.$cont]['perm'] = sprintf('%o',(fileperms($localobjdir.$cont)) & 0777);
						$filarr['up'.$objdir.$cont]['last'] = filemtime($localobjdir.$cont);
						$ext = outfile($localobjdir.$cont);
						$filarr['up'.$objdir.$cont]['info'] = $ext['ext'];
						$filarr['up'.$objdir.$cont]['icon'] = compare_icon($ext['ext']);
					}
				}
			}
			closedir($folder_dir);
			usort($filarr, 'compare_files');
			$types = ($in=='asc') ? 'desc' : 'asc';
			echo '	<script>
					var con = "'.$lang['confirm_del'].'";
					$(function() {
						$("#workfiles #checkall").click(function(){
							var checked_status = this.checked;
							$("#workfiles input[type=checkbox]").each(function(){
								this.checked = checked_status;
							});
						});
						$("#workfiles").submit(function() {
							var inputs = [];
							$(":input", this).each(function() {
								var label = $(this);
								if (label.attr("type") == "checkbox") {
									if(label.is(":checked")) inputs.push(this.name + "=" + escape(this.value));
								} else {
									inputs.push(this.name + "=" + escape(this.value));
								}
							});
							jQuery.ajax({
								data    : inputs.join("&"),
								url     : this.action,
								error   : function() { alert($.errors); },
								success : function(data) {
									if (data.length > 0) {
										$("#files .fb-pad").html(data);
									}
								}
							});
							return false;
						});
					});
					</script>';
			echo '	<form action="'.ADMPATH.'/includes/filebrowser.php" method="post" name="workfiles" id="workfiles">
					<table id="sortfiles" style="width: 100%;">
						<tr>
							<th>&nbsp;</th>
							<th class="'.(($so=='name') ? ' fb-work-sort' : '').'">
								<a class="title" href="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'&so=name&in='.$types.'&ims='.$ims.'\')">
									'.(($so=='name') ?  '<img src="'.ADMPATH.'/template/images/total'.$types.'.gif" />' : '').'
									'.$lang['file_name'].'
								</a>
							</th>
							<th class="'.(($so=='size') ? ' fb-work-sort' : '').'">
								<a class="title" href="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'&so=size&in='.$types.'&ims='.$ims.'\')">
									'.(($so=='size') ?  '<img src="'.ADMPATH.'/template/images/total'.$types.'.gif" />' : '').'
									'.$lang['file_size'].'
								</a>
							</th>
							<th class="'.(($so=='last') ? ' fb-work-sort' : '').'">
								<a class="title" href="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'&so=last&in='.$types.'&ims='.$ims.'\')">
									'.(($so=='last') ?  '<img src="'.ADMPATH.'/template/images/total'.$types.'.gif" />' : '').'
									'.$lang['file_modif'].'
								</a>
							</th>
							<th class="'.(($so=='perm') ? ' fb-work-sort' : '').'">
								<a class="title" href="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'&so=perm&in='.$types.'&ims='.$ims.'\')">
									'.(($so=='perm') ?  '<img src="'.ADMPATH.'/template/images/total'.$types.'.gif" />' : '').'
									'.$lang['file_cmod'].'
								</a>
							</th>
							<th>'.$lang['sys_manage'].'</th>
							<th class="ac"><input type="checkbox" name="checkall" id="checkall"></th>
						</tr>';
			foreach ($filarr as $key => $val)
			{
				if (isset($val['name']) AND $val['name'] != '.htaccess' AND $val['name'] != 'index.html')
				{
					$im = (preg_match("#^(gif|jpg|jpeg|png|webp)$#i", $val['info'])) ? 1 : 0;
					echo '	<tr>
								<td style="width:3%" class="ac">
									'.$val['icon'].'
								</td>
								<td class="work-lite">';
					if ($im) {
						echo '		<img src="'.ADMPATH.'/includes/filebrowser.php?dn=thumb&amp;x=36&amp;h=27&amp;r=no&amp;ops='.$sess['hash'].'&amp;objdir='.$objdir.'&amp;obj='.$val['name'].'"><br />';
					}
					echo '			<a href="javascript:$.filebrowser'.(($ims) ? 'ims' : '').'insert(\''.$val['name'].'\');">'.$val['name'].'</a>
								</td>
								<td>'.$val['psize'].'</td>
								<td>'.format_time($val['last'],1).'</td>
								<td>
									<a href="javascript:$.filebrowserfiles(\'dn=cmods&ops='.$sess['hash'].'&atr='.$val['perm'].'&objdir='.$objdir.'&obj='.$val['name'].'&js=files\');">'.$val['perm'].'</a>
								</td>
								<td class="gov">
									<a href="javascript:$.filebrowserdel(\''.$sess['hash'].'\',\''.$objdir.'\',\''.$val['name'].'\')"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/images/del.gif" /></a>
									<a href="javascript:$.filebrowserfiles(\'dn=rename&ops='.$sess['hash'].'&objdir='.$objdir.'&obj='.$val['name'].'\')"><img alt="'.$lang['file_rename'].'" src="'.ADMPATH.'/template/images/edit.gif" /></a>';
					if ($val['info'] == 'zip') {
						echo '		<a href="javascript:$.filebrowserfiles(\'dn=openzip&ops='.$sess['hash'].'&objdir='.$objdir.'&obj='.$val['name'].'\')"><img alt="'.$lang['file_zip'].'" src="'.ADMPATH.'/template/images/prev.gif" /></a>';
					}
					if ($im) {
						echo '		<a href="javascript:$.filebrowserfiles(\'dn=imagine&ops='.$sess['hash'].'&objdir='.$objdir.'&obj='.$val['name'].'\')"><img src="'.ADMPATH.'/template/images/prev.gif" /></a>';
					}
					if ($val['info'] == 'flv') {
						echo '		<a href="javascript:$.filebrowserfiles(\'dn=viewflv&ops='.$sess['hash'].'&objdir='.$objdir.'&obj='.$val['name'].'\')"><img src="'.ADMPATH.'/template/images/prev.gif" /></a>';
					}
					echo '		</td>
								<td class="ac">
									<input type="checkbox" id="allfiles['.$val['name'].']" name="allfiles['.$val['name'].']" value="yes">
								</td>';
				}
			}
			echo '		<tr>
							<td colspan="7" class="ar">
								'.$lang['all_mark_work'].':&nbsp;
								<select name="dn">
									<option value="move">'.$lang['all_move'].'</option>
									<option value="del">'.$lang['all_delet'].'</option>
								</select>&nbsp;
								<input type="hidden" name="objdir" value="'.$objdir.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>';
		}

		/**
		 * Основной интерфейс
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $lang, $sess, $field, $ims;

			echo '	<div class="section">
					<table class="fb-work">';
			if ($ims == 0) {
				echo '	<caption>'.$lang['filebrowser'].'</caption>';
			}
			echo '		<tr>
							<th><a class="fb-link" href="javascript:$.filebrowserparent(\'folder\',\'dn=newdir&ops='.$sess['hash'].(($ims) ? '&ims='.$ims : '').'\')">'.$lang['dir_new'].'</a></th>
							<th><a class="fb-link" href="javascript:$.filebrowserfiles(\'dn=upload&ops='.$sess['hash'].(($ims) ? '&ims='.$ims : '').'\')">'.$lang['file_upload'].'</a></th>
						</tr>
						<tr>
							<td width="25%">
								<div id="folder"><div class="fb-pad"></div></div>
							</td>
							<td width="75%">
								<div id="files"><div class="fb-pad"></div></div>
							</td>
						</tr>
						<tr>
							<th class="ar" width="25%" style="border-bottom:0;">';
			if ($ims == 1 OR $ims == 2) {
				echo '			<select name="field" id="field" style="width:250px;">
									<option value="thumb">'.$lang['all_image_thumb'].'</option>
									<option value="imagel">'.$lang['all_image'].'</option>
								</select>';
			} else {
				echo '			<strong>'.$lang['all_path'].': </strong>';
				if (is_array($field)) {
					echo '		<select name="field" id="field">';
					foreach ($field as $k => $v) {
						echo '		<option value="'.$v.'">'.$v.'</option>';
					}
					echo '		</select>';
				}
			}
			echo '			</th>
							<th width="75%" style="border-bottom:0;">';
			if ($ims == 1 OR $ims == 2) {
				echo '			<input type="text" name="thumb" id="thumb" size="35" style="width:80%;" value="" class="readonly" readonly="readonly">';
			}
			echo '				<input type="'.(($ims) ? 'hidden' : 'text').'" name="patch" id="patch" size="35" style="width:80%;" value="'.$objdir.'"'.(($ims) ? '' : ' class="readonly" readonly="readonly"').'>
							</th>
						</tr>';
			if ($ims == 1) {
				echo '	<tr>
							<th class="ar" width="25%">
								<input class="side-button" onclick="javascript:$.filebrowserimsload();" value="'.$lang['all_paste'].'" type="button">
							</th>
							<th width="75%">
								<input type="text" name="imagel" id="imagel" size="35" style="width:80%;" value="" class="readonly" readonly="readonly">
							</th>
						</tr>';
			} elseif ($ims == 2) {
				echo '	<tr>
							<th class="ar" width="25%">
								<input class="side-button" onclick="javascript:$.moreimsload();" value="'.$lang['all_paste'].'" type="button">
							</th>
							<th width="75%">
								<input type="text" name="imagel" id="imagel" size="35" style="width:80%;" value="" class="readonly" readonly="readonly">
							</th>
						</tr>';
			}
			echo '	</table>
					</div>
					<script>
					$(function(){
						$("#fb-close").click(function(){
							$("#bg-overlay, #bg-overlay-content").hide();
							return false;
						});
						$("#fb-ref").click(function(){
							$patch = $("#patch").val();
							if($patch.length > 0){
								jQuery.filebrowserupdate("'.$sess['hash'].'", $patch);
							}
							return false;
						});
					});
					</script>';
		}

		/**
		 * Новая директория
		 ----------------------*/
		if ($_REQUEST['dn'] == 'newdir')
		{
			global $sess, $lang, $objdir, $ims;

			echo '	<script>
					$(function() {
						$("#foldernew").submit(function() {
							var inputs = [];
							$(":input", this).each(function() {
								inputs.push(this.name + "=" + escape(this.value));
							});
							jQuery.ajax({
								data    : inputs.join("&"),
								url     : this.action,
								error   : function() { alert($.errors); },
								success : function(data) {
									if (data == 1) {
										$.filebrowserfolders(\'dn=folder&ops='.$sess['hash'].'&objdir='.$objdir.(($ims) ? '&ims='.$ims : '').'\');
									} else {
										alert($.errors);
									}
								}
							});
							return false;
						});
					});
					</script>
					<form id="foldernew" name="foldernew" action="'.ADMPATH.'/includes/filebrowser.php?dn=newdirsave&amp;ops='.$sess['hash'].(($ims) ? '&ims='.$ims : '').'" method="post">
					<table>
						<tr>
							<th>'.$lang['dir_new'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_path'].'</td>
						</tr>
						<tr>
							<td><input type="text" name="objdir" value="'.$objdir.'" size="30" class="readonly" readonly="readonly"></td>
						</tr>
						<tr>
							<td>'.$lang['all_name'].'</td>
						</tr>
						<tr>
							<td><input type="text" name="newdir" size="30"></td>
						</tr>
						<tr>
							<td>'.$lang['dir_creat_hint'].'</td>
						</tr>
						<tr>
							<td>
								<input type="button" onclick="javascript:$.filebrowserfolders(\'dn=folder&ops='.$sess['hash'].'&objdir='.$objdir.(($ims) ? '&ims=1' : '').'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
								<input type="submit" class="side-button" value="'.$lang['all_save'].'">
							</td>
						</tr>
					</table>
					</form>';
		}

		/**
		 * Создание новой директории
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'newdirsave')
		{
			global $newdir;

			$newdir = str_replace(array('up/', '../', './', '..', '/', '%2F'), '', basename($newdir));
			if (preparse($newdir,THIS_SYMNUM) == 0 AND mkdir(WORKDIR.'/up'.$objdir.$newdir))
			{
				$html = fopen(WORKDIR.'/up'.$objdir.$newdir.'/index.html', "wb");
				fwrite($html, '<html><head><meta http-equiv="Content-Type" content="text/html; charset='.$conf['langcharset'].'"></head><body></body></html>');
				fclose($html);
				$error = 1;
			} else {
				$error = 0;
			}
			echo $error;
			exit();
		}

		/**
		 * Загрузка файлов
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'upload')
		{
			global $sess, $conf, $lang;

			echo '<script src="'.ADMPATH.'/js/jquery.ajax.upload.js"></script>';
			echo '	<script>
					$(function() {
						new AjaxUpload("submit", {
							action   : $("form#uploadform").attr("action"),
							name     : "file",
							onSubmit : function(file, extension) {
								this.disable();
								$("#submit").hide();
								$("#goback").hide();
								$("#loading").show();
								this.setData({
									objdir : $("form#uploadform #objdir").val(),
									thumb  : $("select[name=thumb]").val(),
									resize : $("select[name=resize]").val(),
									width  : $("select[name=width]").val(),
									height : $("select[name=height]").val(),
									rbig   : $("select[name=rbig]").val(),
									wbig   : $("select[name=wbig]").val(),
									hbig   : $("select[name=hbig]").val(),
									injpg  : $("select[name=injpg]").val(),
									wmark  : $("select[name=wmark]").val(),
									unique : $("select[name=unique]").val()
								});
							},
							onComplete : function(file, response){
								$("#loading").hide();
								$("#submit").show();
								$("#goback").show();
								$("<p></p>").appendTo("#olupload").html(response);
								this.enable();
							}
						});
					});
					</script>';

			$mszf = ini_get('post_max_size');
			$mte  = ini_get('max_execution_time');

			// Custom settings loading (added Staff)
			$upset = array();
			if ( ! empty($conf['user_upload']))
			{
				$upset = Json::decode($conf['user_upload']);
			}
			else
			{
				$upset = array
				(
					'thumb'  => 'yes',
					'resize' => 'crop',
					'width'  => '145',
					'height' => '90',
					'rbig'   => 'yes',
					'wbig'   => '800',
					'hbig'   => '600',
					'unique' => 'yes',
					'injpg'  => 'yes',
					'wmark'  => 'no'
				);
			}

			echo '	<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realupload&amp;ops='.$sess['hash'].'" method="post" enctype="multipart/form-data" name="uploadform" id="uploadform">
					<table class="work">
						<tr>
							<th class="ac" colspan="2">'.$lang['file_upload'].'</th>
						</tr>
						<tr>
							<td class="ar pw35">'.$lang['all_path'].'</td>
							<td><input type="text" id="objdir" name="objdir" value="'.$objdir.'" size="30" class="readonly" readonly="readonly"></td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['all_image_thumb'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['miniature_yes'].'</td>
							<td>
								<select class="sw85" name="thumb">
									<option value="yes" '.(($upset['thumb'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['thumb'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['process'].'</td>
							<td>
								<select name="resize">
									<option value="crop" '.(($upset['resize'] == 'crop') ? 'selected' : '').'>'.$lang['crop_resize'].'</option>
									<option value="symm" '.(($upset['resize'] == 'symm') ? 'selected' : '').'>'.$lang['all_resize'].'</option>
									<option value="scal" '.(($upset['resize'] == 'scal') ? 'selected' : '').'>'.$lang['scale_resize'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select name="width" class="sw85">';
			for ($w = 70; $w <= 300; $w ++) {
				echo '				<option value="'.$w.'"'.(($w == $upset['width']) ? ' selected' : '').'>'.$w.' px</option>';
									$w = $w + 4;
			}
			echo '				</select> &nbsp;&#215;&nbsp;
								<select name="height" class="sw85">';
			for ($h = 70; $h <= 300; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $upset['height']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 4;
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['all_image_big'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['image_resize'].'</td>
							<td>
								<select class="sw85" name="rbig">
									<option value="yes" '.(($upset['rbig'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['rbig'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select class="sw85" name="wbig">';
			for ($w = 300; $w <= 1900; $w ++) {
				echo '				<option value="'.$w.'"'.(($w == $upset['wbig']) ? ' selected' : '').'>'.$w.' px</option>';
									$w = $w + 99;
			}
			echo '				</select> &nbsp;&#215;&nbsp;
								<select class="sw85" name="hbig">';
			for ($h = 240; $h <= 1200; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $upset['hbig']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 59;
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['opt_set'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['unique_file'].'</td>
							<td>
								<select class="sw85" name="unique">
									<option value="yes" '.(($upset['unique'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['unique'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['convert_in_jpg'].'</td>
							<td>
								<select class="sw85" name="injpg">
									<option value="yes" '.(($upset['injpg'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['injpg'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['watermark'].'</td>
							<td>
								<select class="sw85" name="wmark">
									<option value="no" '.(($upset['wmark'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
									<option value="yes" '.(($upset['wmark'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input id="goback" type="button" class="side-button" value="'.$lang['all_goback'].'" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.(($ims) ? '&ims=1' : '').'\')">&nbsp;
								<input id="submit" type="submit" class="side-button" value="'.$lang['file_review'].'">
								<img id="loading" style="display:none;" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loading.gif" alt="" />
								<div class="fl" id="olupload"><img class="none" id="loading" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loading.gif" alt="" /></div>
							</td>
						</tr>
					</table>
					</form>';
		}

		/**
		 * Загрузка файлов (сохранение)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'realupload')
		{
			global $conf, $objdir, $file, $thumb, $resize, $width, $height, $rbig, $wbig, $hbig, $injpg, $wmark, $unique;

			$filename = $obj = '&#8212; &#8212; &#8212;';
			$width = intval($width);
			$height = intval($height);

			require_once(ADMDIR.'/core/classes/Image.php');
			$image = new Image();

			if(isset($_FILES['file']) AND ! empty($_FILES['file']['name']))
			{
				$extname  = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
				$exttype  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				$filename = preg_replace("/([ &%§]+)/", '', mb_strtolower(trim($extname)));
				$tmpname  = $_FILES['file']['tmp_name'];

				// Если создавать уникальное имя файла
				if (isset($unique) AND $unique == 'yes')
				{
					$newname = date("ymd", time()).'_'.mt_rand(0, 9999);
					$filename_thumb = $newname.'_thumb.'.$exttype;
					$filename = $newname.'.'.$exttype;
				}
				else
				{
					$filename_thumb = $filename.'_thumb.'.$exttype;
					$filename = $filename.'.'.$exttype;
				}

				// Если есть копии добавляем префикс copy_
				if ($injpg == 'yes' AND $exttype != 'jpg')
				{
					if (file_exists(WORKDIR.'/up'.$objdir.$extname.'.jpg')) {
						$filename = 'copy_'.$filename;
					}
				}
				else
				{
					if (file_exists(WORKDIR.'/up'.$objdir.$filename)) {
						$filename_thumb = 'copy_'.$extname.'_thumb.'.$exttype;
						$filename = 'copy_'.$filename;
					}
				}

				// Копируем файл в указанную папку
				if (move_uploaded_file($tmpname, WORKDIR.'/up'.$objdir.$filename))
				{
					$fileinfo = pathinfo(WORKDIR.'/up'.$objdir.$filename);
					$obj = mb_strtolower($fileinfo['extension']);

					// Если файл изображение
					if (preg_match("#^(gif|jpg|jpeg|png|bmp|webp)$#i", $obj))
					{
						$image->start();

						// Если уменьшать большое изображение
						if (isset($rbig) AND $rbig == 'yes' AND file_exists(WORKDIR.'/up'.$objdir.$filename))
						{
							$image->createthumb
								(
									WORKDIR.'/up'.$objdir.$filename,
									WORKDIR.'/up'.$objdir,
									$filename,
									$wbig,
									$hbig,
									'symm'
								);
						}

						// Если конвертировать в jpg
						if ($injpg == 'yes' AND $exttype != 'jpg')
						{
							list($fjpg, $ext) = explode(".", $filename);
							$file_jpg = $fjpg.'.jpg';
							$image->imgconvert(WORKDIR.'/up'.$objdir.$filename, WORKDIR.'/up'.$objdir.$file_jpg);
							$filename_thumb = $fjpg.'_thumb.jpg';
							$filename = $fjpg.'.jpg';
						}

						// Если делать уменьшенную копию
						if (isset($thumb) AND $thumb == 'yes' AND file_exists(WORKDIR.'/up'.$objdir.$filename))
						{
							$image->createthumb
								(
									WORKDIR.'/up'.$objdir.$filename,
									WORKDIR.'/up'.$objdir,
									$filename_thumb,
									$width,
									$height,
									$resize
								);
						}

						// Если ватермарка
						if ($conf['wateruse'] == 'img' AND ! empty($conf['waterpatch']) AND $wmark == 'yes')
						{
							$image->createwater(WORKDIR.'/up'.$objdir.$filename, WORKDIR.'/'.$conf['waterpatch']); // изображением
						}
						elseif ($conf['wateruse'] == 'txt' AND ! empty($conf['watertext']) AND $wmark == 'yes')
						{
							$image->createwater(WORKDIR.'/up'.$objdir.$filename, 0, 1, $conf['watertext']); // текстом
						}
					}
				}
			}

			echo compare_icon($obj).'&nbsp;<strong>'.$filename.'</strong>&nbsp;('.outfilesize(WORKDIR.'/up'.$objdir.$filename).')';

			$upsave = array
			(
				'thumb'  => $thumb,
				'resize' => $resize,
				'width'  => $width,
				'height' => $height,
				'rbig'   => $rbig,
				'wbig'   => $wbig,
				'hbig'   => $hbig,
				'unique' => $unique,
				'injpg'  => $injpg,
				'wmark'  => $wmark
			);

			$upsave = Json::encode($upsave);
			$db->query("UPDATE ".$basepref."_settings SET setval = '".$upsave."' WHERE setname = 'user_upload'");

			$cache->cachesave(1);
			exit();
		}

		/**
		 * Удаление файла
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'realdel')
		{
			global $objdir, $obj, $sess, $lang;

			$error = 0;
			if (unlink(WORKDIR.'/up'.$objdir.$obj)) {
				$error = 1;
			}
			echo $error;
			exit();
		}

		/**
		 * Подтверждение перемещения файлов
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'move')
		{
			global $allfiles, $lang, $sess;

			if (preparse($allfiles, THIS_ARRAY)==1)
			{
				$temparray = $allfiles;
				$count = count($temparray);
				$hidden = '';
				$listen = '';
				foreach ($allfiles as $key => $id)
				{
					if ($key != '.htaccess') {
						$hidden.= '<input type="hidden" name="allfiles['.$key.']" value="yes">';
					}
				}
				echo '	<script>
						$(function() {
							$("#formmove").submit(function() {
								var inputs = [];
								$(":input", this).each(function() {
									var label = $(this);
									if (label.attr("type") == "checkbox") {
										if(label.is(":checked")) inputs.push(this.name + "=" + escape(this.value));
									} else {
										inputs.push(this.name + "=" + escape(this.value));
									}
								});
								jQuery.ajax({
									data    : inputs.join("&"),
									url     : this.action,
									error   : function() { alert($.errors); },
									success : function(data) {
										$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\');
									}
								});
								return false;
							});
						});
						</script>';
				echo '	<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realmove&amp;ops='.$sess['hash'].'" method="post" name="formmove" id="formmove">
						<table class="work">
							<tr>
								<th>&nbsp;</th>
								<th>'.$lang['all_mark_work'].' &nbsp; &#8260; &nbsp; '.$lang['all_move'].' - '.$count.'</th>
							</tr>
							<tr>
								<td class="ar">'.$lang['all_dir'].'</td>
								<td>
									<select name="objmove" id="objmove">
										<option value="/">/</option>
										'.this_selfolder('', $objdir).'
									</select>
								</td>
							</tr>
							<tr>
								<td class="ar">'.$lang['del_original'].'</td>
								<td><input name="delor" value="yes" type="checkbox"></td>
							</tr>
							<tr class="tfoot">
								<td>&nbsp;</td>
								<td class="al">
									'.$hidden.'
									<input type="hidden" name="objdir" value="'.$objdir.'">
									<input id="goback" type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
									<input class="side-button" value=" '.$lang['all_go'].' " type="submit">
								</td>
							</tr>
						</table>
						</form>';
			}
		}

		/**
		 * Перемещение файлов (сохранение)
		 ------------------------------------*/
		if($_REQUEST['dn'] == 'realmove')
		{
			global $delor, $objmove, $allfiles;

			if (is_array($allfiles) AND in_array($objmove, $objcheck))
			{
				foreach ($allfiles as $id => $v)
				{
					$id = str_replace(array('up/', '../', './', '..', '/'), '', $id);
					if (file_exists(WORKDIR.'/up'.$objdir.$id))
					{
						copy(WORKDIR.'/up'.$objdir.$id, WORKDIR.'/up'.$objmove.$id);
						if ($delor == 'yes') {
							unlink(WORKDIR.'/up'.$objdir.$id);
						}
					}
				}
			}
		}

		/**
		 * Подтверждение удаления файлов
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $allfiles, $lang, $sess;

			if (preparse($allfiles, THIS_ARRAY)==1)
			{
				$temparray = $allfiles;
				$count = count($temparray);
				$hidden = '';
				$listen = '';
				foreach ($allfiles as $key => $id)
				{
					if ($key != '.htaccess') {
						$hidden.= '<input type="hidden" name="allfiles['.$key.']" value="yes">';
					}
				}
				echo '	<script>
						$(function() {
							$("#formdel").submit(function() {
								var inputs = [];
								$(":input", this).each(function() {
									inputs.push(this.name + "=" + escape(this.value));
								});
								jQuery.ajax({
									data    : inputs.join("&"),
									url     : this.action,
									error   : function() { alert($.errors); },
									success : function(data) {
										$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\');
									}
								});
								return false;
							});
						});
						</script>
						<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realmassdel&amp;ops='.$sess['hash'].'" method="post" name="formdel" id="formdel">
						<table class="work">
							<tr>
								<th class="ac" colspan="2">
									'.$lang['all_mark_work'].' &nbsp; &#8260; &nbsp; '.$lang['all_delet'].' - '.$count.'
								</th>
							</tr>
							<tr class="tfoot">
								<td>
									'.$hidden.'
									<input type="hidden" name="objdir" value="'.$objdir.'">
									<input id="goback" type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
									<input class="side-button" value=" '.$lang['all_go'].' " type="submit">
								</td>
							</tr>
						</table>
						</form>';
			}
		}

		/**
		 * Удаление файлов (сохранение)
		 ---------------------------------*/
		if ($_REQUEST['dn'] == 'realmassdel')
		{
			global $allfiles;

			if (is_array($allfiles))
			{
				foreach ($allfiles as $id => $v)
				{
					$id = str_replace(array('up/', '../', './', '..', '/'), '', basename($id));
					if (is_file(WORKDIR.'/up'.$objdir.$id)) {
						unlink(WORKDIR.'/up'.$objdir.$id);
					}
				}
			}
		}

		/**
		 * Атрибуты
		 -------------*/
		if ($_REQUEST['dn'] == 'cmods')
		{
			global $objdir, $atr, $obj, $lang, $sess, $js;

			$path = back($objdir);
			$lpath = $path.'/';
			$js = ($js == 'folder') ? 'folders' : 'files';
			$win = ($js == 'folders') ? 'folder' : 'files';
			echo '	<script>
					$(function() {
						$("#foldercmods").submit(function() {
							var inputs = [];
							$(":input", this).each(function() {
								inputs.push(this.name + "=" + escape(this.value));
							});
							jQuery.ajax({
								data    : inputs.join("&"),
								url     : this.action,
								error   : function() { alert($.errors); },
								success : function(data) {
									if (data == 1) {
										$.filebrowser'.$js.'(\'dn='.$win.'&ops='.$sess['hash'].'&objdir='.$lpath.'\');
									} else {
										alert($.errors);
									}
								}
							});
							return false;
						});
					});
					</script>
					<form id="foldercmods" name="foldercmods" action="'.ADMPATH.'/includes/filebrowser.php?dn=cmodssave&amp;ops='.$sess['hash'].'" method="post">
					<table class="work">
						<tr>
							<th class="ac" colspan="2">CMODS</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_path'].'</td>
							<td>
								<input type="text" name="objdir" value="'.$objdir.$obj.'" class="readonly" readonly="readonly">
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['file_owner'].'</td>
							<td>
								<select name="onw">
									'.this_selcmod($atr, 0).'
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['file_group'].'</td>
							<td>
								<select name="gro">
									'.this_selcmod($atr, 1).'
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['file_public'].'</td>
							<td>
								<select name="pub">
									'.this_selcmod($atr, 2).'
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if ($js != 'files') {
				echo '			<input type="button" onclick="javascript:$.filebrowserfolders(\'dn=folder&ops='.$sess['hash'].'&objdir='.$lpath.'\')" class="but" value="'.$lang['all_goback'].'">&nbsp;';
			} else {
				echo '			<input id="goback" type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.(($ims) ? '&ims=1' : '').'\')" class="but" value="'.$lang['all_goback'].'">&nbsp;';
			}
			echo '				<input type="submit" class="side-button" value="'.$lang['all_save'].'">
							</td>
						</tr>
					</table>
					</form>';
		}

		/**
		 * Сохранение атрибутов
		 -------------------------*/
		if ($_REQUEST['dn'] == 'cmodssave')
		{
			global $objdir, $obj, $onw, $gro, $pub, $sess;

			$onw = preparse($onw, THIS_INT, 0, 1);
			$gro = preparse($gro, THIS_INT, 0, 1);
			$pub = preparse($pub, THIS_INT, 0, 1);

			$onw = ($onw < 4) ? 4 : $onw;
			$total = '0'.$onw.$gro.$pub;

			$error = 0;
			if (chmod(WORKDIR.'/up'.$objdir, octdec($total)))
			{
				$error = 1;
			}
			echo $error;
		}

		/**
		 * Переименование файла
		 --------------------------*/
		if ($_REQUEST['dn'] == 'rename')
		{
			global $objdir, $obj, $lang, $sess;

			if (is_file((WORKDIR.'/up'.$objdir.$obj)))
			{
				$file = explode('.', $obj);
				$ext = $file[(count($file)-1)];
				unset($file[(count($file)-1)]);
				$name = implode('.', $file);
				echo '	<script>
						$(function() {
							$("#filepost").submit(function() {
								var inputs = [];
								$(":input", this).each(function() {
									inputs.push(this.name + "=" + escape(this.value));
								});
								jQuery.ajax({
									data	: inputs.join("&"),
									url	: this.action,
									error	: function() { alert($.errors); },
									success	: function(data) {
										if(data == 1) {
											$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\');
										} else {
											alert($.errors);
										}
									}
								});
								return false;
							});
						});
						</script>
						<form id="filepost" name="filepost" action="'.ADMPATH.'/includes/filebrowser.php?dn=resave&amp;ops='.$sess['hash'].'" method="post">
						<table class="work">
							<tr>
								<th>&nbsp;</th>
								<th>'.$lang['file_rename'].'</th>
							</tr>
							<tr>
								<td class="ar">'.$lang['all_path'].'</td>
								<td><input type="text" name="objdir" value="'.$objdir.'" size="50" class="readonly" readonly="readonly"></td>
							</tr>
							<tr>
								<td class="ar">'.$lang['file_old_name'].'</td>
								<td><input type="text" name="obj" value="'.$name.'" size="50" class="readonly" readonly="readonly"></td>
							</tr>
							<tr>
								<td class="ar">'.$lang['file_new_name'].'</td>
								<td><input type="text" name="objnew" value="re_'.$name.'" size="50"></td>
							</tr>
							<tr class="tfoot">
								<td>&nbsp;</td>
								<td class="al" colspan="2">
									<input type="hidden" name="dn" value="resave">
									<input type="hidden" name="obj" value="'.$name.'">
									<input type="hidden" name="ext" value="'.$ext.'">
									<input type="hidden" name="objdir" value="'.$objdir.'">
									<input type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
									<input type="submit" class="side-button" value="'.$lang['all_save'].'">
								</td>
							</tr>
						</table>
						</form>';
			}
		}

		/**
		 * Переименование файла (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'resave')
		{
			global $ext, $objnew;

			$error = 0;
			$objnew = basename($objnew);
			$ext = basename($ext);
			if (file_exists(WORKDIR.'/up'.$objdir.$obj.'.'.$ext) AND ! file_exists(WORKDIR.'/up'.$objdir.$objnew.'.'.$ext))
			{
				if (rename(WORKDIR.'/up'.$objdir.$obj.'.'.$ext,WORKDIR.'/up'.$objdir.$objnew.'.'.$ext))
				{
					$error = 1;
				}
			}
			echo $error;
		}

		/**
		 * Предпросмотр изображения
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'thumb')
		{
			global $objdir, $obj, $sess;

			require_once(ADMDIR.'/core/classes/Image.php');
			$image = new Image();
			$image->start();
			if(file_exists(WORKDIR.'/up'.$objdir.$obj)){
				$image->viewthumb(WORKDIR.'/up'.$objdir.$obj, $x, $h, $r);
			} else {
				$image->viewthumb(ADMDIR.'/template/library/noimage.png', $x, $h, $r);
			}
		}

		/**
		 * Удаление директории
		 -------------------------*/
		if ($_REQUEST['dn'] == 'folderdel')
		{
			global $objdir, $sess;

			$path = back($objdir);
			$lpath = $path.'/';
			echo '	<script>
					$(function() {
						$("#folderdel").submit(function() {
							var inputs = [];
							$(":input", this).each(function() {
								inputs.push(this.name + "=" + escape(this.value));
							});
							jQuery.ajax({
								data	: inputs.join("&"),
								url	: this.action,
								error	: function() { alert($.errors); },
								success	: function(data) {
									if (data == 1) {
										$.filebrowserfolders(\'dn=folder&ops='.$sess['hash'].'&objdir='.$lpath.'\');
									} else {
										alert($.errors);
									}
								}
							});
							return false;
						});
					});
					</script>
					<form id="folderdel" name="folderdel" action="'.ADMPATH.'/includes/filebrowser.php?dn=folderdelreal&ops='.$sess['hash'].'" method="post">
					<table class="work">
						<tr>
							<th class="ac" colspan="2"><strong>'.$lang['confirm_del'].' ?</strong></td>
						</tr>
						<tr>
							<td>'.$lang['all_path'].'</td>
							<td><input type="text" name="objdir" value="'.$objdir.'" size="30" class="readonly" readonly="readonly"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="button" onclick="javascript:$.filebrowserfolders(\'dn=folder&ops='.$sess['hash'].'&objdir='.$lpath.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
								<input type="submit" class="side-button" value="'.$lang['all_yes'].'">
							</td>
						</tr>
					</table>
					</form>';
		}

		/**
		 * Удаление директории (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'folderdelreal')
		{
			$error = 0;
			if (remote(WORKDIR.'/up'.$objdir)) {
				$error = 1;
			}
			echo $error;
			exit();
		}

		/**
		 * Изображение
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'imagine')
		{
			global $objdir, $obj;

			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");
			echo '	<table class="work" style="height:100%;">
						<tr>
							<td class="fb-pads ac" style="height:100%;">
								<div id="imaginebox" style="overflow: scroll; width: 400px; height: 100%;">
									<img src="'.WORKURL.'/up'.$objdir.$obj.'" alt="'.$obj.'" />
								</div>
							</td>
						</tr>
						<tr class="tfoot">
							<td>
								<a class="side-button" href="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')">'.$lang['all_goback'].'</a>
							</td>
						</tr>
					</table>';
		}

		/**
		 * Изображение (сохранение)
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'imaginesave')
		{
			global $x, $y, $w, $h;

			$w = preparse($w, THIS_INT);
			$h = preparse($h, THIS_INT);
			$x = preparse($x, THIS_INT);
			$y = preparse($y, THIS_INT);

			if (file_exists(WORKDIR.'/up'.$objdir.$obj) AND $w > 0 AND $h > 0)
			{
				require_once(ADMDIR.'/core/classes/Image.php');
				$image = new Image();
				$image->start();
				$scrimage = $image->makeimage(WORKDIR.'/up'.$objdir.$obj);
				$cropimage = $image->blankimage($w,$h);
				if ($image->gd >= 2) {
					imagecopyresampled($cropimage, $scrimage, 0, 0, $x, $y, $w, $h, $w, $h);
				} else {
					imagecopyresized($cropimage, $scrimage, 0, 0, $x, $y, $w, $h, $w, $h);
				}
				$image->imagefile($cropimage, WORKDIR.'/up'.$objdir.$obj);
				imagedestroy($scrimage);
				imagedestroy($cropimage);
			}
		}

		/**
		 * Содержимое ZIP файла
		 --------------------------*/
		if ($_REQUEST['dn'] == 'openzip')
		{
			require_once(ADMDIR.'/core/libraries/pclzip.lib.php');
			$err = 0;
			if (is_file(WORKDIR.'/up'.$objdir.$obj)) {
				$zip = new PclZip(WORKDIR.'/up'.$objdir.$obj);
				if (($list = $zip->listContent()) == 0) {
					$err = 1;
				}
			} else {
				$err = 1;
			}
			echo '	<script>
					$(function() {
						$("#zipform").submit(function() {
							$("#ziptable").hide();
							$("#zipload").show();
							var inputs = [];
							$(":input", this).each(function() {
								inputs.push(this.name + "=" + escape(this.value));
							});
							jQuery.ajax({
								data    : inputs.join("&"),
								url     : this.action,
								error   : function() { alert($.errors); },
								success : function(data) {
									if(data == 1) {
										$.filebrowserupdate("'.$sess['hash'].'","'.$objdir.'");
									} else {
										alert($.errors);
									}
								}
							});
							return false;
						});
					});
					</script>
					<form action="'.ADMPATH.'/includes/filebrowser.php?dn=unzip&ops='.$sess['hash'].'" method="post" name="zipform" id="zipform">
					<div class="fb-loader-center" id="zipload" style="display:none;">
						<img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loading.gif">
					</div>
					<table class="work" id="ziptable">
						<tr>
							<th>&nbsp;</th>
							<th>'.$lang['all_file'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_path'].'</td>
							<td>
								<input type="text" style="width:100%" name="objfalse" value="'.'up'.$objdir.$obj.'" size="50" class="readonly" readonly="readonly">
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<th>'.$lang['file_zip'].'</th>
						</tr>';
			if ($err == 1)
			{
				echo '	<tr>
							<td colspan="2">'.$lang['isset_error'].'</td>
						</tr>';
			}
			else
			{
				for ($i = 0; $i < sizeof($list); $i ++)
				{
					if ($list[$i]['folder'] == 1)
					{
						echo '	<tr>
									<td colspan="2">
										<img src="'.ADMPATH.'/template/library/folder.gif" alt="" />
										'.$list[$i]['stored_filename'].'
									</td>
								</tr>';
					}
					else
					{
						echo '	<tr>
									<td class="ar">
										'.compare_icon(substr(strrchr($list[$i]['stored_filename'], '.'), 1)).'
									</td>
									<td>
										'.((strrchr($list[$i]['stored_filename'], '/')) ? substr(strrchr($list[$i]['stored_filename'],'/'), 1) : $list[$i]['stored_filename']).'
									</td>
								</tr>';
					}
				}
				echo '	<tr class="tfoot">
							<td>&nbsp;</td>
							<td class="al" colspan="2">
								<input type="hidden" name="obj" value="'.$obj.'">
								<input type="hidden" name="objdir" value="'.$objdir.'">
								<input type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
								<input type="submit" class="side-button"  value=" ... '.$lang['file_unpack'].'">
							</td>
						</tr>';
			}
			echo '	</table>
					</form>';
		}

		/**
		 * Извлечение содержимого ZIP файла
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'unzip')
		{
			require_once(ADMDIR.'/core/libraries/pclzip.lib.php');
			$zip = new PclZip(WORKDIR.'/up'.$objdir.$obj);
			$ext = $zip->extract(PCLZIP_OPT_PATH, WORKDIR.'/up'.$objdir);
			echo ($ext == 0) ? 0 : 1;
		}

		/**
		 * Предпросмотр FLV
		 ---------------------*/
		if ($_REQUEST['dn'] == 'viewflv')
		{
			if (file_exists(WORKDIR.'/up'.$objdir.$obj))
			{
				echo '	<table class="work">
							<tr>
								<td class="ac fb-pads">
									<object>
										<embed src="'.$conf['site_url'].'/up/mediaplayer.swf" allowscriptaccess="always" allowfullscreen="true" flashvars="file='.$conf['site_url'].'/up/'.$objdir.$obj.'&amp;searchbar=false" width="400" height="350"></embed>
									</object>
								</td>
							</tr>
							<tr class="tfoot">
								<td>
									<input type="button" onclick="javascript:$.filebrowserfiles(\'dn=files&ops='.$sess['hash'].'&objdir='.$objdir.'\')" class="side-button" value="'.$lang['all_goback'].'">&nbsp;
								</td>
							</tr>
						</table>';
			}
		}

		/**
		 * Персональная загрузка изображений
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'personal')
		{
			echo '<script src="'.ADMPATH.'/js/jquery.ajax.upload.js"></script>';
			echo '	<script>
					$(function() {
						new AjaxUpload("submit", {
							action	: $("form#uploadform").attr("action"),
							name	: "file",
							responseType : "json",
							onSubmit	: function (file, extension) {
								this.disable();
								$("#submit").hide();
								$("#loading").show();
								this.setData( {
									objdir : $("form#uploadform #objdir").val(),
									thumb  : $("select[name=thumb]").val(),
									resize : $("select[name=resize]").val(),
									width  : $("select[name=width]").val(),
									height : $("select[name=height]").val(),
									rbig   : $("select[name=rbig]").val(),
									wbig   : $("select[name=wbig]").val(),
									hbig   : $("select[name=hbig]").val(),
									injpg  : $("select[name=injpg]").val(),
									wmark  : $("select[name=wmark]").val(),
									unique : $("select[name=unique]").val()
								});
							},
							onComplete   : function (file, response) {
								$("#loading").hide();
								$("#submit").show();
								if (response.error == 0) {
									$.filebrowserimscreate(response.thumb, response.img);
								} else {
									alert($.errors);
								}
								this.enable();
							}
						});
						$("img, a, input").tooltip();
					});
					</script>';

			$mszf = ini_get('post_max_size');
			$mte  = ini_get('max_execution_time');
			$d = this_selfolder('', $objdir);

			// Custom settings loading (added Staff)
			if ( ! empty($conf['user_upload']))
			{
				$upset = Json::decode($conf['user_upload']);
			}
			else
			{
				$upset = array
				(
					'thumb'  => 'yes',
					'resize' => 'crop',
					'width'  => '145',
					'height' => '90',
					'rbig'   => 'yes',
					'wbig'   => '800',
					'hbig'   => '600',
					'unique' => 'yes',
					'injpg'  => 'yes',
					'wmark'  => 'no'
				);
			}

			echo '	<div class="section">
					<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realpersonal&amp;ops='.$sess['hash'].'" method="post" enctype="multipart/form-data" name="uploadform" id="uploadform">
					<table class="fb-work">
						<caption>'.$lang['image_upload'].'</caption>
						<tr>
							<td class="ar pw35">'.$lang['all_path'].'</td>
							<td>
								<select name="objdir" id="objdir">
									<option value="/">/</option>
									'.$d.'
								</select>';
								$tm->outhint($lang['help_path']);
			echo '
							</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['all_image_thumb'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['miniature_yes'].'</td>
							<td>
								<select class="sw85" name="thumb">
									<option value="yes" '.(($upset['thumb'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['thumb'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['process'].'</td>
							<td>
								<select name="resize">
									<option value="crop" '.(($upset['resize'] == 'crop') ? 'selected' : '').'>'.$lang['crop_resize'].'</option>
									<option value="symm" '.(($upset['resize'] == 'symm') ? 'selected' : '').'>'.$lang['all_resize'].'</option>
									<option value="scal" '.(($upset['resize'] == 'scal') ? 'selected' : '').'>'.$lang['scale_resize'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select name="width" class="sw85">';
			for ($w = 70; $w <= 300; $w ++) {
				echo '				<option value="'.$w.'"'.(($w == $upset['width']) ? ' selected' : '').'>'.$w.' px</option>';
									$w = $w + 4;
			}
			echo '				</select> &nbsp;&#215;&nbsp;
								<select name="height" class="sw85">';
			for ($h = 70; $h <= 300; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $upset['height']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 4;
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['all_image_big'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['image_resize'].'</td>
							<td>
								<select class="sw85" name="rbig">
									<option value="yes" '.(($upset['rbig'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['rbig'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select class="sw85" name="wbig">';
			for ($w = 300; $w <= 1900; $w ++) {
				echo '				<option value="'.$w.'"'.(($w == $upset['wbig']) ? ' selected' : '').'>'.$w.' px</option>';
									$w = $w + 99;
			}
			echo '				</select> &nbsp;&#215;&nbsp;
								<select class="sw85" name="hbig">';
			for ($h = 240; $h <= 1200; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $upset['hbig']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 59;
			}
		echo '					</select>
							</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">'.$lang['opt_set'].'</th>
						</tr>
						<tr>
							<td class="ar">'.$lang['unique_file'].'</td>
							<td>
								<select class="sw85" name="unique">
									<option value="yes" '.(($upset['unique'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['unique'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['convert_in_jpg'].'</td>
							<td>
								<select class="sw85" name="injpg">
									<option value="yes" '.(($upset['injpg'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no" '.(($upset['injpg'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="ar">'.$lang['watermark'].'</td>
							<td>
								<select class="sw85" name="wmark">
									<option value="no" '.(($upset['wmark'] == 'no') ? 'selected' : '').'>'.$lang['all_no'].'</option>
									<option value="yes" '.(($upset['wmark'] == 'yes') ? 'selected' : '').'>'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input id="submit" type="submit" class="side-button" value="'.$lang['file_review'].'">
								<div><img class="none" id="loading" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loadings.gif" alt="" /></div>
							</td>
						</tr>
					</table>
					</form>
					</div>';
		}

		/**
		 * Быстрая загрузка доп. изображений
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'moreupload')
		{
			global $tm;

			echo '<script src="'.ADMPATH.'/js/jquery.ajax.upload.js"></script>';
			echo '	<script>
					$(function() {
						new AjaxUpload("submit", {
							action	: $("form#uploadform").attr("action"),
							name	: "file",
							responseType : "json",
							onSubmit	: function (file, extension) {
								this.disable();
								$("#submit").hide();
								$("#loading").show();
								this.setData( {
									objdir : $("form#uploadform #objdir").val(),
									thumb  : "'.$conf['thumb'].'",
									resize : "'.$conf['resize'].'",
									width  : "'.$conf['width'].'",
									height : "'.$conf['height'].'",
									rbig   : "'.$conf['rbig'].'",
									wbig   : "'.$conf['wbig'].'",
									hbig   : "'.$conf['hbig'].'",
									injpg  : "'.$conf['injpg'].'",
									wmark  : "'.$conf['wateruse'].'",
									unique : "'.$conf['unique'].'"
								});
							},
							onComplete   : function (file, response) {
								$("#loading").hide();
								$("#submit").show();
								if (response.error == 0) {
									$.morecreate(response.thumb, response.img);
								} else {
									alert($.errors);
								}
								this.enable();
							}
						});
						$("img, a, input").tooltip();
					});
					</script>';

			$updir = this_selfolder('', $objdir);

			echo '	<div class="section">
					<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realpersonal&amp;ops='.$sess['hash'].'" method="post" enctype="multipart/form-data" id="uploadform">
					<table class="fb-work">
						<caption>'.$lang['image_upload'].'</caption>
						<tr>
							<td class="ar pw35">'.$lang['all_path'].'</td>
							<td>
								<select name="objdir" id="objdir">
									<option value="/">/</option>
									'.$updir.'
								</select>';
								$tm->outhint($lang['help_path']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input id="submit" type="submit" class="side-button" value="'.$lang['file_review'].'">
								<div><img class="none" id="loading" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loadings.gif" alt="" /></div>
							</td>
						</tr>
					</table>
					</form>
					</div>';
		}

		/**
		 * Быстрая загрузка изображений
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'quickupload')
		{
			global $tm;

			echo '<script src="'.ADMPATH.'/js/jquery.ajax.upload.js"></script>';
			echo '	<script>
					$(function() {
						new AjaxUpload("submit", {
							action	: $("form#uploadform").attr("action"),
							name	: "file",
							responseType : "json",
							onSubmit	: function (file, extension) {
								this.disable();
								$("#submit").hide();
								$("#loading").show();
								this.setData( {
									objdir : $("form#uploadform #objdir").val(),
									thumb  : "'.$conf['thumb'].'",
									resize : "'.$conf['resize'].'",
									width  : "'.$conf['width'].'",
									height : "'.$conf['height'].'",
									rbig   : "'.$conf['rbig'].'",
									wbig   : "'.$conf['wbig'].'",
									hbig   : "'.$conf['hbig'].'",
									injpg  : "'.$conf['injpg'].'",
									wmark  : "'.$conf['wateruse'].'",
									unique : "'.$conf['unique'].'"
								});
							},
							onComplete   : function (file, response) {
								$("#loading").hide();
								$("#submit").show();
								if (response.error == 0) {
									$.quickinsert(response.thumb, response.img);
								} else {
									alert($.errors);
								}
								this.enable();
							}
						});
						$("img, a, input").tooltip();
					});
					</script>';

			$updir = this_selfolder('', $objdir);

			echo '	<div class="section">
					<form action="'.ADMPATH.'/includes/filebrowser.php?dn=realpersonal&amp;ops='.$sess['hash'].'" method="post" enctype="multipart/form-data" id="uploadform">
					<table class="fb-work">
						<caption>'.$lang['image_upload'].'</caption>
						<tr>
							<td class="ar pw35">'.$lang['all_path'].'</td>
							<td>
								<select name="objdir" id="objdir">
									<option value="/">/</option>
									'.$updir.'
								</select>';
								$tm->outhint($lang['help_path']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input id="submit" type="submit" class="side-button" value="'.$lang['file_review'].'">
								<div><img class="none" id="loading" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loadings.gif" alt="" /></div>
							</td>
						</tr>
					</table>
					</form>
					</div>';
		}

		/**
		 * Персональная загрузка изображений (сохранение)
		 --------------------------------------------------*/
		if ($_REQUEST['dn'] == 'realpersonal')
		{
			global $conf, $objdir, $file, $thumb, $resize, $width, $height, $rbig, $wbig, $hbig, $injpg, $wmark, $unique;

			$filename = $obj = '&#8212; &#8212; &#8212;';
			$width = intval($width);
			$height = intval($height);
			$json = array('error' => 1, 'thumb' => '', 'img' => '');

			require_once(ADMDIR.'/core/classes/Image.php');
			$image = new Image();

			if (isset($_FILES['file']) AND ! empty($_FILES['file']['name']) AND preg_match("/.gif$|.jpg$|.jpeg$|.png$|.bmp$|.webp$/i", $_FILES['file']['name']))
			{
				$imginfo = getimagesize($_FILES['file']['tmp_name']);
				$imgarray = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png', 'image/bmp', 'image/webp');

				if (
					isset($imginfo[0]) AND $imginfo[0] > 0 AND
					isset($imginfo[1]) AND $imginfo[1] > 0 AND
					isset($imginfo['mime']) AND in_array($imginfo['mime'], $imgarray)
				) {
					$extname  = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
					$exttype  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
					$filename = preg_replace("/([ &%§]+)/", '', mb_strtolower(trim($extname)));
					$tmpname  = $_FILES['file']['tmp_name'];

					// Если создавать уникальное имя файла
					if (isset($unique) AND $unique == 'yes') {
						$newname = date("ymd", time()).'_'.mt_rand(0, 9999);
						$filename_thumb = $newname.'_thumb.'.$exttype;
						$filename = $newname.'.'.$exttype;
					} else {
						$filename_thumb = $filename.'_thumb.'.$exttype;
						$filename = $filename.'.'.$exttype;
					}

					// Если есть копии добавляем префикс copy_
					if ($injpg == 'yes' AND $exttype != 'jpg') {
						if (file_exists(WORKDIR.'/up'.$objdir.$extname.'.jpg')) {
							$filename = 'copy_'.$filename;
						}
					} else {
						if (file_exists(WORKDIR.'/up'.$objdir.$filename)) {
							$filename_thumb = 'copy_'.$extname.'_thumb.'.$exttype;
							$filename = 'copy_'.$filename;
						}
					}

					// Копируем файл в указанную папку
					if (move_uploaded_file($tmpname, WORKDIR.'/up'.$objdir.$filename))
					{
						$image->start();
						$json['error'] = 0;

						// Если уменьшать большое изображение
						if (isset($rbig) AND $rbig == 'yes' AND file_exists(WORKDIR.'/up'.$objdir.$filename))
						{
							$image->createthumb
								(
									WORKDIR.'/up'.$objdir.$filename,
									WORKDIR.'/up'.$objdir,
									$filename,
									$wbig,
									$hbig,
									'symm'
								);
						}

						// Если конвертировать в jpg
						if ($injpg == 'yes' AND $exttype != 'jpg')
						{
							list($fjpg, $ext) = explode(".", $filename);
							$file_jpg = $fjpg.'.jpg';
							$image->imgconvert(WORKDIR.'/up'.$objdir.$filename, WORKDIR.'/up'.$objdir.$file_jpg);
							$filename_thumb = $fjpg.'_thumb.jpg';
							$filename = $fjpg.'.jpg';
						}

						$json['img'] = $objdir.$filename;
						// Если делать уменьшенную копию
						if (isset($thumb) AND $thumb == 'yes' AND file_exists(WORKDIR.'/up'.$objdir.$filename))
						{
							$image->createthumb
								(
									WORKDIR.'/up'.$objdir.$filename,
									WORKDIR.'/up'.$objdir,
									$filename_thumb,
									$width,
									$height,
									$resize
								);
							$json['thumb'] = $objdir.$filename_thumb;
						}
						else
						{
							$json['thumb'] = '';
						}

						// Если ватермарка
						if ($conf['wateruse'] == 'img' AND ! empty($conf['waterpatch']) AND $wmark == 'yes')
						{
							$image->createwater(WORKDIR.'/up'.$objdir.$filename, WORKDIR.'/'.$conf['waterpatch']); // изображением
						}
						elseif ($conf['wateruse'] == 'txt' AND ! empty($conf['watertext']) AND $wmark == 'yes')
						{
							$image->createwater(WORKDIR.'/up'.$objdir.$filename, 0, 1, $conf['watertext']); // текстом
						}
					}
				}
			}
			echo '{';
			foreach ($json as $k => $v) {
				echo '"'.$k.'" : "'.$v.'",';
			}
			echo '"empty" : ""}';

			$upsave = array
			(
				'thumb'  => $thumb,
				'resize' => $resize,
				'width'  => $width,
				'height' => $height,
				'rbig'   => $rbig,
				'wbig'   => $wbig,
				'hbig'   => $hbig,
				'unique' => $unique,
				'injpg'  => $injpg,
				'wmark'  => $wmark
			);

			$upsave = Json::encode($upsave);
			$db->query("UPDATE ".$basepref."_settings SET setval = '".$upsave."' WHERE setname = 'user_upload'");

			$cache->cachesave(1);
			exit();
		}

		/**
		 * Тихая загрузка файлов
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'allupload')
		{
			global $tm;

			echo '	<script src="'.ADMPATH.'/js/jquery.ajax.upload.js"></script>';
			echo '	<script>
					$(function() {
						new AjaxUpload("submit", {
							action       : $("form#uploadform").attr("action"),
							name         : "file",
							responseType : "json",
							onSubmit     : function (file, extension) {
								this.disable();
								$("#submit").hide();
								$("#loading").show();
								this.setData( {
									objdir : $("input[name=objdir]").attr("value"),
									unique : $("input[name=unique]").attr("value")
								});
							},
							onComplete : function (file, response) {
								$("#loading").hide();
								$("#submit").show();
								if (response.error == 0) {
									$.fileallcreate(response.down,response.id);
								} else {
									alert($.errors);
								}
								this.enable();
							}
						});
						$("img, a, input").tooltip();
					});
					</script>';

			$updir = this_selfolder('', $objdir);
			echo '	<div class="section">
					<form action="'.ADMPATH.'/includes/filebrowser.php?dn=alluploadsave&amp;ops='.$sess['hash'].'&amp;place='.$field.'" method="post" enctype="multipart/form-data" name="uploadform" id="uploadform">
					<table class="fb-work">
						<caption>'.$lang['file_upload'].'</caption>
						<tr>
							<td class="ar pw35">'.$lang['all_path'].'</td>
							<td>
								<select name="objdir" id="objdir">
									<option value="/">/</option>
									'.$updir.'
								</select>';
								$tm->outhint($lang['help_path']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="unique" value="'.$conf['unique'].'">
								<input type="hidden" name="objdir" value="'.$objdir.'">
								<input id="submit" type="submit" class="main-button" value="'.$lang['file_review'].'">
								<div><img class="none" id="loading" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/loadings.gif" alt="" /></div>
							</td>
						</tr>
					</table>
					</form>
					</div>';
		}

		/**
		 * Тихая загрузка файлов (сохранение)
		 ---------------------------------------*/
		if ($_REQUEST['dn'] == 'alluploadsave')
		{
			global $conf, $objdir, $file ,$unique, $place;

			$json = array('error' => 1, 'down' => '', 'id' => '');

			$extname  = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
			$exttype  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			$filename = preg_replace("/([ &%§]+)/",'',strtolower(trim($extname)));
			$tmpname  = $_FILES['file']['tmp_name'];

			if (isset($unique) AND $unique == 'yes')
			{
				$newname = date("ymd",time()).'_'.mt_rand(0, 9999);
				$filename = $newname.'.'.$exttype;
			}
			else
			{
				$filename = $filename.'.'.$exttype;
			}

			if (move_uploaded_file($tmpname,WORKDIR.'/up'.$objdir.$filename))
			{
				$json['error'] = 0;
				$json['down'] = $objdir.$filename;
				$json['id'] = $place;
			}

			echo '{';
			foreach ($json as $k => $v)
			{
				echo '"'.$k.'" : "'.$v.'",';
			}
			echo '"empty" : ""}';
			exit();
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->blankstart();
		$tm->fberror($lang['no_access']);
		$tm->blankend();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect('login.php'); exit();
}
