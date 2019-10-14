<?php
/**
 * File:        /admin/core/classes/Template.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Template
 */
class Template
{
	public $top;
	public $bot;
	public $powered = 0;
	public $platform_id = 0;
	public $forms = NULL;
	public $links = NULL;
	public $filter = NULL;

	public $display_menu = 'table-cell';
	public $type_arrow = 'closed';

	public $manuale  = array();
	public $unmanule = array();

	public function __construct()
	{
		if (isset($_COOKIE['menup']) AND $_COOKIE['menup'] == 'closed')
		{
			$this->display_menu = 'none';
			$this->type_arrow = 'open';
		}
		else
		{
			$this->type_arrow = 'closed';
		}

		if (isset($_COOKIE[PCOOKIE]))
		{
			list($this->platform_id) = unserialize($_COOKIE[PCOOKIE]);
		}
	}

	/**
	 * Шапка шаблона
	 */
	function header()
	{
		global $dn, $conf, $sess, $wysiwyg, $lang, $template, $PLATFORM, $ADMIN_ID, $CHECK_ADMIN, $ADMIN_PERM_ARRAY;

		$this->gzip_start();

		$sess['skin'] = (is_dir(ADMDIR.'/template/skin/'.$sess['skin'])) ? $sess['skin'] : SKIN_DEF;

		header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
		header('Content-Type: text/html; charset='.$conf['langcharset']);
		header('X-Powered-By: Danneo CMS '.$conf['version']);

		$template['charset'] = $conf['langcharset'];
		$template['version'] = $conf['version'];

		$template['apanel'] = ADMPATH.'/';
		$template['def_site'] = DEF_SITE;
		$template['hash'] = $sess['hash'];
		$template['notice'] = $this->globalnotice();
		$template['platform'] = (isset($PLATFORM[$this->platform_id]) ? ' <img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/arrow.png" alt="" /> '.$PLATFORM[$this->platform_id]['name'] : ' <img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/arrow.png" alt="" /> '.$conf['site']);

		$template['wait_up'] = $lang['wait_up'];
		$template['all_save'] = $lang['all_save'];
		$template['all_error'] = $lang['all_error'];
		$template['goto_site'] = $lang['goto_site'];
		$template['spaw_edit'] = $lang['spaw_edit'];
		$template['goto_index'] = $lang['goto_index'];
		$template['filebrowser'] = $lang['filebrowser'];
		$template['goto_logout'] = $lang['goto_logout'];
		$template['re_platform'] = $lang['re_platform'];
		$template['control_panel'] = $lang['control_panel'];
		$template['type_arrow'] = $this->type_arrow;
		$template['openclose'] = $lang['openclose'];

		$crumb = null;
		if (isset($template['breadcrumb']))
		{
			if (is_array($template['breadcrumb']))
			{
				$end = array_pop($template['breadcrumb']);
				foreach ($template['breadcrumb'] as $bread)
				{
					$crumb.= $bread.'<img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/spacer.gif" alt="" />';
				}
				$crumb.= $end;
			}
			else
			{
				$crumb = $template['breadcrumb'];
			}
		}
		else
		{
			$crumb = '<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>';
		}

		if ($_SERVER['PHP_SELF'] == ADMPATH.'/index.php' AND $dn == 'index')
		{
			$template['breadcrumb'] = '<h2>'.$lang['desktop'].'</h2>';
		}
		else
		{
			$template['breadcrumb'] = $crumb;
		}

		$this->unmanule['platform'] = 'no';
		$this->unmanule['icon'] = ($sess['icon'] == 'yes') ? 'yes' : 'no';
		$this->unmanule['form'] = ( ! empty($this->forms)) ? 'yes' : 'no';
		$this->unmanule['filter'] = ( ! empty($this->filter)) ? 'yes' : 'no';
		$this->unmanule['title'] = ( ! empty($this->filter) OR ! empty($this->forms)) ? 'yes' : 'no';
		$this->unmanule['wysiwyg'] = ($wysiwyg == 'yes') ? 'yes' : 'no';
		$this->unmanule['filebrowser'] = (in_array($ADMIN_ID, $CHECK_ADMIN['admid']) OR is_array($ADMIN_PERM_ARRAY) AND in_array('filebrowser', $ADMIN_PERM_ARRAY)) ? 'yes' : 'no';

		$option_platform = null;
		if ($_SERVER['PHP_SELF'] != ADMPATH.'/index.php' AND $_SERVER['PHP_SELF'] != ADMPATH.'/system/platform/index.php')
		{
			if (preparse($PLATFORM, THIS_ARRAY) == 1 AND in_array('platform', $ADMIN_PERM_ARRAY))
			{
				$this->unmanule['platform'] = 'yes';
				foreach ($PLATFORM as $key => $val)
				{
					$selected = ((preparse($this->platform_id, THIS_INT) > 0 AND isset($PLATFORM[$this->platform_id]) AND $key == $this->platform_id) ? ' selected' : '');
					$option_platform.= '	<option value="'.$key.'"'.$selected.'>'.$val['name'].'</option>';
				}
			}
		}
		$template['option_platform'] = $option_platform;

		$template['forms'] = $this->forms;
		$template['links'] = $this->links;
		$template['filter'] = $this->filter;

		$template['aside_menu'] = null;
		$this->aside_menu();

		// Print header
		echo $this->parse($template, $this->parsein($this->create('top')));
	}

