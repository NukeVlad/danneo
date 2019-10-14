<?php
/**
 * File:        /core/classes/Comment.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Comment
 */
class Comment
{
	/**
	 * Текущий мод
	 * @type string
	 */
	private $mod = null;

	/**
	 * Всего комментариев
	 * @type string
	 */
	private $total = 0;

	/**
	 * Количество на страницу
	 * @type string
	 */
	private $limit = 0;

	/**
	 * Номер страницы
	 * @type string
	 */
	private $pages = 0;

	/**
	 * Время добавления
	 * @type string
	 */
	private $timelast = 0;

	/**
	 * Листинг страниц
	 * @type string
	 */
	private $list_pages = null;

	/**
	 * Листинг комментариев
	 * @type string
	 */
	private $list_comment = null;

	/**
	 * Массив данных комментариев
	 * @var array
	 */
	private $comments = array();

	/**
	 * Массив данных пользователей
	 * @var array
	 */
	private $associate = array();

	/**
	 * Массив смайлов
	 * @var array
	 */
	private $smiliearray = array();

	/**
	 * Массив контрольных вопросов
	 * @var array
	 */
	private $controlarray = array();

	/**
	 * Активируем методы класса
	 * new Comment(WORKMOD);
	 */
	public function __construct($mod)
	{
		global $config, $tm;

		if ($config[$mod]['comsmilie'] == 'yes') {
			$this->smilies();
		}

		if ($config['control'] == 'yes') {
			$this->controls();
		}

		$this->mod = $mod;
	}

	/**
	 * Вывод комментариев
	 */
	public function comment($id, $count, $cpu, $catcpu, $p, $c = false)
	{
		global $db, $basepref, $config, $lang, $userapi, $tm, $api, $ro;

		$this->pages($count, $p);
		$this->total = $count;

		$tm->unmanule['title'] = 'yes';

		/**
		 * Шаблон, общий
		 */
		$comment_template = $tm->parsein($tm->create('mod/'.$this->mod.'/comment'));

		/**
		 * Листинг страниц
		 */
		$this->pagination($id, $cpu, $catcpu, $c);

		/**
		 * Шаблон отдельных комментариев
		 */
		$comment_standart = $tm->parsein($tm->create('mod/'.$this->mod.'/comment.standart'));

		$this->associated($id);

		foreach ($this->comments as $item)
		{
			$text = ($config[$this->mod]['comeditor'] == 'yes') ? commentout($api->siteuni($item['ctext'])) : deltags($api->siteuni($item['ctext']));
			$text = ($config[$this->mod]['comsmilie'] == 'yes' AND is_array($this->smiliearray)) ? smilieparse($text, $this->smiliearray) : smilieparse($text, $this->smiliearray, FALSE);

			$guest = $user = null;
			if ($item['userid'] > 0 AND isset($this->associate[$item['userid']]))
			{
				if (isset($userapi->data['linkprofile']) AND $userapi->data['linkprofile']) {
					$link_profile = $ro->seo($userapi->data['linkprofile'].$item['userid']);
				} else {
					$link_profile = FULL_REQUEST_URI;
				}
				$author = $tm->parse(array
								(
									'name'  => $api->siteuni($item['cname']),
									'link'  => $link_profile,
									'title' => $lang['profile']
								),
								$tm->manuale['author']);

				if (isset($this->associate[$item['userid']]) AND ! empty($this->associate[$item['userid']]['avatar'])) {
					$avatar = $this->associate[$item['userid']]['avatar'];
				} else {
					$avatar = '/up/avatar/blank/guest.png';
				}

				$regdate = (isset($this->associate[$item['userid']]) AND ! empty($this->associate[$item['userid']]['regdate'])) ? $this->associate[$item['userid']]['regdate'] : '';
				$user = $tm->parse(array
								(
									'languser'	=> $lang['block_user'],
									'avatar'	=> $avatar,
									'link'		=> $link_profile,
									'nameuser'	=> $api->siteuni($item['cname']),
									'register'	=> $lang['registr_date'],
									'date'	=> $regdate
								),
								$tm->manuale['user']);
			}
			else
			{
				$author = $api->siteuni($item['cname']);
				$guest = $tm->parse(array('guest' => $lang['guest']), $tm->manuale['guest']);
			}

			$this->list_comment.= $tm->parse(array
				(
					'author' => $author,
					'title'  => $api->siteuni(str_word($item['ctitle'], 35)),
					'date'   => $item['ctime'],
					'text'   => $text,
					'guest'  => $guest,
					'user'   => $user
				),
				$comment_standart
			);

			$this->timelast = ($config['ajax'] == 'yes') ? $item['ctime'] : 0;
		}

		/**
		 * Вывод
		 */
		return  $tm->parse(array
			(
				'title' => $lang['comment_last'],
				'total' => $lang['all_alls'],
				'count' => $this->total,
				'comment' => $this->list_comment,
				'pages' => $this->list_pages
			),
			$comment_template
		);
	}

