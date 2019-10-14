<?php
/**
 * File:        /core/userbase/punbb14/danneo.user.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class userapi
 */
class userapi
{
	public $db;

	public $data = array
		(
			'prefix'       => 'punbb_',
			'table'        => '',
			'userid'       => 'id',
			'forumpath'    => 'forum/',
			'cookie'       => 'forum_cookie_1d891e',
			'cookiehost'   => 'domain.ru',
			'cookieexp'    => 3600,
			'setting'      => array(),
			'linkreg'      => 'forum/register.php',
			'linklost'     => 'forum/login.php?action=forget',
			'linkprivmess' => '',
			'linksendmess' => '',
			'linkprofile'  => '/forum/profile.php?id='
		);

	public $set = array
		(
			'prefix'      => 'all_prefix',
			'forumpath'   => 'all_path',
			'cookie'      => 'Cookies',
			'cookiehost'  => 'Cookies domaine',
			'cookieexp'   => 'cookies_expire',
			'linkreg'     => 'registr',
			'linklost'    => 'rest_pass',
			'linkprofile' => 'your_profile'
		);

	public $usermain =  array
		(
			'logged'    => 0,
			'userid'    => 0,
			'gid'       => 0,
			'uname'     => '',
			'umail'     => '',
			'regdate'   => '',
			'lastvisit' => '',
			'phone'     => '',
			'city'      => '',
			'skype'     => '',
			'www'       => '',
			'newmsg'    => 0,
			'newmsgnr'  => 0,
			'avatar'    => ''
		);

	public $userfilter = array
		(
			'login'    => array('username', 'login', 'input'),
			'register' => array('registered', 'registr_date', 'date'),
			'visit'    => array('last_visit', 'last_visit', 'date'),
			'email'    => array('email', 'E-Mail', 'input'),
		);

	public $groups = array();
	public $error = 1;

	public $assoc = array
		(
			'userid'  => 'id',
			'uname'   => 'username',
			'regdate' => 'registered',
			'avatar'  => '',
			'www'     => '',
			'city'    => '',
			'phone'   => '',
			'skype'   => ''
		);

	function __construct(&$db, $logged = false)
	{
		global $config, $conf, $group;

		$this->db = &$db;

		if (isset($config['datainteg']) AND ! empty($config['datainteg']) OR isset($conf['datainteg']) AND ! empty($conf['datainteg']))
		{
			$data = (isset($conf['datainteg']) AND ! empty($conf['datainteg'])) ? Json::decode($conf['datainteg']) : Json::decode($config['datainteg']);

			$n = '';
			foreach ($this->data as $k => $v)
			{
				$n[$k] = (isset($data[$k])) ? $data[$k] : $v;
			}

			if(is_array($n))
			{
				$this->data = $n;
			}
		}

		if (is_array($group))
		{
			foreach ($group as $k => $v)
			{
				if ($v['fid'] > 0) {
					$this->groups[$v['fid']] = array('gid' => $v['gid'], 'title' => $v['title']);
				}
			}
		}

		$this->data['table'] = $this->data['prefix'].'users';

		if ($logged)
		{
			return $this->userarray();
		}
	}

	function avatar($scr, $link = false)
	{
		return;
	}

