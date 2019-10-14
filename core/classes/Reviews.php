<?php
/**
 * File:        /core/classes/Reviews.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Reviews
 */
class Reviews
{
	/**
	 * Текущий мод
	 * @type string
	 */
	private $mod = null;

	/**
	 * Всего отзывов
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
	 * Листинг отзывов
	 * @type string
	 */
	private $list_reviews = null;

	/**
	 * Количество на страницу
	 * @type string
	 */
	private $respage = null;

	/**
	 * Массив данных отзывов
	 * @var array
	 */
	private $reviews = array();

	/**
	 * Массив данных пользователей
	 * @var array
	 */
	private $associate = array();

	/**
	 * Массив контрольных вопросов
	 * @var array
	 */
	private $controlarray = array();

	/**
	 * Активируем методы класса
	 * new Reviews(WORKMOD);
	 */
	public function __construct($mod)
	{
		global $config, $tm;

		if ($config['control'] == 'yes') {
			$this->controls();
		}

		$this->mod = $mod;
		$this->respage = $config[$mod]['respage'];
	}

	/**
	 * Вывод отзывов
	 */
	public function reviews($id, $count, $cpu, $catcpu, $title, $p, $c = false)
	{
		global $db, $basepref, $config, $lang, $userapi, $tm, $api, $ro;

		$this->pages($count, $p);
		$this->total = $count;

		$tm->unmanule['title'] = 'yes';

		/**
		 * Шаблон, общий
		 */
		$template = $tm->parsein($tm->create('mod/'.$this->mod.'/reviews'));

		/**
		 * Листинг страниц
		 */
		$this->pagination($id, $cpu, $catcpu, $c);

		/**
		 * Шаблон отдельных отзывов
		 */
		$standart = $tm->parsein($tm->create('mod/'.$this->mod.'/reviews.standart'));

		$this->associated($id);

		foreach ($this->reviews as $item)
		{
			$message = deltags($api->siteuni($item['message']));

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
									'name'  => $api->siteuni($item['uname']),
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
									'nameuser'	=> $api->siteuni($item['uname']),
									'register'	=> $lang['registr_date'],
									'date'	=> $regdate
								),
								$tm->manuale['user']);
			}
			else
			{
				$author = $api->siteuni($item['uname']);
				$guest = $tm->parse(array('guest' => $lang['guest']), $tm->manuale['guest']);
			}

			$this->list_reviews.= $tm->parse(array
				(
					'author'   => $author,
					'title'    => $api->siteuni(str_word($item['title'], 35)),
					'date'     => $item['public'],
					'message'  => $message,
					'guest'    => $guest,
					'user'     => $user,
					'state'    => $lang['state'],
					'region'   => $item['region'],
					'rate'     => $item['rating'],
					'langrate' => $lang['rate_'.$item['rating']],
					'valrate'  => $lang['rate_emp']
				),
				$standart
			);

			$this->timelast = ($config['ajax'] == 'yes') ? $item['public'] : 0;
		}

		/**
		 * Вывод
		 */
		return  $tm->parse(array
			(
				'review' => $lang['response'],
				'title' => $api->siteuni(str_word($title, 35)),
				'total' => $lang['all_alls'],
				'count' => $this->total,
				'reviews' => $this->list_reviews,
				'pages' => $this->list_pages
			),
			$template
		);
	}

	/**
	 * Форма, обработка и вывод на странице
	 */
	public function reform($id, $title)
	{
		global $config, $lang, $tm, $ro, $api, $userapi;

		if ( $config[$this->mod]['resadd'] == 'user' AND defined('USER') AND ! defined('USER_LOGGED') )
		{
			$massage = this_text(array(
					'reglink' => $ro->seo(SITE_URL.'/'.$userapi->data['linkreg'])
				),
				$api->siteuni($lang['response_add_user'])
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
					$this->controlarray,
					$this->timelast
				);
		}
	}

	/**
	 * Форма отзывов
	 */
	private function form($mod, $id, $title, $control_array, $last)
	{
		global $db, $basepref, $config, $lang, $ro, $tm, $usermain;

		$country = array();
		$controls = $cid = $region = null;

		if ($config['control'] == 'yes' AND is_array($control_array))
		{
			$r = rand(0, sizeof($control_array) - 1);
			$controls = $control_array[$r]['issue'];
			$cid = $control_array[$r]['cid'];
		}

		$maxname = (defined('USER_DANNEO')) ? $config['user']['maxname'] : 0;

		if ( ! empty($usermain['uname']))
		{
			$get = DNDIR.'cache/cache.country.php';
			if (file_exists($get))
			{
				$country = include($get);
			}

			$reg = $db->fetchassoc(
						$db->query(
							"SELECT countryid, regionid  FROM ".$basepref."_user WHERE userid = '".$usermain['userid']."'"
						)
					);
			if ( ! empty($country) AND ! empty($reg['regionid']))
			{
				$region = $country[$reg['countryid']]['countryname'].', '.$country[$reg['countryid']]['region'][$reg['regionid']];
			}
		}

		$tm->unmanule['captcha'] = ($config['captcha'] == 'yes' AND defined('REMOTE_ADDRS')) ? 'yes' : 'no';
		$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['ajax'] = ($config['ajax'] == 'yes') ? 'yes' : 'no';

		noprotectspam();

		$form = $tm->parsein($tm->create('mod/'.$mod.'/form.reviews'));

		return $tm->parse(array
			(
				'post_url'     => $ro->seo('index.php?dn='.$mod.'&re=reviews'),
				'uname'        => $usermain['uname'],
				'mod'          => $mod,
				'title'        => $title,
				'subtitle'     => $lang['response_add'],
				'your_name'    => $lang['comment_name'],
				'email_name'   => $lang['email_name'],
				'region'       => $region,
				'your_region'  => $lang['your_region'],
				'all_text'     => $lang['all_text'],
				'refresh'      => $lang['all_refresh'],
				'sends'        => $lang['all_sends'],
				'control'      => $lang['control_word'],
				'captcha'      => $lang['all_captcha'],
				'help_captcha' => $lang['help_captcha'],
				'help_control' => $lang['help_control'],
				'not_empty'    => $lang['all_not_empty'],
				'message'      => $lang['message'],
				'review'       => $lang['response_one'],
				'rate_emp'     => $lang['rate_emp'],
				'rate_sel'     => $lang['all_select'],
				'rate_1'       => $lang['rate_1'],
				'rate_2'       => $lang['rate_2'],
				'rate_3'       => $lang['rate_3'],
				'rate_4'       => $lang['rate_4'],
				'rate_5'       => $lang['rate_5'],
				'question'     => $controls,
				'textsize'     => $config['comsize'],
				'maxname'      => $maxname,
				'last'         => $last,
				'id'           => $id,
				'cid'          => $cid,
				'add_button'   => $lang['email_send']
			),
			$form);
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

		$nums = ceil($count / $this->respage);
		if ( ! empty($p) ) {
			$this->pages = ($p <= 1) ? 1 : $p;
		} else {
			$this->pages = $nums;
		}
		$this->limit = $this->respage * ($this->pages - 1);
	}

	/**
	 * Листинг страниц
	 */
	private function pagination($id, $cpu, $catcpu, $c)
	{
		global $config, $lang, $tm, $api;

		if ($this->total > $this->respage)
		{
			if ($c == 1) {
				$pagesview = $api->compage('', '', 'index', $this->mod.$catcpu.'&amp;to=page&amp;id='.$id.$cpu, $this->respage, $this->pages, $this->total);
			} elseif ($c == 2) {
				$pagesview = $api->pages('', '', 'index', $this->mod.'&amp;to=index', $this->respage, $this->pages, $this->total);
			} else {
				$pagesview = $api->pages('', '', 'index', $this->mod.$catcpu.'&amp;to=page&amp;id='.$id.$cpu, $this->respage, $this->pages, $this->total);
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
						"SELECT * FROM ".$basepref."_reviews
						 WHERE file = '".$this->mod."' AND pageid = '". $id."'
						 ORDER BY public ".$config['comsort']." LIMIT ".$this->limit.", ".$this->respage
					);

		while ($item = $db->fetchassoc($inq))
		{
			$this->reviews[$item['reid']] = $item;
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
