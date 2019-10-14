<?php
/**
 * File:        /admin/mod/contact/index.php
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
define('PERMISS', basename(__DIR__));

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $modname, $conf, $lang, $sess, $ops, $cache;

	if ( ! isset($modname[PERMISS]))
	{
		redirect(ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash']);
	}

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
			$modname[PERMISS]
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'optsave', 'data', 'datasave', 'vcard', 'vcardsave');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('data').' href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$lang['descript'].'</a>'
					.'<a'.cho('vcard').' href="index.php?dn=vcard&amp;ops='.$sess['hash'].'">'.$lang['organization'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 --------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['contact'].': '.$lang['all_set'].'</caption>';
			while ($itemset = $db->fetchrow($inqset))
			{
				if ($itemset['setname'] != 'vcard')
				{
					echo '	<tr>
								<td class="first">
									'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
								</td>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Сохранение настроек
		 ------------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($modname[PERMISS], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse_html($set[$item['setname']]))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать данные
		 ------------------------*/
		if ($_REQUEST['dn'] == 'data')
		{
			global $cid, $lang;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['descript'],
					$lang['all_edit']
				);

			$tm->header();

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS.""));

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';
			echo '	<script>
					var all_name     = "'.$lang['all_name'].'";
					var all_cpu      = "'.$lang['all_cpu'].'";
					var all_thumb    = "'.$lang['all_image_thumb'].'";
					var all_img      = "'.$lang['all_image'].'";
					var all_images   = "'.$lang['all_image_big'].'";
					var all_align    = "'.$lang['all_align'].'";
					var all_right    = "'.$lang['all_right'].'";
					var all_left     = "'.$lang['all_left'].'";
					var all_center   = "'.$lang['all_center'].'";
					var all_alt      = "'.$lang['all_alt_image'].'";
					var all_copy     = "'.$lang['all_copy'].'";
					var all_delet    = "'.$lang['all_delet'].'";
					var code_paste   = "'.$lang['code_paste'].'";
					var page = "'.PERMISS.'";
					var ops = "'.$sess['hash'].'";
					$(function() {
						$(".imgcount").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['contact'].': '.$lang['descript'].'</caption>
						<tr>
							<th class="ar gray">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="title" id="title" size="70" value="'.preparse_un($item['title']).'" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['input_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);
				$WYSFORM = 'textshort';
				$WYSVALUE = $item['textshort'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textshort', 7, 70, $item['textshort'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'textmore';
				$WYSVALUE = $item['textmore'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textmore', 7, 70, $item['textmore'], (($wysiwyg == 'yes') ? 0 : 1));
			}
			echo '			</td>
						</tr>
						</tbody>';
			$img = Json::decode($item['images']);
			$class = (is_array($img) AND sizeof($img) > 0) ? ' class="image-area"' : '';
			echo '		<tbody class="tab-2">
						<tr><th>&nbsp;</th><th class="site">'.$lang['img_extra_hint'].'</th></tr>
						<tr>
							<td>'.$lang['add_img'].'</td>
							<td>
								<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;ims=1\');">'.$lang['filebrowser'].'</a>&nbsp;
								<a class="side-button" href="javascript:$.personalupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/\');">'.$lang['file_upload'].'</a>
								<div id="image-area"'.$class.'>';
			$ic = 0;
			if (is_array($img))
			{
				foreach ($img as $k => $v)
				{
					$ic ++;
					echo '			<div id="imginput'.$ic.'" style="display:block;">
										<table class="work">
											<tr>
												<td>';
					if ( ! empty($v['image'])) {
						echo '						<img class="sw50" src="../'.$v['thumb'].'" alt="'.$lang['all_image_thumb'].'" />';
					} else {
						echo '						<img class="sw70" src="../'.$v['thumb'].'" alt="'.$lang['all_image_big'].'" />';
					}
					echo '							<input type="hidden" name="images['.$ic.'][image_thumb]" value="'.$v['thumb'].'">';
					if ( ! empty($v['image'])) {
						echo '						&nbsp;&nbsp;<img class="sw70" src="../'.$v['image'].'" alt="'.$lang['all_image'].'" />
													<input type="hidden" name="images['.$ic.'][image]" value="'.$v['image'].'">';
					}
					echo '						</td>
												<td>
													<p><input type="text" size="3" value="{img'.$ic.'}" class="imgcount" readonly="readonly" title="'.$lang['all_copy'].'"> <cite>'.$lang['code_paste'].'</cite></p>
													<p class="label">'.$lang['all_align'].'&nbsp; &nbsp; &nbsp; &nbsp;'.$lang['all_alt_image'].'</p>
													<p>
														<select name="images['.$ic.'][image_align]">
															<option value="left"'.(($v['align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
															<option value="right"'.(($v['align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
															<option value="center"'.(($v['align'] == 'center') ? ' selected' : '').'>'.$lang['all_center'].'</option>
														</select>&nbsp; &nbsp; &nbsp;
														<input type="text" name="images['.$ic.'][image_alt]" size="25" value="'.$v['alt'].'">
													</p>
												</td>
												<td>
													<a class="but" href="javascript:$.filebrowserimsremove(\''.$ic.'\');" title="'.$lang['all_delet'].'">x</a>
												</td>
											</tr>
										</table>
									</div>';
				}
			}
			echo '				</div>
							</td>
						</tr>';
			echo '		</tbody>
						<tbody class="tab-1">
						<tr><th>&nbsp;</th><th class="site">'.$lang['all_image_big'].'</th></tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" value="'.$item['image_thumb'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image_thumb&amp;field[2]=image\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text" value="'.$item['image'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" value="'.preparse_un($item['image_alt']).'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_align_image'].'</td>
							<td>
								<select name="image_align" class="sw165">
									<option value="left"'.(($item['image_align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
									<option value="right"'.(($item['image_align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
								</select>
							</td>
						</tr>
						</tbody>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tbody class="tab-2">
							<tr><th>&nbsp;</th><th class="site">'.$lang['user_texts'].'</th></tr>	<tr>
							<td>'.$lang['message'].'</td>
							<td>';
								$tm->textarea('textnotice', 3, 70, $item['textnotice'], true, false, 'ignorewysywig');
				echo '		</td>
						</tr>
						</tbody>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="datasave">
								<input type="hidden" id="imgid" value="'.$ic.'">
								<input type="hidden" name="cid" value="'.$item['cid'].'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['up_data'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			echo "	<script>
						$(document).ready(function() {
							$('#tabs a').tabs('.tab-1');
						});
					</script>";

			$tm->footer();
		}

		/**
		 * Редактировать данные, сохранение
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'datasave')
		{
			global $cid, $title, $subtitle, $textshort, $textmore,
					$images, $textnotice, $image, $image_thumb, $image_align, $image_alt;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['descript'],
					$lang['all_edit']
				);

			$cid = preparse($cid, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);
			$textshort = preparse($textshort, THIS_TRIM);

			if (preparse($title, THIS_EMPTY) == 1 OR preparse($textshort, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS].'&nbsp; &#8260; &nbsp;'.$lang['descript'], $lang['all_edit'], $lang['news_add_error']);
				$tm->footer();
			}
			else
			{
				$img = NULL;
				if (is_array($images))
				{
					$c = 1;
					foreach ($images as $k => $v)
					{
						if (isset($v['image_thumb']) AND ! empty($v['image_thumb']))
						{
							$img[$c] = array
							(
								'thumb' => $v['image_thumb'],
								'image' => $v['image'],
								'align' => $v['image_align'],
								'alt'   => str_replace(array("'", '"'), '', $v['image_alt']),
							);
							$c ++;
						}
					}
					$img = Json::encode($img);
				}

				$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
				$textmore = preparse($textmore, THIS_TRIM);

				$image = preparse($image, THIS_TRIM, 0, 255);
				$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
				$image_thumb =  preparse($image_thumb, THIS_TRIM, 0, 255);
				$image_align = ($image_align == 'left') ? 'left' : 'right';

				$inq = $db->query("SELECT cid FROM ".$basepref."_".PERMISS." WHERE cid = '".$cid."'");

				if ($db->numrows($inq) > 0)
				{
					$db->query
						(
							"UPDATE ".$basepref."_".PERMISS." SET
							 title       = '".$db->escape(preparse_sp($title))."',
							 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
							 textshort   = '".$db->escape($textshort)."',
							 textmore    = '".$db->escape($textmore)."',
							 images      = '".$db->escape($img)."',
							 textnotice  = '".$db->escape($textnotice)."',
							 image       = '".$db->escape($image)."',
							 image_thumb = '".$db->escape($image_thumb)."',
							 image_align = '".$image_align."',
							 image_alt   = '".$db->escape(preparse_sp($image_alt))."'
							 WHERE cid = '".$cid."'"
						);
				}
				else
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_".PERMISS." VALUES (
							 NULL,
							 '".$db->escape(preparse_sp($title))."',
							 '".$db->escape(preparse_sp($subtitle))."',
							 '".$db->escape($textshort)."',
							 '".$db->escape($textmore)."',
							 '".$db->escape($img)."',
							 '".$db->escape($textnotice)."',
							 '".$db->escape($image)."',
							 '".$db->escape($image_thumb)."',
							 '".$image_align."',
							 '".$db->escape(preparse_sp($image_alt))."'
							)"
						);
				}
			}

			redirect('index.php?dn=data&amp;ops='.$sess['hash']);
		}

		/**
		 * Контакты организации
		 ------------------------*/
		if ($_REQUEST['dn'] == 'vcard')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=data&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['vcard']
				);

			$tm->header();

			$in = array();
			$re = array
			(
				'vcard_org'		=> '',
				'vcard_code'		=> '',
				'vcard_country'	=> '',
				'vcard_region'	=> '',
				'vcard_locality'	=> '',
				'vcard_street'	=> '',
				'vcard_tel'		=> '',
				'vcard_url'		=> '',
				'vcard_email'		=> '',
				'vcard_work'		=> '',
				'longitude'		=> '',
				'latitude'			=> ''
			);

			$item = $db->fetchrow($db->query("SELECT setval FROM ".$basepref."_settings WHERE setname = 'vcard'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['vcard'].': '.$lang['all_edit'].'</caption>';
			$in = Json::decode($item['setval']);
			$ins = (is_array($in) AND ! empty($in)) ? $in : $re;
			foreach ($ins as $k => $v)
			{
				echo '	<tr>
							<td>'.$lang[$k].'</td>
							<td><input name="vcard['.$k.']" value="'.$v.'" size="70" type="text"></td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="vcardsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Контакты организации, сохранение
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'vcardsave')
		{
			global $vcard, $cache;

			$new = array();
			if (isset($vcard) AND is_array($vcard))
			{
				foreach ($vcard as $k => $v)
				{
					$new[$k] = preparse_html($v);
				}
				$ins = Json::encode($new);
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'vcard'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=vcard&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($modname[PERMISS], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