	/**
	 * Подвал шаблона
	 */
	function footer()
	{
		global $conf, $lang, $sess, $template;

		$template['version'] = $conf['version'];
		$template['support'] = $lang['support'];
		$template['newyear'] = NEWYEAR;

		$template['links'] = $this->links;

		// Print footer
		echo $this->parse($template, $this->parsein($this->create('bot')));

		$this->gzip_end();
		exit();
	}

	/**
	 * Верхнее меню, в разделах
	 */
	function this_menu($link, $filter = null, $form = null)
	{
		$this->links = $link;
		$this->forms = $form;
		$this->filter = $filter;
	}

	/**
	 * Блочное меню меню
	 */
	function aside_menu()
	{
		global $dn, $mods, $sess, $lang, $template, $ADMIN_PERM, $ADMIN_ID, $CHECK_ADMIN;

		$this->unmanule['icon'] = ($sess['icon'] == 'yes') ? 'yes' : 'no';

		$template['type_arrow'] = $this->type_arrow;
		$template['openclose'] = $lang['openclose'];
		$template['menu_content'] = $lang['all_content'];
		$template['menu_system'] = $lang['all_system'];
		$template['menu_server'] = $lang['manage_server'];

		$content = $system = null;
		$blocklink = $this->parsein($this->create('block.link'));

		// Menu mods
		foreach ($this->mod_menu() as $file_menu)
		{
			include ($file_menu);
			if (
				isset($block) AND
				is_array($block) AND
				isset($block['id']) AND
				isset($mods[$block['id']])
			) {
				$rows = null;
				$class = (isset($_COOKIE['openmenu']) AND $_COOKIE['openmenu'] == $block['id']) ? ' menupanelopen' : '';
				$title = (isset($block['title']) ? $block['title'] : $block['id']);
				$icon = $this->icon_menu('/template/skin/'.$sess['skin'].'/images/menu/'.$block['id'].'.png');
				$display = (isset($_COOKIE['openmenu']) AND $_COOKIE['openmenu'] == $block['id']) ? 'block' : 'none';

				if (isset($block['link']) AND is_array($block['link']))
				{
					foreach ($block['link'] as $url => $name)
					{
						parse_str(parse_url($url, PHP_URL_QUERY), $nod);

						$box = is_array($name) ? $name[1] : null;
						$link_name = is_array($name) ? $name[0] : $name;
						$active = (isset($dn) AND $nod['dn'] == $dn AND $_SERVER['PHP_SELF'] == parse_url($url, PHP_URL_PATH)) ? ' active' : '';

						$rows.= $this->parse(array
							(
								'url'    => $url,
								'box'    => $box,
								'name'   => $link_name,
								'active' => $active
							),
							$this->manuale['rows']);
					}
				}
				$content.= $this->parse(array
					(
						'id'      => $block['id'],
						'icon'    => $icon,
						'title'   => $title,
						'class'   => $class,
						'display' => $display,
						'rows'    => $rows
					),
					$blocklink);
			}
		}

		// Menu system
		foreach ($this->sys_menu() as $file_menu)
		{
			include ($file_menu);
			if (
				isset($block) AND
				is_array($block) AND
				isset($block['id']) AND
				isset($block['title'])
			) {
				$rows = null;
				$class = (isset($_COOKIE['openmenu']) AND $_COOKIE['openmenu'] == $block['id']) ? ' menupanelopen' : '';
				$title = (isset($block['title']) ? $block['title'] : $block['id']);
				$icon = $this->icon_menu('/template/skin/'.$sess['skin'].'/images/menu/'.$block['id'].'.png');
				$display = (isset($_COOKIE['openmenu']) AND $_COOKIE['openmenu'] == $block['id']) ? 'block' : 'none';

				if (isset($block['link']) AND is_array($block['link']))
				{
					foreach ($block['link'] as $url => $name)
					{
						parse_str(parse_url($url, PHP_URL_QUERY), $nod);

						$box = is_array($name) ? $name[1] : null;
						$link_name = is_array($name) ? $name[0] : $name;
						$active = (isset($dn) AND ! empty($nod) AND $nod['dn'] == $dn AND $_SERVER['PHP_SELF'] == parse_url($url, PHP_URL_PATH)) ? ' active' : '';

						$rows.= $this->parse(array
							(
								'url'    => $url,
								'box'    => $box,
								'name'   => $link_name,
								'active' => $active
							),
							$this->manuale['rows']);
					}
				}
				$system.= $this->parse(array
					(
						'id'      => $block['id'],
						'icon'    => $icon,
						'title'   => $title,
						'class'   => $class,
						'display' => $display,
						'rows'    => $rows
					),
					$blocklink);
			}
		}

		$server = (isset($dn) AND $dn == 'server') ? ' menupanelopen' : '';
		$support = (isset($dn) AND $dn == 'support') ? ' menupanelopen' : '';

		$server_link = null;
		if (in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
		{
			$server_link = $this->parse(array
							(
								'open-server' => $server,
								'server' => $lang['server'],
								'hash' => $sess['hash']
							),
							$this->parsein($this->create('server.link')));
		}

		/**
		 * Menu out
		 */
		$template['aside_menu'] = $this->parse(array
			(
				'title'   => $lang['public_last'],
				'support' => $lang['support'],
				'logout'  => $lang['goto_logout'],
				'display' => $this->display_menu,
				'content' => $content,
				'system'  => $system,
				'hash'    => $sess['hash'],
				'open-support' => $support,
				'server-link' => $server_link
			),
			$this->parsein($this->create('block')));
	}

	/**
	 * Блочное меню | Моды сайта
	 */
	function mod_menu()
	{
		$mod = $out = array();
		$mod = new GlobIterator(ADMDIR.'/mod/*/mod.menu.php');
		foreach ($mod as $file)
		{
			include ($file->getPathname());
			if (
				isset($block) AND
				is_array($block) AND
				isset($block['id']) AND
				isset($block['posit'])
			) {
				$num = (strlen(intval($block['posit'])) > 1) ? $block['posit'] : '0'.$block['posit'];
				$out[$num.$block['id']] = $file->getPathname();
			}
		}
		ksort($out);
		return $out;
	}

	/**
	 * Блочное меню | Ноды системы
	 */
	function sys_menu()
	{
		$sys = $out = array();
		$mod = new GlobIterator(ADMDIR.'/system/*/nod.menu.php');
		foreach ($mod as $file)
		{
			include ($file->getPathname());
			if (
				isset($block) AND
				is_array($block) AND
				isset($block['id']) AND
				isset($block['posit'])
			) {
				$num = (strlen(intval($block['posit'])) > 1) ? $block['posit'] : '0'.$block['posit'];
				$out[$num.$block['id']] = $file->getPathname();
			}
		}
		ksort($out);
		return $out;
	}

	/**
	 * Блочное меню, Иконки
	 */
	function icon_menu($path)
	{
		global $sess;

		if (file_exists(ADMDIR.$path)) {
			$out = ADMPATH.$path;
		} else {
			$out = ADMPATH.'//template/skin/'.$sess['skin'].'/images/menu/blank.png';
		}
		return $out;
	}

	/**
	 * Подключение шаблонов контента
	 */
	public function create($tpl)
	{
		global $sess;

		$path = ADMDIR.'/template/skin/'.$sess['skin'].'/'.$tpl.'.tpl';

		if (file_exists($path))
		{
			return  file_get_contents($path);
		}
		else {
			return '<strong>'.$tpl.'.tpl</strong> Not found!';
		}
	}

	/**
	 * Парсинг шаблонов, возврат значений
	 */
	public function parse($var, $contents)
	{
		global $lang, $conf, $sess;

		$sub = array
			(
				'adm_url'	=> ADMPATH,
				'site_url'	=> WORKURL,
				'site_dir'	=> SITEDIR,
				'adm_path'	=> ADMPATH,
				'adm_temp'	=> $sess['skin'],
				'lang'		=> $conf['langcode']
			);

		$newkey = $newval = array();
		$result = array_merge($var, $sub);

		foreach ($result as $key => $val)
		{
			$newkey[] = '{'.$key.'}';
			$newval[] = $val;
		}

		return str_replace($newkey, $newval, $contents);
	}

	/**
	 * Парсинг, вывод на страницу
	 */
	public function parseprint($var, $contents)
	{
		echo $this->parse($var, $contents);
	}

	/**
	 * Парсинг вложенных шаблонов
	 */
	public function parsein($content)
	{
		if ($count = preg_match_all('#<\!--(if|buffer|add):([a-zA-Z0-9_]*):([a-zA-Z0-9_]*)-->(.*?)<\!--(if|buffer|add)-->#is', $content, $attribut))
		{
			for ($i = 0; $i < $count; $i ++)
			{
				$tags = $attribut[1][$i];
				$pers = $attribut[2][$i];
				$vals = $attribut[3][$i];

				if ($tags == 'if')
				{
					if (isset($this->unmanule[$pers]))
					{
						if ($this->unmanule[$pers] == $vals) {
							$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->', '', $content);
						} else {
							$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', '', $content);
						}
					}
				}

				if ($tags == 'buffer')
				{
					$this->manuale[$pers] = $attribut[4][$i];
					$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', '', $content);
				}

				if ($tags == 'add')
				{
					if (isset($attribut[4][$i]) AND ! empty($attribut[4][$i]))
					{
						$add = file_get_contents(DNDIR.$attribut[4][$i]);
						$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', $add, $content);
					}
				}
			}
		}
		return str_replace('<!--if-->', '', $content);
	}

	/**
	 * Страница не существует
	 */
	function noexistprint()
	{
		global $conf, $lang;

		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		return 'Not Found';

		exit();
	}

	/**
	 * Информационные сообщения, всплывающие подсказки
	 */
	function globalnotice()
	{
		global $conf, $sess, $wysiwyg, $lang, $PLATFORM, $ADMIN_ID, $CHECK_ADMIN, $ADMIN_PERM_ARRAY;

		if ($ADMIN_ID == 1)  // Показывать только для главного админа
		{
			$alert  = array();
			$i = 0;

			// Если не установлен Curl
			if (defined('NOTCURL'))
			{
				$alert[$i]['title'] = $lang['isset_error'];
				$alert[$i]['desc']  = preparse_lga($lang['not_curl']);
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = '';
				$i ++;
			}
			// Время оптимизировать базу
			if ((in_array('base', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND ($conf['lastopt'] + 604800) < NEWTIME)
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = preparse_lga(mb_str_replace('{url}', ADMPATH.'/system/base/index.php?dn=improvement&ops='.$sess['hash'], $lang['mess_optimize']));
				$alert[$i]['ico']   = ADMPATH.'/template/images/iinfo.png';
				$alert[$i]['class'] = 'alert-info';
				$i ++;
			}
			// Доп. условие cookie админа
			if ((in_array('amanage', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND SALT_ADMIN == "123456")
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = preparse_lga(mb_str_replace('{file}', ADMPATH.'/core/permission.php', $lang['error_salt']));
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = 'alert-pass';
				$i ++;
			}
			// Секретное слово
			if ((in_array('amanage', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND $CHECK_ADMIN['sword'] == 'qwerty')
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = preparse_lga(mb_str_replace('{file}', ADMPATH.'/core/permission.php', $lang['mess_permiss']));
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = 'alert-pass';
				$i ++;
			}
			// Каталог /setup/
			if ((in_array('amanage', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND is_dir(DNDIR.'setup'))
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = $lang['adm_delsetup'].': <strong>setup</strong>';
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = 'alert-pass';
				$i ++;
			}
			// Файл /core/config.php
			if ((in_array('amanage', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND is_writable(DNDIR.'core/config.php'))
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = $lang['chmod_config'].': <strong>core/config.php</strong>';
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = 'alert-pass';
				$i ++;
			}
			// Файл /dump.php
			if ((in_array('amanage', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) AND file_exists(ADMDIR.'/dump.php'))
			{
				$alert[$i]['title'] = $lang['all_alert'];
				$alert[$i]['desc']  = $lang['adm_deldump'].': <strong>'.ADMPATH.'/dump.php</strong>';
				$alert[$i]['ico']   = ADMPATH.'/template/images/iwarn.png';
				$alert[$i]['class'] = 'alert-pass';
				$i ++;
			}
			if (count($alert) == 0)
			{
				$alert = FALSE;
			}
			$result = '';
			if (is_array($alert))
			{
				foreach ($alert as $k => $out)
				{
					$result .= " globalnotice('".$out['title']."', '".$out['desc']."', '".$out['ico']."', '".$out['class']."');\n";
				}
			}

			return $result;
		}
		return FALSE;
	}

	/**
	 * Всплывающие подсказки
	 */
	function outhint($hint, $type = FALSE)
	{
		global $sess;

		if ($type) {
			return '<p class="hint" title="'.$hint.'">?</p>';
		} else {
			echo '<p class="hint" title="'.$hint.'">?</p>';
		}
	}

	/**
	 * Кнопка транслита для ЧПУ
	 */
	function outtranslit($gui, $obj, $hint)
	{
		global $sess;

		echo '&nbsp;<a class="but" href="javascript:$.translit(\''.$gui.'\',\''.$obj.'\',\''.$sess['hash'].'\')" title="'.$hint.'">T</a>';
	}

	/**
	 * Обработка textarea
	 */
	function textarea($name, $rows, $cols, $value, $resize, $hint = FALSE, $class = FALSE, $req = FALSE)
	{
		global $sess;

		$name = ($name) ? trim($name) : '';
		$value = ($value) ? notslashes(trim($value)) : '';
		$rowclass  = ($resize == 1) ? ' class="textr resize {class}"' : ' class="textr noresize {class}"';
		$req = ($req) ? ' required="required"' : '';
		if ($class) {
			$rowclass = mb_str_replace('{class}', $class, $rowclass);
		} else {
			$rowclass = mb_str_replace('{class}', '', $rowclass);
		}
		echo '<textarea name="'.$name.'" id="'.$name.'" rows="'.$rows.'" cols="'.$cols.'"'.$rowclass.''.$req.'>'.$value.'</textarea>';
		if ($hint) {
			echo ' '.$hint;
		}
	}

	/**
	 * Предупреждения
	 */
	function alert($title, $message, $add = null)
	{
		$add = ($add) ? '<span>'.$add.'</span>' : '';
		echo '	<table id="del" class="work">
				<caption>'.$title.'</caption>
					<tr>
						<td>
							'.$message.' '.$add.'
						</td>
					</tr>
				</table>';
	}

	/**
	 * Сообщение об удалении
	 */
	function shortdel($enter, $title = null, $yes = null, $not = null, $mess = null)
	{
		global $lang, $sess;

		$head = ($title) ? $enter.': '.$title : $enter;
		$title = (empty($mess)) ? '<p class="bold">'.$title.'</p>' : '';
		$mess = ( ! empty($mess)) ? '<p class="bold site">'.$mess.'</p>' : '';

		echo '	<table id="del" class="work">
				<caption>'.$head.'</caption>
					<tr>
						<td>
							'.$title.'
							'.$mess.'
							<h4>'.$lang['confirm_del'].'</h4>
						</td>
					</tr>
					<tr class="tfoot">
						<td>
							<a onclick="loads();" class="side-button" href="'.$yes.'">'.$lang['all_go'].'</a> &nbsp; <a class="side-button" href="'.$not.'">'.$lang['cancel'].'</a>
						</td>
					</tr>
				</table>
				<div id="lds"></div>';
	}

	/**
	 * Сообщение об ошибках
	 */
	function error($enter, $title = null, $mess = null, $text = null)
	{
		global $lang;

		$title = ($title) ? $enter.': '.$title : $enter;
		$text = ( ! empty($text)) ? '<p class="bold">'.$text.'</p>' : '';

		echo '	<table id="del" class="work">
				<caption>'.$title.'</caption>
					<tr>
						<td>
							'.$text.'
							<h4>'.$mess.'</h4>
						</td>
					</tr>
					<tr class="tfoot">
						<td>
							<a class="side-button" href="javascript:history.go(-1)">'.$lang['goback_fix'].'</a>
						</td>
					</tr>
				</table>';
	}

	/**
	 * Нет прав доступа
	 */
	function access($title, $mess = null)
	{
		global $lang;

		if ($mess) {
			$mess = $mess;
		} else {
			$mess = $title;
			$title = $lang['all_access'].': '.$lang['all_error'];
		}
		echo '	<table id="del" class="work">
				<caption>'.$title.'</caption>
					<tr>
						<td>
							<h4>'.$mess.'</h4>
						</td>
					</tr>
				</table>';
	}

	/**
	 * файл-браузер, ланг-браузер, ошибка доступ
	 */
	function fberror($mess)
	{
		global $lang;
		echo '	<div class="fb-err attention">
					<div class="attention-title">'.$lang['all_error'].'!</div>
					<div class="attention-text">
						'.$mess.'
					</div>
				</div>';
	}

	/**
	 * Сообщение об ошибке, box
	 */
	function errorbox($mess)
	{
		global $lang;
		echo '	<div class="box-err">
					'.$mess.'
				</div>';
	}

	/**
	 * Сообщение об успехе, box
	 */
	function successbox($mess)
	{
		global $lang;
		echo '	<div class="box-ok">
					'.$mess.'
				</div>';
	}

	function blankstart()
	{
		global $conf, $title, $lang, $sess;
		echo '	<!DOCTYPE html>
				<html>
				<head>
				<meta charset='.$conf['langcharset'].'">
				'.((isset($title)) ? '<title>'.$title.'</title>' : '<title>CMS Danneo '.$conf['version'].' / Apanel</title>').'
              </head>
				<body>';
	}

	function blankend()
	{
		echo '	</body>
				</html>';
		exit();
	}

	/**
	 * Фильтрация данных в листинге
	 */
	function filter($act, $arr, $mod)
	{
		global $conf, $db, $basepref, $lang, $sess, $vals, $pl;

		echo '	<div id="filter" class="none unpad">
				<form action="'.$act.'" method="post">
				<div class="section">
				<table class="work">
					<caption>'.$mod.': '.$lang['search_in_section'].'</caption>
					<tr>
						<th class="ar">'.$lang['all_filter'].'</th>
						<th>'.$lang['all_value'].'</th>
					</tr>';
		foreach ($arr as $k => $v)
		{
			echo '	<tr>
						<td>'.(isset($lang[$v[1]]) ? $lang[$v[1]] : $v[1]).'</td>
						<td>';
			if ($v[2] == 'input') {
				echo '		<input type="text" name="filter['.$k.']" size="70">';
			}
			if ($v[2] == 'checkbox') {
				echo '		<input type="checkbox" name="filter['.$k.']" value="1">';
			}
			if ($v[2] == 'type') {
				echo '		<select class="sw250" name="filter['.$k.']">';
				foreach ($v[3] as $sk => $sv) {
					echo '		<option value="'.$v[4][$sk].'">'.$lang[$sv].'</option>';
				}
				echo '		</select>';
			}
			if ($v[2] == 'date') {
				echo '		<input type="text" id="filter0-'.$v[0].'" name="filter['.$k.'][0]" size="18" placeholder="'.$lang['fro'].'">';
							Calendar('filter0_'.$v[0],'filter0-'.$v[0]);
				echo '		<input type="text" id="filter1-'.$v[0].'" name="filter['.$k.'][1]" size="18" placeholder="'.$lang['to'].'">';
							Calendar('filter1_'.$v[0],'filter1-'.$v[0]);
			}
			if ($v[2] == 'intval') {
				echo '		<input type="text" id="filter0-'.$v[0].'" name="filter['.$k.'][0]" size="18" placeholder="'.$lang['fro'].'">
							<input type="text" id="filter1-'.$v[0].'" name="filter['.$k.'][1]" size="18" placeholder="'.$lang['to'].'">';
			}
			if ($v[2] == 'access') {
				echo '		<select class="sw250" name="filter['.$k.']">
								<option value="0">'.$lang['unimportant'].'</option>
								<option value="all">'.$lang['all_all'].'</option>
								<option value="user">'.$lang['all_user_only'].'</option>';
				echo '		</select>';
			}
			echo '		</td>
					</tr>';
		}
		echo '		<tr class="tfoot">
						<td></td>
						<td class="al">
							<input type="hidden" name="pl" value="'.$pl.'">
							<input class="side-button" value="'.$lang['all_apply'].'" type="submit">
						</td>
					</tr>
				</table>
				</div>
				</form>
				<div class="pad"></div>
				</div>';
	}
	/**
	 * Gzip Start
	 */
	function gzip_start()
	{
		global $conf;

		if (extension_loaded('zlib') AND $conf['gzip'] == 'yes')
		{
			ob_start();
			ob_implicit_flush(0);
		}
	}

	/**
	 * Gzip Output
	 */
	function gzip_end()
	{
		global $conf;

		if (extension_loaded('zlib') AND $conf['gzip'] == 'yes')
		{
			if(headers_sent()) {
				$gzipenc = FALSE;
			} elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== FALSE) {
				$gzipenc = 'x-gzip';
			} elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== FALSE) {
				$gzipenc = 'gzip';
			} else {
				$gzipenc = FALSE;
			}

			if( $gzipenc )
			{
				$contents = ob_get_contents();
				ob_end_clean();

				$width = strlen($contents);
				if ($width < 2048) {
					echo ($contents);
				} else {
					header('Content-Encoding: '.$gzipenc);
					echo("\x1f\x8b\x08\x00\x00\x00\x00\x00");
					$contents = gzcompress($contents, $conf['gziplevel']);
					$contents = substr($contents, 0, $width);
					echo ($contents);
				}
			}
			else
			{
				ob_end_flush();
			}
		}
	}
}