	/**
	 * Форма, обработка и вывод на странице
	 */
	public function comform($id, $title)
	{
		global $config, $lang, $tm, $ro, $api, $userapi;

		if ( $config[$this->mod]['comwho'] == 'user' AND defined('USER') AND ! defined('USER_LOGGED') )
		{
			$massage = this_text(array(
					'reglink' => $ro->seo(SITE_URL.'/'.$userapi->data['linkreg'])
				),
				$api->siteuni($lang['comment_add_user'])
			);

			return $tm->norightprint($massage, 1, 1, 0, 1).
					$tm->noaccessprint(1, 0, 1, 1);
		}
		else
		{
			return $this->form
				(
					$this->mod,
					$id,
					$api->siteuni($title),
					$config[$this->mod]['comeditor'],
					$config[$this->mod]['comsmilie'],
					$this->smiliearray,
					$this->controlarray,
					$this->timelast
				);
		}
	}

	/**
	 * Форма комментариев
	 */
	private function form($mod, $id, $title, $conf_editor, $conf_smilie, $smilie_array, $control_array, $last)
	{
		global $config, $lang, $ro, $tm, $usermain;

		$smilies = $controls = $cid = null;

		if ($conf_smilie == 'yes' AND is_array($smilie_array))
		{
			$i = 0;
			$smilies.= '$.smilie = new Array('.sizeof($smilie_array).');';
			foreach ($smilie_array as $img)
			{
				$smilies.=' $.smilie['.$i.'] = new Array(\''.SITE_URL.'/'.$img['img'].'\',\''.$img['code'].'\',\''.$img['alt'].'\');';
				$i ++;
			}
		}

		if ($config['control'] == 'yes' AND is_array($control_array))
		{
			$r = rand(0, sizeof($control_array) - 1);
			$controls = $control_array[$r]['issue'];
			$cid = $control_array[$r]['cid'];
		}

		$comname = (defined('USER_DANNEO')) ? $config['user']['maxname'] : 0;
		$button = (($conf_editor == 'yes') ? '$.template = \''.$config['site_temp'].'\';' : '');

		$tm->unmanule['captcha'] = ($config['captcha'] == 'yes' AND defined('REMOTE_ADDRS')) ? 'yes' : 'no';
		$tm->unmanule['uname'] = (empty($usermain['uname'])) ? 'yes' : 'no';
		$tm->unmanule['ajax'] = ($config['ajax'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';

		noprotectspam(); // Отключить проверки, для пользователей

		$form_comment = $tm->parsein($tm->create('mod/'.$mod.'/form.comment'));

		$activejs = 'smilie:'.(($conf_smilie == 'yes') ? 'true' : 'false').',';
		$activejs.= 'editor:'.(($conf_editor == 'yes') ? 'true' : 'false');

		return $tm->parse(array
			(
				'post_url'            => $ro->seo('index.php?dn='.$mod.'&re=comment'),
				'uname'               => $usermain['uname'],
				'mod'                 => $mod,
				'title'               => $title,
				'subtitle'            => $lang['comment_add'],
				'comment_name'        => $lang['comment_name'],
				'comment_title'       => $lang['all_title'],
				'comment_editor'      => $lang['comment_editor'],
				'all_text'            => $lang['email_text'],
				'all_refresh'         => $lang['all_refresh'],
				'all_sends'           => $lang['all_sends'],
				'smilies'             => $lang['smilies'],
				'control_word'        => $lang['control_word'],
				'captcha'             => $lang['all_captcha'],
				'help_captcha'        => $lang['help_captcha'],
				'help_control'        => $lang['help_control'],
				'not_empty'           => $lang['all_not_empty'],
				'smiliearray'         => $smilies,
				'control'             => $controls,
				'activejs'            => $activejs,
				'template'            => $button,
				'comsize'             => $config['comsize'],
				'comname'             => $comname,
				'last'                => $last,
				'id'                  => $id,
				'cid'                 => $cid,
				'comment_add_button'  => $lang['all_add']
			),
			$form_comment);
	}

	/**
	 * Смайлы
	 */
	private function smilies()
	{
		global $config, $db, $basepref;

		if (isset($config['smilie']) AND is_array($config['smilie']))
		{
			$array = $config['smilie'];
		}
		else
		{
			$si = $db->query("SELECT * FROM ".$basepref."_smilie ORDER BY posit", $config['cachetime']);

			$i = 0;
			while ($sm = $db->fetchassoc($si, $config['cache']))
			{
				$array[$i] = array
					(
						'code' => $sm['smcode'],
						'alt'  => $sm['smalt'],
						'img'  => $sm['smimg']
					);
				$i ++;
			}
		}

		$this->smiliearray = $array;
	}

	/**
	 * Контрольные вопросы
	 */
	private function controls()
	{
		global $config, $db, $basepref;

		if (isset($config['controls']) AND is_array($config['controls']))
		{
			$array = $config['controls'];
		}
		else
		{
			$ci = $db->query("SELECT * FROM ".$basepref."_control", $config['cachetime']);

			$i = 0;
			while ($cm = $db->fetchassoc($ci, $config['cache']))
			{
				$array[$i] = array('cid' => $cm['cid'], 'issue' => $cm['issue']);
				$i ++;
			}
		}

		$this->controlarray = $array;
	}

	/**
	 * Количество страниц
	 */
	private function pages($count, $p)
	{
		global $config;

		$nums = ceil($count / $config['compage']);
		if ( ! empty($p) ) {
			$this->pages = ($p <= 1) ? 1 : $p;
		} else {
			$this->pages = $nums;
		}
		$this->limit = $config['compage'] * ($this->pages - 1);
	}

	/**
	 * Листинг страниц
	 */
	private function pagination($id, $cpu, $catcpu, $c)
	{
		global $config, $lang, $tm, $api;

		if ($this->total > $config['compage'])
		{
			if ($c) {
				$pagesview = $api->compage('', '', 'index', $this->mod.$catcpu.'&amp;to=page&amp;id='.$id.$cpu, $config['compage'], $this->pages, $this->total);
			} else {
				$pagesview = $api->pages('', '', 'index', $this->mod.$catcpu.'&amp;to=page&amp;id='.$id.$cpu, $config['compage'], $this->pages, $this->total);
			}

			$this->list_pages = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $pagesview
									),
									$tm->manuale['pagesout']);
		}
	}

	/**
	 * Ассоциация пользователей
	 */
	private function associated($id)
	{
		global $db, $basepref, $config, $userapi;

		$ins = array();
		$inq = $db->query
					(
						"SELECT comid, file, ctime, userid, cname, ctitle, ctext FROM ".$basepref."_comment
						 WHERE file = '".$this->mod."' AND id = '". $id."'
						 ORDER BY ctime ".$config['comsort']." LIMIT ".$this->limit.", ".$config['compage']
					);

		while ($item = $db->fetchassoc($inq))
		{
			$this->comments[$item['comid']] = $item;
			if ($item['userid'] > 0)
			{
				$ins[$item['userid']] = $item['userid'];
			}
		}

		if (isset($config['user']))
		{
			$this->associate = $userapi->associat(implode(',', $ins));
		}
	}
}
