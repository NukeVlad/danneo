<?php
/**
 * File:        /admin/system/seo/index.php
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
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $lang, $sess, $ops, $cache;

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
			$lang['seo']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID,$CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'optsave', 'sitemapsave',
				'social', 'socialup', 'socialadd', 'socialdel', 'socialedit', 'socialeditsave',
				'linking', 'linkaddsave', 'linkedit', 'linkeditsave', 'linkdel'
			);

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

			$link = '<a'.cho('index, sitemapsave').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_map'].'</a>'
					.'<a'.cho('linking, linkedit, linkdel, linkeditsave').' href="index.php?dn=linking&amp;ops='.$sess['hash'].'">'.$lang['seo_link'].'</a>'
					.'<a'.cho('social, socialedit').' href="index.php?dn=social&amp;ops='.$sess['hash'].'">'.$lang['social_bookmark'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Файл Sitemap
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['seo'].'</a>',
					$lang['site_map']
				);

			$tm->header();

			$changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['site_map'].'</caption>';
			$inq = $db->query("SELECT * FROM ".$basepref."_mods WHERE sitemap = 'yes' AND active = 'yes'");
			$mod = array();
			while ($item = $db->fetchrow($inq)) {
				$mod[$item['file']] = $item;
			}

			foreach ($mod as $k => $v)
			{
					echo '	<tr>
								<td class="site vm">'.$v['name'].'</td>
								<td class="alternative sw50 vm">'.$v['file'].'</td>
								<td class="mod check vm">
									<input name="mod['.$k.'][add]" value="1" type="checkbox" checked="checked" />
								</td>
								<td class="vm">
									<div class="fl">
										<span class="black vm">&lt;changefreq&gt;</span>&nbsp;
										<select name="mod['.$k.'][freq]">';
					foreach ($changefreq as $v)
					{
						echo '				<option value="'.$v.'"'.(($v == 'never') ? ' selected' : '').'>'.$v.'</option>';
					}
					echo '				</select> &nbsp; &nbsp;
										<span class="black vm">&lt;priority&gt;</span>&nbsp;
										<select name="mod['.$k.'][prio]">';
					$f = 0.1;
					for ($i = 0; $i < 11; $i ++)
					{
						$ceil = number_format(($f * $i), 1);
						echo '				<option value="'.$ceil.'"'.(($ceil == '0.5') ? ' selected' : '').'>'.$ceil.'</option>';
					}
					echo '				</select>
									</div>
								</td>
							</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="dn" value="sitemapsave">
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
		 * Файл Sitemap (сохранение)
		 */
		if ($_REQUEST['dn'] == 'sitemapsave')
		{
			global $mod, $ro;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['seo'].'</a>',
					$lang['site_map']
				);

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			$mapmod = array();
			$inqmod = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes' AND sitemap = 'yes'");
			while ($item = $db->fetchassoc($inqmod)) {
				$mapmod[$item['file']] = $item;
			}

			$add = array();
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
				  ."<urlset\n"
				  ."xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n"
				  ."xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n"
				  ."xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
			if (is_array($mod))
			{
				foreach ($mod as $k => $v)
				{
					if (isset($mapmod[$k]) AND isset($v['add']) AND $v['add'] == 1)
					{
						$add[] = $v['add'];

						$check_catid = 0;
						$table = ($k == 'pages' OR $mapmod[$k]['parent'] > 0) ? 'pages' : $k;
						$check_table = $db->numrows($db->query("SHOW tables LIKE '".$basepref."_".$table."'"));
						if ($mapmod[$k]['parent'] == 0) {
							if ($check_table == 1)
							{
								$check_catid = $db->numrows($db->query("SHOW columns FROM ".$basepref."_".$k." WHERE field = 'catid'"));
							}
						}

						if ($check_catid == 1)
						{
							$sdata = $db->fetchrow($db->query("SELECT public FROM ".$basepref."_".$k." ORDER BY public ASC LIMIT 1"));
							$xml .= '<url>'
									.'<loc>'.$conf['site_url'].$ro->seo('index.php?dn='.$k).'</loc>'
									.'<lastmod>'.date('Y-m-d', $sdata['public']).'T'.date('H:m:s', $sdata['public']).'+00:00</lastmod>'
									.'<changefreq>'.$v['freq'].'</changefreq>'
									.'<priority>'.$v['prio'].'</priority>'
									.'</url>';

							$inqcat = $db->query("SELECT * FROM ".$basepref."_".$k."_cat ORDER BY posit ASC");
							while ($item = $db->fetchrow($inqcat))
							{
								$xml.= '<url>';
								if (defined('SEOURL') AND $item['catcpu'])
								{
									$xml.= '<loc>'.$conf['site_url'].'/'.$k.'/'.$item['catcpu'].'/</loc>';
								} else {
									$xml.= '<loc>'.$conf['site_url'].'/'.$ro->seo('index.php?dn='.$k.'&amp;to=cat&amp;id='.intval($item['catid'].'&ccpu='.$item['catcpu'])).'</loc>';
								}
								$xml.=  '<lastmod>'.date('Y-m-d', $sdata['public']).'T'.date('H:m:s', $sdata['public']).'+00:00</lastmod>'
										.'<changefreq>'.$v['freq'].'</changefreq>'
										.'<priority>'.$v['prio'].'</priority>'
										.'</url>';
							}
						}
						else
						{
							if ($k != 'pages')
							{
								$xml.=  '<url>'
										.'<loc>'.$conf['site_url'].'/'.(defined('SEOURL') ? $k.'/' : $conf['site_url'].'/index.php?dn='.$k).'</loc>'
										.'<changefreq>'.$v['freq'].'</changefreq>'
										.'<priority>'.$v['prio'].'</priority>'
										.'</url>';
							}
						}

						if ($check_table == 1)
						{
							$check_act = $db->numrows($db->query("SHOW columns FROM ".$basepref."_".$table." WHERE field = 'act'"));
							if ($check_act == 1)
							{
								$inqpage = $db->query("SELECT * FROM ".$basepref."_".$table." WHERE act = 'yes'");
								if ($check_catid == 1)
								{
									$inqs = $db->query("SELECT * FROM ".$basepref."_".$k."_cat ORDER BY posit ASC");
									while ($c = $db->fetchrow($inqs, $conf['cache'])) {
									$obj[$c['catid']] = $c;
									}
								}
								while ($item = $db->fetchrow($inqpage))
								{
									$catcpu = '';
									if ($check_catid == 1) {
										$catcpu = (defined('SEOURL') AND  ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';
									}
									if ($table == 'pages')
									{
										if (isset($mod[$item['mods']]['add']))
										{
											$xml.= '<url>';
											$cpu = ($item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
											$pa = (isset($item['mods']) AND $item['mods'] != 'pages') ? '&amp;pa='.$item['mods'] : '';
											$xml.= '<loc>'.$conf['site_url'].$ro->seo('index.php?dn=pages'.$pa.$cpu).'</loc>';
											$xml.= ((isset($item['public']) ) ? '<lastmod>'.date('Y-m-d', $item['public']).'T'.date('H:m:s', $item['public']).'+00:00</lastmod>' : '')
												.'<changefreq>'.$v['freq'].'</changefreq>'
												.'<priority>'.$v['prio'].'</priority>';
											$xml.= '</url>';
										}
									}
									else
									{
										$xml.= '<url>';
										$cpu = (defined('SEOURL') AND  ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
										$xml.= '<loc>'.$conf['site_url'].$ro->seo('index.php?dn='.$k.$catcpu.'&amp;to=page&amp;id='.$item['id'].$cpu).'</loc>';
										$xml.= ((isset($item['public']) ) ? '<lastmod>'.date('Y-m-d', $item['public']).'T'.date('H:m:s', $item['public']).'+00:00</lastmod>' : '')
											.'<changefreq>'.$v['freq'].'</changefreq>'
											.'<priority>'.$v['prio'].'</priority>';
										$xml.= '</url>';
									}
								}
							}
						}
					}
				}
			}
			$xml.= '</urlset>';

			if (is_array($add) AND ! empty($add))
			{
				$path_xml = WORKDIR.'/sitemap.xml';
				$url_xml = $conf['site_url'].'/sitemap.xml';

				if (is_file($path_xml))
				{
					if ( ! is_writable($path_xml)) {
						$tm->header();
						$tm->alert($lang['site_map'].': '.$lang['all_save'], $lang['not_writable'].':', $path_xml);
						$tm->footer();
					}
				}
				else
				{
					if (touch($path_xml)) {
						chmod($path_xml, 0666);
					} else {
						$tm->header();
						$tm->alert($lang['site_map'].': '.$lang['all_save'], $lang['not_create'].':', $path_xml);
						$tm->footer();
					}
				}

				$xml_write = fopen($path_xml, 'wb');
				fputs($xml_write, $xml);
				fclose($xml_write);

				$tm->header();
				$tm->alert($lang['sitemap_up'].'!', $lang['link_to_file'].': <a href="'.$url_xml.'" target="_blank">'.$url_xml.'</a>');
				$tm->footer();
			}
			else
			{
				$tm->header();
				$tm->error($lang['site_map'], $lang['all_save'], $lang['nodelsel']);
				$tm->footer();
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Перелинковка
		 */
		if ($_REQUEST['dn'] == 'linking')
		{
			global $nu, $said, $mod, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['seo'].'</a>',
					$lang['seo_link']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$url = (isset($mod) AND ! empty($mod)) ? '&amp;mod='.$mod : '';
			$sql = (isset($mod) AND ! empty($mod)) ? " WHERE mods = '".$mod."'" : '';
			$inq = $db->query("SELECT * FROM ".$basepref."_seo_anchor".$sql." ORDER BY said ASC LIMIT ".$sf.", ".$nu);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages('seo_anchor'.$sql, 'said', 'index', 'linking&amp;mod='.$mod, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=linking&amp;p=".$p."&amp;mod=".$mod."&amp;ops=".$sess['hash'], $nu);

			$inqs = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes' AND linking = 'yes'");

			$seo = array();
			while ($items = $db->fetchrow($inqs))
			{
				$seo[$items['file']] = $items['name'];
			}

			$itemset = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' AND setname = 'anchor'"));

			// Настройки
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['seo_link'].': '.$lang['all_set'].'</caption>
						<tr>
							<td>
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
			echo			eval($itemset['setcode']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave">
								<input type="hidden" name="type" value="seo_link">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>';
			if ( ! empty($seo))
			{
				// Все анкоры
				echo '	<div class="section">
						<table class="work">
							<caption>'.$lang['all_anchor'].'</caption>
							<tr class="tfoot">
								<td colspan="4">
									<form action="index.php?dn=linking&amp;ops='.$sess['hash'].'" method="post">
										<select name="mod">';
				echo '					<option value="">'.$lang['link_all'].'</option>';
				foreach ($seo as $k => $v)
				{
					echo '				<option value="'.$k.'"'.(($k == $mod) ? ' selected' : '').'>'.$v.'</option>';
				}
				echo '					</select> &nbsp;
										<input type="hidden" name="p" value="'.$p.'">
										<input type="hidden" name="nu" value="'.$nu.'">
										<input class="side-button" value=" '.$lang['all_sorting'].' " type="submit">
									</form>
								</td>
							</tr>
							<tr>
								<td colspan="4">'.$amount.'</td>
							</tr>
							<tr>
								<th class="ar">'.$lang['search_word'].'</th>
								<th>'.$lang['parent_view'].'</th>
								<th>'.$lang['all_link'].'</th>
								<th>'.$lang['sys_manage'].'</th>
							</tr>';
				while ($item = $db->fetchrow($inq))
				{
					if (isset($seo[$item['mods']]))
					{
						echo '	<tr>
									<td class="first vm"><span class="norm">'.$item['word'].'</span></td>
									<td class="vm"><span class="vars">'.$seo[$item['mods']].'</span></td>
									<td class="vm">
										<input value="'.$item['link'].'" size="25" readonly="readonly" class="readonly" type="text">
									</td>
									<td class="gov ac sw130">
										<a href="index.php?dn=linkedit'.$url.'&amp;said='.$item['said'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
										<a href="index.php?dn=linkdel'.$url.'&amp;said='.$item['said'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
									</td>
								</tr>';
					}
				}
				echo '		<tr>
								<td colspan="4">'.$pages.'</td>
							</tr>
						</table>
						</div>
						<div class="pad"></div>';
				// Добавить анкор
				echo '	<div class="section">
						<form action="index.php?dn=linkaddsave&amp;ops='.$sess['hash'].'" method="post">
						<table class="work">
							<caption>'.$lang['add_anchor'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['parent_view'].'</td>
								<td>
									<select name="mod" class="sw165">';
				foreach ($seo as $k => $v) {
					echo '				<option value="'.$k.'"'.((isset($mod) AND ! empty($mod) AND $k == $mod) ? ' selected' : '').'>'.$v.'</option>';
				}
				echo '				</select>
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['amount_on_page'].'</td>
								<td>
									<select name="count" class="sw165">';
				for ($i = 1; $i < 6; $i ++) {
					echo '				<option value="'.$i.'">'.$i.'</option>';
				}
				echo '				</select>
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['search_word'].'</td>
								<td>
									<input type="text" name="word" size="50" required="required">
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_link'].'</td>
								<td>
									<input type="text" name="link" size="90" required="required">';
									$tm->outhint($lang['link_www_hint']);
				echo '			</td>
							</tr>
							<tr>
								<td>Title</td>
								<td>
									<input type="text" name="title" size="90">
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}
			else
			{
				// Нет модов для перелинковки
				echo '	<div class="section">
						<table class="work">
							<caption>'.$lang['seo_link'].'</caption>
							<tr>
								<td><div class="ac pads">'.$lang['data_not'].'</div></td>
							</tr>
							<tr class="tfoot">
								<td>&nbsp;</td>
							</tr>
						</table>
						</div>';
			}

			$tm->footer();
		}

		/**
		 * Добавить анкор (сохранение)
		 */
		if ($_REQUEST['dn'] == 'linkaddsave')
		{
			global $mod, $count, $word, $link, $title;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['seo_link']
				);

			$count = preparse($count, THIS_INT);

			$count = ($count > 0 AND $count < 6) ? $count : 1;
			$sinq = $db->query("SELECT * FROM ".$basepref."_seo_anchor WHERE mods = '".$mod."' AND word = '".$db->escape(trim($word))."'");

			if ($db->numrows($sinq) == 0 AND mb_strlen($link) > 3 AND mb_strlen($word) > 1)
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_seo_anchor VALUES (
						 NULL,
						 '".$mod."',
						 '".$count."',
						 '".$db->escape($word)."',
						 '".$db->escape($link)."',
						 '".$db->escape($title)."'
						 )"
					);

				$cache = new DN\Cache\CacheSeolink;
				$cache->cacheseolink();

				$url = (isset($mod) AND ! empty($mod)) ? '&amp;mod='.$mod : '';
				redirect('index.php?dn=linking'.$url.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();
				$tm->error($lang['seo_link'], $lang['add_anchor'], $lang['forgot_name']);
				$tm->footer();
			}
		}

		/**
		 * Редактировать анкор
		 */
		if ($_REQUEST['dn'] == 'linkedit')
		{
			global $said;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['seo_link']
				);

			$tm->header();

			$said = preparse($said, THIS_INT);
			$result = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_seo_anchor WHERE said = '".$said."'"));

			echo '	<div class="section">
					<form action="index.php?&amp;dn=linkeditsave&amp;ops='.$sess['hash'].'" method="post">
					<table class="work">
						<caption>'.$lang['edit_anchor'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['parent_view'].'</td>
							<td>
								<select name="mod">';
			$inq = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes' AND linking = 'yes'");
			while ($item = $db->fetchrow($inq))
			{
				echo '			<option value="'.$item['file'].'"'.(($result['mods'] == $item['file']) ? ' selected' : '').'>'.$item['name'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['amount_on_page'].'</td>
							<td>
								<select name="count">';
			for ($i = 1; $i < 6; $i ++) {
				echo '				<option value="'.$i.'"'.(($result['count'] == $i) ? ' selected' : '').'>'.$i.'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['search_word'].'</td>
							<td>
								<input type="text" name="word" size="50" value="'.$result['word'].'">
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_link'].'</td>
							<td>
								<input type="text" name="link" size="90" value="'.$result['link'].'">';
								$tm->outhint($lang['link_www_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>Title</td>
							<td>
								<input type="text" name="title" size="90" value="'.$result['title'].'">
							</td>
						</tr>
						<tr class="tfoot" >
							<td colspan="2">
								<input type="hidden" name="said" value="'.$said.'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать анкор (сохранение)
		 */
		if ($_REQUEST['dn'] == 'linkeditsave')
		{
			global $said, $mod, $count, $word, $link, $title;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['seo_link']
				);

			$said  = preparse($said, THIS_INT);
			$count = preparse($count, THIS_INT);

			$count = ($count > 0 AND $count < 6) ? $count : 1;
			$sinq = $db->query
						(
							"SELECT * FROM ".$basepref."_seo_anchor WHERE word = '".$db->escape(trim($word))."'
							 AND link = '".$db->escape(trim($link))."'
							 AND mods = '".$mod."'
							 AND said <> '".$said."'
							");

			if ($db->numrows($sinq) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_anchor'], null, $lang['duplicate_anchor']);
				$tm->footer();
			}

			if (mb_strlen($link) > 3 AND mb_strlen($word) > 1)
			{
				$db->query
					(
						"UPDATE ".$basepref."_seo_anchor SET
						 mods='".$mod."',
						 count='".$count."',
						 word='".$db->escape($word)."',
						 link='".$db->escape($link)."',
						 title='".$db->escape($title)."'
						 WHERE said = '".$said."'"
					);

				$cache = new DN\Cache\CacheSeolink;
				$cache->cacheseolink();

				$url = (isset($mod) AND ! empty($mod)) ? '&amp;mod='.$mod : '';
				redirect('index.php?dn=linking'.$url.'&amp;mod='.$mod.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();
				$tm->error($lang['edit_anchor'], null, $lang['forgot_name']);
				$tm->footer();
			}
		}

		/**
		 * Удалить анкор
		 */
		if ($_REQUEST['dn'] == 'linkdel')
		{
			global $said, $mod, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['seo_link']
				);

			$said = preparse($said, THIS_INT);
			$url = (isset($mod) AND ! empty($mod)) ? '&amp;mod='.$mod : '';

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_seo_anchor WHERE said = '".$said."'");
				$cache = new DN\Cache\CacheSeolink;
				$cache->cacheseolink();

				redirect('index.php?dn=linking'.$url.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_seo_anchor WHERE said = '".$said."'"));

				$yes = 'index.php?dn=linkdel'.$url.'&amp;said='.$said.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=linking'.$url.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_anchor'], preparse_un($item['word']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Социальные закладки
		 */
		if ($_REQUEST['dn'] == 'social')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['social_bookmark']
				);

			$tm->header();

			$itemset = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' AND setname = 'social_bookmark'"));
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['social_bookmark'].': '.$lang['all_set'].'</caption>
						<tr>
							<td>
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
			echo			eval($itemset['setcode']);
			echo '		</td>
							</tr>
							<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave">
								<input type="hidden" name="type" value="social_bookmark">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php?ops='.$sess['hash'].'&amp;dn=socialup" method="post">
					<table class="work">
						<caption>'.$lang['social_bookmark'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_icon'].'</th>
							<th>'.$lang['all_link'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_status'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$social = Json::decode($conf['social']);
			if (is_array($social))
			{
				foreach ($social as $k => $v)
				{
					$style = ($v['act'] == 'no') ? 'noactive' : '';
					echo '	<tr>
								<td class="'.$style.'"><img src="'.WORKURL.'/'.$v['icon'].'" alt="" /></td>
								<td class="'.$style.' vm">
									<input type="text" name="link" readonly="readonly" class="readonly" value="'.$v['link'].'" size="50">
								</td>
								<td class="'.$style.' vm">
									<input type="text" name="posit['.$k.']" value="'.$v['posit'].'" size="3">
								</td>
								<td class="'.$style.' vm">
									<input name="act['.$k.']" value="'.$v['act'].'" type="checkbox"'.(($v['act'] == 'yes') ? ' checked="checked"' : '').'>
								</td>
								<td class="'.$style.' gov vm">
									<a href="index.php?dn=socialedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
									<a href="index.php?dn=socialdel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="5">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php?ops='.$sess['hash'].'&amp;dn=socialadd" method="post">
					<table class="work">
						<caption>'.$lang['all_submint'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_link'].'</td>
							<td>
								<input type="text" name="link" size="90" style="width: 75%;" required="required">
								<div class="help">'.$lang['social_hint'].'</div>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="50" type="text" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/social/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td>
								<input name="alt" id="alt" size="50" type="text">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input class="main-button" value="'.$lang['all_add'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Социальные закладки (добавление)
		 */
		if ($_REQUEST['dn'] == 'socialadd')
		{
			global $link, $icon, $alt, $act;

			$social = Json::decode($conf['social']);

			if (strrpos($link, '{link}') AND strlen($icon) > 5)
			{
				$sim = preg_match('/\&amp;\b/i', $link) ? '&' : '&';
				$social[] = array(
							'posit' => 0,
							'link'  => str_replace(array("&", '"', "'"), array($sim, '', ''), $link),
							'icon'  => str_replace(array('"', "'"), '', $icon),
							'alt'   => str_replace(array('"', "'"), '', $alt),
							'act'   => $act
							);
				$i = Json::encode($social);
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($i)."' WHERE setopt = '".PERMISS."' AND setname = 'social'");
				$cache->cachesave(1);
			}

			redirect('index.php?dn=social&amp;ops='.$sess['hash']);
		}

		/**
		 * Социальные закладки (обновление)
		 */
		if ($_REQUEST['dn'] == 'socialup')
		{
			global $posit, $act;

			$social = Json::decode($conf['social']);

			if (is_array($social) AND is_array($posit) AND is_array($act))
			{
				$n = array();
				foreach ($social as $k => $v)
				{
					$p = (isset($posit[$k])) ? intval($posit[$k]) : $v['posit'];
					$a = (isset($act[$k]) AND ! empty($v['act'])) ? 'yes' : 'no';
					$sim = preg_match('/\&amp;\b/i', $v['link']) ? '&' : '&';
					$n[] = array(
							'posit' => $p,
							'link'  => str_replace(array("&", '"', "'"), array($sim, '', ''), $v['link']),
							'icon'  => str_replace(array('"', "'"), '', $v['icon']),
							'alt'   => str_replace(array('"', "'"), '', $v['alt']),
							'act'   => $a
							);
				}
				sort($n);
				$i = Json::encode($n);
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($i)."' WHERE setopt = '".PERMISS."' AND setname = 'social'");
				$cache->cachesave(1);
			}

			redirect('index.php?dn=social&amp;ops='.$sess['hash']);
		}

		/**
		 * Социальные закладки (редактирование)
		 */
		if ($_REQUEST['dn'] == 'socialedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['social_bookmark']
				);

			$tm->header();

			$social = Json::decode($conf['social']);
			if (is_array($social) AND isset($social[$id]))
			{
				echo '	<div class="section">
						<form action="index.php?ops='.$sess['hash'].'&amp;dn=socialeditsave&amp;id='.$id.'" method="post">
						<table class="work">
							<caption>'.$lang['all_edit'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_link'].'</td>
								<td>
									<input type="text" name="link" size="90" style="width: 75%;" value="'.$social[$id]['link'].'">
									<div class="help">'.$lang['social_hint'].'</div>
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_icon'].'</td>
								<td>
									<input name="icon" id="icon" size="50" type="text" value="'.$social[$id]['icon'].'">
									<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/social/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_alt_image'].'</td>
								<td>
									<input name="alt" id="alt" size="50" type="text" value="'.$social[$id]['alt'].'">
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_status'].'</td>
								<td>
									<select name="act" class="sw165">
										<option value="yes"'.(($social[$id]['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
										<option value="no"'.(($social[$id]['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
									</select>
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="3">
									<input class="main-button" value="'.$lang['all_save'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}

			$tm->footer();
		}

		/**
		 * Социальные закладки (сохранение редактирования)
		 */
		if ($_REQUEST['dn'] == 'socialeditsave')
		{
			global $link, $icon, $alt, $act, $id;

			$id = preparse($id, THIS_INT);
			$social = Json::decode($conf['social']);

			if (strrpos($link, '{link}') AND strlen($icon) > 5)
			{
				if (isset($social[$id]))
				{
					$sim = preg_match('/\&amp;\b/i', $link) ? '&' : '&';
					$social[$id] = array(
									'posit' => $social[$id]['posit'],
									'link'  => str_replace(array("&", '"', "'"), array($sim, '', ''), $link),
									'icon'  => str_replace(array('"', "'"), '', $icon),
									'alt'   => str_replace(array('"', "'"), '', $alt),
									'act'   => str_replace(array('"', "'"), '', $act)
									);
					$i = Json::encode($social);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($i)."' WHERE setopt = '".PERMISS."' AND setname = 'social'");
					$cache->cachesave(1);
				}
			}

			redirect('index.php?dn=social&amp;ops='.$sess['hash']);
		}

		/**
		 * Социальные закладки (удаление)
		 */
		if ($_REQUEST['dn'] == 'socialdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang['social_bookmark']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$social = Json::decode($conf['social']);
				$newsocial = '';
				foreach ($social as $dk => $v) {
					if ($dk != $id) {
						$newsocial[] = $v;
					}
				}
				if (is_array($newsocial)) {
					$i = Json::encode($newsocial);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($i)."' WHERE setopt = '".PERMISS."' AND setname = 'social'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$link = null;
				$social = Json::decode($conf['social']);
				if (isset($social[$id]) AND is_array($social))
				{
					$link = $social[$id]['link'];
				}

				$yes = 'index.php?dn=socialdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=social&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], null, $yes, $not, $link);
				$tm->footer();
			}

			redirect('index.php?dn=social&amp;ops='.$sess['hash']);
		}

		/**
		 * Настройки (сохранение)
		 */
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $setname, $type, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['seo'],
					$lang[$type]
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($lang[$type], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0)
					{
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			$cacheseo = new DN\Cache\CacheSeolink;
			$cacheseo->cacheseolink();

			$label = (array_key_exists('anchor', $set)) ? 'linking' : 'social';
			redirect('index.php?dn='.$label.'&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['seo'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