	function userarray()
	{
		global $group;

        if (isset($_COOKIE[$this->data['cookie']]))
		{
			$pun['userid'] = 1;
			$pun['password'] = 'Guest';
			$pun['expiration_time'] = 0;
			$pun['password_hash'] = $pun['expire_hash'] = null;

			$data = explode('|', base64_decode($_COOKIE[$this->data['cookie']]));

			if ( ! empty($data) AND count($data) == 4)
			{
				list($pun['userid'], $pun['password_hash'], $pun['expiration_time'], $pun['expire_hash']) = $data;
			}

			$pun['userid'] = intval($pun['userid']);

			if ($pun['userid'] > 1 AND  intval($pun['expiration_time']) > NEWTIME)
			{
				$newuser = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."users WHERE id='".$pun['userid']."' LIMIT 1"));

				if ($pun['userid'] > 1 AND $pun['password_hash'] == $newuser['password'])
				{
					$newban = $this->db->fetchassoc($this->db->query("SELECT id FROM ".$this->data['prefix']."bans WHERE username='".$this->db->escape($newuser['username'])."' LIMIT 1"));

					if (intval($newban['id']) == 0)
					{
						$gid = (isset($this->groups[$newuser['group_id']]['gid']) AND intval($this->groups[$newuser['group_id']]['gid']) > 0) ? $this->groups[$newuser['group_id']]['gid'] : 0;

						$this->usermain = array
							(
								'logged'    => 1,
								'userid'    => intval($newuser['id']),
								'gid'       => $gid,
								'uname'     => $newuser['username'],
								'umail'     => $newuser['email'],
								'regdate'   => $newuser['registered'],
								'lastvisit' => $newuser['last_visit'],
								'www'       => $newuser['url'],
								'phone'     => '',
								'city'      => '',
								'skype'     => '',
								'newmsg'    => '',
								'newmsgnr'  => '',
								'avatar'    => ''
							);
					}
				}
			}
		}
	}

	function forum_hash($str, $salt)
	{
		return sha1($salt.sha1($str));
	}

	function checkpwd($passw)
	{
		return (mb_strlen($passw) > 32) ? 0 : 1;
	}

	function checklogin($login)
	{
		return (mb_strlen($login) > 32 OR mb_strlen($login) < 3) ? 0 : 1;
	}

	function issetmail($mail)
	{
		return $this->db->numrows($this->db->query("SELECT id FROM ".$this->data['prefix']."users WHERE email = '".$this->db->escape($mail)."' AND id <> ".$this->usermain['userid'].""));
	}

	function addmail($mail)
	{
		$this->db->query("UPDATE ".$this->data['prefix']."users SET email = '".$this->db->escape($mail)."' WHERE id = ".$this->usermain['userid']."");
	}

	function addurl($url)
	{
		if ( ! empty($url))
		{
			$data = parse_url($url);
			$scheme = ( ! array_key_exists('scheme', $data) OR ! in_array($data['scheme'], array('http', 'https'))) ? 'http://' : $data['scheme'].'://';
			$host = (array_key_exists('host', $data) AND ! empty($data['host'])) ? $data['host'] : $data['path'];
			$url = filter_var(IDNA::encode($scheme.$host), FILTER_SANITIZE_URL);
			$url = filter_var($url, FILTER_VALIDATE_URL) !== false ? $scheme.$host : '';
			return $url;
		}
		return '';
	}

	function adduse($phone, $city, $skype, $www, $avatar)
	{
		return;
	}

	function associat($in = false)
	{
		$associat = array();

		if ($in)
		{
			$inq = $this->db->query("SELECT id, username, email, url, registered FROM ".$this->data['prefix']."users WHERE id IN (".$this->db->escape($in).")");

			while ($item = $this->db->fetchassoc($inq))
			{
				$associat[$item['id']] = array
					(
						'userid'  => $item['id'],
						'uname'   => $item['username'],
						'regdate' => $item['registered'],
						'www'     => $item['url'],
						'avatar'  => '',
						'phone'   => '',
						'city'    => '',
						'skype'   => ''
					);
			}
		}

		return $associat;
	}

	function userdata($key, $val = FALSE)
	{
		$udata = $assoc = array();
		if (isset($this->assoc[$key]) AND $val)
		{
			$udata = $this->db->fetchassoc
						(
							$this->db->query
							(
								"SELECT id, username, registered, url
								 FROM ".$this->data['prefix']."users
								 WHERE ".$this->db->escape($this->assoc[$key])." = '".$this->db->escape($val)."'"
							)
						);
			if ($udata)
			{
				$assoc = array
					(
						'userid'  => $udata['id'],
						'uname'   => $udata['username'],
						'regdate' => $udata['registered'],
						'www'     => $udata['url'],
						'city'    => '',
						'phone'   => '',
						'skype'   => ''
					);
			}
		}
		return $assoc;
	}

	function login($login, $pass)
	{
		if ($this->usermain['logged'] == 0 AND intval($this->usermain['userid']) == 0)
		{
			$newuser = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."users WHERE username = '".$this->db->escape($login)."'"));
			$newban = $this->db->fetchassoc($this->db->query("SELECT id FROM ".$this->data['prefix']."bans WHERE username = '".$this->db->escape($newuser['username'])."' LIMIT 1"));

			if (intval($newban['id']) > 0)
			{
				define('THIS_BANNED', 1);
			}

			$hash = $this->forum_hash($pass,$newuser['salt']);

			if (intval($newuser['id']) > 1 AND intval($newban['id']) == 0 AND $hash == $newuser['password'])
			{
				$expire = NEWTIME + $this->data['cookieexp'];
				$printcookie = base64_encode($newuser['id'].'|'.$hash.'|'.$expire.'|'.sha1($newuser['salt'].$hash.$this->forum_hash($expire, $newuser['salt'])));
				setcookie($this->data['cookie'], $printcookie, NEWTIME + $this->data['cookieexp'], '/', '', 0);

				$gid = (isset($this->groups[$newuser['group_id']]['gid']) AND intval($this->groups[$newuser['group_id']]['gid']) > 0) ? $this->groups[$newuser['group_id']]['gid'] : 0;

				$this->usermain = array
					(
						'logged'    => 1,
						'userid'    => intval($newuser['id']),
						'gid'       => $gid,
						'uname'     => $newuser['username'],
						'umail'     => $newuser['email'],
						'regdate'   => $newuser['registered'],
						'lastvisit' => $newuser['last_visit'],
						'www'       => $newuser['url'],
						'phone'     => '',
						'city'      => '',
						'skype'     => '',
						'newmsg'    => '',
						'newmsgnr'  => '',
						'avatar'    => ''
					);
			}
		}
	}

	function logout()
	{
		if ($this->usermain['logged'] == 1 AND intval($this->usermain['userid']) > 0)
		{
			setcookie($this->data['cookie'], '', NEWTIME - $this->data['cookieexp'], '/');
		}
	}

	function group()
	{
		$associat = array();

		$inq = $this->db->query("SELECT * FROM ".$this->data['prefix']."groups");
		while ($item = $this->db->fetchassoc($inq))
		{
			$associat[$item['g_id']] = $item['g_title'];
		}

		return $associat;
	}

	function userlist($sf, $nu, $p, $sess, $sql)
	{
		global $lang;

		if ($this->db->tables($this->data['prefix']."users"))
		{
			$inq = $this->db->query
						(
							"SELECT u.*, b.id AS blocked FROM ".$this->data['prefix']."users AS u
							 LEFT JOIN ".$this->data['prefix']."bans AS b
							 ON b.username = u.username ".str_replace('email', 'u.email', $sql)."
							 ORDER BY u.id DESC LIMIT ".$sf.", ".$nu
						);

			while ($item = $this->db->fetchassoc($inq))
			{
				$style = (intval($item['blocked']) > 0) ? 'noactive' : '';

				if ($item['id'] > 1)
				{
					echo '	<tr>
								<td class="'.$style.'">'.$item['username'].'</td>
								<td class="'.$style.'">'.format_time($item['registered'], 0, 1).'</td>
								<td class="'.$style.'">'.(($item['last_visit'] != 0) ? format_time($item['last_visit'], 1, 1) : '&#8212;').'</td>
								<td class="'.$style.'"><a href=mailto:'.$item['email'].'>'.$item['email'].'</a></td>
								<td class="'.$style.'">';
					echo '			<a href="user.php?dn=edit&amp;uid='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="template/images/edit.gif" alt="'.$lang['all_edit'].'" /></a>';
				}
				echo '			</td>
							</tr>';
			}
		}
		else
		{
			$this->error = 0;
		}
	}

	function userdel($uid)
	{
		return;
	}

	function bandel($uid)
	{
		return;
	}

	function banadd($uid)
	{
		return;
	}

	function useredit($uid)
	{
		global $lang;

		if ($uid > 0)
		{
			$item = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."users WHERE id = '".$uid."'"));

			echo '	<table  class="work">
						<caption>'.$lang['edit_user'].'</caption>
						<tr>
							<td>'.$lang['all_user'].'</td>
							<td><strong>'.$item['username'].'</strong></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><input name="edit[mail]" size="50" maxlength="50" type="text" value="'.$item['email'].'"></td>
						</tr>';

		}
	}

	function usersave($uid, $edit)
	{
		if ($uid > 0 AND is_array($edit))
		{
			if (isset($edit['mail']) AND ! empty($edit['mail']) AND $this->is_umail($uid, $edit['mail']) == 0)
			{
				$this->db->query("UPDATE ".$this->data['prefix']."users SET email = '".$this->db->escape($edit['mail'])."' WHERE id = '".$uid."'");
			}
		}
	}

	function is_umail($uid, $umail)
	{
		return $this->db->numrows
				(
					$this->db->query
					(
						"SELECT id FROM ".$this->data['prefix']."users
						 WHERE email = '".$this->db->escape($umail)."'
						 AND id <> ".$uid
					)
				);
	}

	function messagelast($bs)
	{
		global $api, $lang;

		$re = null;
		$target = ( ! empty($bs['target'])) ? ' target="'.$bs['target'].'"' : '';
		$bs['order'] = (isset($bs['order']) AND $bs['order'] == 'desc') ? 'desc' : 'asc';

		$inq = $this->db->query
					(
						"SELECT a.id, a.subject, a.poster, a.last_post_id, a.last_post, a.last_poster, a.num_replies, a.num_views, p.id, p.poster_id, p.posted
						 FROM ".$this->data['prefix']."topics a
						 LEFT JOIN ".$this->data['prefix']."posts p ON (p.id = a.last_post_id)
						 ORDER BY a.last_post ".$bs['order']." LIMIT 0, ".$bs['col']
					);

		$user = $this->db->query("SELECT id, username FROM ".$this->data['prefix']."users");
		while ($p = $this->db->fetchassoc($user))
		{
			$poster[$p['username']] = $p;
		}

		if ($this->db->numrows($inq) > 0)
		{
			$re.= '<table class="forum">
					<tbody>
						<tr>
							<th>'.$lang['subject'].'</th>';
			if ($bs['author'] == 'yes') {
				$re.= '<th>'.$lang['author'].'</th>';
			}
			if ($bs['replie'] == 'yes') {
				$re.= '<th>'.$lang['of_replies'].'</th>';
			}
			if ($bs['hits'] == 'yes') {
				$re.= '		<th>'.$lang['all_hits'].'</th>';
			}
			if ($bs['last'] == 'yes') {
				$re.= '<th>'.$lang['last_replies'].'</th>';
			}
			$re.= '		</tr>
					</tbody>
					<tbody>';
			while ($item = $this->db->fetchassoc($inq))
			{
				$time = ($bs['time'] == 'yes') ? $api->sitetime($item['posted'], 'hm') : '';
				$subject = str_word(deltags($item['subject']), $bs['wrap']);

				$re.= '	<tr class="topic">
							<td>
								<a href="/forum/viewtopic.php?pid='.$item['last_post_id'].'&#p='.$item['last_post_id'].'">'.$subject.'</a>
							</td>';
				if ($bs['author'] == 'yes') {
					$re.= '	<td>
								<a href="'.SITE_URL.'/forum/profile.php?id='.$poster[$item['poster']]['id'].'">'.$item['poster'].'</a>
							</td>';
				}
				if ($bs['replie'] == 'yes') {
					$re.= '	<td>
								'.$item['num_replies'].'
							</td>';
				}
				if ($bs['hits'] == 'yes') {
					$re.= '	<td>'.$item['num_views'].'</td>';
				}
				if ($bs['last'] == 'yes') {
					$re.= '	<td>
								'.$api->sitetime($item['posted'], 'ru').' '.$time.' &nbsp;&#8260;&nbsp;
								<a href="'.SITE_URL.'/forum/profile.php?id='.$item['poster_id'].'">'.$item['last_poster'].'</a>
							</td>';
				}
				$re.= '	</tr>';
			}
			$re.= '	</tbody>
					</table>';
		}

		return $re;
	}
}
