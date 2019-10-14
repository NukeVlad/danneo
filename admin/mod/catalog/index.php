<?php
/**
 * File:        /admin/mod/catalog/index.php
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
	/**
	 * Глобальные
	 */
	global $ADMIN_ID, $CHECK_ADMIN, $AJAX, $db, $basepref, $tm, $conf, $modname, $wysiwyg, $lang, $sess, $ops, $cache;

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
	 * Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'optsave',
				'cat', 'catadd', 'catedit', 'catdel', 'cataddsave', 'catup', 'cateditsave',
				'list', 'fields', 'add', 'save', 'edit', 'editsave', 'act', 'del', 'autocomplete', 'listcat', 'work',
				'arrmove', 'arrdel', 'arract', 'arracc', 'arrprice',
				'makerlist', 'makerup', 'makeradd', 'makeredit', 'makersave', 'makerdel',
				'curlist', 'curup', 'curadd', 'curedit', 'cursave', 'curdel',
				'filedlist', 'filedup', 'filedadd', 'filededit', 'filededitsave', 'fileddel', 'filedaddval', 'filedupval', 'fileddelval',
				'weightlist', 'weightadd', 'weightedit', 'weightsave', 'weightdel',
				'sizelist', 'sizeadd', 'sizeedit', 'sizesave', 'sizedel',
				'taxlist', 'taxadd', 'taxedit', 'taxsave', 'taxdel',
				'delivlist', 'delivup', 'delivedit', 'delivsave', 'deliveditsave', 'delivdel', 'delivaddext', 'delivsaveext', 'deliveditext', 'deliveditsaveext', 'delivact',
				'statlist', 'statadd', 'statedit', 'statsave', 'statdel',
				'paylist', 'payup', 'payadd', 'payedit', 'paysave', 'paydel', 'payact',
				'ordlist', 'ordwork', 'ordarrdel', 'ordarrstatus', 'orddel',
				'reviews', 'reviewsrep', 'reviewsedit', 'reviewseditrep', 'newreviews', 'newreviewsrep',
				'ajaxedittitle', 'ajaxsavetitle', 'ajaxeditcat', 'ajaxsavecat', 'ajaxeditdate', 'ajaxsavedate',
				'ajaxeditprice', 'ajaxsaveprice', 'ajaxeditmaker', 'ajaxsavemaker',
				'tag', 'tagsetsave', 'tagedit', 'tageditsave', 'tagaddsave', 'tagdel',
				'agreement', 'saveagreement'
			);

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Доп. функции мода
		 */
		include('mod.function.php');

		/**
		 * Меню
		 */
		function this_menu()
		{
			global $db, $basepref, $conf, $tm, $lang, $sess, $AJAX;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, add, edit, del').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['add_product'].'</a>'
					.'<a'.cho('cat').' href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>'
					.'<a'.cho('catadd').' href="index.php?dn=catadd&amp;ops='.$sess['hash'].'">'.$lang['all_add_cat'].'</a>'
					.'<a'.cho('makerlist, makeredit, makerdel').' href="index.php?dn=makerlist&amp;ops='.$sess['hash'].'">'.$lang['maker'].'</a>'
					.'<a'.cho('filedlist, filededit, fileddel').' href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>'
					.'<a'.cho('weightlist, weightedit, weightdel').' href="index.php?dn=weightlist&amp;ops='.$sess['hash'].'">'.$lang['weight'].'</a>'
					.'<a'.cho('sizelist, sizeedit, sizedel').' href="index.php?dn=sizelist&amp;ops='.$sess['hash'].'">'.$lang['size'].'</a>';

			if (isset($conf[PERMISS]['buy']) AND $conf[PERMISS]['buy'] == 'yes')
			{
				$c = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".PERMISS."_order"));
				$cnew = ($c['total'] > 0) ? ' ('.$c['total'].')' : '';
				$link.= '<a'.cho('taxlist, taxedit, taxdel').' href="index.php?dn=taxlist&amp;ops='.$sess['hash'].'">'.$lang['tax'].'</a>'
						.'<a'.cho('delivlist, delivedit, delivdel, delivaddext, deliveditext').' href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>'
						.'<a'.cho('statlist, statedit, statdel').' href="index.php?dn=statlist&amp;ops='.$sess['hash'].'">'.$lang['buy_status'].'</a>'
						.'<a'.cho('paylist, payedit, paydel').' href="index.php?dn=paylist&amp;ops='.$sess['hash'].'">'.$lang['pay'].'</a>'
						.'<a'.cho('ordlist, ordwork, ordarrdel, ordarrstatus, orddel').' href="index.php?dn=ordlist&amp;ops='.$sess['hash'].'">'.$lang['orders'].$cnew.'</a>'
						.'<a'.cho('curlist, curadd, curedit, curdel').' href="index.php?dn=curlist&amp;ops='.$sess['hash'].'">'.$lang['currency'].'</a>';
			}

			if (isset($conf[PERMISS]['tags']) AND $conf[PERMISS]['tags'] == 'yes')
			{
				$link.= '<a'.cho('tag, tagedit, tagdel').' href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>';
			}

			if (isset($conf[PERMISS]['resact']) AND $conf[PERMISS]['resact'] == 'yes')
			{
				if ($AJAX) {
					$link.= '<a class="all-comments" href="index.php?dn=reviews&amp;ajax=1&amp;ops='.$sess['hash'].'">'.$lang['response'].'</a>';
				} else {
					$link.= '<a href="index.php?dn=reviews&amp;ajax=0&amp;ops='.$sess['hash'].'">'.$lang['response'].'</a>';
				}
			}

			if (isset($conf[PERMISS]['resmoder']) AND $conf[PERMISS]['resmoder'] == 'yes')
			{
				$c = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND active = '0'"));
				if ($AJAX) {
					$link.= '<a class="all-comments" href="index.php?dn=newreviews&amp;ajax=1&amp;ops='.$sess['hash'].'">'.$lang['response_new'].'&nbsp; &#8260; &nbsp;'.$c['total'].'</a>';
				} else {
					$link.= '<a href="index.php?dn=newreviews&amp;ajax=0&amp;ops='.$sess['hash'].'">'.$lang['response_new'].'&nbsp; &#8260; &nbsp;'.$c['total'].'</a>';
				}
			}

			if (isset($conf[PERMISS]['buy']) AND $conf[PERMISS]['buy'] == 'yes')
			{
				$link.= '<a'.cho('agreement').' href="index.php?dn=agreement&amp;ops='.$sess['hash'].'">'.$lang['agree'].'</a>';
			}

			$filter = null;
			if (cho('list')) {
				$filter = '<a'.cho('list', 1).' href="#" onclick="$(\'#filter\').slideToggle();" title="'.$lang['search_in_section'].'">'.$lang['all_filter'].'</a>';
			}

			$tm->this_menu($link, $filter);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

        /**
         * Настройки
         ------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $ro, $realmod;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			$ignored = array('currencys', 'status', 'taxes', 'weights', 'sizes', 'payment', 'groups', 'agreement');
			$not_buy = array();
			if ($conf[PERMISS]['buy'] == 'no')
			{
				$not_buy = array('request', 'request_email', 'history', 'currency', 'cookie', 'cookieexp', 'statusok', 'statuscheckout', 'statusdelive', 'statuspersonal');
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' ORDER BY setid ASC");
			while ($itemset = $db->fetchrow($inqset))
			{
				if ( ! in_array($itemset['setname'], $ignored) AND ! in_array($itemset['setname'], $not_buy))
				{
					echo	in_array($itemset['setname'], array('resact', 'rating', 'buy', 'search', 'rec', 'agreement', 'rss')) ? '<tr><th colspan="2"></th></tr>' : '';
					echo '	<tr>
								<td class="first">'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'</td>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
						</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input accesskey="s" class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

        /**
         * Настройки (сохранение)
         ---------------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
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
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Категории
		 ----------------*/
		if ($_REQUEST['dn'] == 'cat')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_cat']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_cat'].'</caption>
						<tr>
							<th>ID</th>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['all_cat_access'].'</th>
							<th>'.$lang['all_col'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$inquiry = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			catalog_cat(0, 0);
			echo '		<tr class="tfoot">
							<td colspan="7">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="dn" value="catup" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Категории (сохранение позиций)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'catup')
		{
			global $posit;

			if (preparse($posit, THIS_ARRAY) == 1)
			{
				this_catup($posit, PERMISS);
			}

			$counts = new Counts(PERMISS, 'id');
			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить категорию
		 ------------------------*/
		if ($_REQUEST['dn'] == 'catadd')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$inquiry = $db->query("SELECT catid,parentid,catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			this_selectcat(0);

			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_add_cat'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="catname" id="catname" size="70" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70" />';
								$tm->outtranslit('catname', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="catcustom" size="70" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" />';
								$tm->outhint($lang['keyword_hint']);
            echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
							<select name="catid" class="sw250">
								<option value="0">'.$lang['all_cat_new'].'</option>
								'.$selective.'
							</select>
						</td>
						</tr>
						<tr>
							<td>'.$lang['all_sorting'].'</td>
							<td>
								<select name="sort" class="sw165">';
			foreach ($catsort as $k => $v)
			{
				echo '				<option value="'.$k.'">'.$v.'</option>';
			}
			echo '				</select> &nbsp;&#247;&nbsp;
								<select name="ord" class="sw150">
									<option value="asc">'.$lang['all_acs'].'</option>
									<option value="desc">'.$lang['all_desc'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cat_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs))
					{
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes" /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>RSS</td>
							<td>
								<select name="rss" class="sw165">
									<option value="yes">'.$lang['all_yes'].'</option>
									<option value="no">'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, '', 1);
			echo '			</td>
						</tr>
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['multi_fields'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="optin" id="optin" size="5" multiple class="blue pw100 app">';
				$opts = $db->query("SELECT oid, title FROM ".$basepref."_".PERMISS."_option");
				while ($items = $db->fetchrow($opts))
				{
						echo '					<option value="'.$items['oid'].'">'.$items['title'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addopt();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.delopt();" value="&#9668;" />
										</td>
										<td>
											<select name="optout" id="optout" size="5" multiple class="green pw100 app">
											</select>
											<div id="area-opt">
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="cataddsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление категории (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'cataddsave')
		{
			global $catid, $catname, $subtitle, $cpu, $catcustom, $keywords, $descript, $icon, $descr, $acc, $group, $sort, $ord, $opt, $rss;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_add']
				);

			$catname = preparse($catname, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$icon    = preparse($icon, THIS_TRIM);

			if (preparse($catname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['all_add_cat'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($catname);
				}

				$inqure = $db->query("SELECT catname, catcpu FROM ".$basepref."_".PERMISS."_cat WHERE catname = '".$db->escape($catname)."' OR catcpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['all_add_cat'], $catname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$sort = isset($catsort[$sort]) ? $sort : 'public';
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$ord = ($ord == 'asc') ? 'asc' : 'desc';
			$rss = ($rss == 'yes') ? 'yes' : 'no';

			$options = null;
			if (is_array($opt) AND sizeof($opt) > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option ORDER BY posit ASC");
				$check = $newopt = array();
				while ($item = $db->fetchrow($inq)) {
					$check[$item['oid']] = $item['oid'];
				}
				foreach ($opt as $k)
				{
					if (isset($check[$k])) {
						$newopt[$k] = $k;
					}
				}
				if (sizeof($newopt) > 0) {
					$options = Json::encode($newopt);
				}
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_cat VALUES (
					 NULL,
					 '".$catid."',
					 '".$db->escape($cpu)."',
					 '".$db->escape(preparse_sp($catname))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape($descr)."',
					 '".$db->escape($catcustom)."',
					 '".$db->escape($keywords)."',
					 '".$db->escape($descript)."',
					 '0',
					 '".$db->escape($icon)."',
					 '".$acc."',
					 '".$db->escape($group)."',
					 '".$sort."',
					 '".$ord."',
					 '".$rss."',
					 '".$db->escape($options)."',
					 '0'
					 )"
				);

			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление категории
		 -------------------------*/
		if ($_REQUEST['dn'] == 'catdel')
		{
			global $catid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_delet']
				);

			$catid = preparse($catid, THIS_INT);

			if ($ok == 'yes')
			{
				$del = catalog_delcat($catid, PERMISS);
				if ($del > 0) {
					$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE catid = '".$catid."'");
					$db->query("DELETE FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'");
				}
				$counts = new Counts(PERMISS, 'id');
				$cache->cachesave(1);
				redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT catname FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));

				$yes = 'index.php?dn=catdel&amp;catid='.$catid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=cat&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_cat'], preparse_un($item['catname']), $yes, $not, $lang['del_cat_alert']);
				$tm->footer();
			}
		}

		/**
		 * Редактировать категорию
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'catedit')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$catid = preparse($catid, THIS_INT);
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			this_selectcat(0);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['cat_edit'].': '.preparse_un($item['catname']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="catname" id="catname" size="70" value="'.preparse_un($item['catname']).'" required="required" /> <span class="light">&lt;h1&gt;</span>
							</td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70" value="'.$item['catcpu'].'" />';
								$tm->outtranslit('catname', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="catcustom" size="70" value="'.preparse_un($item['catcustom']).'" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="parentid" class="sw250">
									<option value="0">'.$lang['all_cat_new'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_sorting'].'</td>
							<td>
								<select name="sort" class="sw165">';
			foreach ($catsort as $k => $v)
			{
				echo '				<option value="'.$k.'"'.(($item['sort'] == $k) ? ' selected' : '').'>'.$v.'</option>';
			}
			echo '				</select> &nbsp;&#247;&nbsp;
								<select name="ord" class="sw150">
									<option value="asc"'.(($item['ord'] == 'asc') ? ' selected' : '').'>'.$lang['all_acs'].'</option>
									<option value="desc"'.(($item['ord'] == 'desc') ? ' selected' : '').'>'.$lang['all_desc'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cat_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all"'.(($item['access'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['access'] == 'user' AND empty($item['groups'])) ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['access'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['access'] == 'all' OR $item['access'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs))
					{
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').'><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>RSS</td>
							<td>
								<select name="rss" class="sw165">
									<option value="yes"'.(($item['rss'] == 'yes') ? ' selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no"'.(($item['rss'] == 'no') ? ' selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, $item['catdesc'], 1);
			echo '			</td>
						</tr>
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['multi_fields'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="optin" id="optin" size="5" multiple class="blue pw100 app">';
				$optins = $optshow = NULL;
				$options = ( ! empty($item['options']) ? implode(',', array_keys(Json::decode($item['options']))) : '');
				if ( ! empty($options))
				{
					$tag_in = $db->query("SELECT oid, title FROM ".$basepref."_".PERMISS."_option WHERE oid IN (".$options.")");
					while ($tag = $db->fetchrow($tag_in))
					{
						$optshow.= '			<option value="'.$tag['oid'].'">'.$tag['title'].'</option>';
						$optins.= '				<input type="hidden" name="opt[]" value="'.$tag['oid'].'">';
					}
				}
				$sql = ( ! empty($options)) ? " WHERE oid NOT IN (".$options.")" : '';
				$tag_not = $db->query("SELECT oid, title FROM ".$basepref."_".PERMISS."_option".$sql);
				while ($tag = $db->fetchrow($tag_not))
				{
						echo '					<option value="'.$tag['oid'].'">'.$tag['title'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addopt();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.delopt();" value="&#9668;" />
										</td>
										<td>
											<select name="optout" id="optout" size="5" multiple class="green pw100 app">
												'.$optshow.'
											</select>
											<div id="area-opt">
												'.$optins.'
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="cateditsave" />
								<input type="hidden" name="catid" value="'.$catid.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать категорию (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'cateditsave')
		{
			global $parentid, $catid, $catname, $subtitle, $cpu, $catcustom, $keywords, $descript, $icon, $descr, $acc, $group, $sort, $ord, $rss, $opt;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$catname = preparse($catname, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);
			$parentid = preparse($parentid, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$err = this_councat($catid, $parentid, PERMISS);

			if (preparse($catname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['cat_edit'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($catname);
				}

				$inqure = $db->query
							(
								"SELECT catname, catcpu FROM ".$basepref."_".PERMISS."_cat
								 WHERE (catname = '".$db->escape($catname)."' OR catcpu = '".$db->escape($cpu)."')
								 AND catid <> '".$catid."'
								"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['cat_edit'], $catname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if ($err == 1)
			{
				$tm->header();
				$tm->error($lang['cat_edit'], $catname, $lang['move_cat_alert']);
				$tm->footer();
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$sort  = isset($catsort[$sort]) ? $sort : 'public';
			$acc   = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$ord   = ($ord == 'asc') ? 'asc' : 'desc';
			$rss   = ($rss == 'yes') ? 'yes' : 'no';
			$upparentid = ($catid != $parentid) ? "parentid = '".$parentid."'," : "";

			$options = null;
			if (is_array($opt) AND sizeof($opt) > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option ORDER BY posit ASC");
				$check = $newopt = array();
				while ($item = $db->fetchrow($inq)) {
					$check[$item['oid']] = $item['oid'];
				}
				$item = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
				$old = Json::decode($item['options']);
				foreach ($opt as $k)
				{
					if (isset($check[$k])) {
						$newopt[$k] = $k;
						unset($old[$k]);
					}
				}
				if (is_array($old) AND sizeof($old) > 0)
				{
					$in = implode(',', $old);
					$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE id IN (SELECT id FROM ".$basepref."_".PERMISS." WHERE catid IN (".$db->escape($in)."))");
				}
				if (is_array($newopt) AND sizeof($newopt) > 0) {
					$options = Json::encode($newopt);
				}
			}
			else
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE id IN (SELECT id FROM ".$basepref."_".PERMISS." WHERE catid = '".$catid."')");
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_cat SET ".$upparentid."
					 catcpu    = '".$db->escape($cpu)."',
					 catname   = '".$db->escape(preparse_sp($catname))."',
					 subtitle  = '".$db->escape(preparse_sp($subtitle))."',
					 catdesc   = '".$db->escape($descr)."',
					 catcustom = '".$db->escape(preparse_sp($catcustom))."',
					 keywords  = '".$db->escape(preparse_sp($keywords))."',
					 descript  = '".$db->escape(preparse_sp($descript))."',
					 icon      = '".$db->escape($icon)."',
					 access    = '".$acc."',
					 groups    = '".$db->escape($group)."',
					 sort      = '".$sort."',
					 ord       = '".$ord."',
					 rss       = '".$rss."',
					 options   = '".$db->escape($options)."'
					 WHERE catid = '".$catid."'"
				);

			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Все товары (листинг)
		 ------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $conf, $selective, $selmark, $catid, $nu, $p, $cat, $mark, $s, $l, $ajax, $filter, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['products']
				);

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;

			if (preparse($ajax,THIS_INT) == 0)
			{
				$tm->header();
				echo '<div id="ajaxbox">';
			}
			else
			{
				echo '<script>$(function(){ $("img, a").tooltip(); });</script>';
			}

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$sort = array('id', 'public', 'title', 'makid', 'price', 'buyhits', 'hits', 'reviews');
			$limit = array('desc', 'asc');
			$s  = (in_array($s, $sort)) ? $s : 'id';
			$l  = (in_array($l, $limit)) ? $l : 'desc';
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$groups_only = array();
			if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
			{
				$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
				while ($items = $db->fetchrow($inqs)) {
					$groups_only[] =  $items['title'];
				}
			}

			$maker_only = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_maker");
			while ($item = $db->fetchrow($inq)) {
				$maker_only[$item['makid']] =  $item['makname'];
			}

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = $catcaches = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}

			if (isset($cat) AND isset($catcaches[$cat]) OR isset($cat) AND $cat == 0 AND $cat != 'all')
			{
				$sql = " WHERE catid = '".preparse($cat, THIS_INT)."'";
				$link = "&amp;cat=".preparse($cat, THIS_INT);
				$catid = $cat;
			}
			elseif (isset($mark) OR isset($mark) AND $mark == 0 AND $mark != 'all')
			{
				$sql = " WHERE makid = '".preparse($mark, THIS_INT)."'";
				$link = "&amp;mark=".preparse($mark, THIS_INT);
				$makid = $mark;
			}
			else
			{
				$sql = '';
				$link = '&amp;cat=all';
				$cat = 'all';
				$catid = 0;
			}
			$fu = '';
			$fid = preparse($fid, THIS_INT);
			$myfilter = array(
									'title'	=> array('title', 'all_name', 'input'),
									'articul'	=> array('articul', 'articul', 'input'),
									'public'	=> array('public', 'all_data', 'date'),
									'price'	=> array('price', 'price', 'intval'),
									'acc'		=> array('acc', 'all_access', 'type', array('unimportant', 'all_all', 'all_user_only'), array('', 'all', 'user')),
									'store'	=> array('store', 'storehouse', 'type', array('unimportant', 'all_there', 'all_there_no'), array('', 'yes', 'no')),
									'rec'		=> array('rec','recommended','checkbox')
								);
			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0)
				{
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ',$insert);
					$fu = '&fid='.$item['fid'];
				}
			}
			else
			{
				if (isset($filter) AND is_array($filter))
				{
					$sw = array();
					foreach ($filter as $k => $v)
					{
						if (isset($myfilter[$k]))
						{
							$f = $myfilter[$k];
							if ($f[2] == 'input' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'type' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'checkbox' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'date' AND is_array($v)) {
								if(isset($v[0]) AND ! empty($v[0])){
									$sw[] = $f[0]." > '".$db->escape(ReDate($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." < '".$db->escape(ReDate($v[1]))."'";
								}
							}
							if ($f[2] == 'intval' AND is_array($v))
							{
								if(isset($v[0]) AND ! empty($v[0])){
									$sw[] = $f[0]." >= '".$db->escape(intval($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." <= '".$db->escape(intval($v[1]))."'";
								}
							}
						}
					}
					if (sizeof($sw) > 0)
					{
						$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ', $sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$fid = $db->insertid();
						if ($fid > 0) {
							$fu = '&fid='.$fid;
						}
					}
				}
			}
			$link.= $fu;
			$a = ($ajaxlink) ? '&amp;ajax=1' : '';
			$revs = $link.$a.'&amp;nu='.$nu.'&amp;s='.$s.'&amp;l='.(($l=='desc') ? 'asc' : 'desc');
			$rev =  $link.$a.'&amp;nu='.$nu.'&amp;l=desc&amp;s=';
			$link.= $a.'&amp;s='.$s.'&amp;l='.$l;
			//$links = 'index.php?dn=list'.$a.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'&amp;cat=';
			//$lmark = 'index.php?dn=list'.$a.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'&amp;mark=';
			$c = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".PERMISS.$sql.""));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS.$sql." ORDER BY ".$s." ".$l." LIMIT ".$sf.", ".$nu);
			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.$sql, 'id', 'index', 'list'.$link, $nu, $p, $sess, $ajaxlink);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'].$link, $nu, $ajaxlink);
			$tm->filter('index.php?dn=list&amp;ops='.$sess['hash'], $myfilter, $modname[PERMISS]);
			this_selectcat(0);
			foreach ($maker_only as $key => $val)
			{
				$selmark.= '<option value="'.$key.'"'.(($mark == $key) ? ' selected' : '').'>'.preparse_un($val).'</option>';
			}
			echo '	<script>
						var ajax = '.$ajaxlink.';
					</script>';
			if ($ajaxlink)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache: false, async: false});
							$(".comment-view").colorbox({
								transition : "elastic",
								scrolling: false,
								width : "92%",
								height	: "90%",
								initialWidth	: 800,
								initialHeight	: 600,
								maxHeight : 800,
								maxWidth : 1200,
								fixed: true,
								onComplete: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_product'].'</caption>
						<tr>
							<td class="vm">
								'.$lang['maker'].':&nbsp;
								<form action="index.php" method="post">
									<select name="mark">
									<option value="all">'.$lang['all_all'].'</option>
									'.$selmark.'
									</select>
									<input type="hidden" name="dn" value="list" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
								</form> &nbsp;
								'.$lang['all_cat_one'].':&nbsp;
								<form action="index.php" method="post">
									<select name="cat">
										<option value="all">'.$lang['all_all'].'</option>
										<option value="0"'.(($cat != 'all' AND $cat == 0) ? ' selected' : '').'>'.$lang['cat_not'].'</option>
										'.$selective.'
									</select>
									<input type="hidden" name="dn" value="list" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
								</form>
							</td>
						</tr>
					</table>
					<div class="upad"></div>
					<form action="index.php" method="post">
					<table id="list" class="work">
						<tr><td colspan="11">'.$amount.'</td></tr>
						<tr>
							<th'.isort('id').'>ID</th>
							<th'.isort('title').'>'.$lang['all_name'].'&nbsp; &#8260; &nbsp;'.$lang['all_cat_one'].'</th>
							<th'.isort('makid').'>'.$lang['maker'].'</th>
							<th'.isort('public').'>'.$lang['all_data'].'</th>
							<th'.isort('price').'>'.$lang['price'].'&nbsp; &#8260; &nbsp;'.$conf[PERMISS]['currency'].'</th>
							<th'.isort('buyhits').'>'.$lang['buy_amount'].'</th>
							<th'.isort('hits').'>'.$lang['all_hits'].'</th>
							<th'.isort('reviews').'>'.$lang['response'].'</th>
							<th class="work-no-sort">'.$lang['all_access'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac">
								<input name="checkboxall" id="checkboxall" value="yes" type="checkbox" />
							</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = ($item['act'] == 'no') ? 'no-active' : '';
				$stylework = ($item['act'] == 'no') ? 'no-active' : '';

				$groupact = NULL;
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					if ( ! empty($item['groups']))
					{
						$groups = Json::decode($item['groups']);
						reset($groups);
						foreach ($groups as $key => $val)
						{
							$groupact.=  ' '.$groups_only[$key - 1].',';
						}
						$groupact = chop($groupact, ',');
					}
				}

				echo '	<tr class="list">
							<td class="'.$style.' ac pw5">'.$item['id'].'</td>
							<td class="'.$style.' pw25">';
				if ($item['public'] >= (TODAY - 86400)) {
					echo '		<img src="'.ADMPATH.'/template/images/totalinfo.gif" style="float: right; padding: 1px;" alt="'.$lang['add_today'].'" />';
				}
				if ($ajaxlink == 1) {
					echo '		<div id="te'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'te'.$item['id'].'\',\'405\')" title="'.$lang['all_name'].'&nbsp; &#8260; &nbsp;'.$lang['all_change'].'">
										'.preparse_un($item['title']).'
									</a>
								</div>';
				} else {
					echo '		<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'">'.preparse_un($item['title']).'</a>';
				}
				if ($ajaxlink == 1) {
					echo '		<div class="cats" id="ce'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'ce'.$item['id'].'\',\'305\')" title="'.$lang['all_cat_one'].'&nbsp; &#8260; &nbsp;'.$lang['all_change'].'">
										'.preparse_un(linecat($item['catid'], $catcaches)).'
									</a>
								</div>';
				} else {
					echo '		<div class="cats">'.preparse_un(linecat($item['catid'], $catcaches)).'</div>';
				}
				echo '		</td>
							<td class="'.$style.' pw10">';
				if ($ajaxlink == 1) {
					echo '		<div id="me'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditmaker&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'me'.$item['id'].'\',\'305\')" title="'.$lang['all_change'].'">
										'.(($item['makid'] != 0) ? $maker_only[$item['makid']] : '&#8212;').'
									</a>
								</div>';
				} else {
					echo 		(($item['makid'] != 0) ? $maker_only[$item['makid']] : '&#8212;');
				}
				echo '		</td>
							<td class="'.$style.' pw10">';
				if ($ajaxlink == 1) {
					echo '		<div id="pe'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'pe'.$item['id'].'\',\'220\')" title="'.$lang['all_change'].'">
										'.format_time($item['public'], 0, 1).'
									</a>
								</div>';
				} else {
					echo		format_time($item['public'], 0, 1);
				}
				echo '		</td>
							<td class="'.$style.' pw10">';
				if ($ajaxlink == 1) {
					echo '		<div id="de'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditprice&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'\',\'de'.$item['id'].'\',\'205\')" title="'.$lang['all_change'].'">'.formats($item['price'], 2, '.', '').'</a>
								</div>';
				} else {
					echo $item['price'];
				}
				echo '		</td>
							<td class="'.$style.' pw10">'.$item['buyhits'].'</td>
							<td class="'.$style.' pw10">'.$item['hits'].'</td>
							<td class="'.$style.' pw10">';
				if ($item['reviews'] > 0) {
					echo '		'.$item['reviews'].'&nbsp; &nbsp;
								<a class="comment-view" href="index.php?dn=reviewsedit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].(($ajaxlink) ? '&amp;ajax=1' : '').'">
									<img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" />
								</a>';
				} else {
					echo '0';
				}
				echo '		</td>
							<td class="'.$style.' pw10">';
				echo '			'.(($item['acc'] == 'user') ? ( ! empty($item['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']);
				echo '		</td>
							<td class="'.$style.' gov pw10">
								<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if ($item['act'] == 'yes') {
					echo '		<a href="index.php?dn=act&amp;act=no&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=act&amp;act=yes&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				}
				echo '			<a href="index.php?dn=del&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
							<td class="'.$style.' mark pw5">
								<input type="checkbox" name="array['.$item['id'].']" value="yes" />
							</td>
						</tr>';
			}
			echo '		<tr>
							<td colspan="11">'.$lang['all_mark_work'].':&nbsp;
								<select name="workname">
									<option value="price">'.$lang['price'].'</option>
									<option value="del">'.$lang['all_delet'].'</option>
									<option value="active">'.$lang['included'].'&nbsp; &#8260; &nbsp;'.$lang['not_included'].'</option>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes') {
				echo '				<option value="access">'.$lang['all_access'].'</option>';
			}
			echo '					<option value="move">'.$lang['all_move'].'</option>
								</select>
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="cat" value="'.$cat.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="s" value="'.$s.'" />
								<input type="hidden" name="l" value="'.$l.'" />';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'" />';
			}
			echo '				<input type="hidden" name="dn" value="work" />
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
							</td>
						</tr>
						<tr><td colspan="11">'.$pages.'</td></tr>
                  </table>
                  </form>
                  </div>';
			if (preparse($ajax, THIS_INT) == 0) {
				echo '</div>';
				$tm->footer();
			}
		}

		/**
		 * Изменение состояния (вкл./выкл.)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'act')
		{
			global $act, $id, $p, $nu, $cat, $mark, $s, $l, $fid;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			if ($act == 'no' OR $act == 'yes') {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET act='".$act."' WHERE id = '".$id."'");
			}

			$cache->cachesave(1);
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ($cat !== '') ? '&amp;cat='.$cat : '';
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($mark)) ? '&amp;mark='.$mark : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовая работа с товарами
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'work')
		{
			global $array, $workname, $selective, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['array_control']
				);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				$temparray = $array;
				$count = count($temparray);

				$hidden = '';
				foreach ($array as $key => $id) {
					$hidden.= '<input type="hidden" name="array['.$key.']" value="yes" />';
				}

				$p = preparse($p, THIS_INT);
				$cat = preparse($cat, THIS_INT);
				$nu = preparse($nu, THIS_INT);
				$s = preparse($s, THIS_TRIM, 1, 7);
				$l = preparse($l, THIS_TRIM, 1, 4);
				$fid = preparse($fid, THIS_INT);

				$h = '	<input type="hidden" name="p" value="'.$p.'" />
						<input type="hidden" name="cat" value="'.$cat.'" />
						<input type="hidden" name="nu" value="'.$nu.'" />
						<input type="hidden" name="s" value="'.$s.'" />
						<input type="hidden" name="l" value="'.$l.'" />
						<input type="hidden" name="fid" value="'.$fid.'" />
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />';

				// Удаление
				if ($workname == 'del')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['all_delet'].': '.$lang['all_goods'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
                              <tr class="tfoot">
                                  <td>
                                      <input type="hidden" name="ops" value="'.$sess['hash'].'" />
                                      '.$hidden.'
                                      '.$h.'
                                      <input type="hidden" name="dn" value="arrdel" />
                                      <input class="side-button" value="'.$lang['all_go'].'" type="submit" />
                                      <input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
                                  </td>
                              </tr>
                          </table>
                          </form>
                          </div>';
					$tm->footer();

				// Перемещение
				}
				elseif ($workname == 'move')
				{
					$tm->header();
					$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
					$catcache = array();
					while ($item = $db->fetchrow($inquiry))
					{
						$catcache[$item['parentid']][$item['catid']] = $item;
					}
					this_selectcat(0);
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['all_move'].' '.$lang['all_in_cat'].': '.$lang['all_goods'].' ('.$count.')</caption>
								<tr>
									<td class="cont">
										<select name="catid">
											<option value="0">'.$lang['cat_not'].'</option>
											'.$selective.'
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrmove" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				}
				// Активация
				elseif ($workname == 'active')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['change_status'].': '.$lang['all_goods'].' ('.$count.')</caption>
								<tr>
									<td class="cont">
										<select name="act">
											<option value="yes">'.$lang['included'].'</option>
											<option value="no">'.$lang['not_included'].'</option>
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arract" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				}
				// Цены
				elseif ($workname == 'price')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['change_price'].': '.$lang['all_goods'].' ('.$count.')</caption>
								<tr>
									<td class="pw45">'.$lang['all_value'].'</td>
									<td><input name="value" size="10" value="0.0000" type="text" /></td>
								</tr>
								<tr>
									<td>'.$lang['all_action'].'</td>
									<td>
										<select name="act" class="red strong sw165" style="font-size: 1.2em; padding: 1px 3px;">
											<option value="plus"> + </option>
											<option value="minus"> &#8722; </option>
											<option value="multiply"> &#215; </option>
										</select>
									</td>
								</tr>
								<tr>
									<td>'.$lang['type'].'</td>
									<td>
										<select name="type" class="sw165">
											<option value="fix">'.$lang['fix_price'].'</option>
											<option value="percent">'.$lang['percent'].'</option>
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td colspan="2">
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrprice" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
						$tm->footer();

				}
				// Доступ
				elseif ($workname == 'access' AND isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['all_access'].'&nbsp; &#8260; &nbsp;'.$lang['all_change'].': '.$lang['all_goods'].' ('.$count.')</caption>
								';
					if($conf['user']['regtype'] == 'yes')
					{
						echo '	<tr>
									<td class="pw45">'.$lang['all_access'].'</td>
									<td>
										<select class="group-sel sw165" name="acc" id="acc">
											<option value="all">'.$lang['all_all'].'</option>
											<option value="user">'.$lang['all_user_only'].'</option>
											'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '').'
										</select>
										<div id="group" class="group" style="display: none;">';
						if ($conf['user']['groupact'] == 'yes')
						{
							$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
							$group_out = '';
							while ($items = $db->fetchrow($inqs)) {
								$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes" /><span>'.$items['title'].'</span>,';
							}
							echo chop($group_out, ',');
						}
						echo '			</div>
									</td>
								</tr>';
					}
					echo '		<tr class="tfoot">
									<td colspan="2">
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arracc" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();
				}
			}
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Массовое удаление
		 ---------------------*/
		if ($_REQUEST['dn'] == 'arrdel')
		{
			global $array, $p, $cat, $nu, $s, $l, $fid;

			if (preparse($array, THIS_ARRAY) == 1)
			{
				foreach ($array as $id => $v)
				{
					$id = preparse($id, THIS_INT);
					$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE id IN (".$id.")");
					$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");
				}
			}
			$counts = new Counts(PERMISS, 'id');

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое перемещение (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'arrmove')
		{
			global $catid, $array, $p, $cat, $nu, $s, $l, $fid;

			$catid = preparse($catid, THIS_INT);
			allarrmove($array, $catid, PERMISS);

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(1);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовая активация
		 ----------------------*/
		if ($_REQUEST['dn'] == 'arract')
		{
			global $array, $act, $p, $cat, $nu, $s, $l, $fid;

			$fid = preparse($fid, THIS_INT);
			$act = ($act == 'yes') ? 'yes' : 'no';

			allarract($array, 'id', PERMISS, $act);
			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое изменение доступа
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'arracc')
		{
			global $array, $group, $acc, $p, $cat, $nu, $s, $l, $fid;

			if (preparse($array, THIS_ARRAY) == 1)
			{
				if (
					isset($conf['user']['groupact']) AND
					$conf['user']['groupact'] == 'yes' AND
					$acc == 'group' AND is_array($group)
				)
				{
					$group = Json::decode($group);
				}

				$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
				foreach ($array as $id => $v)
				{
					$id = preparse($id, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS." SET acc = '".$acc."', groups = '".$db->escape($group)."' WHERE id = '".$id."'");
				}
			}

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое изменение цен
		 --------------------------*/
		if ($_REQUEST['dn'] == 'arrprice')
		{
			global $array, $type, $value, $act, $p, $cat, $nu, $s, $l, $fid;

			$value = (ceil($value) > 0) ? formats($value, 4, '.', '') : '0.0000';

			if (preparse($array, THIS_ARRAY) == 1 AND $value > 0)
			{
				$ins = array();
				foreach ($array as $id => $v)
				{
					$id = intval($id);
					$ins[$id] = $id;
				}

				$in = implode(',', $ins);
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id IN (".$db->escape($in).")");

				echo $type;
				while ($item = $db->fetchrow($inq))
				{
					$price = $oldprice = $item['price'];
					if ($type == 'percent')
					{
						$pc = ($item['price'] / 100) * $value;
						if ($act == 'plus') {
							$price = $price + $pc;
						} elseif ($act == 'minus') {
							$price = $price - $pc;
						} elseif ($act == 'multiply') {
							$price = $price * $pc;
						}
					}
					if ($type == 'fix')
					{
						if ($act == 'plus') {
							$price = $price + $value;
						} elseif ($act == 'minus') {
							$price = $price - $value;
						} elseif ($act == 'multiply') {
							$price = $price * $value;
						}
					}
					if ($price != $oldprice) {
						$db->query("UPDATE ".$basepref."_".PERMISS." SET price = '".$db->escape(formats($price, 4, '.', ''))."' WHERE id = '".$item['id']."'");
					}
				}
			}

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Добавить товар
		 ------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			global $catid, $selective, $field, $data;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['add_product']
				);

			$tm->header();

			$public = CalendarFormat(NEWTIME);
			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$inqmak = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_maker ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script src="'.ADMPATH.'/js/jquery.autocomplete.js"></script>
					<script>
					var all_name     = "'.$lang['all_name'].'";
					var all_cpu      = "'.$lang['all_cpu'].'";
					var all_popul    = "'.$lang['all_popul'].'";
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
					var all_file     = "'.$lang['all_file'].'";
					var all_path     = "'.$lang['all_path'].'";
					var all_name     = "'.$lang['all_name'].'";
					var page         = "'.PERMISS.'";
					var ops          = "'.$sess['hash'].'";
					var filebrowser = "'.$lang['filebrowser'].'";
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
						<caption>'.$modname[PERMISS].': '.$lang['add_product'].'</caption>
						<tr>
							<th></th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> &lt;h1&gt; '.$lang['all_name'].'</td>
							<td><input type="text" name="title" id="title" size="70" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input name="cpu" id="cpu" size="70" type="text" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>&lt;title&gt; '.$lang['all_title'].'</td>
							<td><input type="text" name="customs" size="70" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input name="descript" size="70" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input name="keywords" size="70" type="text" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" size="25" value="'.$public.'" />';
								Calendar('cal','public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic" size="25" />';
								Calendar('stcal','stpublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_unpublic'].'</td>
							<td><input type="text" name="unpublic" id="unpublic" size="25" />';
								Calendar('uncal','unpublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" onchange="$.changecategory(this,\'index.php?dn=fields&amp;ops='.$sess['hash'].'&amp;catid=\',\'extra\');" class="sw250">
									<option value="0"> &#8212; '.$lang['cat_not'].' &#8212; </option>';
				$catcache = array();
				while ($item = $db->fetchrow($inqcat))
				{
					$catcache[$item['parentid']][$item['catid']] = $item;
				}
				this_selectcat(0);
				echo '				'.$selective.'
								</select>
							</td>
						</tr>';
			}
			if ($db->numrows($inqmak) > 0)
			{
				echo '	<tr>
							<td>'.$lang['maker'].'</td>
							<td>
								<select name="makid" class="sw250">
									<option value="0">&#8212;</option>';
				while ($items = $db->fetchrow($inqmak))
				{
					echo '				<option value="'.$items['makid'].'">'.$items['makname'].'</option>';
				}
				echo '			</select>
							</td>
						</tr>';
			}
			echo '		<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['price'].'</th>
						</tr>
						<tr>
							<td>'.$lang['price'].'</td>
							<td><input name="price" size="25" placeholder="0.0000" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['old_price'].'</td>
							<td><input name="priceold" size="25" placeholder="0.0000" type="text" /></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['order_detail'].'</th>
						</tr>
						<tr>
							<td>'.$lang['catalog_data'].'</td>
							<td><input name="creation" id="creation" size="25" type="text" />';
								Calendar('ccal','creation');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['tax'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['taxes']);
			if (is_array($in))
			{
				echo '			<select name="taxe" class="sw165">
									<option value="0">&#8212;</option>';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'">'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['articul'].'</td>
							<td><input name="articul" size="25" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['amount_min'].'</td>
							<td><input name="amountmin" size="25" value="1" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['amount_max'].'</td>
							<td><input name="amountmax" size="25" value="0" type="text" />';
								$tm->outhint($lang['amount_zero']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['size'].'</td>
							<td>
								<input name="length" size="8" placeholder="0.00" type="text" />&nbsp;
								<input name="width" size="8" placeholder="0.00" type="text" />&nbsp;
								<input name="height" size="8" placeholder="0.00" type="text" />';
								$tm->outhint($lang['size_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['size_val'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['sizes']);
			if (is_array($in))
			{
				echo '			<select name="size" class="sw165">';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'"'.(($k == $conf[PERMISS]['size']) ? ' selected' : '').'>'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['weight'].'</td>
							<td><input name="weight" size="25" placeholder="0.00" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['size_val'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['weights']);
			if (is_array($in))
			{
				echo '			<select name="weights" class="sw165">';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'"'.(($k == $conf[PERMISS]['weight']) ? ' selected' : '').'>'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['storehouse'].'</td>
							<td>
								<select name="store" class="sw165">
									<option value="yes" selected>'.$lang['all_there'].' </option>
									<option value="no">'.$lang['all_there_no'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2" id="extra">
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['associat'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_cat'].'</td>
							<td class="al">
								<select name="cid" onchange="$.getproduct(this,\'index.php?dn=listcat&amp;ops='.$sess['hash'].'&amp;id=\');" class="sw250">
									<option value="0">'.$lang['cat_not'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div class="section tag">
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="product" id="product" size="5" multiple="multiple" class="blue pw100 app">
											</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addproduct();" value="&#9658;" title="'.$lang['add_product'].'" />
											<br /><br />
											<input class="side-button" type="button" onclick="$.delproduct();" value="&#9668;" title="'.$lang['delet_of_list'].'" />
										</td>
										<td>
											<select name="associat" id="associat" size="5" multiple="multiple" class="green pw100 app">
											</select>
											<div id="area-associat">
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['all_decs'].'</th>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['input_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);
				$WYSFORM = 'textshort';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textshort', 5, 70, '', 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'textmore';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textmore', 7, 70, '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['all_image_big'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;field[1]=image_thumb&amp;field[2]=image&amp;field[3]=video\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/product/\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;field[1]=image&amp;field[2]=image_thumb&amp;field[3]=video\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_align_image'].'</td>
							<td>
								<select name="image_align" class="sw165">
									<option value="left">'.$lang['all_left'].'</option>
									<option value="right">'.$lang['all_right'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['more_images'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td class="vm">
								<div id="image-area"></div>
								<div>
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;ims=2\');">'.$lang['filebrowser'].'</a>&nbsp;
									<a class="side-button" href="javascript:$.moreimages(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/product/\');">'.$lang['file_review'].'</a>
								</div>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['buy_info'].'</th>
						</tr>
						<tr>
							<td>'.$lang['stock'].'</td>
							<td>';
								$tm->textarea('buyinfo', 3, 50, '', true, false, 'ignorewysywig');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="actinfo" class="sw165">
									<option value="yes" selected>'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if($conf[PERMISS]['tags'] == 'yes')
			{
				echo '	<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_tags'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="tagarea">
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="tagin" id="tagin" size="5" multiple class="blue pw100 app">';
				$tags = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag");
				while ($tag = $db->fetchrow($tags))
				{
						echo '					<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addtag();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.deltag();" value="&#9668;" />
										</td>
										<td>
											<select name="tagout" id="tagout" size="5" multiple class="green pw100 app">
											</select>
											<div id="area-tags">
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_video'].'</th>
						</tr>
						<tr>
							<td>'.$lang['local_file'].'</td>
							<td>
								<input name="video" id="video" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/video/\',\'&amp;field[1]=video&amp;field[2]=image_thumb&amp;field[3]=image\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['all_files'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="file-area"></div>
								<input class="side-button" onclick="javascript:$.addfileinput(\'total-form\',\'file-area\',\'/'.PERMISS.'/file/\')" value="'.$lang['down_add'].'" type="button" />
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['options'].'</th>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes" /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out,',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes" selected>'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['recommended'].'</td>
							<td>
								<select name="rec" class="sw165">
									<option value="0">'.$lang['all_no'].'</option>
									<option value="1">'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						</tbody>';


			echo '		<tr class="tfoot">
							<td colspan="2">';
			if(isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="save" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" id="imgid" value="0" />
								<input type="hidden" id="countid" value="0" />
								<input type="hidden" id="fileid" value="2" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
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
		 * Добавить товар, сохранение
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'save')
		{
			global $catid, $makid, $title, $subtitle, $public, $stpublic, $unpublic, $price, $priceold, $articul, $taxe, $creation, $cpu, $customs, $customs2, $descript, $keywords, $textshort,
					$textmore, $image_align, $image_thumb, $image, $image_alt, $video, $buyinfo, $acc, $act, $actinfo, $files,
					$images, $opt, $group, $act, $tagword, $weight, $weights, $size, $length, $width,
					$height, $amountmin, $amountmax, $rec, $store, $associats, $files;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['add_product']
				);

			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);

			if (preparse($title, THIS_EMPTY) == 1 OR preparse($textshort, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['add_product'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query("SELECT title, cpu FROM ".$basepref."_".PERMISS." WHERE title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['add_product'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (is_array($files) AND ! empty($files))
			{
				$f = 1;
				foreach ($files as $k => $v)
				{
					if (isset($v['path']) AND ! empty($v['path']) AND isset($v['title']) AND ! empty($v['title']))
					{
						$file[$f] = array
									(
										'path'  => $v['path'],
										'title' => str_replace(array("'", '"'), '', $v['title']),
									);
						$f ++;
					}
				}
				$files = Json::encode($file);
			}

			if (is_array($images) AND ! empty($images))
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
										'title' => str_replace(array("'", '"'), '', $v['image_title']),
									);
						$c ++;
					}
				}
				$images = Json::encode($img);
			}

			if ( ! empty($articul))
			{
				if (preg_match('/^[\p{L}\p{Nd}\_-]+$/u', $articul))
				{
					$inqure = $db->query("SELECT id FROM ".$basepref."_".PERMISS." WHERE articul = '".$db->escape($articul)."'");
					if ($db->numrows($inqure) > 0) {
						$articul = null;
					}
				}
				else
				{
					$articul = null;
				}
			}

			$associat = null;
			if (is_array($associats))
			{
				$ins = array();
				foreach ($associats as $k => $v)
				{
					$v = intval($v);
					$ins[$v] = $v;
				}

				if (sizeof($ins) > 0)
				{
					$in = implode(',', $ins);
					$inqure = $db->query("SELECT id FROM ".$basepref."_".PERMISS." WHERE id IN (".$db->escape($in).")");
					if ($db->numrows($inqure) > 0)
					{
						$ass = array();
						while ($item = $db->fetchrow($inqure)) {
							$ass[$item['id']] = $item['id'];
						}
						if (sizeof($ass) > 0) {
							$associat = Json::encode($ass);
						}
					}
				}
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$tags = ($tagword) ? implode(',', $tagword) : '';
			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;
			$creation = (empty($creation)) ? 0 : ReDate($creation);
			$catid = preparse($catid, THIS_INT);
			$makid = preparse($makid, THIS_INT);
			$rec = preparse($rec, THIS_INT);
			$rec = ($rec == 1) ? 1 : 0;
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
			$image_thumb =  preparse($image_thumb, THIS_TRIM, 0, 255);
			$image_align = ($image_align == 'left') ? 'left' : 'right';
			$video =  preparse($video, THIS_TRIM, 0, 255);
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$actinfo = ($actinfo == 'yes') ? 'yes' : 'no';
			$store = ($store == 'yes') ? 'yes' : 'no';
			$in = Json::decode($conf[PERMISS]['weights']);
			$weights = (is_array($in) AND isset($in[$weights])) ? $weights : $conf[PERMISS]['weight'];
			$in = Json::decode($conf[PERMISS]['sizes']);
			$size = (is_array($in) AND isset($in[$size])) ? $size : $conf[PERMISS]['size'];
			$in = Json::decode($conf[PERMISS]['taxes']);
			$tax = (ceil($price) > 0 AND is_array($in) AND isset($in[$taxe])) ? $taxe : 0;
			$price = (ceil($price) > 0) ? formats($price, 4, '.', '') : '0.0000';
			$priceold = (ceil($price) > 0) ? formats((double)$priceold, 4, '.', '') : '0.0000';
			$length = (ceil($length) > 0) ? formats($length, 2, '.', '') : '0.00';
			$width = (ceil($width) > 0) ? formats($width, 2, '.', '') : '0.00';
			$height = (ceil($height) > 0) ? formats($height, 2, '.', '') : '0.00';
			$weight = (ceil($weight) > 0) ? formats($width, 2, '.', '') : '0.00';
			$amountmin = (intval($amountmin) > 0) ? intval($amountmin) : 1;
			$amountmax = (intval($amountmax) > 0) ? intval($amountmax) : 0;

			$descript = str_replace(array("'", '"'), '', $descript);
			$keywords = str_replace(array("'", '"'), '', $keywords);
			$image_alt = str_replace(array("'", '"'), '', $image_alt);

			$inq = $db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS." VALUES (
						 NULL,
						 '".$catid."',
						 '".$makid."',
						 '".$public."',
						 '".$stpublic."',
						 '".$unpublic."',
						 '".$db->escape($price)."',
						 '".$db->escape($priceold)."',
						 '".$db->escape($articul)."',
						 '".$db->escape($creation)."',
						 '".$db->escape($tax)."',
						 '".$db->escape($amountmin)."',
						 '".$db->escape($amountmax)."',
						 '".$db->escape($cpu)."',
						 '".$db->escape(preparse_sp($customs))."',
						 '".$db->escape(preparse_sp($descript))."',
						 '".$db->escape(preparse_sp($keywords))."',
						 '".$db->escape(preparse_sp($title))."',
						 '".$db->escape(preparse_sp($subtitle))."',
						 '".$db->escape($textshort)."',
						 '".$db->escape($textmore)."',
						 '".$db->escape($buyinfo)."',
						 '".$actinfo."',
						 '".$db->escape($weight)."',
						 '".$db->escape($weights)."',
						 '".$db->escape($length)."',
						 '".$db->escape($width)."',
						 '".$db->escape($height)."',
						 '".$db->escape($size)."',
						 '".$db->escape($image)."',
						 '".$db->escape($image_thumb)."',
						 '".$db->escape($image_align)."',
						 '".$db->escape(preparse_sp($image_alt))."',
						 '".$db->escape($video)."',
						 '".$act."',
						 '".$acc."',
						 '".$db->escape($group)."',
						 '0',
						 '".$db->escape($tags)."',
						 '".$db->escape($images)."',
						 '".$db->escape($files)."',
						 '',
						 '".$db->escape($associat)."',
						 '0',
						 '".$db->escape($rec)."',
						 '".$store."',
						 '0',
						 '0',
						 '0'
						 )"
					);

			$id = $db->insertid();

			if (intval($id) > 0 AND is_array($opt) AND sizeof($opt) > 0)
			{
				$item = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
				if ( ! empty($item['options']))
				{
					$so = Json::decode($item['options']);
					if (is_array($so))
					{
						$op = $type = $options = array();
						$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option");
						while ($item = $db->fetchrow($inq))
						{
							if (isset($so[$item['oid']])) {
								$op[$item['oid']] = array();
								$type[$item['oid']] = $item['type'];
							}
						}
						$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option_value");
						while ($item = $db->fetchrow($inq))
						{
							if (isset($so[$item['oid']])) {
								$op[$item['oid']][$item['vid']] = $item['vid'];
							}
						}
						foreach ($opt as $k => $v)
						{
							if (isset($op[$k]))
							{
								if (is_array($v))
								{
									foreach ($v as $kv => $vv)
									{
										if (isset($op[$k][$vv]))
										{
											$db->query
												(
													"INSERT INTO ".$basepref."_".PERMISS."_product_option VALUES (
													 NULL,
													 '".intval($id)."',
													 '".intval($k)."',
													 '".intval($vv)."'
													 )"
												);
										}
									}
								}
								else
								{
									if (isset($op[$k][$v]) OR isset($type[$k]) AND ($type[$k] == 'text' OR $type[$k] == 'textarea'))
									{
										if ($type[$k] == 'text' OR $type[$k] == 'textarea') {
											$options[$k] = htmlspecialchars($v, ENT_QUOTES, $conf['langcharset']);
										} else {
											$db->query
												(
													"INSERT INTO ".$basepref."_".PERMISS."_product_option VALUES (
													 NULL,
													 '".intval($id)."',
													 '".intval($k)."',
													 '".intval($v)."'
													 )"
												);
										}
									}
								}
							}
						}
					}

					$options = array_diff($options, array(''));
					$options = (is_array($options) AND ! empty($options)) ? $db->escape(Json::encode($options)) : NULL;

					$db->query("UPDATE ".$basepref."_".PERMISS." SET options = '".$options."' WHERE id = '".$id."'");
				}
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать товар
		 ------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $id, $catid, $selective, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['edit_product']
				);

			$tm->header();

			$p = preparse($p, THIS_INT);
			$cat = preparse($cat, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$s = preparse($s, THIS_TRIM, 1, 7);
			$l = preparse($l, THIS_TRIM, 1, 4);
			$fid = preparse($fid, THIS_INT);
			$id = preparse($id, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$public = CalendarFormat($item['public']);
			$stpublic = ($item['stpublic'] == 0) ? '' : CalendarFormat($item['stpublic']);
			$unpublic = ($item['unpublic'] == 0) ? '' : CalendarFormat($item['unpublic']);

			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$inqmak = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_maker ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script src="'.ADMPATH.'/js/jquery.autocomplete.js"></script>
					<script>
					var all_name     = "'.$lang['all_name'].'";
					var all_cpu      = "'.$lang['all_cpu'].'";
					var all_popul    = "'.$lang['all_popul'].'";
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
					var all_file     = "'.$lang['all_file'].'";
					var all_path     = "'.$lang['all_path'].'";
					var all_name     = "'.$lang['all_name'].'";
					var page         = "product";
					var ops          = "'.$sess['hash'].'";
					var filebrowser = "'.$lang['filebrowser'].'";
					$(function() {
						$(".imgcount").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
							$.loadcategory("index.php?dn=fields&ops='.$sess['hash'].'&catid='.$item['catid'].'&id='.$item['id'].'","extra");
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
						<caption>'.$lang['all_edit'].': '.$item['title'].'</caption>
						<tr>
							<th class="ar gray">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> &lt;h1&gt; '.$lang['all_name'].'</td>
						<td><input type="text" name="title" id="title" size="70" value="'.preparse_un($item['title']).'" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input name="cpu" type="text" id="cpu" size="70" value="'.$item['cpu'].'" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>&lt;title&gt; '.$lang['all_title'].'</td>
							<td><input type="text" name="customs" size="70" value="'.preparse_un($item['customs']).'" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input name="descript" type="text" size="70" value="'.$item['descript'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input name="keywords" type="text" size="70" value="'.$item['keywords'].'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" size="25" value="'.$public.'" />';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic" size="25" value="'.$stpublic.'" />';
								Calendar('stcal', 'stpublic');
			echo '			</td>
						</tr>
						<tr>
						<td>'.$lang['all_unpublic'].'</td>
						<td><input type="text" name="unpublic" id="unpublic" size="25" value="'.$unpublic.'" />';
						Calendar('uncal','unpublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" onchange="$.changecategory(this,\'index.php?dn=fields&amp;ops='.$sess['hash'].'&amp;id='.$id.'&amp;catid=\',\'extra\');" class="sw250">
									<option value="0"> &#8212; '.$lang['cat_not'].' &#8212; </option>';
				$catcache = array();
				$catid = $item['catid'];
				while ($items = $db->fetchrow($inqcat))
				{
					$catcache[$items['parentid']][$items['catid']] = $items;
				}
				this_selectcat(0);
				echo				$selective.'
								</select>
							</td>
						</tr>';
			}
			if ($db->numrows($inqmak) > 0)
			{
				echo '	<tr>
							<td>'.$lang['maker'].'</td>
							<td>
								<select name="makid" class="sw250">
									<option value="0"> &#8212; </option>';
				while ($items = $db->fetchrow($inqmak))
				{
					echo '				<option value="'.$items['makid'].'"'.(($item['makid'] == $items['makid']) ? ' selected' : '').'>'.$items['makname'].'</option>';
				}
				echo '			</select>
							</td>
						</tr>';
			}
			echo '		<tr>
							<th></th><th class="site">&nbsp;'.$lang['price'].'</th>
						</tr>
						<tr>
							<td>'.$lang['price'].'</td>
							<td><input name="price" type="text" size="25" value="'.$item['price'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['old_price'].'</td>
							<td><input name="priceold" type="text" size="25" value="'.$item['priceold'].'" /></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['order_detail'].'</th>
						</tr>
						<tr>
							<td>'.$lang['catalog_data'].'</td>
							<td>
								<input name="creation" id="creation" type="text" size="25" value="'.(($item['creation'] == 0) ? '' : CalendarFormat($item['creation'])).'" />';
								Calendar('ccal', 'creation');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['tax'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['taxes']);
			if (is_array($in))
			{
				echo '			<select name="taxe" class="sw165">
									<option value="0"> &#8212; </option>';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'"'.(($item['tax'] == $k) ? ' selected' : '').'>'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['articul'].'</td>
							<td><input name="articul" type="text" size="25" value="'.$item['articul'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['amount_min'].'</td>
							<td><input name="amountmin" type="text" size="25" value="'.$item['amountmin'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['amount_max'].'</td>
							<td><input name="amountmax" type="text" size="25" value="'.$item['amountmax'].'" />';
								$tm->outhint($lang['amount_zero']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['size'].'</td>
							<td>
								<input name="length" size="8" type="text" value="'.$item['length'].'" placeholder="0.00" />&nbsp;
								<input name="width" size="8" type="text" value="'.$item['width'].'" placeholder="0.00" />&nbsp;
								<input name="height" size="8" type="text" value="'.$item['height'].'" placeholder="0.00" />';
								$tm->outhint($lang['size_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['size_val'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['sizes']);
			if (is_array($in)) {
				echo '			<select name="size" class="sw165">';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'"'.(($k == $item['size']) ? ' selected' : '').'>'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['weight'].'</td>
							<td><input name="weight" size="25" type="text" value="'.$item['weight'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['size_val'].'</td>
							<td>';
			$in = Json::decode($conf[PERMISS]['weights']);
			if (is_array($in)) {
				echo '			<select name="weights" class="sw165">';
				foreach ($in as $k => $v) {
					echo '			<option value="'.$k.'"'.(($k == $item['weights']) ? ' selected' : '').'>'.$v['title'].'</option>';
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['storehouse'].'</td>
							<td>
								<select name="store" class="sw165">
									<option value="yes"'.(($item['store'] == 'yes') ? ' selected' : '').'>'.$lang['all_there'].' </option>
									<option value="no"'.(($item['store'] == 'no')  ? ' selected' : '').'>'.$lang['all_there_no'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2" id="extra">
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['associat'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_cat'].'</td>
							<td class="al">
								<select name="cid" onchange="$.getproduct(this,\'index.php?dn=listcat&amp;subid='.$item['id'].'&amp;ops='.$sess['hash'].'&amp;id=\');" class="sw250">
									<option value="0">'.$lang['cat_not'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div class="section tag">
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="product" id="product" size="5" multiple="multiple" class="blue pw100 app">';
			$hidden = $ass = '';
			$sf = array();
			if ( ! empty($item['associat']))
			{
				$sf = Json::decode($item['associat']);
				if (sizeof($sf) > 0)
				{
					$in = implode(',', $sf);
					$sinq = $db->query("SELECT id, title FROM ".$basepref."_".PERMISS." WHERE id IN (".$in.") AND id <> '".$item['id']."'");
					while ($sitem = $db->fetchrow($sinq))
					{
						$ass .= '				<option value="'.$sitem['id'].'">'.$sitem['title'].'</option>';
						$hidden .= '			<input type="hidden" name="associats[]" value="'.$sitem['id'].'" />';
					}
				}
			}
			if ($catid > 0)
			{
				$sinq = $db->query("SELECT id, title FROM ".$basepref."_".PERMISS." WHERE catid = '".$catid."' AND id <> '".$item['id']."'");
				while ($sitem = $db->fetchrow($sinq))
				{
					if ( ! isset($s[$sitem['id']])) {
						echo '					<option value="'.$sitem['id'].'">'.$sitem['title'].'</option>';
					}
				}
			}
			echo '							</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addproduct();" value="&#9658;" title="'.$lang['add_product'].'" />
											<br /><br />
											<input class="side-button" type="button" onclick="$.delproduct();" value="&#9668;" title="'.$lang['delet_of_list'].'" />
										</td>
										<td>
											<select name="associat" id="associat" size="5" multiple="multiple" class="green pw100 app">
												'.$ass.'
											</select>
											<div id="area-associat">
												'.$hidden.'
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_decs'].'</th>
						</tr>
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
				$tm->textarea('textshort', 5, 70, $item['textshort'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
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
				$tm->textarea('textmore', 7, 70, $item['textmore'], 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_image_big'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" value="'.$item['image_thumb'].'" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;field[1]=image_thumb&amp;field[2]=image&amp;field[3]=video\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/product/\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text" value="'.$item['image'].'" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;field[1]=image&amp;field[2]=image_thumb&amp;field[3]=video\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" value="'.$item['image_alt'].'" /></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_align_image'].'</td>
							<td>
								<select name="image_align" class="sw165">
									<option value="left"'.(($item['image_align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
									<option value="right"'.(($item['image_align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['more_images'].'</th>
						</tr>';
			$img = Json::decode($item['images']);
			$class = (is_array($img) AND sizeof($img) > 0) ? ' class="image-area"' : '';
			echo '		<tr>
							<td>'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td class="vm">
								<div id="image-area"'.$class.'>';
			$ic = 0;
			if (is_array($img))
			{
				foreach ($img as $v)
				{
					$ic ++;
					echo '			<div class="section tag" id="imginput'.$ic.'" style="display: block;">
										<table class="work">
											<tr>
												<td>';
					if ( ! empty($v['image'])) {
						echo '						<img class="sw30" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_thumb'].'" />';
					} else {
						echo '						<img class="sw50" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_big'].'" />';
					}
					echo '							<input type="hidden" name="images['.$ic.'][image_thumb]" value="'.$v['thumb'].'" />';
					if ( ! empty($v['image'])) {
						echo '						&nbsp;&nbsp;<img class="sw50" src="'.WORKURL.'/'.$v['image'].'" alt="'.$lang['all_image'].'" />
													<input name="images['.$ic.'][image]" type="hidden" value="'.$v['image'].'" />';
					}
					echo '						</td>
												<td class="vm">
													<a class="but fr" href="javascript:$.filebrowserimsremove(\''.$ic.'\');" title="'.$lang['all_delet'].'">x</a>
													<p><input type="text" name="images['.$ic.'][image_title]" size="25" value="'.$v['title'].'" title="'.$lang['all_name'].'" /></p>
												</td>
											</tr>
										</table>
									</div>';
				}
			}
			echo '				</div>
								<div>
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/product/\',\'&amp;ims=2\');">'.$lang['filebrowser'].'</a>&nbsp;
									<a class="side-button" href="javascript:$.moreimages(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/product/\');">'.$lang['file_review'].'</a>
								</div>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['buy_info'].'</th>
						</tr>
						<tr>
							<td>'.$lang['stock'].'</td>
							<td>';
								$tm->textarea('buyinfo', 3, 50, $item['buyinfo'], true, false, 'ignorewysywig');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="actinfo" class="sw165">
									<option value="yes"'.(($item['actinfo'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['actinfo'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if($conf[PERMISS]['tags'] == 'yes')
			{
				echo '	<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_tags'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="tagarea">
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="tagin" id="tagin" size="5" multiple class="blue pw100 app">';
				$tagword = $tagshow = NULL;
				if ( ! empty($item['tags']))
				{
					$tag_in = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag WHERE tagid IN (".$item['tags'].")");
					while ($tag = $db->fetchrow($tag_in))
					{
						$tagshow.= '				<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
						$tagword.= '				<input type="hidden" name="tagword[]" value="'.$tag['tagid'].'" />';
					}
				}
				$sql = ( ! empty($item['tags'])) ? ' WHERE tagid NOT IN ('.$item['tags'].')' : '';
				$tag_not = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag".$sql);
				while ($tag = $db->fetchrow($tag_not))
				{
						echo '					<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addtag();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.deltag();" value="&#9668;" />
										</td>
										<td>
											<select name="tagout" id="tagout" size="5" multiple class="green pw100 app">
												'.$tagshow.'
											</select>
											<div id="area-tags">
												'.$tagword.'
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_video'].'</th>
						</tr>
						<tr>
							<td>'.$lang['local_file'].'</td>
							<td>
								<input name="video" id="video" size="70" type="text" value="'.$item['video'].'" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/video/\',\'&amp;field[1]=video&amp;field[2]=image_thumb&amp;field[3]=image\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_files'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="file-area">';
			$fp = Json::decode($item['files']);
			$f = 1;
			if (is_array($fp) AND sizeof($fp) > 0)
			{
				foreach ($fp as $k => $v)
				{
					echo '			<div class="section tag" id="file-'.$f.'">
										<table class="work">
											<tr>
												<td>'.$lang['all_path'].'</td>
												<td>
													<input name="files['.$f.'][path]" id="files'.$f.'" size="50" type="text" value="'.$v['path'].'" />
													<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/file/\',\'&amp;field[1]=files'.$f.'\')" value="'.$lang['filebrowser'].'" type="button" />';
					echo '							<a class="side-button fr" href="javascript:$.removetaginput(\'total-form\',\'file-area\',\'file-'.$f.'\');">&#215;</a>';
					echo '						</td>
											<tr>
												<td>'.$lang['all_name'].'</td>
												<td><input name="files['.$f.'][title]" size="50" type="text" value="'.$v['title'].'" /></td>
											</tr>
										</table>
									</div>';
					$f ++;
				}
			}
			echo '				</div>
								<div><input class="side-button" onclick="javascript:$.addfileinput(\'total-form\',\'file-area\',\'/'.PERMISS.'/file/\')" value="'.$lang['down_add'].'" type="button" /></div>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['stat'].'</th>
						</tr>
						<tr>
							<td>'.$lang['buy_amount'].'</td>
							<td><input name="buyhits" type="text" size="25" value="'.$item['buyhits'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_hits'].'</td>
							<td><input name="hits" type="text" size="25" value="'.$item['hits'].'" /></td>
						</tr>
						<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_set'].'</th>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
			echo '		<tr>
							<td>'.$lang['all_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all"'.(($item['acc'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['acc'] == 'user' AND empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
			echo '					'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['acc'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
			echo '				</select>
								<div class="group" id="group"'.(($item['acc'] == 'all' OR $item['acc'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').' /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out,',');
				}
			echo '				</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['recommended'].'</td>
							<td>
								<select name="rec" class="sw165">
									<option value="0"'.(($item['rec'] == 0) ? ' selected' : '').'>'.$lang['all_no'].'</option>
									<option value="1"'.(($item['rec'] == 1) ? ' selected' : '').'>'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						</tbody>';

			echo '		<tr class="tfoot">
							<td colspan="2">';
			if ($conf['cpu'] == 'no')
			{
				echo '			<input type="hidden" name="cpu" />';
			}
			if(isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="editsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="cat" value="'.$cat.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="s" value="'.$s.'" />
								<input type="hidden" name="l" value="'.$l.'" />
								<input type="hidden" name="id" value="'.$item['id'].'" />
								<input type="hidden" name="extarticul" value="'.$item['articul'].'" />
								<input type="hidden" id="imgid" value="'.$ic.'" />
								<input type="hidden" id="fileid" value="'.$f.'" />';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'" />';
			}
			echo '				<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
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
		 * Редактировать товар, сохранение
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $id, $catid, $makid, $title, $subtitle, $public, $stpublic, $unpublic, $price, $priceold, $extarticul, $articul, $taxe, $creation, $cpu, $customs, $customs2, $descript, $keywords,
					$textshort, $textmore, $image_align, $image_thumb, $image, $image_alt, $video, $buyinfo, $acc, $act, $actinfo, $files, $hits, $buyhits,
					$images, $opt, $group, $tagword, $weight, $weights, $size, $length, $width,
					$height, $amountmin, $amountmax, $rec, $store, $associats, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['edit_product']
				);

			$id = preparse($id, THIS_INT);
			$fid = preparse($fid, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($textshort, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($modname[PERMISS], $lang['edit_product'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query
							(
								"SELECT title, cpu FROM ".$basepref."_".PERMISS."
								 WHERE (title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."')
								 AND id <> '".$id."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['all_edit'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (is_array($files) AND ! empty($files))
			{
				$f = 1;
				foreach ($files as $k => $v)
				{
					if (isset($v['path']) AND ! empty($v['path']) AND isset($v['title']) AND ! empty($v['title']))
					{
						$file[$f] = array
									(
										'path'  => $v['path'],
										'title' => str_replace(array("'", '"'), '', $v['title']),
									);
						$f ++;
					}
				}
				$files = Json::encode($file);
			}

			if (is_array($images) AND ! empty($images))
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
										'title' => str_replace(array("'", '"'), '', $v['image_title']),
									);
						$c ++;
					}
				}
				$images = Json::encode($img);
			}

			if ( ! empty($articul))
			{
				if (preg_match('/^[\p{L}\p{Nd}\_-]+$/u', $articul))
				{
					$inqure = $db->query("SELECT id FROM ".$basepref."_".PERMISS." WHERE articul = '".$db->escape($articul)."' AND id <> '".$id."'");
					if ($db->numrows($inqure) > 0) {
						$articul = null;
					}
				}
				else
				{
					$articul = null;
				}
			}

			$associat = null;
			if (is_array($associats))
			{
				$ins = array();
				foreach ($associats as $k => $v)
				{
					$v = intval($v);
					$ins[$v] = $v;
				}

				if (sizeof($ins) > 0)
				{
					$in = implode(',', $ins);
					$inqure = $db->query("SELECT id FROM ".$basepref."_".PERMISS." WHERE id IN (".$db->escape($in).")");
					if ($db->numrows($inqure) > 0)
					{
						$ass = array();
						while ($item = $db->fetchrow($inqure)) {
							$ass[$item['id']] = $item['id'];
						}
						if (sizeof($ass) > 0) {
							$associat = Json::encode($ass);
						}
					}
				}
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;
			$creation = (empty($creation)) ? 0 : ReDate($creation);
			$tags = ( ! empty($tagword)) ? implode(',', $tagword) : '';
			$catid = preparse($catid, THIS_INT);
			$makid = preparse($makid, THIS_INT);
			$rec = preparse($rec, THIS_INT);
			$rec = ($rec == 1) ? 1 : 0;
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
			$image_thumb =  preparse($image_thumb, THIS_TRIM, 0, 255);
			$image_align = ($image_align == 'left') ? 'left' : 'right';
			$video =  preparse($video, THIS_TRIM, 0, 255);
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$actinfo = ($actinfo == 'yes') ? 'yes' : 'no';
			$store = ($store == 'yes') ? 'yes' : 'no';
			$hits = ($hits) ? preparse($hits, THIS_INT) : 0;
			$buyhits = ($buyhits) ? preparse($buyhits, THIS_INT) : 0;

			$in = Json::decode($conf[PERMISS]['weights']);
			$weights = (is_array($in) AND isset($in[$weights])) ? $weights : $conf[PERMISS]['weight'];
			$in = Json::decode($conf[PERMISS]['sizes']);
			$size = (is_array($in) AND isset($in[$size])) ? $size : $conf[PERMISS]['size'];

			$in = Json::decode($conf[PERMISS]['taxes']);
			$tax = (ceil($price) > 0 AND is_array($in) AND isset($in[$taxe])) ? $taxe : 0;
			$price = (ceil($price) > 0) ? formats($price, 4, '.', '') : '0.0000';
			$priceold = (ceil($price) > 0) ? formats((double)$priceold, 4, '.', '') : '0.0000';
			$length = (ceil($length) > 0) ? formats($length, 2, '.', '') : '0.00';
			$width = (ceil($width) > 0) ? formats($width, 2, '.', '') : '0.00';
			$height = (ceil($height) > 0) ? formats($height, 2, '.', '') : '0.00';
			$weight = (ceil($weight) > 0) ? formats($weight, 2, '.', '') : '0.00';
			$amountmin = (intval($amountmin) > 0) ? intval($amountmin) : 1;
			$amountmax = (intval($amountmax) > 0) ? intval($amountmax) : 0;

			$descript = str_replace(array("'", '"'), '', $descript);
			$keywords = str_replace(array("'", '"'), '', $keywords);
			$image_alt = str_replace(array("'", '"'), '', $image_alt);

			$inq = $db->query
					(
						"UPDATE ".$basepref."_".PERMISS." SET
						 catid       = '".$catid."',
						 makid       = '".$makid."',
						 public      = '".$public."',
						 stpublic    = '".$stpublic."',
						 unpublic    = '".$unpublic."',
						 price       = '".$db->escape($price)."',
						 priceold    = '".$db->escape($priceold)."',
						 articul     = '".$db->escape($articul)."',
						 creation    = '".$db->escape($creation)."',
						 tax         = '".$db->escape($tax)."',
						 amountmin   = '".$db->escape($amountmin)."',
						 amountmax   = '".$db->escape($amountmax)."',
						 cpu         = '".$db->escape($cpu)."',
						 customs     = '".$db->escape(preparse_sp($customs))."',
						 descript    = '".$db->escape(preparse_sp($descript))."',
						 keywords    = '".$db->escape(preparse_sp($keywords))."',
						 title       = '".$db->escape(preparse_sp($title))."',
						 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
						 textshort   = '".$db->escape($textshort)."',
						 textmore    = '".$db->escape($textmore)."',
						 buyinfo     = '".$db->escape($buyinfo)."',
						 actinfo     = '".$actinfo."',
						 weight      = '".$db->escape($weight)."',
						 weights     = '".$db->escape($weights)."',
						 length      = '".$db->escape($length)."',
						 width       = '".$db->escape($width)."',
						 height      = '".$db->escape($height)."',
						 size        = '".$db->escape($size)."',
						 image       = '".$db->escape($image)."',
						 image_thumb = '".$db->escape($image_thumb)."',
						 image_align = '".$db->escape($image_align)."',
						 image_alt   = '".$db->escape(preparse_sp($image_alt))."',
						 video       = '".$db->escape($video)."',
						 act         = '".$act."',
						 acc         = '".$acc."',
						 groups      = '".$db->escape($group)."',
						 tags        = '".$db->escape($tags)."',
						 images      = '".$db->escape($images)."',
						 files       = '".$db->escape($files)."',
						 associat    = '".$db->escape($associat)."',
						 buyhits     = '".$buyhits."',
						 rec         = '".$db->escape($rec)."',
						 store       = '".$store."',
						 hits        = '".$hits."'
						 WHERE id = '".$id."'"
					);

			$old = array();
			if ($id AND is_array($opt) AND sizeof($opt) > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_product_option WHERE id = '".$id."'");
				while ($item = $db->fetchrow($inq))
				{
					$old[$item['oid']][$item['vid']] = $item['poid'];
				}
				$item = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
				if ( ! empty($item['options']))
				{
					$so = Json::decode($item['options']);
					if (is_array($so))
					{
						$op = $type = $options = array();
						$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option");
						while ($item = $db->fetchrow($inq))
						{
							if (isset($so[$item['oid']]))
							{
								$op[$item['oid']] = array();
								$type[$item['oid']] = $item['type'];
							}
						}

						$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option_value");
						while ($item = $db->fetchrow($inq))
						{
							if (isset($so[$item['oid']]))
							{
								$op[$item['oid']][$item['vid']] = $item['vid'];
							}
						}

						foreach ($opt as $k => $v)
						{
							if (isset($op[$k]))
							{
								if (is_array($v))
								{
									foreach ($v as $kv => $vv)
									{
										if (isset($op[$k][$vv]))
										{
											unset($old[$k][$vv]);
											if ($db->numrows($db->query("SELECT * FROM ".$basepref."_".PERMISS."_product_option WHERE id = '".intval($id)."' AND oid = '".intval($k)."' AND vid = '".intval($vv)."'")) == 0)
											{
												$db->query
													(
														"INSERT INTO ".$basepref."_".PERMISS."_product_option VALUES (
														 NULL,
														 '".intval($id)."',
														 '".intval($k)."',
														 '".intval($vv)."'
														 )"
													);
											}
										}
									}
								}
								else
								{
									if (isset($op[$k][$v]) OR isset($type[$k]) AND ($type[$k] == 'text' OR $type[$k] == 'textarea'))
									{
										if ($type[$k] == 'text' OR $type[$k] == 'textarea')
										{
											$options[$k] = htmlspecialchars($v, ENT_QUOTES, $conf['langcharset']);
										}
										else
										{
											unset($old[$k]);
											if ($db->numrows($db->query("SELECT * FROM ".$basepref."_".PERMISS."_product_option WHERE id = '".$id."' AND oid = '".$k."'")) == 0)
											{
												$db->query
													(
														"INSERT INTO ".$basepref."_".PERMISS."_product_option VALUES (
														 NULL,
														 '".intval($id)."',
														 '".intval($k)."',
														 '".intval($v)."'
														 )"
													);
											}
											else
											{
												$db->query
													(
														"UPDATE ".$basepref."_".PERMISS."_product_option SET
														 vid = '".intval($v)."'
														 WHERE id = '".intval($id)."'
														 AND oid = '".intval($k)."'"
													);
											}
										}
									}
								}
							}
						}
					}

					if (is_array($old) AND sizeof($old) > 0)
					{
						foreach ($old as $k => $v)
						{
							if (is_array($v) AND sizeof($v) > 0)
							{
								$in = implode(',', $v);
								$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE poid IN (".$db->escape($in).")");
							}
						}
					}

					$options = array_diff($options, array(''));
					$options = (is_array($options) AND ! empty($options)) ? $db->escape(Json::encode($options)) : NULL;

					$db->query("UPDATE ".$basepref."_".PERMISS." SET options = '".$options."' WHERE id = '".$id."'");
				}
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$counts = new Counts(PERMISS, 'id');

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Удалить товар
		 -----------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $p, $cat, $nu, $ok, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['del_product']
				);

			$id = preparse($id, THIS_INT);
			$fid = preparse($fid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE id IN (".$id.")");
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");

				$counts = new Counts(PERMISS, 'id');

				// Cache option
				$cache_opt = new DN\Cache\CacheOption;
				$cache_opt->cacheoption(PERMISS);

				$cache->cachesave(1);

				$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
				$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
				$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

				$yes = 'index.php?dn=del&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');
				$not = 'index.php?dn=list&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Дополнительные поля
		 -----------------------*/
		if ($_REQUEST['dn'] == 'fields')
		{
			global $catid, $id;

			$tar = 0;
			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
			if ( ! empty($item['options']) AND ! empty($catid))
			{
				$opt = Json::decode($item['options']);
				if (is_array($opt) AND sizeof($opt) > 0)
				{
					$in = implode(',', $opt);
					$fields = $product = $productopt = array();
					$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option_value ORDER BY posit ASC");
					while ($item = $db->fetchrow($inq))
					{
						$fields[$item['oid']][$item['vid']]	= $item;
					}
					if ($id > 0)
					{
						$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_product_option WHERE id = '".$id."'");
						while ($item = $db->fetchrow($inq))
						{
							$product[$item['oid']][$item['vid']] = $item['vid'];
						}
						$item = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
						$productopt = Json::decode($item['options']);
					}
					$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option WHERE oid IN (".$in.") ORDER BY posit ASC");
					if ($db->numrows($inq) > 0)
					{
						echo '	<tr>
									<th>&nbsp;</th><th class="site">&nbsp;'.$lang['multi_fields'].'</th>
								</tr>';
						$types = array
						(
							'select'   => 'input_select',
							'radio'    => 'input_radio',
							'checkbox' => 'input_checkbox',
							'text'     => 'input_textarea'
						);
						while ($item = $db->fetchrow($inq))
						{
							if (isset($fields[$item['oid']]) OR $item['type'] == 'text' OR $item['type'] == 'textarea')
							{
								echo '	<tr>
											<td>'.$item['title'].'</td>
											<td>';
								if ($item['type'] == 'radio')
								{
									if (is_array($fields[$item['oid']])) {
										foreach ($fields[$item['oid']] as $k => $v) {
											echo '<input name="opt['.$item['oid'].']" value="'.$k.'" type="radio"'.((isset($product[$item['oid']][$k]) AND $product[$item['oid']][$k] == $k) ? ' checked' : '').' /> '.$v['title'].' &nbsp; ';
										}
									}
								}
								if ($item['type'] == 'checkbox' OR $item['type'] == 'select')
								{
									if (is_array($fields[$item['oid']])) {
										foreach ($fields[$item['oid']] as $k => $v) {
											echo '<input name="opt['.$item['oid'].']['.$k.']" value="'.$k.'" type="checkbox"'.((isset($product[$item['oid']][$k]) AND $product[$item['oid']][$k] == $k) ? ' checked' : '').' /> &nbsp; '.$v['title'].'<br />';
										}
									}
								}
								if ($item['type'] == 'text')
								{
									echo '		<input name="opt['.$item['oid'].']" size="70" value="'.(($id > 0 AND isset($productopt[$item['oid']])) ? $productopt[$item['oid']] : '').'" />';
								}
								if ($item['type'] == 'textarea')
								{
									echo '		<textarea name="opt['.$item['oid'].']" cols="70" rows="4" style="width: 70%; min-width: 330px;">'.(($id > 0 AND isset($productopt[$item['oid']])) ? $productopt[$item['oid']] : '').'</textarea>';
								}
								echo '		</td>
										</tr>';
							}
						}
					}
				}
			}
		}

		/**
		 * Отзывы отдельного товара
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'reviewsedit')
		{
			global $nu, $p, $id, $ajax;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$total = $db->fetchrow($db->query("SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND pageid = '".$id."' AND active = '1' ORDER BY reid DESC"));
			if (($p - 1) * $nu > $total['total'])
			{
				$p = 1;
			}

			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("reviews WHERE file = '".PERMISS."' AND pageid='".$id."' AND active = '1' ORDER BY reid DESC", 'pageid', 'index', 'reviewsedit&amp;id='.$id.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages('index.php?dn=reviewsedit&amp;p='.$p.'&amp;id='.$id.'&amp;ops='.$sess['hash'].'&amp;ajax='.$ajax, $nu);

			$inq = $db->query("SELECT * FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND pageid='".$id."' AND active = '1' ORDER BY reid DESC LIMIT ".$sf.", ".$nu);
			$alltotal = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id='".$id."'"));

			echo '	<script>
					$(function()
					{
						$("#select, #selects").click(function() {
							$("#reviews-form input[type=checkbox]").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
					});
					</script>';
			if ($ajax)
			{
				echo '	<script>
						$(function()
						{
							$.ajaxSetup({cache:false,async:false});
							$(".sort a").colorbox({
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								"href"	: $(this).attr("href"),
								onComplete : function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 145) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#reviews-form");
										$.ajax({
											cache	: false,
											type	: "POST",
											data	: $elm.serialize() + "&ajax=1",
											url		: $.apanel + "/mod/'.PERMISS.'/index.php",
											error	: function(data) {  },
											success	: function(data) { $("#reviews-form").html(data).show(); }
										});
									},
									width	: "92%",
									height	: "90%",
									maxHeight	:  800,
									maxWidth	:  1200,
									fixed: true,
									onComplete: function () {
										var $h = $("#cboxLoadedContent").height();
										$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
									},
									"href"  : $.apanel + "/mod/'.PERMISS.'/index.php?dn=reviewsedit&p='.$p.'&id='.$id.'&ops='.$sess['hash'].'&ajax=1&t='.time().'"
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<form action="index.php" method="post" name="reviews-form" id="reviews-form">
					<table class="fb-work">
						<caption>'.$alltotal['title'].'&nbsp; &#8260; &nbsp;'.$lang['response'].'</caption>
						<tr>
							<td class="sort" colspan="3">'.$amount.'</td>
						</tr>
					</table>';
			echo '	<div id="fb-work-comm">
					<table class="fb-work">
						<tr>
							<th class="ac">'.$lang['author'].'</th>
							<th class="ac">'.$lang['response_one'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><input class="but" id="selects" value="x" type="button" title="'.$lang['all_delet'].'" /></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac vm">';
				if ($item['userid'] > 0) {
					echo '		<a href="user.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['uname'].'</a>';
				} else {
					echo '		'.$item['uname'];
				}
				echo '		</td>
							<td>';
								$tm->textarea('text['.$item['reid'].']', 5, 25, $item['message'], 1);
				echo '		</td>
							<td class="vt pw16">'.format_time($item['public'],1,1).'</td>
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['reid'].']" value="1" /></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="dn" value="reviewseditrep" />
								<input class="but submit" value="'.$lang['all_save'].'" type="'.(($ajax) ? 'button' : 'submit').'" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Отзывы отдельного товара (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'reviewseditrep')
		{
			global $id, $p, $nu, $text, $author, $dels, $ajax;

			$ajax = preparse($ajax, THIS_INT);
			$id   = preparse($id, THIS_INT);
			$nu   = preparse($nu, THIS_INT);
			$p    = preparse($p, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					$key = intval($key);
					if (isset($dels[$key]) AND $dels[$key] == 1)
					{
						$db->query("UPDATE ".$basepref."_".PERMISS." SET reviews = reviews - 1 WHERE id = '".$id."'");
						$db->query("DELETE FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND reid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$authors = preparse($author[$key], THIS_TRIM, 0, 255);
							$texts = preparse($text[$key], THIS_TRIM);

							if ($authors) {
								$db->query("UPDATE ".$basepref."_reviews SET uname = '".$db->escape($authors)."', message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							} else {
								$db->query("UPDATE ".$basepref."_reviews SET message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							}
						}
					}
				}
			}

			$count = $db->fetchrow($db->query("SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND pageid = '".$id."' AND active = '1'"));
			$db->query("UPDATE ".$basepref."_".PERMISS." SET reviews = '".$count['total']."' WHERE id = '".$id."'");

			if ($ajax == 0)
			{
				redirect('index.php?dn=reviewsedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Отзывы - Все товары
		 -----------------------*/
		if ($_REQUEST['dn'] == 'reviews')
		{
			global $nu, $p, $id, $ajax, $atime;

			$ajax  = preparse($ajax, THIS_INT);
			$id    = preparse($id, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$total = $db->fetchrow($db->query("SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND (public >= '".$atime."') AND active = '1'"));
			if (($p - 1) * $nu > $total['total'])
			{
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("reviews WHERE (public >= '".$atime."') AND active = '1' ORDER BY reid DESC", 'pageid', ADMPATH.'/mod/'.PERMISS.'/index', 'reviews&amp;atime='.$atime.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages(ADMPATH.'/mod/'.PERMISS.'/index.php?dn=reviews&amp;p='.$p.'&amp;atime='.$atime.'&amp;ops='.$sess['hash'].'&amp;ajax='.$ajax, $nu);

			$inq = $db->query
					(
						"SELECT a.*, b.title FROM ".$basepref."_reviews AS a
						 LEFT JOIN ".$basepref."_".PERMISS." AS b ON (a.pageid = b.id)
						 WHERE a.file = '".PERMISS."' AND (a.public >= '".$atime."') AND a.active = '1'
						 ORDER BY reid DESC LIMIT ".$sf.", ".$nu
					);

			echo '	<script>
					$(document).ready(function()
					{
						$("#select").click(function() {
							$("#reviews-form input[type=checkbox]").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
					});
					</script>';
			if ($ajax)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache: false, async: false});
							$(".sort a").colorbox({
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								"href"	: $(this).attr("href"),
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#reviews-form");
									$.ajax({
										cache	: false,
										type	: "POST",
										data	: $elm.serialize() + "&ajax=1",
										url		: $.apanel + "/mod/'.PERMISS.'/index.php",
										error	: function(data) {  },
										success : function(data) { $("#reviews-form").html(data).show(); }
									});
								},
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								},
								"href" : $.apanel + "/mod/'.PERMISS.'/index.php?dn=reviews&p='.$p.'&nu='.$nu.'&atime='.$atime.'&ops='.$sess['hash'].'&ajax=1&t='.time().'"
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<form id="reviews-form" action="index.php" method="post">
					<table class="fb-work">
						<caption>'.$modname[PERMISS].'&nbsp; &#8260; &nbsp;'.(($atime == 0) ? $lang['response'] : $lang['comment_last']).'</caption>
						<tr>
							<td class="sort" colspan="3">'.$amount.'</td>
						</tr>
					</table>';
			echo '	<div id="fb-work-comm">
					<table class="fb-work">
						<tr>
							<th class="ac">'.$lang['author'].'</th>
							<th>'.$lang['products'].'</th>
							<th>'.$lang['response'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><input class="but" id="select" value="x" type="button" title="'.$lang['all_delet'].'" /></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac pw10">';
				if ($item['userid'] > 0) {
					echo '		<a href="'.ADMPATH.'/mod/user/index.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['uname'].'</a>';
				} else {
					echo '		'.$item['uname'];
				}
				echo '		</td>
							<td class="vt pw20">'.$item['title'].'</td>
							<td>';
								$tm->textarea('text['.$item['reid'].']', 5, 25, $item['message'], 1);
				echo '		</td>
							<td class="vt pw16">'.format_time($item['public'], 1, 1).'</td>
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['reid'].']" value="1" /></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="atime" value="'.$atime.'" />
								<input type="hidden" name="dn" value="reviewsrep" />
								<input class="but submit" value="'.$lang['all_save'].'" type="'.(($ajax) ? 'button' : 'submit').'" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Отзывы - Все товары (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'reviewsrep')
		{
			global $id, $p, $nu, $text, $author, $dels, $ajax, $atime;

			$ajax  = preparse($ajax, THIS_INT);
			$nu    = preparse($nu, THIS_INT);
			$p     = preparse($p, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					$key = intval($key);
					if (isset($dels[$key]) AND $dels[$key] == 1)
					{
						$count = $db->fetchrow($db->query("SELECT pageid FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND reid = '".$key."' AND active = '1'"));
						$db->query("UPDATE ".$basepref."_".PERMISS." SET reviews = reviews - 1 WHERE id = '".$count['id']."'");
						$db->query("DELETE FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND reid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$authors = preparse($author[$key], THIS_TRIM, 0, 255);
							$texts = preparse($text[$key], THIS_TRIM);

							if ($authors) {
								$db->query("UPDATE ".$basepref."_reviews SET uname = '".$db->escape($authors)."', message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							} else {
								$db->query("UPDATE ".$basepref."_reviews SET message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							}
						}
					}
				}
			}

			if ($ajax == 0)
			{
				redirect('index.php?dn=reviews&amp;p='.$p.'&amp;nu='.$nu.'&amp;atime='.$atime.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Новые отзывы
		 ----------------*/
		if ($_REQUEST['dn'] == 'newreviews')
		{
			global $nu, $p, $id, $ajax, $atime;

			$ajax  = preparse($ajax, THIS_INT);
			$id    = preparse($id, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$total = $db->fetchrow($db->query("SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND (public >= '".$atime."') AND active = '0'"));
			if (($p - 1) * $nu > $total['total'])
			{
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("reviews WHERE file = '".PERMISS."' AND (public >= '".$atime."') AND active = '0' ORDER BY reid DESC", 'pageid', ADMPATH.'/mod/'.PERMISS.'/index', 'newreviews&amp;atime='.$atime.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages(ADMPATH.'/mod/'.PERMISS.'/index.php?dn=newreviews&amp;p='.$p.'&amp;atime='.$atime.'&amp;ops='.$sess['hash'].'&amp;ajax='.$ajax, $nu);

			$inq = $db->query
					(
						"SELECT a.*, b.title FROM ".$basepref."_reviews AS a
						 LEFT JOIN ".$basepref."_".PERMISS." AS b ON (a.pageid = b.id)
						 WHERE a.file = '".PERMISS."' AND (a.public >= '".$atime."') AND a.active = '0'
						 ORDER BY reid DESC LIMIT ".$sf.", ".$nu
					);

			echo '	<script>
					$(document).ready(function()
					{
						$("#act").click(function() {
							$("#reviews-form input.act").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
						$("#del").click(function() {
							$("#reviews-form input.del").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
					});
					</script>';
			if ($ajax)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache: false, async: false});
							$(".sort a").colorbox({
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								"href"	: $(this).attr("href"),
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#reviews-form");
									$.ajax({
										cache	: false,
										type	: "POST",
										data	: $elm.serialize() + "&ajax=1",
										url		: $.apanel + "/mod/'.PERMISS.'/index.php",
										error	: function(data) {  },
										success : function(data) { $("#reviews-form").html(data).show(); }
									});
								},
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								},
								"href" : $.apanel + "/mod/'.PERMISS.'/index.php?dn=newreviews&p='.$p.'&nu='.$nu.'&atime='.$atime.'&ops='.$sess['hash'].'&ajax=1&t='.time().'"
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<form id="reviews-form" action="index.php" method="post">
					<table class="fb-work">
						<caption>'.$modname[PERMISS].'&nbsp; &#8260; &nbsp;'.$lang['response_new'].'</caption>
						<tr>
							<td class="sort" colspan="4">'.$amount.'</td>
						</tr>
					</table>';
			echo '	<div id="fb-work-comm">
					<table class="fb-work">
						<tr>
							<th class="ac">'.$lang['author'].'</th>
							<th>'.$lang['products'].'</th>
							<th>'.$lang['response'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><a id="act" href="#">'.$lang['publish'].'</a></th>
							<th class="ac"><a id="del" href="#">'.$lang['all_delet'].'</a></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac pw10">';
				if ($item['userid'] > 0) {
					echo '		<a href="'.ADMPATH.'/mod/user/index.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['uname'].'</a>';
				} else {
					echo '		'.$item['uname'];
				}
				echo '		</td>
							<td class="vt pw20">'.$item['title'].'</td>
							<td class="vt pw45">';
								$tm->textarea('text['.$item['reid'].']', 5, 25, $item['message'], 1);
				echo '		</td>
							<td class="vt pw15">'.format_time($item['public'], 1, 1).'</td>
							<td class="ac pw5"><input class="act" type="checkbox" name="act['.$item['reid'].']" value="1" /></td>
							<td class="ac pw5"><input class="del" type="checkbox" name="del['.$item['reid'].']" value="1" /></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="4">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="atime" value="'.$atime.'" />
								<input type="hidden" name="dn" value="newreviewsrep" />
								<input class="but submit" value="'.$lang['all_save'].'" type="'.(($ajax) ? 'button' : 'submit').'" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Новые отзывы (сохранение)
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'newreviewsrep')
		{
			global $id, $p, $nu, $text, $author, $act, $del, $ajax, $atime;

			$p = preparse($p, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$ajax = preparse($ajax, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					$key = intval($key);
					$reid = $db->fetchrow($db->query("SELECT pageid FROM ".$basepref."_reviews WHERE file = '".PERMISS."' AND reid = '".$key."'"));
					if (isset($del[$key]) AND $del[$key] == 1)
					{
						$db->query("DELETE FROM ".$basepref."_reviews WHERE reid = '".$key."'");
					}
					elseif (isset($act[$key]) AND $act[$key] == 1)
					{
						$db->query("UPDATE ".$basepref."_".PERMISS." SET reviews = reviews + 1 WHERE id = '".$reid['pageid']."'");
						$db->query("UPDATE ".$basepref."_reviews SET active = '1' WHERE file = '".PERMISS."' AND reid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$authors = preparse($author[$key], THIS_TRIM, 0, 255);
							$texts = preparse($text[$key], THIS_TRIM);

							if ($authors) {
								$db->query("UPDATE ".$basepref."_reviews SET uname = '".$db->escape($authors)."', message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							} else {
								$db->query("UPDATE ".$basepref."_reviews SET message = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND reid = '".$key."'");
							}
						}
					}
				}
			}

			if ($ajax == 0)
			{
				redirect('index.php?dn=newreviews&amp;p='.$p.'&amp;nu='.$nu.'&amp;atime='.$atime.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Автозаполнение, при добавлении тегов
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'autocomplete')
		{
			$q = '';
			if (isset($_REQUEST['q'])) {
				$q = preparse($_REQUEST['q'], THIS_TRIM, 0, 255);
			}
			if ( ! $q) {
				return;
			}
			$inq = $db->query("SELECT tagcpu, tagword FROM ".$basepref."_".PERMISS."_tag WHERE tagword LIKE '%".$db->escape($q)."%' ORDER BY tagword");
			while ($item = $db->fetchrow($inq)) {
				echo $item['tagword']."|".$item['tagcpu']."\n";
			}
			exit();
		}

		/**
		 * Список категорий
		 --------------------*/
		if ($_REQUEST['dn'] == 'listcat')
		{
			global $id, $subid;

			$id = preparse($id, THIS_INT);
			$subid = preparse($subid, THIS_INT);
			$inq = ($subid > 0) ? $db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE catid = '".$id."' AND id <> '".$subid."'") : $db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE catid = '".$id."'");
			while ($item = $db->fetchrow($inq)) {
				echo '<option value="'.$item['id'].'">'.$item['title'].'</option>';
			}
			exit();
		}

		/**
		 * Быстрое редактирование названия товара
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxedittitle')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			echo '	<form action="index.php" method="post" id="postprod" name="postprod" onsubmit="return $.posteditor(this,\'te'.$item['id'].'\',\'index.php?dn=ajaxsavetitle&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'\')">
					<div style="width: 400px;">
						<input type="text" name="title" size="60" value="'.preparse_un($item['title']).'" />&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavetitle" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" &#187; " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое редактирование названия товара (сохранение)
		 -------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavetitle')
		{
			global $id, $title;

			$id = preparse($id, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);
			if ($id > 0 AND $title) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET title = '".$db->escape(preparse_sp($title))."' WHERE id = '".$id."'");
			}
			echo '<a title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'te'.$id.'\',\'405\')">'.preparse_un($title).'</a>';
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение категории товара
		 --------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditcat')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			echo '	<form action="index.php" method="post" id="postprod" name="postprod" onsubmit="return $.posteditor(this,\'ce'.$item['id'].'\',\'index.php?dn=ajaxsavecat&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'\')">
					<div style="width: 290px;">
						<select name="catid" style="float: left; width: 240px;">
							<option value="0">'.$lang['cat_not'].'</option>';
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			$catid = $item['catid'];
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			this_selectcat(0);
			echo '        '.$selective.'
						</select>&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavecat" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" &#187; " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение категории товара (сохранение)
		 ---------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavecat')
		{
			global $id, $catid;

			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET catid = '".$catid."' WHERE id = '".$id."'");
			}
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcaches = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}
			echo '<a title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'ce'.$id.'\',\'305\')">'.preparse_un(linecat($catid,$catcaches)).'</a>';
			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение производителя
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditmaker')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			echo '	<form action="index.php" method="post" id="postprod" name="postprod" onsubmit="return $.posteditor(this,\'me'.$item['id'].'\',\'index.php?dn=ajaxsavemaker&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'\')">
					<div style="width: 290px;">
						<select name="makid" style="float: left; width: 240px;">
							<option value="0"'.(($item['makid'] == 0) ? ' class="selective" selected="selected"' : '').'>&#8212;</option>';
			$inq = $db->query("SELECT makid, makname FROM ".$basepref."_".PERMISS."_maker ORDER BY posit ASC");
			while ($items = $db->fetchrow($inq)) {
				echo '		<option value="'.$items['makid'].'"'.(($items['makid'] == $item['makid']) ? ' class="selective" selected="selected"' : '').'>'.preparse_un($items['makname']).'</option>';
			}
			echo '		</select>&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavemaker" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" &#187; " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение производителя (сохранение)
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavemaker')
		{
			global $id, $makid;

			$id = preparse($id, THIS_INT);
			$makid = preparse($makid, THIS_INT);
			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET makid = '".$makid."' WHERE id = '".$id."'");
			}
			$inq = $db->query("SELECT makid, makname FROM ".$basepref."_".PERMISS."_maker ORDER BY posit ASC");

			$maker_only = array();
			while ($item = $db->fetchrow($inq)) {
				$maker_only[$item['makid']] =  $item['makname'];
			}
			echo '<a title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditmaker&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'me'.$id.'\',\'305\')">'.preparse_un((($makid != 0) ? $maker_only[$makid] : '&#8212;')).'</a>';
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение даты публикации
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditdate')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time = CalendarFormat($item['public']);
			echo '	<form action="index.php" method="post" id="postprod" name="postprod" onsubmit="return $.posteditor(this,\'pe'.$item['id'].'\',\'index.php?dn=ajaxsavedate&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 200px;">
						<input type="text" name="public" id="public" size="16" value="'.$time.'" />';
						Calendar('cal', 'public');
			echo '		<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavedate" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" » " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение даты публикации (сохранение)
		 --------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavedate')
		{
			global $id, $public;

			$id = preparse($id, THIS_INT);
			$time = (empty($public)) ? NEWTIME : ReDate($public);
			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET public = '".$time."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'pe'.$id.'\',\'220\')">'.format_time($time,0,1).'</a>';
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое редактирование цены товара
		 --------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditprice')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			echo '	<form action="index.php" method="post" id="postprod" name="postprod" onsubmit="return $.posteditor(this,\'de'.$item['id'].'\',\'index.php?dn=ajaxsaveprice&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'\')">
					<div style="width: 215px;">
						<input type="text" name="price" size="20"  value="'.preparse_un($item['price']).'" />&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsaveprice" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" &#187; " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое редактирование цены товара (сохранение)
		 ---------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsaveprice')
		{
			global $id, $price;

			$id = preparse($id, THIS_INT);
			$price = preparse($price, THIS_TRIM, 0, 255);
			if ($id > 0 AND $price) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET price = '".$db->escape(preparse_sp($price))."' WHERE id = '".$id."'");
			}
			echo '<a title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditprice&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'de'.$id.'\',\'205\')">'.preparse_un($price).'</a>';
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Все производители
		 ----------------------*/
		if ($_REQUEST['dn'] == 'makerlist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['maker']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['maker'].'</caption>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_maker ORDER BY posit ASC");
			while ($item = $db->fetchrow($inqset))
			{
				echo '	<tr class="list">
							<td class="al site pw25">'.$item['makname'].'</td>
							<td><input name="posit['.$item['makid'].']" type="text" size="3" maxlength="3" value="'.$item['posit'].'" /></td>
							<td class="gov">
								<a href="index.php?dn=makeredit&id='.$item['makid'].'&ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=makerdel&id='.$item['makid'].'&ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="dn" value="makerup" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="sline"></div>';

					// Добавить производителя
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_maker'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="makname" id="makname" type="text" size="70" required="required" /></td>
						</tr>';
			if($conf['cpu'] == 'yes')
			{
				echo '		<tr>
								<td>'.$lang['all_cpu'].'</td>
								<td><input name="cpu" id="cpu" type="text" size="70" />';
									$tm->outtranslit('makname', 'cpu', $lang['cpu_int_hint']);
				echo '			</td>
							</tr>';
			}
			echo '			<tr>
								<td>'.$lang['custom_title'].'</td>
								<td><input name="makcustom" type="text" size="70" /></td>
							</tr>
							<tr>
								<td>'.$lang['all_descript'].'</td>
								<td><input name="descript" type="text" size="70" /></td>
							</tr>
							<tr>
								<td>'.$lang['all_keywords'].'</td>
								<td><input name="keywords" type="text" size="70" />';
									$tm->outhint($lang['keyword_hint']);
			echo '				</td>
							</tr>
							<tr>
								<td>'.$lang['all_icon'].'</td>
								<td>
									<input name="icon" id="icon" size="40" type="text" />
									<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/maker/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_decs'].'</td>
								<td>';
									$tm->textarea('makdesc', 5, 50, '', 1, '', '', 1);
			echo '				</td>
							</tr>
							<tr>
								<td>'.$lang['phone'].'</td>
								<td><input name="phone" size="70" type="text" /></td>
							</tr>
							<tr>
								<td>'.$lang['site'].'</td>
								<td><input name="site" size="70" type="text" /></td>
							</tr>
							<tr>
								<td>'.$lang['adress'].'</td>
								<td>';
									$tm->textarea('adress', 5, 50, '', 1);
			echo '				</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="makeradd" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

			$tm->footer();
		}

		/**
		 * Сохранить позиции
		 ----------------------*/
		if ($_REQUEST['dn'] == 'makerup')
		{
			global $posit;

			if (is_array($posit))
			{
				foreach ($posit as $k => $v)
				{
					$id = preparse($k, THIS_INT);
					$v  = preparse($v, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS."_maker SET posit = '".$db->escape($v)."' WHERE makid = '".$id."'");
				}
			}
			redirect('index.php?dn=makerlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить производителя, сохранение
		 ---------------------------------------*/
		if ($_REQUEST['dn'] == 'makeradd')
		{
			global $makname, $cpu, $makcustom, $keywords, $descript, $icon, $makdesc, $phone, $site, $adress;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=makerlist&amp;ops='.$sess['hash'].'">'.$lang['maker'].'</a>',
					$lang['all_add']
				);

			$makname = preparse($makname, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);

			if (preparse($makname, THIS_EMPTY) == 1 OR preparse($makdesc, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['add_maker'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($makname);
				}

				$inqure = $db->query("SELECT makname, cpu FROM ".$basepref."_".PERMISS."_maker WHERE makname = '".$db->escape($makname)."' OR cpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['add_maker'], $makname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_maker VALUES (
					 NULL,
					 '".$db->escape($cpu)."',
					 '".$db->escape(preparse_sp($makname))."',
					 '".$db->escape(preparse_sp($makdesc))."',
					 '".$db->escape(preparse_sp($makcustom))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '0',
					 '".$db->escape($icon)."',
					 '".$db->escape($site)."',
					 '".$db->escape($phone)."',
					 '".$db->escape($adress)."'
					 )"
				);

			redirect('index.php?dn=makerlist&ops='.$sess['hash']);
		}

		/**
		 * Редактировать производителя
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'makeredit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=makerlist&amp;ops='.$sess['hash'].'">'.$lang['maker'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_maker WHERE makid = '".$id."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.$item['makname'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="makname" id="makname" type="text" value="'.$item['makname'].'" size="70" required="required" /></td>
						</tr>';
			if($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input name="cpu" id="cpu" type="text" size="70" value="'.$item['cpu'].'" />';
								$tm->outtranslit('makname', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
								<td><input name="makcustom" type="text" size="70" value="'.$item['makcustom'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input name="descript" type="text" size="70" value="'.$item['descript'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input name="keywords" type="text" size="70" value="'.$item['keywords'].'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="40" type="text" value="'.$item['icon'].'" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/maker/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('makdesc', 5, 50, $item['makdesc'], 1, '', '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['phone'].'</td>
							<td><input name="phone" type="text" size="70" value="'.$item['phone'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['site'].'</td>
							<td><input type="text" name="site" size="70" value="'.$item['site'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['adress'].'</td>
							<td>';
								$tm->textarea('adress', 5, 50, $item['adress'], 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="makersave" />
								<input type="hidden" name="id" value="'.$item['makid'].'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать производителя, сохранение
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'makersave')
		{
			global $id, $makname, $cpu, $makcustom, $keywords, $descript, $icon, $makdesc, $phone, $site, $adress;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=makerlist&amp;ops='.$sess['hash'].'">'.$lang['maker'].'</a>',
					$lang['all_edit']
				);

			$makname = preparse($makname, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			if (preparse($makname, THIS_EMPTY) == 1 OR preparse($makdesc, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_maker'], null, $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($makname);
				}

				$inqure = $db->query
							(
								"SELECT makname, cpu FROM ".$basepref."_".PERMISS."_maker
								 WHERE (makname = '".$db->escape($makname)."' OR cpu = '".$db->escape($cpu)."')
								 AND makid <> '".$id."'
								"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['edit_maker'], $makname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$inquiry = $db->query
						(
							"UPDATE ".$basepref."_".PERMISS."_maker SET
							 cpu			= '".$db->escape($cpu)."',
							 makname		= '".$db->escape(preparse_sp($makname))."',
							 makdesc		= '".$db->escape(preparse_sp($makdesc))."',
							 makcustom	= '".$db->escape(preparse_sp($makcustom))."',
							 keywords		= '".$db->escape(preparse_sp($keywords))."',
							 descript		= '".$db->escape(preparse_sp($descript))."',
							 icon			= '".$db->escape($icon)."',
							 site			= '".$db->escape($site)."',
							 phone		= '".$db->escape($phone)."',
							 adress		= '".$db->escape($adress)."'
							 WHERE makid	= '".$id."'"
						);

			redirect('index.php?dn=makerlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить производителя
		 --------------------------*/
		if ($_REQUEST['dn'] == 'makerdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=makerlist&amp;ops='.$sess['hash'].'">'.$lang['maker'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id,THIS_INT);

			if ($ok == 'yes') {
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_maker WHERE makid = '".$id."'");
				redirect('index.php?dn=makerlist&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT makname FROM ".$basepref."_".PERMISS."_maker WHERE makid = '".$id."'"));

				$yes = 'index.php?dn=makerdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=makerlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['makname']), $yes, $not);
				$tm->footer();
			}

			redirect('index.php?dn=makerlist&ops='.$sess['hash']);
		}

		/**
		 * Все валюты
		 ------------------*/
		if ($_REQUEST['dn'] == 'curlist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['currency']
				);

			$tm->header();

			echo '	<script src="'.ADMPATH.'/js/jquery.autocomplete.js"></script>';
			echo '	<div class="section">
					<table id="list" class="work">
                      <caption>'.$modname[PERMISS].': '.$lang['currency'].'</caption>
                      <tr>
                          <th class="al pw20">'.$lang['all_name'].'</th>
                          <th>'.$lang['all_code'].'</th>
                          <th>'.$lang['all_value'].'</th>
                          <th>'.$lang['sys_manage'].'</th>
                      </tr>';
			$in = Json::decode($conf[PERMISS]['currencys']);
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					if ($k == $conf[PERMISS]['currency']) {
						$defcur = '<img src="'.ADMPATH.'/template/images/totalinfo.gif" class="fr" alt="'.$lang['def_value'].'" />';
						$defcolor = 'alternative';
					} else {
						$defcur = '';
						$defcolor = 'site';
					}
					echo '	<tr class="list">
								<td class="al '.$defcolor.'">'.$v['title'].$defcur.'</td>
								<td class="server">'.$k.'</td>
								<td>'.$v['value'].'</td>
								<td class="gov pw15">
									<a href="index.php?dn=curedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
					if ($k != $conf[PERMISS]['currency']) {
						echo '		<a href="index.php?dn=curdel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
					}
					echo '		</td>
							</tr>';
				}
			}
			echo '			<tr class="tfoot">
								<td colspan="6">
									<form action="index.php" method="post">
										<input type="hidden" name="dn" value="curup" />
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										<input accesskey="s" class="main-button" value="'.$lang['def_up'].'" type="submit" />
									</form>
								</td>
							</tr>
					</table>
					</div>
					<div class="sline"></div>';

			// Добавить валюту
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_cur'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_code'].'</td>
							<td>
								<input name="code" id="code" type="text" autocomplete="off" size="50" required="required" />';
								$tm->outhint($lang['only_latin']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['symbol_left'].'</td>
							<td><input name="sl" type="text" size="50" /></td>
						</tr>
						<tr>
							<td>'.$lang['symbol_right'].'</td>
							<td><input name="sr" type="text" size="50" /></td>
						</tr>
						<tr>
							<td>'.$lang['decimal_point'].'</td>
							<td>
								<select name="decimalpoint" class="sw85">
									<option value=".">.</option>
									<option value=",">,</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['decimal'].'</td>
							<td><input name="decimal" type="text" size="50" /></td>
						</tr>
						<tr>
							<td>'.$lang['thousand_point'].'</td>
							<td>
								<select name="thousandpoint" class="sw85">
									<option value="">'.$lang['all_no'].'</option>
									<option value=".">.</option>
									<option value=",">,</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_value'].'</td>
							<td><input type="text" name="value" size="50" maxlength="255" />';
								$tm->outhint($lang['currency_def']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="curadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';
			echo '	<script>
					$(function() {
						$("#code").autocomplete({
							url:"index.php?dn=autocomplete&ops='.$sess['hash'].'",
							matchCase:true,
							onItemSelect:
							function(item){
								$("#code").attr("value",item.data);
							};
						});
					});
					</script>';

			$tm->footer();
		}

		/**
		 * Обновить значение
		 ----------------------*/
		if ($_REQUEST['dn'] == 'curup')
		{
			$in = Json::decode($conf[PERMISS]['currencys']);
			$u = 0;
			if (is_array($in))
			{
				$d = array();
				foreach ($in as $k => $v)
				{
					if ($conf[PERMISS]['currency'] !== $k) {
						$d[] = $conf[PERMISS]['currency'].$k.'=X';
					}
				}
				if (sizeof($d) > 0)
				{
					$f = 'http://download.finance.yahoo.com/d/quotes.csv?s='.implode(',',$d).'&f=sl1&e=.csv';
					$content = file_get_contents($f);
					if ($content)
					{
						$lines = explode("\n", trim($content));
						foreach ($lines as $line)
						{
							$currency = substr($line, 4, 3);
							$value = substr($line, 11, 6);
							if ((float)$value AND isset($in[$currency])) {
								$u = 1;
								$in[$currency]['value'] = ''.formats((float)$value, 5, '.', '').'';
							}
						}
					}
					if ($u)
					{
						$ins = Json::encode($in);
						$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'currencys'");
						$cache->cachesave(1);
					}
				}
			}
			redirect('index.php?dn=curlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить валюту
		 ----------------------*/
		if ($_REQUEST['dn'] == 'curadd')
		{
			global $title, $code, $sl, $sr, $decimal, $value, $decimalpoint, $thousandpoint;

			$code = preparse($code, THIS_TRIM, 0, 3);
			if ( ! empty($title) AND preparse($code, THIS_SYMNUM) == 0)
			{
				if (empty($conf[PERMISS]['currencys']))
				{
					$in = array();
					$in[$code] = array
									(
										'title'         => $title,
										'symbol_left'   => $sl,
										'symbol_right'  => $sr,
										'decimal'       => ((empty($decimal)) ? 2 : intval($decimal)),
										'value'         => ((empty($value) OR ceil($value) == 0) ? '0.01' : formats($value, 5, '.', '')),
										'decimalpoint'  => (($decimalpoint == ',') ? ',' : '.'),
										'thousandpoint' => (($thousandpoint == ',' OR $thousandpoint == '.') ? $thousandpoint : '')
									);
					$ins = Json::encode($in);
				}
				else
				{
					$in = Json::decode($conf[PERMISS]['currencys']);
					if (isset($in[$code]))
					{
						$ins = $conf[PERMISS]['currencys'];
					} else {
						$newin = array();
						$in[$code] = array
										(
											'title'         => $title,
											'symbol_left'   => $sl,
											'symbol_right'  => $sr,
											'decimal'       => ((empty($decimal)) ? 2 : intval($decimal)),
											'value'         => ((empty($value)) ? '0.01' : formats($value, 5, '.', '')),
											'decimalpoint'  => (($decimalpoint == ',') ? ',' : '.'),
											'thousandpoint' => (($thousandpoint == ',' OR $thousandpoint == '.') ? $thousandpoint : '')
										);
						$ins = Json::encode(array_merge($in, $newin));
					}
				}
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'currencys'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=curlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать валюту
		 -------------------------*/
		if ($_REQUEST['dn'] == 'curedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=curlist&amp;ops='.$sess['hash'].'">'.$lang['currency'].'</a>',
					$lang['all_edit']
				);

			$in = Json::decode($conf[PERMISS]['currencys']);
			if (isset($in[$id]))
			{
				$tm->header();

				$v = $in[$id];
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['all_edit'].': '.$v['title'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" type="text" size="50" value="'.$v['title'].'" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_code'].'</td>
								<td><input name="code" type="text" id="code" autocomplete="off" size="50" value="'.$id.'" required="required" />';
									$tm->outhint($lang['only_latin']);
				echo '			</td>
							</tr>
							<tr>
								<td>'.$lang['symbol_left'].'</td>
								<td><input name="sl" type="text" size="50" value="'.$v['symbol_left'].'" /></td>
							</tr>
							<tr>
								<td>'.$lang['symbol_right'].'</td>
								<td><input name="sr" type="text" size="50" value="'.$v['symbol_right'].'" /></td>
							</tr>
							<tr>
								<td>'.$lang['decimal_point'].'</td>
								<td>
									<select name="decimalpoint" class="sw85">
										<option value="."'.(($v['decimalpoint'] == '.') ? ' selected' : '').'>.</option>
										<option value=","'.(($v['decimalpoint'] == ',') ? ' selected' : '').'>,</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>'.$lang['decimal'].'</td>
								<td><input name="decimal" type="text" size="50" value="'.$v['decimal'].'" /></td>
							</tr>
							<tr>
								<td>'.$lang['thousand_point'].'</td>
								<td>
									<select name="thousandpoint" class="sw85">
										<option value="">'.$lang['all_no'].'</option>
										<option value=" "'.(($v['thousandpoint'] == ' ') ? ' selected' : '').'>'.$lang['space'].'</option>
										<option value="."'.(($v['thousandpoint'] == '.') ? ' selected' : '').'>.</option>
										<option value=","'.(($v['thousandpoint'] == ',') ? ' selected' : '').'>,</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_value'].'</td>
								<td><input name="value" type="text" size="50" value="'.$v['value'].'" />';
									$tm->outhint($lang['currency_def']);
				echo '			</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="cursave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';
				echo '	<script>
						$(function() {
							$("#code").autocomplete({
								url:"index.php?dn=autocomplete&ops='.$sess['hash'].'",
								matchCase:true,
								onItemSelect:
								function(item){
									$("#code").attr("value",item.data);
								};
							});
						});
						</script>';

				$tm->footer();
			}
			redirect('index.php?dn=curlist&ops='.$sess['hash']);
		}

		/**
		 * Редактировать валюту, сохранение
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'cursave')
		{
			global $title, $code, $sl, $sr, $decimal, $value, $decimalpoint, $thousandpoint;

			$in = Json::decode($conf[PERMISS]['currencys']);
			if (isset($in[$code]))
			{
				$code = preparse($code, THIS_TRIM, 0, 3);
				if ( ! empty($title) AND preparse($code, THIS_SYMNUM) == 0)
				{
					$in[$code] = array
									(
										'title'         => $title,
										'symbol_left'   => $sl,
										'symbol_right'  => $sr,
										'decimal'       => ((empty($decimal)) ? 0 : ((intval($decimal) > 4) ? 4 : intval($decimal))),
										'value'         => ((empty($value) OR ceil($value) == 0) ? '0.01' : formats($value, 5, '.', '')),
										'decimalpoint'  => (($decimalpoint == ',') ? ',' : '.'),
										'thousandpoint' => (($thousandpoint == ',' OR $thousandpoint == '.' OR $thousandpoint == ' ') ? $thousandpoint : '')
									);
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'currencys'");
					$cache->cachesave(1);
				}
			}
			redirect('index.php?dn=curlist&ops='.$sess['hash']);
		}

		/**
		 * Удалить валюту
		 --------------------*/
		if ($_REQUEST['dn'] == 'curdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=curlist&amp;ops='.$sess['hash'].'">'.$lang['currency'].'</a>',
					$lang['all_delet']
				);

			$in = Json::decode($conf[PERMISS]['currencys']);
			if ($ok == 'yes')
			{
				if (isset($in[$id]) AND $id !== $conf[PERMISS]['currency']) {
					unset($in[$id]);
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'currencys'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$yes = 'index.php?dn=curdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=curlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($in[$id]['title']), $yes, $not);
				$tm->footer();
			}

			redirect('index.php?dn=curlist&ops='.$sess['hash']);
		}

		/**
		 * Доп. поля, листинг
		 ---------------------*/
		if ($_REQUEST['dn'] == 'filedlist')
		{
			global $types, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['multi_fields']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu = ( ! is_null($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$c  = $db->fetchrow($db->query("SELECT COUNT(oid) AS total FROM ".$basepref."_".PERMISS."_option"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$c['total'] = ($c['total'] == 0) ? 1 : $c['total'];
			$link = '';
			$sf = $nu * ($p - 1);
			$pages = $lang['all_pages'].':&nbsp; '.adm_pages('', '', 'index', 'filedlist', $nu, $p, $sess, 0, $c['total']);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages('index.php?dn=filedlist&amp;p='.$p.'&amp;ops='.$sess['hash'], $nu, 0, $c['total']);
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['multi_fields'].'</caption>
						<tr>
							<td colspan="4">'.$amount.'</td>
						</tr>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['item_type'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option ORDER BY posit ASC LIMIT ".$sf.", ".$nu);
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="al site">'.$item['title'].'</td>
							<td><input name="posit['.$item['oid'].']" size="3" maxlength="3" value="'.$item['posit'].'" type="text" /></td>
							<td>'.(isset($lang[$types[$item['type']]]) ? $lang[$types[$item['type']]] : $item['type']).'</td>
							<td class="gov">
								<a href="index.php?dn=filededit&amp;id='.$item['oid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=fileddel&amp;id='.$item['oid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="4">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="dn" value="filedup" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['multi_fields'].': '.$lang['all_add'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="70" required="required" /></td>
						</tr>
						<tr>
							<td>'.$lang['item_type'].'</td>
							<td>
								<select name="type">';
			foreach ($types as $k => $v) {
				echo '				<option value="'.$k.'">'.(isset($lang[$v]) ? $lang[$v] : $v).'</option>';
			}
			echo '				</select>
							</td>
						</tr>';
			if ($conf[PERMISS]['search'] == 'yes') {
				echo '	<tr>
							<td>'.$lang['search'].'</td>
							<td>
								<select name="search">
									<option value="0">'.$lang['all_no'].'</option>
									<option value="1">'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>';
			}
			if ($conf[PERMISS]['buy'] == 'yes') {
				echo '	<tr>
							<td>'.$lang['order'].'</td>
							<td>
								<select name="buy">
									<option value="0">'.$lang['all_no'].'</option>
									<option value="1">'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="filedadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />';
			if ($conf[PERMISS]['buy'] == 'no') {
				echo '			<input type="hidden" name="buy" value="0" />';
			}
			if ($conf[PERMISS]['search'] == 'no') {
				echo '			<input type="hidden" name="search" value="0" />';
			}
			echo '				<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Все поля, сохранить позиции
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'filedup')
		{
			global $posit;

			if (is_array($posit))
			{
				foreach ($posit as $k => $v)
				{
					$id = preparse($k, THIS_INT);
					$v = preparse($v, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS."_option SET posit = '".$db->escape($v)."' WHERE oid = '".$id."'");
				}
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			redirect('index.php?dn=filedlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить поле, сохранение
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'filedadd')
		{
			global $title, $type, $types, $search, $buy;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					$lang['all_add']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$search = preparse($search, THIS_INT);
			if (preparse($title, THIS_EMPTY) == 1 OR ! isset($types[$type]))
			{
				$tm->header();
				$tm->error($lang['multi_fields'], $lang['all_add'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$search = ($search == 1 AND $type != 'checkbox' AND $type != 'text' AND $type != 'textarea') ? 1 : 0;
				$buy = ($buy == 1) ? 1 : 0;
				//if ($type == 'text' OR $type == 'checkbox') {
				if ($type == 'text' OR $type == 'textarea') {
					$buy = 0;
				}
				if ($conf[PERMISS]['buy'] == 'no') {
					$buy = 0;
				}
				if ($conf[PERMISS]['search'] == 'no') {
					$search = 0;
				}
				$db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS."_option VALUES (
						 NULL,
						 '".$db->escape($title)."',
						 '".$db->escape($type)."',
						 '".$db->escape($search)."',
						 '".$db->escape($buy)."',
						 '0'
						 )"
					);
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			redirect('index.php?dn=filedlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать поле
		 ----------------------*/
		if ($_REQUEST['dn'] == 'filededit')
		{
			global $id, $type, $types;

			$id = preparse($id, THIS_INT);

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['multi_fields'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.$item['title'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="70" value="'.$item['title'].'" required="required" /></td>
						</tr>
						<tr>
							<td>'.$lang['item_type'].'</td>
							<td>';
			if ($item['type'] == 'text') {
				echo $lang['input_texts'].' <input name="type" type="hidden" value="text" />';
			} elseif ($item['type'] == 'textarea') {
				echo $lang['input_textarea'].' <input name="type" type="hidden" value="textarea" />';
			} else {
				echo '			<select name="type">';
				foreach ($types as $k => $v)
				{
					if ($k != 'text' AND $k != 'textarea') {
						echo '		<option value="'.$k.'"'.(($k == $item['type']) ? ' selected' : '').'>'.(isset($lang[$v]) ? $lang[$v] : $v).'</option>';
					}
				}
				echo '			</select>';
			}
			echo '			</td>
						</tr>';
			if ($conf[PERMISS]['search'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['search'].'</td>
							<td>';
				if ($item['type'] == 'text' OR $item['type'] == 'textarea') {
					echo $lang['all_no'].' <input name="search" type="hidden" value="0" />';
				} else {
					echo '		<select name="search">
									<option value="0"'.(($item['search'] == 0) ? ' selected' : '').'>'.$lang['all_no'].'</option>
									<option value="1"'.(($item['search'] == 1) ? ' selected' : '').'>'.$lang['all_yes'].'</option>
								</select>';
				}
				echo '		</td>
						</tr>';
			}
			if ($conf[PERMISS]['buy'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['order'].'</td>
							<td>';
				if ($item['type'] == 'text' OR $item['type'] == 'textarea') {
					echo $lang['all_no'].' <input type="hidden" name="buy" value="0" />';
				} else {
					echo '		<select name="buy">
									<option value="0"'.(($item['buy'] == 0) ? ' selected' : '').'>'.$lang['all_no'].'</option>
									<option value="1"'.(($item['buy'] == 1) ? ' selected' : '').'>'.$lang['all_yes'].'</option>
								</select>';
				}
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="filededitsave" />
								<input type="hidden" name="id" value="'.$item['oid'].'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />';
			if ($conf[PERMISS]['buy'] == 'no') {
				echo '			<input type="hidden" name="buy" value="0" />';
			}
			if ($conf[PERMISS]['search'] == 'no') {
				echo '			<input type="hidden" name="search" value="0" />';
			}
			echo '				<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';
			if ($item['type'] != 'text' AND $item['type'] != 'textarea')
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option_value WHERE oid = '".$id."' ORDER BY posit ASC");
				if ($db->numrows($inq) > 0)
				{
					echo '	<div class="sline"></div>
							<div class="section">
							<form action="index.php" method="post">
							<table class="work">
								<caption>'.$lang['list_items'].'</caption>
								<tr>
									<th>'.$lang['all_posit'].'</th>
									<th>'.$lang['all_name'].'</th>';
					if ($item['buy'] == 1)
					{
						echo '		<th class="work-no-sort strong center">'.$lang['modif'].'&nbsp; &#8260; &nbsp;'.$lang['price'].'</th>';
					}
					echo '			<th>'.$lang['sys_manage'].'</th>
								</tr>';
					while ($items = $db->fetchrow($inq))
					{
						echo '	<tr>
									<td><input name="posit['.$items['vid'].']" type="text" size="3" maxlength="3" value="'.$items['posit'].'" /></td>
									<td><input name="title['.$items['vid'].']" type="text" size="50" value="'.$items['title'].'" /></td>';
						if ($item['buy'] == 1)
						{
							echo '	<td>
										<input name="modvalue['.$items['vid'].']" size="5" type="text" value="'.$items['modvalue'].'" />
										<select name="modify['.$items['vid'].']">
											<option value="not"'.(($items['modify'] == 'not') ? ' selected' : '').'>&#8212;</option>
											<option value="fix"'.(($items['modify'] == 'fix') ? ' selected' : '').'> '.$lang['fix_price'].' </option>
											<option value="percent"'.(($items['modify'] == 'percent') ? ' selected' : '').'> '.$lang['percent'].' </option>
										</select>
									</td>';
						}
						echo '		<td class="gov">
										<a href="index.php?dn=fileddelval&amp;oid='.$item['oid'].'&amp;id='.$items['vid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
									</td>
								</tr>';
					}
					echo '		<tr class="tfoot">
									<td colspan="'.(($item['buy'] == 1) ? '4' : '3').'">
										<input type="hidden" name="dn" value="filedupval" />
										<input type="hidden" name="id" value="'.$item['oid'].'" />
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
									</td>
								</tr>
							</table>
							</form>
							</div>
							<div class="sline"></div>';
				}
				echo '		<div class="section">
							<form action="index.php" method="post">
							<table class="work">
								<caption>'.$lang['add_item'].'</caption>
								<tr>
									<td class="first"><span>*</span> '.$lang['all_name'].'</td>
									<td><input name="title" type="text" size="50" required="required" /></td>
								</tr>
								<tr class="tfoot">
									<td colspan="2">
										<input type="hidden" name="dn" value="filedaddval" />
										<input type="hidden" name="id" value="'.$item['oid'].'" />
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
			}

			$tm->footer();
		}

		/**
		 * Редактировать поле, сохранение
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'filededitsave')
		{
			global $id, $title, $type, $types, $search, $buy;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					$lang['add_item']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$id = preparse($id, THIS_INT);
			$search = preparse($search, THIS_INT);

			if (preparse($title, THIS_EMPTY) == 1 OR ! isset($types[$type]))
			{
				$tm->header();
				$tm->error($lang['multi_fields'], $lang['add_item'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$search = ($search == 1 AND $type != 'checkbox' AND $type != 'text' AND $type != 'textarea') ? 1 : 0;
				$buy = ($buy == 1) ? 1 : 0;
				$item = $db->fetchrow($db->query("SELECT type FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'"));
				if ($item['type'] == 'text') {
					$type = 'text';
				}
				if ($item['type'] == 'textarea') {
					$type = 'textarea';
				}
				//if ($conf[PERMISS]['buy'] == 'no' OR $type == 'checkbox') {
				if ($conf[PERMISS]['buy'] == 'no') {
					$buy = 0;
				}
				if ($conf[PERMISS]['search'] == 'no') {
					$search = 0;
				}
				$db->query
					(
						"UPDATE ".$basepref."_".PERMISS."_option SET
						 title  = '".$db->escape($title)."',
						 type   = '".$db->escape($type)."',
						 search = '".$db->escape($search)."',
						 buy    = '".$db->escape($buy)."'
						 WHERE oid = '".$id."'"
					);
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			redirect('index.php?dn=filededit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление нового пункта, сохранение
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'filedaddval')
		{
			global $id,	$title;

			$title = preparse($title, THIS_TRIM, 0, 255);
			$id = preparse($id, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					'<a href="index.php?dn=filededit&amp;id='.$id.'&amp;ops='.$sess['hash'].'">'.$item['title'].'</a>',
					$lang['add_item']
				);

			if ($item['type'] != 'text' AND $item['type'] != 'textarea')
			{
				if (preparse($title, THIS_EMPTY) == 1)
				{
					$tm->header();
					$tm->error($item['title'], $lang['add_item'], $lang['forgot_name']);
					$tm->footer();
				}
				else
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_".PERMISS."_option_value VALUES (
							 NULL,
							 '".$db->escape($item['oid'])."',
							 '".$db->escape($title)."',
							 'not',
							 '0.0000',
							 '0'
							 )"
						);
				}
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			redirect('index.php?dn=filededit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Список пунктов, сохранение
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'filedupval')
		{
			global $id, $title, $posit, $modify, $modvalue;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'"));

			if ($item['type'] != 'text' AND $item['type'] != 'textarea')
			{
				$check = array('not', 'fix', 'percent');
				foreach ($title as $vid => $names)
				{
					if (isset($vid) AND ( ! empty($names)))
					{
						$names = preparse($names, THIS_TRIM, 0, 255);
						$p = isset($posit[$vid]) ? preparse($posit[$vid], THIS_INT) : 0;
						if ($item['buy'] == 1)
						{
							$m = (isset($modify[$vid]) AND in_array($modify[$vid],$check)) ? $modify[$vid] : 'not';
							$mv = (isset($modvalue[$vid]) AND $m != 'not' AND ceil($modvalue[$vid]) > 0) ? formats($modvalue[$vid], 4, '.', '') : '0.000';
							$db->query
								(
									"UPDATE ".$basepref."_".PERMISS."_option_value SET
									 title    = '".$db->escape($names)."',
									 modify   = '".$db->escape($m)."',
									 modvalue = '".$db->escape($mv)."',
									 posit    = '".$db->escape($p)."'
									 WHERE vid = '".intval($vid)."'"
								);
						}
						else
						{
							$db->query
								(
									"UPDATE ".$basepref."_".PERMISS."_option_value SET
									 title = '".$db->escape($names)."',
									 posit = '".$db->escape($p)."'
									 WHERE vid = '".intval($vid)."'"
								);
						}
					}
				}
			}

			// Cache option
			$cache_opt = new DN\Cache\CacheOption;
			$cache_opt->cacheoption(PERMISS);

			$cache->cachesave(1);
			redirect('index.php?dn=filededit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить дополнительное поле
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'fileddel')
		{
			global $id, $ok;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT title FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					'<a href="index.php?dn=filededit&amp;id='.$id.'&amp;ops='.$sess['hash'].'">'.$item['title'].'</a>',
					$lang['all_delet']
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_option_value WHERE oid = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE oid = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$id."'");

				// Cache option
				$cache_opt = new DN\Cache\CacheOption;
				$cache_opt->cacheoption(PERMISS);

				$cache->cachesave(1);
				redirect('index.php?dn=filedlist&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=fileddel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=filedlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Удалить дополнительный пункт
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'fileddelval')
		{
			global $id, $oid, $ok;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT oid, title FROM ".$basepref."_".PERMISS."_option_value WHERE vid = '".$id."'"));
			$items = $db->fetchrow($db->query("SELECT oid, title FROM ".$basepref."_".PERMISS."_option WHERE oid = '".$item['oid']."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=filedlist&amp;ops='.$sess['hash'].'">'.$lang['multi_fields'].'</a>',
					'<a href="index.php?dn=filededit&amp;id='.$item['oid'].'&amp;ops='.$sess['hash'].'">'.$items['title'].'</a>',
					$lang['del_item']
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_product_option WHERE vid = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_option_value WHERE vid = '".$id."'");

				// Cache option
				$cache_opt = new DN\Cache\CacheOption;
				$cache_opt->cacheoption(PERMISS);

				$cache->cachesave(1);
				redirect('index.php?dn=filededit&amp;id='.$oid.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=fileddelval&amp;oid='.$oid.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=filededit&amp;id='.$oid.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($items['title'].'&nbsp; &#8260; &nbsp;'.$lang['del_item'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Вес, все пункты
		 -------------------*/
		if ($_REQUEST['dn'] == 'weightlist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['weight']
				);

			$tm->header();

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['weight'].'</caption>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['all_alias'].'</th>
							<th>'.$lang['all_code'].'</th>
							<th>'.$lang['all_value'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$in = Json::decode($conf[PERMISS]['weights']);
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					if ($k == $conf[PERMISS]['weight']) {
						$dw = '<img src="'.ADMPATH.'/template/images/totalinfo.gif" class="fr" alt="'.$lang['def_value'].'" />';
						$color = 'alternative';
					} else {
						$dw = '';
						$color = 'site';
					}
					echo '	<tr class="list">
								<td class="al '.$color.'">'.$v['title'].$dw.'</td>
								<td class="al '.$color.'">'.$v['alias'].'</td>
								<td class="server">'.$k.'</td>
								<td>'.$v['value'].'</td>
								<td class="gov">';
					echo '		<a href="index.php?dn=weightedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
					if ($k != $conf[PERMISS]['weight']) {
						echo '	<a href="index.php?dn=weightdel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
					}
					echo '		</td>
							</tr>';
				}
			}
			echo '	</table>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_item'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_alias'].'</td>
							<td><input name="alias" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_code'].'</td>
							<td><input name="code" id="code" type="text" autocomplete="off" size="50" required="required" />';
								$tm->outhint($lang['only_latin']);
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_value'].'</td>
							<td><input name="value" type="text" size="50" required="required" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="weightadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Вес, добавить, сохранение
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'weightadd')
		{
			global $title, $alias, $code, $value;

			if ( ! empty($title) AND ! empty($alias) AND ! empty($value) AND preparse($code, THIS_SYMNUM) == 0 AND $code != 'kg' AND $code != 'g')
			{
				if (empty($conf[PERMISS]['weights']))
				{
					$in = array();
					$in[$code] = array
						(
							'title' => $title,
							'alias' => $alias,
							'value' => ((empty($value)) ? '0.9' : $value)
						);

					$ins = Json::encode($in);
				}
				else
				{
					$in = Json::decode($conf[PERMISS]['weights']);
					if(isset($in[$code]))
					{
						$ins = $conf[PERMISS]['weights'];
					}
					else
					{
						$newin = array();
						$in[$code] = array
							(
								'title' => $title,
								'alias' => $alias,
								'value' => ((empty($value)) ? '0.9' : $value)
							);

						$ins = Json::encode(array_merge($in,$newin));
					}
				}
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'weights'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=weightlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Вес, редактировать
		 ----------------------*/
		if ($_REQUEST['dn'] == 'weightedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=weightlist&amp;ops='.$sess['hash'].'">'.$lang['weight'].'</a>',
					$lang['all_edit']
				);

			$in = Json::decode($conf[PERMISS]['weights']);
			if (isset($in[$id]))
			{
				$tm->header();

				$v = $in[$id];
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['weight'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.$v['title'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" type="text" value="'.$v['title'].'" size="50" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_alias'].'</td>
								<td><input name="alias" type="text" value="'.$v['alias'].'" size="50" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_code'].'</td>
								<td><input name="code" id="code" type="text" autocomplete="off" value="'.$id.'" size="50" required="required" />';
									$tm->outhint($lang['only_latin']);
				echo '			</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_value'].'</td>
								<td><input name="value" type="text" value="'.$v['value'].'" size="50" required="required" /></td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="id" value="'.$id.'" />
									<input type="hidden" name="dn" value="weightsave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
			redirect('index.php?dn=weightlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Вес, редактировать, сохранение
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'weightsave')
		{
			global $id, $title, $alias, $code, $value;

			$not = array('kg', 'g'); // Not editing key (only key)
			$in = Json::decode($conf[PERMISS]['weights']);

			if (isset($in[$id]) AND preparse($code, THIS_SYMNUM) == 0)
			{
				if ( ! empty($title) AND ! empty($alias) AND ! empty($value))
				{
					if ($code == $id)
					{
						$in[$code] = array
							(
								'title' => $title,
								'alias' => $alias,
								'value' => ((empty($value)) ? '0.9' : $value)
							);
						$ins = Json::encode($in);
					}
					else
					{
						$newin = array();
						foreach ($in as $k => $v)
						{
							if ($k == $id)
							{
								if (in_array($id, $not))
								{
									$newin[$id] = array
										(
											'title' => $title,
											'alias' => $alias,
											'value' => ((empty($value)) ? '0.9' : $value)
										);
								}
							}
						}
						unset($in[$id]);
						$ins = Json::encode(array_merge($in, $newin));
					}

					$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'weights'");
					$cache->cachesave(1);
				}
			}

			redirect('index.php?dn=weightlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Вес, удалить
		 ---------------*/
		if ($_REQUEST['dn'] == 'weightdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=weightlist&amp;ops='.$sess['hash'].'">'.$lang['weight'].'</a>',
					$lang['all_delet']
				);

			$in = Json::decode($conf[PERMISS]['weights']);
			if ($ok == 'yes' AND $id != 'kg' AND $id != 'g')
			{
				if (isset($in[$id]) AND $id !== $conf[PERMISS]['weight'])
				{
					unset($in[$id]);
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'weights'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$yes = 'index.php?dn=weightdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=weightlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['weight'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], $in[$id]['title'], $yes, $not);
				$tm->footer();
			}
			redirect('index.php?dn=weightlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Размер, все пункты
		 ---------------------*/
		if ($_REQUEST['dn'] == 'sizelist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['size']
				);

			$tm->header();

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['size'].'</caption>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['all_alias'].'</th>
							<th>'.$lang['all_code'].'</th>
							<th>'.$lang['all_value'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$in = Json::decode($conf[PERMISS]['sizes']);
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					if ($k == $conf[PERMISS]['size']) {
						$ds = '<img src="'.ADMPATH.'/template/images/totalinfo.gif" class="fr" alt="'.$lang['def_value'].'" />';
						$color = 'alternative';
					} else {
						$ds = '';
						$color = 'site';
					}
					echo '	<tr class="list">
								<td class="al '.$color.'">'.$v['title'].$ds.'</td>
								<td class="al '.$color.'">'.$v['alias'].'</td>
								<td class="server">'.$k.'</td>
								<td>'.$v['value'].'</td>
								<td class="gov">
									<a href="index.php?dn=sizeedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
					if ($k != $conf[PERMISS]['size']) {
						echo '		<a href="index.php?dn=sizedel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
					}
					echo '		</td>
							</tr>';
				}
			}
			echo '	</table>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_item'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_alias'].'</td>
							<td><input name="alias" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_code'].'</td>
							<td><input name="code" id="code" type="text" autocomplete="off" size="50" required="required" />';
									$tm->outhint($lang['only_latin']);
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_value'].'</td>
							<td><input type="text" name="value" size="50" required="required" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="sizeadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Размер, добавить пункт, сохранение
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'sizeadd')
		{
			global $title, $alias, $code, $value;

			if ( ! empty($title) AND ! empty($alias) AND ! empty($value) AND preparse($code, THIS_SYMNUM) == 0)
			{
				if (empty($conf[PERMISS]['sizes']))
				{
					$in = array();
					$in[$code] = array
						(
							'title' => $title,
							'alias' => $alias,
							'value' => ((empty($value)) ? '0.9' : $value)
						);
					$ins = Json::encode($in);
				}
				else
				{
					$in = Json::decode($conf[PERMISS]['sizes']);
					if(isset($in[$code]))
					{
						$ins = $conf[PERMISS]['sizes'];
					}
					else
					{
						$newin = array();
						$newin[$code] = array
							(
								'title' => $title,
								'alias' => $alias,
								'value' => ((empty($value)) ? '0.9' : $value)
							);
						$ins = Json::encode(array_merge($in, $newin));
					}
				}
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'sizes'");
			}
			$cache->cachesave(1);
			redirect('index.php?dn=sizelist&amp;ops='.$sess['hash']);
		}

		/**
		 * Размер, редактировать пункт
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'sizeedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=sizelist&amp;ops='.$sess['hash'].'">'.$lang['size'].'</a>',
					$lang['all_edit']
				);

			$in = Json::decode($conf[PERMISS]['sizes']);
			if (isset($in[$id]))
			{
				$tm->header();

				$v = $in[$id];
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['size'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.$v['title'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" type="text" value="'.$v['title'].'" size="50" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_alias'].'</td>
								<td><input name="alias" type="text" value="'.$v['alias'].'" size="50" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_code'].'</td>
								<td><input name="code" id="code" type="text" autocomplete="off" value="'.$id.'" size="50" required="required" />';
									$tm->outhint($lang['only_latin']);
				echo '			</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_value'].'</td>
								<td><input type="text" name="value" value="'.$v['value'].'" size="50" required="required" /></td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="id" value="'.$id.'" />
									<input type="hidden" name="dn" value="sizesave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
			redirect('index.php?dn=sizelist&amp;ops='.$sess['hash']);
		}

		/**
		 * Размер, редактировать пункт, сохранение
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'sizesave')
		{
			global $id, $title, $alias, $code, $value;

			$in = Json::decode($conf[PERMISS]['sizes']);
			if (isset($in[$id]) AND preparse($code, THIS_SYMNUM) == 0)
			{
				if ( ! empty($title) AND ! empty($alias) AND ! empty($value))
				{
					if ($code == $id)
					{
						$in[$code] = array
							(
								'title' => $title,
								'alias' => $alias,
								'value' => ((empty($value)) ? '0.9' : $value)
							);
						$ins = Json::encode($in);
					}
					else
					{
						$newin = array();
						foreach ($in as $k => $v)
						{
							if ($k == $id)
							{
								$newin[$code] = array
									(
										'title' => $title,
										'alias' => $alias,
										'value' => ((empty($value)) ? '0.9' : $value)
									);
							}
						}
						unset($in[$id]);
						$ins = Json::encode(array_merge($in, $newin));
					}

					$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'sizes'");
					$cache->cachesave(1);
				}
			}
			redirect('index.php?dn=sizelist&amp;ops='.$sess['hash']);
		}

		/**
		 * Размер, удалить пункт
		 ------------------------*/
		if ($_REQUEST['dn'] == 'sizedel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=sizelist&amp;ops='.$sess['hash'].'">'.$lang['size'].'</a>',
					$lang['all_delet']
				);

			$in = Json::decode($conf[PERMISS]['sizes']);
			if ($ok == 'yes')
			{
				if (isset($in[$id]) AND $id !== $conf[PERMISS]['size'])
				{
					unset($in[$id]);
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'sizes'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$yes = 'index.php?dn=sizedel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=sizelist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['size'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], $in[$id]['title'], $yes, $not);
				$tm->footer();
			}
			redirect('index.php?dn=sizelist&amp;ops='.$sess['hash']);
		}

		/**
		 * Налоги, все позиции
		 ----------------------*/
		if ($_REQUEST['dn'] == 'taxlist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['tax']
				);

			$tm->header();

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['tax'].'</caption>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['tax_fix'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$in = Json::decode($conf[PERMISS]['taxes']);
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					echo '	<tr class="list">
								<td class="al site">'.$v['title'].'</td>
								<td>'.$v['tax'].'</td>
								<td class="gov mark">
									<a href="index.php?dn=taxedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
									<a href="index.php?dn=taxdel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '	</table>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_item'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['tax_fix'].'</td>
							<td><input name="tax" type="text" size="50" required="required" /> %</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="taxadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Налоги, добавление позиции, сохранение
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'taxadd')
		{
			global $title, $tax;

			$title = preparse($title, THIS_TRIM, 0, 255);
			$tax = preparse($tax, THIS_INT);

			if ( ! empty($title))
			{
				if (empty($conf[PERMISS]['taxes']))
				{
					$in[1] = array('title' => $title, 'tax' => formats($tax, 2, '.', ''));
					$ins = Json::encode($in);
				} else {
					$in = Json::decode($conf[PERMISS]['taxes']);
					$in[] = array('title' => $title,'tax' => formats($tax,2,'.',''));
					$ins = Json::encode($in);
				}
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'taxes'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=taxlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Налоги, редактирование позиции
		 ---------------------------------*/
		if ($_REQUEST['dn'] == 'taxedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=taxlist&amp;ops='.$sess['hash'].'">'.$lang['tax'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$in = Json::decode($conf[PERMISS]['taxes']);

			if (isset($in[$id]))
			{
				$tm->header();

				$v = $in[$id];
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['tax'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.$v['title'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" size="50" type="text" value="'.$v['title'].'" required="required" /></td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['tax_fix'].'</td>
								<td><input name="tax" type="text" size="50" value="'.$v['tax'].'" required="required" /> %</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="taxsave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input type="hidden" name="id" value="'.$id.'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
			redirect('index.php?dn=taxlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Налоги, редактирование позиции, сохранение
		 ----------------------------------------------*/
		if ($_REQUEST['dn'] == 'taxsave')
		{
			global $id, $title, $tax;

			$id = preparse($id, THIS_INT);
			$tax = preparse($tax, THIS_INT);

			$in = Json::decode($conf[PERMISS]['taxes']);

			if (isset($in[$id]))
			{
				if ( ! empty($title))
				{
					$in[$id] = array('title' => $title, 'tax' => formats($tax, 2, '.', ''));
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'taxes'");
					$cache->cachesave(1);
				}
			}

			redirect('index.php?dn=taxlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Налоги, удаление позиции
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'taxdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=sizelist&amp;ops='.$sess['hash'].'">'.$lang['tax'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			$in = Json::decode($conf[PERMISS]['taxes']);

			if ($ok == 'yes')
			{
				if (isset($in[$id]))
				{
					unset($in[$id]);

					sort($in);
					array_unshift($in, NULL);
					unset($in[0]);

					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'taxes'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$yes = 'index.php?dn=taxdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=taxlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['tax'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], $in[$id]['title'], $yes, $not);
				$tm->footer();
			}

			redirect('index.php?dn=taxlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Доставка, все позиции
		 ------------------------*/
		if ($_REQUEST['dn'] == 'delivlist')
		{
			global $type;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['delivery']
				);

			$tm->header();

			$darr = $ignore = $delivery = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$delivery[$item['did']] = $item;
				if ($item['type'] == 'auto')
				{
					$ignore[$item['ext']] = $item['ext'];
				}
			}
			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['delivery'].'</caption>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['status'].'</th>
							<th>'.$lang['type'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			if (is_array($delivery))
			{
				foreach ($delivery as $k => $v)
				{
					$t = (isset($lang[$k])) ? $lang[$k] : $k;
					$status = ($delivery[$k]['act'] == 1) ? '<span class="server">'.$lang['included'].'</span>' : '<span class="alternative">'.$lang['not_included'].'</span>';
					$class = ($delivery[$k]['act'] == 1) ? '' : 'no-active';
					echo '	<tr class="list">
								<td class="'.$class.' vm al site">'.$v['title'].'</td>
								<td class="'.$class.'">'.$status.'</td>
								<td class="'.$class.'">'.(($v['type'] == 'custom') ? $lang['delivery_custom'] : $lang['delivery_auto']).'</td>
								<td class="'.$class.'"><input name="posit['.$delivery[$k]['did'].']" size="3" maxlength="3" value="'.$delivery[$k]['posit'].'" type="text" /></td>
								<td class="'.$class.' gov">
									<a href="index.php?dn=delivedit'.(($delivery[$k]['type'] == 'auto') ? 'ext' : '').'&amp;id='.$delivery[$k]['did'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if ($delivery[$k]['act'] == 1) {
					echo '		<a href="index.php?dn=delivact&amp;act=0&amp;id='.$delivery[$k]['did'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=delivact&amp;act=1&amp;id='.$delivery[$k]['did'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				}
				echo '			<a href="index.php?dn=delivdel&amp;id='.$delivery[$k]['did'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="5">
								<input type="hidden" name="dn" value="delivup" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="sline"></div>';
			$deliverydir = opendir(WORKDIR.'/core/shop/delivery/');
			while ($name = readdir($deliverydir))
			{
				if ($name != '.' AND $name != '..' AND strpos($name, '.php'))
				{
					$ext = basename($name, '.php');
					if ( ! isset($ignore[$ext]))
					{
						$darr[$ext] = array();
					}
				}
			}
			if ($type == 'custom') //  Добавить настраиваемую доставку
			{
				echo '	<div class="section">
						<form action="index.php" method="post" id="total-form">
						<table border="0" cellpadding="1" cellspacing="1" class="work">
							<caption>'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_custom'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" type="text" size="70" required="required" /></td>
							</tr>
							<tr>
								<td>'.$lang['price'].'</td>
								<td><input name="price" type="text" size="34" /></td>
							</tr>
							<tr>
								<td>'.$lang['percent'].'</td>
								<td><input name="percent" type="text" size="34" /></td>
							</tr>
							<tr>
								<td>'.$lang['type'].'</td>
								<td>
									<select name="data" class="sw210">
										<option value="fix"> '.$lang['fix_price'].' </option>
										<option value="percent"> '.$lang['percent'].' </option>
										<option value="fixpercent"> '.$lang['fix_price'].' + '.$lang['percent'].' </option>
									</select>
								</td>
							</tr>';
				echo '		<tr>
								<td>'.$lang['currency'].'</td>
								<td>
									<select name="currency" class="sw210">';
				$in = Json::decode($conf[PERMISS]['currencys']);
				if (is_array($in))
				{
					foreach ($in as $k => $v)
					{
						$kdef = (($conf[PERMISS]['currency'] == $k) ? ' style="color: #c03; background: #eef;" selected' : '');
						$vdef = (($conf[PERMISS]['currency'] == $k) ? ' ('.$lang['def_value'].')' : '');
						echo '			<option value="'.$k.'"'.$kdef.'>'.$v['title'].$vdef.'</option>';
					}
				}
				echo '				</select>
								</td>
							</tr>';
				echo '		<tr>
								<td class="first"><span>*</span> '.$lang['parent_view'].'</td>
								<td>
									<table class="work">';
				$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit");
				$region = array();
				while ($item = $db->fetchrow($inq))
				{
					$region[$item['countryid']][$item['regionid']] = $item['regionname'];
				}
				$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit");
				while ($item = $db->fetchrow($inq))
				{
					$label = isset($region[$item['countryid']]) ? $region[$item['countryid']] : array();
					$click = (sizeof($label) > 0) ? ' onclick="$(\'#'.$item['countryid'].'_toggle\').toggle(\'fast\');" class="site strong pointer"' : ' class="site"';
					echo '				<tr>
											<td class="al">
												<input type="checkbox" id="'.$item['countryid'].'checkbox" name="country['.$item['countryid'].']" onclick="$.modcheck(\''.$item['countryid'].'\');" value="yes" checked />&nbsp
												'.((sizeof($label) > 0) ? '<img onclick="$(\'#'.$item['countryid'].'_toggle\').toggle(\'fast\');" src="'.ADMPATH.'/template/images/plus.png" />' : '').'&nbsp
												<span'.$click.'>'.$item['countryname'].'</span>';
					if (is_array($label))
					{
						echo '					<div class="work-lite" id="'.$item['countryid'].'_toggle" style="display:none;">
													<div class="blocking">';
						foreach ($label as $k => $v)
						{
							echo '						<div class="site" style="margin: 3px -4px;"><input type="checkbox" name="state['.$k.']" value="yes" checked /> <em class="pad">'.$v.'</em></div>';
						}
						echo '						</div>
												</div>';
					}
					echo '					</td>
										</tr>';
				}
				echo '				</table>
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_icon'].'</td>
								<td>
									<input name="icon" id="icon" size="40" type="text" />
									<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_decs'].'</td>
								<td>';
									$tm->textarea('descr', 5, 50, '', 1);
				echo '			</td>
							</tr>
						<tr>
							<td>'.$lang['status'].'</td>
							<td>
								<select name="actdel" class="sw150">
									<option value="0">'.$lang['not_included'].'</option>
									<option value="1">'.$lang['included'].'</option>
								</select>
							</td>
						</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="delivsave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}
			elseif ($type == 'auto') // Выбор типа автоматизированной доставки
			{
				if (sizeof($darr) > 0)
				{
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table class="work">
								<caption>'.$lang['all_submint'].' &nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_auto'].'</caption>
								<tr>
									<td class="first"><span>*</span> '.$lang['all_file'].'</td>
									<td>
										<select name="id">';
					if (is_array($darr))
					{
						foreach ($darr as $k => $v)
						{
							echo '			<option value="'.$k.'">core/shop/delivery/'.$k.'.php</option>';
						}
					}
					echo '				</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td colspan="2">
										<input type="hidden" name="dn" value="delivaddext" />
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
				}
			}
			else // Выбор типа доставки
			{
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['add_item'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['type'].'</td>
								<td>
									<select name="type" class="sw210">
										<option value="custom">'.$lang['delivery_custom'].'</option>';
				if (sizeof($darr) > 0)
				{
					echo '				<option value="auto">'.$lang['delivery_auto'].'</option>';
				}
				echo '				</select>
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="delivlist" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}

			$tm->footer();
		}

		/**
		 * Доставка, сохранение позиций
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'delivup')
		{
			global $posit;

			if (is_array($posit))
			{
				foreach ($posit as $k => $v)
				{
					$id = preparse($k, THIS_INT);
					$v = preparse($v, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS."_delivery SET posit = '".$db->escape($v)."' WHERE did = '".$id."'");
				}
			}
			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Изменение состояния доставки
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'delivact')
		{
			global $act, $id;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			if ($act == 0 OR $act == 1)
			{
				$db->query("UPDATE ".$basepref."_".PERMISS."_delivery SET act='".$act."' WHERE did = '".$id."'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить доставку, Автоматизированная
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'delivaddext')
		{
			global $id, $opt;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_add']
				);

			$darr = $ignore = $delivery = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$delivery[$item['did']] = $item;
				if ($item['type'] == 'auto')
				{
					$ignore[$item['ext']] = $item['ext'];
				}
			}

			$deliverydir = opendir(WORKDIR.'/core/shop/delivery/');
			while ($name = readdir($deliverydir))
			{
				if ($name != '.' AND $name != '..' AND strpos($name, '.php'))
				{
					$ext = basename($name, '.php');
					if ( ! isset($ignore[$ext]))
					{
						$darr[$ext] = array();
					}
				}
			}

			if ( ! empty($id) AND isset($darr[$id]))
			{
				include(WORKDIR.'/core/shop/delivery/'.$id.'.php');
				$class = str_replace('.', '', $id);
				$d = new $class;
				$tm->header();
				$d->add();
				$tm->footer();
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить автоматизированную доставку, сохранение
		 ----------------------------------------------------*/
		if ($_REQUEST['dn'] == 'delivsaveext')
		{
			global $id, $opt;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_add']
				);

			$darr = $ignore = $delivery = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$delivery[$item['did']] = $item;
				if ($item['type'] == 'auto')
				{
					$ignore[$item['ext']] = $item['ext'];
				}
			}

			$deliverydir = opendir(WORKDIR.'/core/shop/delivery/');
			while ($name = readdir($deliverydir))
			{
				if ($name != '.' AND $name != '..' AND strpos($name, '.php'))
				{
					$ext = basename($name, '.php');
					if ( ! isset($ignore[$ext]))
					{
						$darr[$ext] = array();
					}
				}
			}

			if ( ! empty($id) AND isset($darr[$id]))
			{
				include(WORKDIR.'/core/shop/delivery/'.$id.'.php');
				$class = str_replace('.', '', $id);
				$d = new $class;
				$d->save();
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать автоматизированную доставку
		 ---------------------------------------------*/
		if ($_REQUEST['dn'] == 'deliveditext')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$edit = array('did', 'price', 'icon', 'title', 'descr', 'data', 'weight', 'posit', 'type', 'ext', 'act');

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				if ($item['type'] == 'auto' AND $id == $item['did'])
				{
					$edit = $item;
				}
			}

			if (sizeof($edit) > 0)
			{
				include(WORKDIR.'/core/shop/delivery/'.$edit['ext'].'.php');
				$class = str_replace('.', '', $edit['ext']);
				$d = new $class;
				$tm->header();
				$d->edit($edit);
				$tm->footer();
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать автоматизированную доставку, сохранение
		 ---------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'deliveditsaveext')
		{
			global $id, $opt, $title, $icon, $descr, $weight, $country;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_edit']
				);

			$edit = array();
			$id = preparse($id,THIS_INT);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				if ($item['type'] == 'auto' AND $id == $item['did'])
				{
					$edit = $item;
				}
			}

			if (sizeof($edit) > 0)
			{
				include(WORKDIR.'/core/shop/delivery/'.$edit['ext'].'.php');

				$class = str_replace('.', '', $edit['ext']);
				$d = new $class;
				$d->editsave();

				redirect('index.php?dn=deliveditext&amp;id='.$id.'&amp;ops='.$sess['hash']);
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить настраиваемую доставку, сохранение
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'delivsave')
		{
			global $title, $icon, $descr, $price, $percent, $currency, $data, $country, $state, $actdel;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_add']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			$c = $uc = array();

			while ($items = $db->fetchrow($inq))
			{
				$c[$items['countryid']] = $items['countryid'];
			}

			if (is_array($country) AND sizeof($country) > 0)
			{
				foreach ($country as $k => $v)
				{
					if (isset($c[$k]))
					{
						$uc[$k] = $k;
					}
				}
			}

			if (sizeof($uc) < 1)
			{
				$tm->header();
				$tm->error($lang['all_add'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'], $lang['delivery_custom'], $lang['forgot_name']);
				$tm->footer();
			}

			$check = array('fix', 'percent', 'fixpercent');
			$data = in_array($data, $check) ? $data : 'fix';
			$price = (ceil($price) > 0) ? formats($price, 2, '.', '') : '0.00';
			$percent = (ceil($percent) > 0) ? formats($percent, 2, '.', '') : '0.00';

			if (empty($title) OR $data == 'percent' AND $percent == 0 OR $data == 'fixpercent' AND $percent == 0 AND $price == 0)
			{
				$tm->header();
				$tm->error($lang['all_add'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'], $lang['delivery_custom'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$s = array();
				foreach ($state as $k => $v)
				{
					$s[$k] = $k;
				}

				$new = Json::encode(array
					(
						'country' => $uc,
						'state'   => $s,
						'data'    => $data,
						'percent' => $percent
					)
				);

				$db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS."_delivery VALUES (
						 NULL,
						 '".$db->escape($price)."',
						 '".$db->escape($currency)."',
						 '".$db->escape($icon)."',
						 '".$db->escape($title)."',
						 '".$db->escape($descr)."',
						 '".$db->escape($new)."',
						 '0',
						 'custom',
						 '',
						 '".$actdel."'
						)"
					);
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать настраиваемую доставку
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'delivedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_add']
				);

			$id = preparse($id, THIS_INT);
			$edit = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				if ($item['type'] == 'custom' AND $id == $item['did'])
				{
					$edit = $item;
				}
			}

			if (sizeof($edit) > 0)
			{
				$tm->header();

				$data = Json::decode($edit['data']);
				echo '	<div class="section">
						<form action="index.php" method="post" id="total-form">
						<table class="work">
							<caption>'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_custom'].'</caption>
							<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" value="'.$edit['title'].'" size="70" required="required" /></td>
						</tr>
						<tr>
							<td>'.$lang['price'].'</td>
							<td><input name="price" type="text" value="'.$edit['price'].'" size="34" /></td>
						</tr>
						<tr>
							<td>'.$lang['percent'].'</td>
							<td><input name="percent" type="text" value="'.$data['percent'].'" size="34" /></td>
						</tr>
						<tr>
							<td>'.$lang['type'].'</td>
							<td>
								<select name="data" class="sw210">
									<option value="fix"'.(($data['data'] == 'fix') ? ' selected' : '').'> '.$lang['fix_price'].' </option>
									<option value="percent"'.(($data['data'] == 'percent') ? ' selected' : '').'> '.$lang['percent'].' </option>
									<option value="fixpercent"'.(($data['data'] == 'fixpercent') ? ' selected' : '').'> '.$lang['fix_price'].' + '.$lang['percent'].' </option>
								</select>
							</td>
							</tr>';
				echo '		<tr>
								<td>'.$lang['currency'].'</td>
								<td>
									<select name="currency" class="sw210">';
				$in = Json::decode($conf[PERMISS]['currencys']);
				if (is_array($in))
				{
					foreach ($in as $k => $v)
					{
						$ksel = (($edit['currency'] == $k) ? ' style="color: #c03; background: #eef;" selected' : '');
						$kdef = (($conf[PERMISS]['currency'] == $k) ? '' : '');
						$vdef = (($conf[PERMISS]['currency'] == $k) ? ' ('.$lang['def_value'].')' : '');
						echo '			<option value="'.$k.'"'.$kdef.$ksel.'>'.$v['title'].$vdef.'</option>';
					}
				}
				echo '				</select>
								</td>
							</tr>';
				echo '		<tr>
							<td class="first"><span>*</span> '.$lang['parent_view'].'</td>
							<td>
								<table class="work">';
				$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit");
				$region = array();
				while ($item = $db->fetchrow($inq))
				{
					$region[$item['countryid']][$item['regionid']] = $item['regionname'];
				}
				$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit");
				while ($item = $db->fetchrow($inq))
				{
					$label = isset($region[$item['countryid']]) ? $region[$item['countryid']] : array();
					$click = (sizeof($label) > 0) ? ' onclick="$(\'#'.$item['countryid'].'_toggle\').toggle(\'fast\');" class="site strong pointer"' : ' class="site"';
					echo '			<tr>
										<td class="al">
											<input type="checkbox" id="'.$item['countryid'].'checkbox" name="country['.$item['countryid'].']" onclick="$.modcheck(\''.$item['countryid'].'\');" value="yes" '.(isset($data['country'][$item['countryid']]) ? ' checked' : '').' />&nbsp
											'.((sizeof($label) > 0) ? '<img onclick="$(\'#'.$item['countryid'].'_toggle\').toggle(\'fast\');" src="'.ADMPATH.'/template/images/plus.png" />' : '').'&nbsp
											<span'.$click.'>'.$item['countryname'].'</span>';
					if (is_array($label))
					{
						echo '				<div id="'.$item['countryid'].'_toggle" style="display: none;">
												<div class="blocking">';
						foreach ($label as $k => $v)
						{
							echo '					<div class="site" style="margin: 3px -4px;"><input type="checkbox" name="state['.$k.']" value="yes" '.(isset($data['state'][$k]) ? ' checked' : '').' /> <em class="pad">'.$v.'</em></div>';
						}
						echo '					</div>
											</div>';
					}
					echo '				</td>
									</tr>';
				}
				echo '			</table>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="45" type="text" value="'.$edit['icon'].'" />&nbsp
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&field[1]=icon\')" value=" '.$lang['filebrowser'].' " type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr' ,5, 50, $edit['descr'], 1);
				echo '		</td>
						</tr>
						<tr>
							<td>'.$lang['status'].'</td>
							<td>
								<select name="actdel" class="sw150">
									<option value="1"'.(($edit['act'] == 1) ? ' selected' : '').'>'.$lang['included'].'</option>
									<option value="0"'.(($edit['act'] == 0) ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="deliveditsave" />
								<input type="hidden" name="id" value="'.$edit['did'].'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

				$tm->footer();
			}
			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать настраиваемую доставку, сохранение
		 ----------------------------------------------------*/
		if ($_REQUEST['dn'] == 'deliveditsave')
		{
			global $id, $title, $icon, $descr, $price, $percent, $currency, $data, $country, $state;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_edit']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			$c = $uc = array();
			while ($items = $db->fetchrow($inq))
			{
				$c[$items['countryid']] = $items['countryid'];
			}

			if (is_array($country) AND sizeof($country) > 0)
			{
				foreach ($country as $k => $v)
				{
					if (isset($c[$k]))
					{
						$uc[$k] = $k;
					}
				}
			}

			if (sizeof($uc) < 1)
			{
				$tm->header();
				$tm->error($lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'], $lang['delivery_custom'], $lang['forgot_name']);
				$tm->footer();
			}

			$id = preparse($id, THIS_INT);
			$check = array('fix', 'percent', 'fixpercent');
			$data = in_array($data, $check) ? $data : 'fix';
			$price = (ceil($price) > 0) ? formats($price, 2, '.', '') : '0.00';
			$percent = (ceil($percent) > 0) ? formats($percent, 2, '.', '') : '0.00';

			if (empty($title) OR $data == 'percent' AND $percent == 0 OR $data == 'fixpercent' AND $percent == 0 AND $price == 0)
			{
				$tm->header();
				$tm->error($lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'], $lang['delivery_custom'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$s = array();
				foreach ($state as $k => $v)
				{
					$s[$k] = $k;
				}

				$new = Json::encode(array
							(
								'country' => $uc,
								'state'   => $s,
								'data'    => $data,
								'percent' => $percent
							)
						);

				$db->query
					(
						"UPDATE ".$basepref."_".PERMISS."_delivery SET
						 price    = '".$db->escape($price)."',
						 currency = '".$db->escape($currency)."',
						 icon     = '".$db->escape($icon)."',
						 title    = '".$db->escape($title)."',
						 descr    = '".$db->escape($descr)."',
						 data     = '".$db->escape($new)."',
						 act      = '".$db->escape($actdel)."'
						 WHERE did = '".$id."'"
					);
			}

			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Доставка, удалить
		 --------------------*/
		if ($_REQUEST['dn'] == 'delivdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=delivlist&amp;ops='.$sess['hash'].'">'.$lang['delivery'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_delivery WHERE did = '".$id."'");
				redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery WHERE did = '".$id."'"));

				$yes = 'index.php?dn=delivdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=delivlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['delivery'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
			redirect('index.php?dn=delivlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Все статусы
		 ----------------*/
		if ($_REQUEST['dn'] == 'statlist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['buy_status']
				);

			$tm->header();

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['buy_status'].'</caption>
						<tr>
							<th class="al pw20">'.$lang['all_name'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			$in = Json::decode($conf[PERMISS]['status']);
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					echo '	<tr class="list">
								<td class="al site vm">'.$v.'</td>
								<td class="gov">
									<a href="index.php?dn=statedit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
									<a href="index.php?dn=statdel&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '	</table>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_item'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="title" type="text" size="50" required="required" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="statadd" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить статус, сохранение
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'statadd')
		{
			global $title;

			if ( ! empty($title))
			{
				if (empty($conf[PERMISS]['status'])) {
					$in[1] = $title;
					$ins = Json::encode($in);
				} else {
					$in = Json::decode($conf[PERMISS]['status']);
					$in[] = $title;
					$ins = Json::encode($in);
				}
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'status'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=statlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать статус
		 ------------------------*/
		if ($_REQUEST['dn'] == 'statedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=statlist&amp;ops='.$sess['hash'].'">'.$lang['buy_status'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$in = Json::decode($conf[PERMISS]['status']);

			if (isset($in[$id]))
			{
				$tm->header();

				$v = $in[$id];
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['buy_status'].': '.$lang['all_edit'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="title" type="text" value="'.$v.'" size="50" required="required" /></td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="statsave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input type="hidden" name="id" value="'.$id.'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}

			redirect('index.php?dn=statlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать статус, сохранение
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'statsave')
		{
			global $title, $id;

			$id = preparse($id, THIS_INT);
			$in = Json::decode($conf[PERMISS]['status']);

			if (isset($in[$id]))
			{
				if ( ! empty($title)) {
					$in[$id] = $title;
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'status'");
					$cache->cachesave(1);
				}
			}

			redirect('index.php?dn=statlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить статус
		 ------------------*/
		if ($_REQUEST['dn'] == 'statdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=statlist&amp;ops='.$sess['hash'].'">'.$lang['buy_status'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			$in = Json::decode($conf[PERMISS]['status']);

			if ($ok == 'yes')
			{
				if (isset($in[$id]))
				{
					unset($in[$id]);
					$ins = Json::encode($in);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'status'");
					$cache->cachesave(1);
				}
			}
			else
			{
				$yes = 'index.php?dn=statdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=statlist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $in[$id], $yes, $not);
				$tm->footer();
			}

			redirect('index.php?dn=statlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Все виды платежей
		 ---------------------*/
		if ($_REQUEST['dn'] == 'paylist')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['pay']
				);

			$tm->header();

			$paymentdir = opendir(WORKDIR.'/core/shop/payment/');
			$parr = $payment = array();
			while ($name = readdir($paymentdir))
			{
				if ($name != '.' AND $name != '..' AND strpos($name, '.php'))
				{
					$ext = basename($name, '.php');
					$parr[$ext] = array();
				}
			}
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment order by payposit");
			while ($item = $db->fetchrow($inq))
			{
				$payment[$item['payext']] = $item;
				unset($parr[$item['payext']]);
			}
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['pay'].'</caption>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['status'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			if (is_array($payment))
			{
				foreach ($payment as $k => $v)
				{
					$status = ($payment[$k]['payact'] == 1) ? '<span class="server">'.$lang['included'].'</span>' : '<span class="alternative">'.$lang['not_included'].'</span>';
					$class = ($payment[$k]['payact'] == 1) ? 'work-lite' : 'no-active';
					echo '	<tr class="list">
								<td class="'.$class.' al site">'.$v['paytitle'].'</td>
								<td class="'.$class.'">'.$status.'</td>
								<td class="'.$class.'">
									<input name="posit['.$payment[$k]['payid'].']" size="3" maxlength="3" value="'.$payment[$k]['payposit'].'" type="text" />
								</td>
								<td class="'.$class.' gov">
									<a href="index.php?dn=payedit&amp;id='.$payment[$k]['payid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if ($payment[$k]['payact'] == 1) {
					echo '		<a href="index.php?dn=payact&amp;act=0&amp;id='.$payment[$k]['payid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=payact&amp;act=1&amp;id='.$payment[$k]['payid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" /></a>';
				}
				echo '			<a href="index.php?dn=paydel&amp;id='.$payment[$k]['payid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="dn" value="payup" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';
			if (is_array($parr) AND sizeof($parr) > 0)
			{
				echo '	<div class="sline"></div>
						<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['pay'].': '.$lang['all_submint'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td>
									<select name="id">';
				if (is_array($parr))
				{
					foreach ($parr as $k => $v)
					{
						$t = (isset($lang[$k])) ? $lang[$k] : $k;
						echo '			<option value="'.$k.'">'.$t.'</option>';
					}
				}
				echo '				</select>
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="payadd" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}

			$tm->footer();
		}

		/**
		 * Сохранение позиций
		 -----------------------*/
		if ($_REQUEST['dn'] == 'payup')
		{
			global $posit;

			if (is_array($posit))
			{
				foreach ($posit as $k => $v)
				{
					$id = preparse($k, THIS_INT);
					$v = preparse($v, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS."_payment SET payposit = '".$db->escape($v)."' WHERE payid = '".$id."'");
				}
			}
			redirect('index.php?dn=paylist&amp;ops='.$sess['hash']);
		}

		/**
		 * Изменение состояния оплат
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'payact')
		{
			global $act, $id;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			if ($act == 0 OR $act == 1)
			{
				$db->query("UPDATE ".$basepref."_".PERMISS."_payment SET payact='".$act."' WHERE payid = '".$id."'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=paylist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить вид платежа
		 ------------------------*/
		if ($_REQUEST['dn'] == 'payadd')
		{
			global $id;

			$parr = $payment = array();

			$paymentdir = opendir(WORKDIR.'/core/shop/payment/');
			while ($name = readdir($paymentdir))
			{
				if ($name != '.' AND $name != '..' AND strpos($name, '.php'))
				{
					$ext = basename($name, '.php');
					$parr[$ext] = array();
				}
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment");
			while ($item = $db->fetchrow($inq))
			{
				$payment[$item['payext']] = $item;
			}

			if ( ! empty($id) AND isset($parr[$id]) AND ! isset($payment[$id]))
			{
				include(WORKDIR.'/core/shop/payment/'.$id.'.php');
				$p = new payment;
				$arr = $p->data;

				if (is_array($arr))
				{
					$in = array();
					foreach ($arr as $k => $v)
					{
						$in[$k] = '';
					}
					if (sizeof($in) > 0)
					{
						$db->query
							(
								"INSERT INTO ".$basepref."_".PERMISS."_payment VALUES (
								 NULL,
								 '".$db->escape($id)."',
								 '".$db->escape($id)."',
								 '',
								 '',
								 '',
								 0,
								 0,
								 0
								 )"
							);
					}
				}
			}

			redirect('index.php?dn=paylist&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать вид платежа
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'payedit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=paylist&amp;ops='.$sess['hash'].'">'.$lang['pay'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$payment = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment");
			while ($item = $db->fetchrow($inq))
			{
				$payment[$item['payid']] = $item;
			}

			if (isset($payment[$id]))
			{
				$tm->header();

				$r = $payment[$id];
				$data = Json::decode($r['paydata']);

				@include(WORKDIR.'/core/shop/payment/'.$r['payext'].'.php');
				$p = new payment;
				$arr = $p->data;

				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['pay'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.$r['paytitle'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_name'].'</td>
								<td><input name="paytitle" type="text" size="70" value="'.$r['paytitle'].'" required="required" /></td>
							</tr>
							<tr>
								<td>'.$lang['all_decs'].'</td>
								<td>';
									$tm->textarea('paydescr', 5, 50, $r['paydescr'], 1);
				echo '			</td>
							</tr>';
				foreach ($arr as $k => $v)
				{
					$value = isset($data[$k]) ? $data[$k] : '';
					echo '	<tr>
								<td class="first"><span>*</span> '.(isset($lang[$v['lang']]) ? $lang[$v['lang']] : $v['lang']).'</td>
								<td>';
					if ($v['field'] == 'textarea')
					{
						$tm->textarea('pay['.$k.']', 5, 50, $value, 1);
					}
					elseif ($v['field'] == 'input')
					{
						$readonly = '';
						if (isset($v['url']) AND isset($v['urlcpu']))
						{
							$readonly = ' readonly="readonly" class="readonly"';
							if (defined('SEOURL'))
							{
								$value = $conf['site_url'].'/'.str_replace(array('{mod}', '{id}', '{suf}'), array(PERMISS, $id, SUF), $v['urlcpu']);
							}
							else
							{
								$value = $conf['site_url'].'/'.str_replace(array('{mod}', '{id}'), array(PERMISS, $id), $v['url']);
							}
						}
						echo '		<input type="text" name="pay['.$k.']" value="'.$value.'" size="50"'.$readonly.' required="required" />';
					}
					elseif ($v['field'] == 'select')
					{
						echo '		<select name="pay['.$k.']" class="sw150">';
						if (is_array($v['value']))
						{
							foreach ($v['value'] as $vk => $vv)
							{
								echo '	<option value="'.$vk.'"'.(($value == $vk) ? ' selected' : '').'>'.((isset($lang[$vv])) ? $lang[$vv] : $vv).'</option>';
							}
						}
						echo '		</select>';
					}
					if ( ! empty($v['hint']))
					{
						if (isset($lang[$v['hint']]))
						{
							$tm->outhint($lang[$v['hint']]);
						}
						else
						{
							$tm->outhint($v['hint']);
						}
					}
					echo '		</td>
							</tr>';
				}
				$in = Json::decode($conf[PERMISS]['status']);
				echo '		<tr>
								<td>'.$lang['all_icon'].'</td>
								<td>
									<input name="icon" id="icon" size="50" type="text" value="'.$r['payicon'].'" />&nbsp;
									<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
								</td>
							</tr>
							<tr>
								<td>'.$lang['status_order'].'</td>
								<td>
									<select name="paystatus" class="sw250">';
				if (is_array($in))
				{
					foreach ($in as $k => $v)
					{
						echo '			<option value="'.$k.'"'.(($r['paystatus'] == $k) ? ' selected' : '').'>'.$v.'</option>';
					}
				}
				echo '				</select>
								</td>
							</tr>
							<tr>
								<td>'.$lang['status'].'</td>
								<td>
									<select name="payact" class="sw150">
										<option value="1"'.(($r['payact'] == 1) ? ' selected' : '').'>'.$lang['included'].'</option>
										<option value="0"'.(($r['payact'] == 0) ? ' selected' : '').'>'.$lang['not_included'].'</option>
									</select>
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="paysave" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input type="hidden" name="id" value="'.$id.'" />
									<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
			redirect('tax.php?dn=index&ops='.$sess['hash']);
		}

		/**
		 * Редактировать вид платежа, сохранение
		 -----------------------------------------*/
		if ($_REQUEST['dn'] == 'paysave')
		{
			global $pay, $id, $payact, $icon, $paystatus, $paytitle, $paydescr, $paycur;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=paylist&amp;ops='.$sess['hash'].'">'.$lang['pay'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$payact = preparse($payact, THIS_INT);
			$paystatus = preparse($paystatus, THIS_INT);
			$paytitle = preparse($paytitle, THIS_TRIM, 0, 255);
			$payment = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment");
			while ($item = $db->fetchrow($inq))
			{
				$payment[$item['payid']] = $item;
			}

			if (isset($payment[$id]))
			{
				$r = $payment[$id];

				include(WORKDIR.'/core/shop/payment/'.$r['payext'].'.php');
				$p = new payment;

				if ($p->save($pay) OR preparse($paytitle, THIS_EMPTY) == 1)
				{
					$tm->header();
					$tm->error($lang['pay'], $lang['all_edit'], $lang['forgot_name']);
					$tm->footer();
				}
				else
				{
					$in = array();
					foreach ($pay as $k => $v)
					{
						$in[$k] = htmlspecialchars($v, ENT_QUOTES, $conf['langcharset'], true);
					}
					if (sizeof($in) > 0)
					{
						$payact = ($payact == 1) ? 1 : 0;
						$db->query
							(
								"UPDATE ".$basepref."_".PERMISS."_payment SET
								 paytitle  = '".$db->escape($paytitle)."',
								 paydescr  = '".$db->escape($paydescr)."',
								 payicon   = '".$db->escape($icon)."',
								 paydata   = '".$db->escape(Json::encode($in))."',
								 paystatus = '".$db->escape($paystatus)."',
								 payact    = '".$db->escape($payact)."'
								 WHERE payid = '".$id."'"
							);
					}
				}
			}

			redirect('index.php?dn=paylist&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить вид оплаты
		 -----------------------*/
		if ($_REQUEST['dn'] == 'paydel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=paylist&amp;ops='.$sess['hash'].'">'.$lang['pay'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			if ($ok == 'yes') {
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_payment WHERE payid = '".$id."'");
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment WHERE payid = '".$id."'"));
				$t = (isset($lang[$item['payext']])) ? $lang[$item['payext']] : $item['payext'];

				$yes = 'index.php?dn=paydel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=paylist&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['pay'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], $t, $yes, $not);
				$tm->footer();
			}
			redirect('index.php?dn=paylist&amp;ops='.$sess['hash']);
		}

		/**
		 * Все заказы
		 --------------*/
		if ($_REQUEST['dn'] == 'ordlist')
		{
			global $selective, $sid, $nu, $p, $cat, $s, $l, $ajax, $filter, $fid, $atime;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['orders']
				);

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;
			if (preparse($ajax, THIS_INT) == 0) {
				$tm->header();
				echo '<div id="ajaxbox">';
			} else {
				echo '<script>$(function(){$("img, a").tooltip();});</script>';
			}

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$sort = array('oid', 'public', 'price');
			$limit = array('desc', 'asc');
			$payment = $delivery = array();
			$s = (in_array($s, $sort)) ? $s : 'oid';
			$l = (in_array($l, $limit)) ? $l : 'desc';
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$fu = $sql = $link = $sel = '';
			$in = Json::decode($conf[PERMISS]['status']);
			$atime = preparse($atime,THIS_INT);
			if (isset($in[$sid])) {
				$sql = " WHERE statusid = '".preparse($sid, THIS_INT)."'";
				$link = "&amp;sid=".preparse($sid, THIS_INT);
			} else {
				$sql = '';
				$link = '&amp;sid=0';
			}
			$fid = preparse($fid, THIS_INT);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment order by payposit");
			while ($item = $db->fetchrow($inq)) {
				$payment[$item['payid']] = $item;
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_delivery ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq)) {
				$delivery[$item['did']] = $item;
			}

			$myfilter = array
			(
				'public' => array('public', 'input_date', 'date'),
				'price'  => array('price', 'price', 'intval')
			);

			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0) {
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ', $insert);
					$fu = '&fid='.$item['fid'];
				}
			}
			else
			{
				if (isset($filter) AND is_array($filter))
				{
					$sw = array();
					foreach ($filter as $k => $v)
					{
						if (isset($myfilter[$k]))
						{
							$f = $myfilter[$k];
							if ($f[2] == 'input' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'checkbox' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'date' AND is_array($v)) {
								if(isset($v[0]) AND ! empty($v[0])){
									$sw[] = $f[0]." > '".$db->escape(ReDate($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." < '".$db->escape(ReDate($v[1]))."'";
								}
							}
							if ($f[2] == 'intval' AND is_array($v)) {
								if(isset($v[0]) AND ! empty($v[0])) {
									$sw[] = $f[0]." > '".$db->escape(intval($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." < '".$db->escape(intval($v[1]))."'";
								}
							}
						}
					}
					if (sizeof($sw) > 0)
					{
						$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ', $sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$fid = $db->insertid();
						if ($fid > 0) {
							$fu = '&fid='.$fid;
						}
					}
				}
			}
			$link.= $fu;
			$a = ($ajaxlink) ? '&amp;ajax=1' : '';
			$revs = $link.$a.'&amp;nu='.$nu.'&amp;s='.$s.'&amp;l='.(($l=='desc') ? 'asc' : 'desc');
			$rev =  $link.$a.'&amp;nu='.$nu.'&amp;l=desc&amp;s=';
			$link.= $a.'&amp;s='.$s.'&amp;l='.$l;
			$links = 'index.php?dn=ordlist'.$a.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'&amp;sid=';
			$c = $db->fetchrow($db->query("SELECT COUNT(oid) AS total FROM ".$basepref."_".PERMISS."_order".$sql.""));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);
			if ($atime != 0) {
				$sql.= " WHERE (public  >= '".$atime."')";
			}
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_order".$sql." ORDER BY ".$s." ".$l." LIMIT ".$sf.", ".$nu);
			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_order'.$sql, 'oid', 'orders', 'index'.$link, $nu, $p, $sess, $ajaxlink);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=ordlist&amp;p=".$p."&amp;ops=".$sess['hash'].$link, $nu, $ajaxlink);

			// Поиск по фильтру
			$tm->filter('index.php?dn=ordlist&amp;ops='.$sess['hash'], $myfilter, $modname[PERMISS]);

			$sels = '';
			if (is_array($in))
			{
				foreach ($in as $k => $v)
				{
					$sels.= '<option value="'.$links.$k.'"'.(($k == $sid) ? ' selected="selected"' : '').'> '.$v.' </option>';
				}
			}
			echo '	<script>
						var ajax = "'.$ajaxlink.'";
					</script>';
			if ($ajaxlink)
			{
				echo '	<script>
						$(document).ready(function() {
							$.ajaxSetup({cache:false, async:false});
						});
						</script>';
			}
			$c = Json::decode($conf[PERMISS]['currencys']);
			$cur = (isset($c[$conf[PERMISS]['currency']])) ? $c[$conf[PERMISS]['currency']] : array('value' => 1, 'title' => '', 'symbol_left' => '', 'symbol_right' => '', 'decimal' => 2, 'decimalpoint' => '.', 'thousandpoint' => ',');
			$arr = $ins = $goods = $opt = array();
			while ($item = $db->fetchrow($inq))
			{
				$arr[] = $item;
				$ids = Json::decode($item['orders']);
				foreach ($ids as $k => $v)
				{
					$ins[$k] = $k;
				}
			}
			$in = implode(',', $ins);
			if ( ! empty($in))
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id IN (".$db->escape($in).")");
				while ($item = $db->fetchrow($inq))
				{
					$goods[$item['id']] = $item;
				}
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option");
			while ($item = $db->fetchrow($inq)) {
				$opt[$item['oid']]['title'] = $item['title'];
				$opt[$item['oid']]['option'] = array();
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_option_value");
			while ($item = $db->fetchrow($inq)) {
				$opt[$item['oid']]['option'][$item['vid']] = $item;
			}

			$country = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			while ($itemc = $db->fetchrow($inq)) {
				$country[$itemc['countryid']] = $itemc;
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
			while ($itemc = $db->fetchrow($inq)) {
				$country[$itemc['countryid']]['region'][$itemc['regionid']] = $itemc['regionname'];
			}

			echo '	<div class="section">
					<form action="index.php" id="total-form" name="total-form" method="post">
					<table id="list" class="work ord">
						<caption>'.$modname[PERMISS].': '.$lang['orders'].'</caption>
						<tr>
							<td colspan="8">
								'.$lang['order_status'].':&nbsp;
								<select name="sid" id="sid" onchange="$.changeselect(this);">
									<option value="'.$links.'0"'.(($sid == 0) ? ' selected="selected"' : '').'>'.$lang['all_all'].' '.$lang['orders'].'</option>
									'.$sels.'
								</select>
							</td>
						</tr>
						<tr><td colspan="8">'.$amount.'</td></tr>
						<tr>
							<th'.ordsort('oid').'>&#8470;</th>
							<th class="work-no-sort">'.$lang['buyer'].'</th>
							<th class="work-no-sort">'.$lang['products'].'</th>
							<th'.ordsort('public').'>'.$lang['input_date'].'</th>
							<th'.ordsort('price').'>'.$lang['sum'].'&nbsp; &#8260; &nbsp;'.$conf[PERMISS]['currency'].'</th>
							<th class="work-no-sort">'.$lang['order_status'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox" /></th>
						</tr>';
			$sin = Json::decode($conf[PERMISS]['status']);
			foreach ($arr as $k => $item)
			{
				$st = isset($sin[$item['statusid']]) ? $sin[$item['statusid']] : ' &#8212; ';
				$co = isset($country[$item['countryid']]) ? $country[$item['countryid']]['countryname'] : ' &#8212; ';
				$re = isset($country[$item['countryid']]['region'][$item['regionid']]) ? $country[$item['countryid']]['region'][$item['regionid']] : ' &#8212; ';
				echo '	<tr class="list">
							<td class="ac pw5">'.$item['oid'].'</td>
							<td class="pw20">
								<div class="ipad vars">
									<a href="'.ADMPATH.'/mod/user/index.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'">'.$item['firstname'].' '.$item['surname'].'</a>
									<img onclick="$(\'.buyer'.$item['oid'].'_minus,.buyer'.$item['oid'].'_plus\').toggle();" class="vm image-toggle buyer'.$item['oid'].'_plus" src="'.ADMPATH.'/template/images/plus.png" alt="'.$lang['order_detail'].'" />
									<img onclick="$(\'.buyer'.$item['oid'].'_minus,.buyer'.$item['oid'].'_plus\').toggle();" class="vm image-toggle buyer'.$item['oid'].'_minus none" src="'.ADMPATH.'/template/images/minus.png" alt="'.$lang['close_window'].'" />
								</div>
								<div class="buyer'.$item['oid'].'_plus buyer'.$item['oid'].'_minus none infosexample">
									'.$lang['pay'].': '.((isset($payment[$item['payid']])) ? $payment[$item['payid']]['paytitle'] : ' &#8212; ').'<br>
									'.$lang['delivery'].': '.((isset($delivery[$item['delid']])) ? $delivery[$item['delid']]['title'] : ' &#8212; ').'<br>
									'.$lang['country'].': '.$co.'<br>
									'.$lang['state'].': '.$re.'<br>
									'.$lang['city'].': '.$item['city'].'<br>
									'.$lang['zip'].': '.$item['zip'].'<br>
									'.$lang['adress'].': '.$item['adress'].'<br>
									'.$lang['phone'].': '.$item['phone'].'<br>
									'.$lang['order_notice'].': '.(( ! empty($item['comment'])) ? $item['comment'] : ' &#8212; ').'
								</div>
							</td>
							<td class="pw20">';

				$ids = Json::decode($item['orders']);
				$i = 1;
				foreach ($ids as $k => $v)
				{
					if (isset($goods[$k]))
					{
						echo '	<div class="ipad vars">
								<a href="index.php?dn=edit&amp;id='.$goods[$k]['id'].'&amp;ops='.$sess['hash'].'">'.$goods[$k]['title'].'</a>';
						if (isset($v['option']) AND is_array($v['option']))
						{
							echo '	<img onclick="$(\'.products'.$item['oid'].'-'.$i.'_minus,.products'.$item['oid'].'-'.$i.'_plus\').toggle();" class="vm image-toggle products'.$item['oid'].'-'.$i.'_plus" src="'.ADMPATH.'/template/images/plus.png" alt="'.$lang['order_detail'].'" />
									<img onclick="$(\'.products'.$item['oid'].'-'.$i.'_minus,.products'.$item['oid'].'-'.$i.'_plus\').toggle();" class="vm image-toggle products'.$item['oid'].'-'.$i.'_minus none" src="'.ADMPATH.'/template/images/minus.png" alt="'.$lang['close_window'].'" />';
						}
						echo '	</div>';

						if (isset($v['option']) AND is_array($v['option']))
						{
							echo '	<div class="products'.$item['oid'].'-'.$i.'_plus products'.$item['oid'].'-'.$i.'_minus none infosexample">';
							$new = array();
							foreach($v['option'] as $ko => $vo)
							{
								if (isset($opt[$ko]['option'][$vo])) {
									$new[] = $opt[$ko]['title'].': '.$opt[$ko]['option'][$vo]['title'];
								}
							}
							if (sizeof($new) > 0) {
								echo implode('<br>',$new);
							}
							echo '	</div>';
						}
						$i ++;
					}
				}
				echo '		</td>
							<td class="pw15">'.format_time($item['public'], 1 ,1).'';
				if ($item['public'] >= (TODAY - 86400)) {
					echo '		<img class="fr" src="'.ADMPATH.'/template/images/totalinfo.gif" alt="'.$lang['add_today'].'" />';
				}
				echo '		</td>
							<td class="pw15">'.formats($item['price'], 2, '.', '').'</td>
							<td class="pw15">'.$st.'</td>
							<td class="gov pw10">
								<a href="index.php?dn=orddel&p='.$p.'&nu='.$nu.$link.'&oid='.$item['oid'].'&ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
							<td class="mark pw5">
								<input type="checkbox" name="arr['.$item['oid'].']" value="yes" />
							</td>
						</tr>';
			}
			echo '		<tr>
							<td colspan="8">
								'.$lang['all_mark_work'].':&nbsp;
								<select name="workname">
									<option value="orddel">'.$lang['all_delet'].'</option>
									<option value="status">'.$lang['order_status'].'</option>
								</select>
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="s" value="'.$s.'" />
								<input type="hidden" name="l" value="'.$l.'" />';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'" />';
			}
			echo '				<input type="hidden" name="dn" value="ordwork" />
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
							</td>
						</tr>
						<tr><td colspan="8">'.$pages.'</td></tr>
					</table>
					</form>
					</div>';
			if (preparse($ajax, THIS_INT) == 0)
			{
				echo '</div>';
				$tm->footer();
			}
		}

		/**
		 * Массовая обработка
		 ------------------------*/
		if ($_REQUEST['dn'] == 'ordwork')
		{
			global $arr, $workname, $p, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=ordlist&amp;ops='.$sess['hash'].'">'.$lang['orders'].'</a>',
					$lang['array_control']
				);

			if (preparse($arr, THIS_ARRAY) == 1)
			{
				$temparray = $arr;
				$count = count($temparray);
				$hidden = '';
				foreach ($arr as $key => $id) {
					$hidden.= '<input type="hidden" name="arr['.$key.']" value="'.$key.'" />';
				}
				$p = preparse($p, THIS_INT);
				$s = preparse($s, THIS_TRIM, 1, 7);
				$l = preparse($l, THIS_TRIM, 1, 4);
				$fid = preparse($fid, THIS_INT);
				$h = '	<input type="hidden" name="p" value="'.$p.'" />
						<input type="hidden" name="nu" value="'.$nu.'" />
						<input type="hidden" name="s" value="'.$s.'" />
						<input type="hidden" name="l" value="'.$l.'" />
						<input type="hidden" name="fid" value="'.$fid.'" />
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />';

				// Удаление
				if ($workname == 'orddel')
				{
					$tm->header();
					echo '	<form action="index.php" method="post">
							<div class="section">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_delet'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="ordarrdel" />
										<input class="main-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="main-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				// Статус
				}
				elseif ($workname == 'status')
				{
					$tm->header();
					echo '	<form action="index.php" method="post">
							<div class="section">
							<table class="work">
								<caption>'.$lang['array_control'].': '.$lang['order_status'].' ('.$count.')</caption>
								<tr>
									<td class="ac">
										<select name="sid">';
					$in = Json::decode($conf[PERMISS]['status']);
					if (is_array($in))
					{
						foreach ($in as $k => $v)
						{
							echo '			<option value="'.$k.'">'.$v.'</option>';
						}
					}
					echo '				</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="ordarrstatus" />
										<input class="main-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="main-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</div>
							</form>';
					$tm->footer();
				}
			}
			else
			{
				redirect('index.php?dn=ordlist&ops='.$sess['hash']);
			}
		}

		/**
		 * Массовое удаление (сохранение)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'ordarrdel')
		{
			global $arr, $ok, $p, $nu, $s, $l, $fid;

			if (preparse($arr, THIS_ARRAY) == 1 AND sizeof($arr) > 0)
			{
				$in = implode(',', $arr);
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_order WHERE oid IN (".$db->escape($in).")");
			}

			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=ordlist&ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&s='.$s : '';
			$redir.= ( ! empty($l)) ? '&l='.$l : '';
			$redir.= ($fid > 0) ? '&fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое изменение статуса (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'ordarrstatus')
		{
			global $arr, $ok, $p, $nu, $s, $l, $fid, $sid;

			$fid = preparse($fid, THIS_INT);
			$sid = preparse($sid, THIS_INT);

			if (preparse($arr, THIS_ARRAY) == 1 AND sizeof($arr) > 0)
			{
				$sin = Json::decode($conf[PERMISS]['status']);
				if (is_array($sin) AND isset($sin[$sid]))
				{
					$in = implode(',', $arr);
					$db->query("UPDATE ".$basepref."_".PERMISS."_order SET statusid = '".$sid."' WHERE oid IN (".$db->escape($in).")");
				}
			}

			$redir = 'index.php?dn=ordlist&ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&s='.$s : '';
			$redir.= ( ! empty($l)) ? '&l='.$l : '';
			$redir.= ($fid > 0) ? '&fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Удаление
		 --------------*/
		if ($_REQUEST['dn'] == 'orddel')
		{
			global $oid, $ok, $p, $nu, $s, $l;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=ordlist&amp;ops='.$sess['hash'].'">'.$lang['orders'].'</a>',
					$lang['all_delet']
				);

			$oid = preparse($oid, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_order WHERE oid = '".$oid."'");

				$redir = 'index.php?dn=ordlist&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p,THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';
				$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
				$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';

				redirect($redir);
			}
			else
			{
				$yes = 'index.php?dn=orddel&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;&amp;nu='.$nu.'&amp;oid='.$oid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=ordlist&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $lang['order'].' No '.preparse_un($oid), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Все теги
		 -------------*/
		if ($_REQUEST['dn'] == 'tag')
		{
			global $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['all_tags']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$c  = $db->fetchrow($db->query("SELECT COUNT(tagid) AS total FROM ".$basepref."_".PERMISS."_tag"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag ORDER BY tagid DESC LIMIT ".$sf.", ".$nu);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_tag', 'tagid', 'index', 'tag', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages('index.php?dn=tag&amp;p='.$p.'&amp;ops='.$sess['hash'], $nu);

			echo '	<script>
						var all_cpu   = "'.$lang['all_cpu'].'";
						var all_name  = "'.$lang['all_name'].'";
						var all_popul = "'.$lang['all_popul'].'";
					</script>';
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_tags'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th class="ar pw20">'.$lang['all_name'].'</th>
							<th>'.$lang['all_cpu'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>'.$lang['all_popul'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="site">'.$item['tagword'].'</td>
							<td class="server">'.$item['tagcpu'].'</td>
							<td>';
				if( ! empty($item['icon'])) {
					echo '		<img src="'.WORKURL.'/'.$item['icon'].'" alt="'.preparse_un($item['tagword']).'" style="max-width: 36px; max-height: 27px; " />';
				}
				echo '		</td>
							<td><input type="text" name="ratingid['.$item['tagid'].']" value="'.$item['tagrating'].'" size="3" maxlength="3" /></td>
							<td class="gov">
								<a href="index.php?dn=tagedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$item['tagid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=tagdel&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$item['tagid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="5">
								<input type="hidden" name="dn" value="tagsetsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
						<tr><td colspan="5">'.$pages.'</td></tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['all_tags'].': '.$lang['all_submint'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="tagword" id="tagword" size="70" required="required" />
							</td>
						</tr>';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="tagcpu" id="cpu" size="70" />';
								$tm->outtranslit('tagword', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="custom" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('tagdesc', 5, 50, '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_popul'].'</td>
							<td><input type="text" name="tagrating" size="25" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="tagaddsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
                          </td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Все теги, сохранение
		 -------------------------*/
		if ($_REQUEST['dn'] == 'tagsetsave')
		{
			global $ratingid, $p, $nu;

			if (preparse($ratingid, THIS_ARRAY) == 1)
			{
				this_tagup($ratingid, PERMISS);
			}

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';
			redirect($redir);
		}

		/**
		 * Добавление метки (сохранение)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'tagaddsave')
		{
			global $tagcpu, $tagword, $custom, $keywords, $descript, $icon, $tagdesc, $tagrating, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>',
					$lang['all_add']
				);

			$tagword = preparse($tagword, THIS_TRIM, 0, 255);
			$icon    = preparse($icon, THIS_TRIM);
			$tagcpu = preparse($tagcpu, THIS_TRIM, 0, 255);
			$tagdesc = preparse($tagdesc, THIS_TRIM);
			$custom = preparse($custom, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);

			if (preparse($tagword, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['all_tags'], $lang['all_add'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($tagcpu, THIS_EMPTY) == 1)
				{
					$tagcpu = cpu_translit($tagword);
				}

				$inqure = $db->query
							(
								"SELECT tagword, tagcpu FROM ".$basepref."_".PERMISS."_tag
								 WHERE tagword = '".$db->escape($tagword)."' OR tagcpu = '".$db->escape($tagcpu)."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['all_add'], $tagword, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$tagrating = ( ! empty($tagrating)) ? preparse($tagrating, THIS_INT) : 0;
			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_tag VALUES (
					 NULL,
					 '".$db->escape($tagcpu)."',
					 '".$db->escape(preparse_sp($tagword))."',
					 '".$db->escape(preparse_sp($tagdesc))."',
					 '".$db->escape(preparse_sp($custom))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape($icon)."',
					 '".$tagrating."'
					 )"
				);

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Редактировать метку
		 ----------------------*/
		if ($_REQUEST['dn'] == 'tagedit')
		{
			global $tagid, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$tagid = preparse($tagid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_tags'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.preparse_un($item['tagword']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="tagword" id="tagword" size="70" value="'.preparse_un($item['tagword']).'" required="required" />
							</td>
						</tr>';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="tagcpu" id="cpu" size="70" value="'.$item['tagcpu'].'" />';
								$tm->outtranslit('tagword', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="custom" size="70" value="'.preparse_un($item['custom']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('tagdesc', 5, 50, $item['tagdesc'], 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_popul'].'</td>
							<td><input type="text" name="tagrating" size="25" value="'.$item['tagrating'].'" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="tageditsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="tagid" value="'.$tagid.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать метку (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'tageditsave')
		{
			global $tagid, $tagword, $tagcpu, $custom, $keywords, $descript, $icon, $tagdesc, $tagrating, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>',
					$lang['all_edit']
				);

			$tagword = preparse($tagword, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);
			$tagid = preparse($tagid, THIS_INT);

			if (preparse($tagword, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_tag'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($tagcpu, THIS_EMPTY) == 1)
				{
					$tagcpu = cpu_translit($tagword);
				}

				$inqure = $db->query
							(
								"SELECT tagid, tagcpu, tagword FROM ".$basepref."_".PERMISS."_tag
								 WHERE (tagcpu = '".$db->escape($tagcpu)."' OR tagword = '".$db->escape($tagword)."')
								 AND tagid <> '".$tagid."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['edit_tag'], $tagword, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$tagrating = ( ! empty($tagrating)) ? preparse($tagrating, THIS_INT) : 0;
			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_tag SET
					 tagcpu    = '".$db->escape($tagcpu)."',
					 tagword   = '".$db->escape(preparse_sp($tagword))."',
					 tagdesc   = '".$db->escape(preparse_sp($tagdesc))."',
					 custom    = '".$db->escape(preparse_sp($custom))."',
					 keywords  = '".$db->escape(preparse_sp($keywords))."',
					 descript  = '".$db->escape(preparse_sp($descript))."',
					 icon      = '".$db->escape($icon)."',
					 tagrating = '".$db->escape($tagrating)."'
					 WHERE tagid = '".$tagid."'"
				);

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Удаление тегов
		 ------------------*/
		if ($_REQUEST['dn'] == 'tagdel')
		{
			global $p, $nu, $ok, $tagid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>',
					$lang['all_delet']
				);

			$tagid = preparse($tagid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'");
				$db->increment(PERMISS.'_tag');

				$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? '&amp;nu='.preparse($nu, THIS_INT) : '';
				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'"));
				$yes = 'index.php?dn=tagdel&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$tagid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=tag&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_tags'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'], $item['tagword'], $yes, $not);
				$tm->footer();
			}
		}

        /**
         * Условия соглашения
         ----------------------*/
		if ($_REQUEST['dn'] == 'agreement')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					$lang['agreement']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['agree'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' ORDER BY setid ASC");
			while ($itemset = $db->fetchrow($inqset))
			{
				if ($itemset['setname'] == 'agreement')
				{
					echo '	<tr>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
						</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="saveagreement" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input accesskey="s" class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

        /**
         * Соглашение (сохранение)
         ---------------------------*/
		if ($_REQUEST['dn'] == 'saveagreement')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_product'].'</a>',
					'<a href="index.php?dn=agreement&amp;ops='.$sess['hash'].'">'.$lang['agreement'].'</a>',
					$lang['all_change']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($lang['agreement'], $lang['all_change'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=agreement&amp;ops='.$sess['hash']);
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
