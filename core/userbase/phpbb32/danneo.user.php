<?php
/**
 * File:        /core/userbase/phpbb31/danneo.user.php
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
	public $itoa64;

	public $data = array
		(
			'prefix'        => 'phpbb_',
			'table'         => '',
			'userid'        => 'user_id',
			'forumpath'     => '/forum/',
			'avatarpath'    => '/forum/images/avatars/upload/',
			'avatargalpath' => '/forum/images/avatars/gallery/',
			'setting'       => array(),
			'linkreg'       => 'forum/ucp.php?mode=register',
			'linklost'      => 'forum/ucp.php?mode=sendpassword',
			'linkprivmess'  => 'forum/ucp.php?i=pm&amp;folder=inbox',
			'linksendmess'  => '/forum/ucp.php?i=pm&amp;mode=compose&amp;u=',
			'linkprofile'   => '/forum/memberlist.php?mode=viewprofile&amp;u='
		);

	public $set = array
		(
			'prefix'        => 'all_prefix',
			'forumpath'     => 'all_path',
			'avatarpath'    => 'avatar_path',
			'avatargalpath' => 'avatar_galpath',
			'linkreg'       => 'registr',
			'linklost'      => 'rest_pass',
			'linkprivmess'  => 'private_message',
			'linksendmess'  => 'link_send_mess',
			'linkprofile'   => 'your_profile'
		);

	public $usermain = array
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
			'login'    => array('username','login','input'),
			'register' => array('user_regdate','registr_date','date'),
			'visit'    => array('user_lastvisit','last_visit','date'),
			'email'    => array('user_email','E-Mail','input'),
		);

	public $groups = array();
	public $error = 1;

	public $assoc = array
		(
			'userid'  => 'user_id',
			'uname'   => 'username',
			'regdate' => 'user_regdate',
			'avatar'  => 'user_avatar',
			'www'     => 'user_website',
			'city'    => '',
			'phone'   => '',
			'skype'   => ''
		);

	function __construct(&$db, $logged = FALSE)
	{
		global $config, $conf, $group;

		$this->db = &$db;
		if (isset($config['datainteg']) AND ! empty($config['datainteg']) OR isset($conf['datainteg']) AND ! empty($conf['datainteg']))
		{
			$data = (isset($conf['datainteg']) AND ! empty($conf['datainteg'])) ? Json::decode($conf['datainteg']) : Json::decode($config['datainteg']);
			$n = '';
			foreach ($this->data as $k => $v) {
				$n[$k] = (isset($data[$k])) ? $data[$k] : $v;
			}
			if (is_array($n)) {
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

		$this->data['table'] = $this->data['prefix'].'users WHERE user_id > 1 AND user_type <> 2';
		if ($logged)
		{
			$sinq = $this->db->query
						(
							"SELECT config_name, config_value FROM ".$this->data['prefix']."config
							 WHERE config_name IN ('browser_check', 'cookie_domain', 'cookie_name', 'forwarded_for_check', 'ip_check', 'session_length')"
						);

			while ($sitem = $this->db->fetchassoc($sinq))
			{
				$this->data['setting'][$sitem['config_name']] = $sitem['config_value'];
			}

			return $this->userarray();
		}
	}

	function short_ipv6($ip, $length)
	{
		if($length < 1)
			return '';

		$blocks = substr_count($ip, ':') + 1;
		if ($blocks < 9) {
			$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
		}

		if ($ip[0] == ':') {
			$ip = '0000' . $ip;
		}

		if ($length < 4) {
			$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
		}

		return $ip;
	}

	function avatar($type, $scr, $link = FALSE)
	{
		$avatar = '';

		if ($scr)
		{
			if ($type == 1) {
				$avatar = ($link) ? '<img src="'.$this->data['forumpath'].'download/file.php?avatar='.$scr.'">' : $this->data['forumpath'].'download/file.php?avatar='.$scr;
			}
			if ($type == 2) {
				$avatar = ($link) ? '<img src="'.$scr.'">' : $scr;
			}
			if ($type == 3) {
				$avatar = ($link) ? '<img src="'.$this->data['avatarpath'].$this->data['avatargalpath'].$scr.'">' : $this->data['avatarpath'].$this->data['avatargalpath'].$scr;
			}
		}

		return $avatar;
	}

	function userarray()
	{
		global $group;

		$this->ua['session'] = isset($_COOKIE[$this->data['setting']['cookie_name'].'_sid']) ? stripslashes($_COOKIE[$this->data['setting']['cookie_name'].'_sid']) : '';

		if( ! preg_match('/^[A-Za-z0-9]*$/',$this->ua['session']))
		{
			return;
		}

		$this->ua['userid'] = isset($_COOKIE[$this->data['setting']['cookie_name'].'_u']) ? intval($_COOKIE[$this->data['setting']['cookie_name'].'_u']) : '';
		$this->ua['browser'] = ( ! empty($_SERVER['HTTP_USER_AGENT'])) ? substr(htmlspecialchars($_SERVER['HTTP_USER_AGENT']), 0, 149) : '';
		$this->ua['forward'] = ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? (string) $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

		if ($this->data['setting']['forwarded_for_check'])
		{
			$this->ua['forward'] = preg_replace('#, +#', ', ', $this->ua['forward']);
			$ipv4 = '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
			$ipv6 = '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))$#i';
			$ips = explode(', ', $this->ua['forward']);

			foreach ($ips as $ip)
			{
				if( ! empty($ip) AND ! preg_match($ipv4, $ip) AND ! preg_match($ipv6, $ip))
				{
					$this->ua['forward'] = '';
					break;
				}
			}
		}

		$this->ua['ip'] = htmlspecialchars(( ! empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : (( ! empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv('REMOTE_ADDR')));

		if ( ! empty($this->ua['session']) AND $this->ua['userid'] > 1)
		{
			$new = $this->db->fetchassoc
						(
							$this->db->query
								(
									"SELECT u.user_id, s.* FROM ".$this->data['prefix']."sessions s, ".$this->data['prefix']."users u
									 WHERE session_id = '".$this->db->escape(trim($this->ua['session']))."'
									 AND s.session_ip = '".$this->db->escape($this->ua['ip'])."'
									 AND s.session_time > ".(int)(NEWTIME - $this->data['setting']['session_length'])."
									 AND u.user_id = s.session_user_id"
								)
						);

			if (isset($new['user_id']))
			{
				if (strpos($this->ua['ip'], ':') !== FALSE AND strpos($new['session_ip'], ':') !== FALSE)
				{
					$s_ip = short_ipv6($new['session_ip'], $this->data['setting']['ip_check']);
					$u_ip = short_ipv6($this->ua['ip'], $this->data['setting']['ip_check']);
				}
				else
				{
					$s_ip = implode('.', array_slice(explode('.', $new['session_ip']), 0, $this->data['setting']['ip_check']));
					$u_ip = implode('.', array_slice(explode('.', $this->ua['ip']), 0, $this->data['setting']['ip_check']));
				}

				$s_browser = ($this->data['setting']['browser_check']) ? strtolower(substr($new['session_browser'], 0, 149)) : '';
				$u_browser = ($this->data['setting']['browser_check']) ? strtolower(substr($this->ua['browser'], 0, 149)) : '';
				$s_forwarded_for = ($this->data['setting']['forwarded_for_check']) ? substr($new['session_forwarded_for'], 0, 254) : '';
				$u_forwarded_for = ($this->data['setting']['forwarded_for_check']) ? substr($this->ua['forward'], 0, 254) : '';

				if ($u_ip === $s_ip AND $s_browser === $u_browser AND $s_forwarded_for === $u_forwarded_for)
				{
					$newuser = $this->db->fetchassoc
									(
										$this->db->query
											(
												"SELECT u.*, b.ban_id FROM ".$this->data['prefix']."users u
												 LEFT JOIN ".$this->data['prefix']."banlist b ON b.ban_userid = u.user_id
												 WHERE user_id = '".intval($new['user_id'])."' LIMIT 1"
											)
									);

					if (intval($newuser['ban_id']) == 0 AND intval($newuser['user_id']) > 1)
					{
						$gid = (isset($this->groups[$newuser['group_id']]['gid']) AND intval($this->groups[$newuser['group_id']]['gid']) > 0) ? $this->groups[$newuser['group_id']]['gid'] : 0;

						$this->usermain = array
							(
								'logged'    => 1,
								'userid'    => intval($newuser['user_id']),
								'gid'       => $gid,
								'uname'     => $newuser['username'],
								'umail'     => $newuser['user_email'],
								'regdate'   => $newuser['user_regdate'],
								'lastvisit' => $newuser['user_lastvisit'],
								'phone'     => '',
								'city'      => '',
								'skype'     => '',
								'www'       => '',
								'newmsg'    => intval($newuser['user_new_privmsg']),
								'newmsgnr'  => intval($newuser['user_unread_privmsg']),
								'avatar'    => $this->avatar(intval($newuser['user_avatar_type']), $newuser['user_avatar'], TRUE)
							);
					}
				}
			}
		}
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
        return $this->db->numrows($this->db->query("SELECT user_id FROM ".$this->data['prefix']."users WHERE user_email='".$this->db->escape($mail)."' AND user_id <> ".$this->usermain['userid'].""));
    }

	function addmail($mail)
	{
		$this->db->query("UPDATE ".$this->data['prefix']."users SET user_email = '".$this->db->escape($mail)."' WHERE user_id = ".$this->usermain['userid']."");
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

	function associat($in = FALSE)
	{
		$associat = array();

		if ($in)
		{
			$inq = $this->db->query
					(
						"SELECT user_id, username, user_regdate, user_avatar, user_avatar_type
						 FROM ".$this->data['prefix']."users
						 WHERE user_id IN (".$this->db->escape($in).")"
					);

			while ($item = $this->db->fetchassoc($inq))
			{
				$associat[$item['user_id']] = array
					(
						'userid'  => $item['user_id'],
						'uname'   => $item['username'],
						'regdate' => $item['user_regdate'],
						'avatar'  => $this->avatar(intval($item['user_avatar_type']), $item['user_avatar'], FALSE),
						'phone'   => '',
						'city'    => '',
						'skype'   => ''
						'www'     => ''
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
								"SELECT user_id, username, user_regdate, user_avatar, user_avatar_type
								 FROM ".$this->data['prefix']."users
								 WHERE ".$this->db->escape($this->assoc[$key])." = '".$this->db->escape($val)."'"
							)
						);
			if ($udata)
			{
				$assoc = array
					(
						'userid'  => $udata['user_id'],
						'uname'   => $udata['username'],
						'regdate' => $udata['user_regdate'],
						'www'     => '',
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
			$newuser = $this->db->fetchassoc
							(
								$this->db->query
									(
										"SELECT u.*, b.ban_id FROM ".$this->data['prefix']."users u
										 LEFT JOIN ".$this->data['prefix']."banlist b ON b.ban_userid = u.user_id
										 WHERE u.username = '".$this->db->escape($login)."'"
									)
							);

			if (intval($newuser['ban_id']) > 0)
			{
				define('THIS_BANNED', 1);
			}

			if (intval($newuser['user_id']) > 1 AND intval($newuser['ban_id']) == 0)
			{
				if ($this->check_password($pass, $newuser['user_password']))
				{
					$this->ua['sessionhash'] = md5(uniqid($this->ua['ip']));
					$this->ua['forward'] = substr($this->ua['forward'], 0, 254);

					$this->db->query
						(
							"REPLACE INTO ".$this->data['prefix']."sessions (
							 session_id,
							 session_user_id,
							 session_last_visit,
							 session_start,
							 session_time,
							 session_ip,
							 session_browser,
							 session_forwarded_for,
							 session_page,
							 session_viewonline,
							 session_autologin,
							 session_admin
							 ) VALUES (
							 '".$this->db->escape($this->ua['sessionhash'])."',
							 '".intval($newuser['user_id'])."',
							 '".NEWTIME."',
							 '".NEWTIME."',
							 '".NEWTIME."',
							 '".$this->db->escape($this->ua['ip'])."',
							 '".$this->db->escape($this->ua['browser'])."',
							 '".$this->db->escape($this->ua['forward'])."', '', 0, 0, 0
							)"
						);

					setcookie($this->data['setting']['cookie_name'].'_u', $newuser['user_id'], NEWTIME + 31536000, '/', $this->data['setting']['cookie_domain'], 0);
					setcookie($this->data['setting']['cookie_name'].'_k', 0, NEWTIME + 31536000, '/', $this->data['setting']['cookie_domain'], 0);
					setcookie($this->data['setting']['cookie_name'].'_sid', $this->ua['sessionhash'], NEWTIME + 31536000, '/', $this->data['setting']['cookie_domain'], 0);

					$gid = (isset($this->groups[$newuser['group_id']]['gid']) AND intval($this->groups[$newuser['group_id']]['gid']) > 0) ? $this->groups[$newuser['group_id']]['gid'] : 0;

					$this->usermain = array
						(
							'logged'    => 1,
							'userid'    => intval($newuser['user_id']),
							'gid'       => $gid,
							'uname'     => $newuser['username'],
							'umail'     => $newuser['user_email'],
							'regdate'   => $newuser['user_regdate'],
							'lastvisit' => $newuser['user_lastvisit'],
							'phone'     => '',
							'city'      => '',
							'skype'     => '',
							'www'       => '',
							'newmsg'    => intval($newuser['user_new_privmsg']),
							'newmsgnr'  => intval($newuser['user_unread_privmsg']),
							'avatar'    => $this->avatar(intval($newuser['user_avatar_type']),$newuser['user_avatar'],TRUE)
						);
				}
			}
		}
	}

	function encode64($input, $count)
	{
		$output = '';
		$i = 0;
		do {
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];
			if ($i < $count)
				$value |= ord($input[$i]) << 8;
			$output .= $this->itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
				break;
			if ($i < $count)
				$value |= ord($input[$i]) << 16;
			$output .= $this->itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
				break;
			$output .= $this->itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}

	function crypt_private($password, $setting)
	{
		$output = '*0';
		if (substr($setting, 0, 2) == $output)
			$output = '*1';

		$id = substr($setting, 0, 3);

		if ($id != '$P$' && $id != '$H$')
			return $output;

		$count_log2 = strpos($this->itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
			return $output;

		$count = 1 << $count_log2;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
			return $output;

		if (PHP_VERSION >= '5') {
			$hash = md5($salt . $password, TRUE);
			do {
				$hash = md5($hash . $password, TRUE);
			} while (--$count);
		} else {
			$hash = pack('H*', md5($salt . $password));
			do {
				$hash = pack('H*', md5($hash . $password));
			} while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= $this->encode64($hash, 16);

		return $output;
	}

	function check_password($password, $stored_hash)
	{
		$hash = $this->crypt_private($password, $stored_hash);
		if ($hash[0] == '*')
			$hash = crypt($password, $stored_hash);

		return $hash == $stored_hash;
	}

	function logout()
	{
		if ($this->usermain['logged'] == 1 AND intval($this->usermain['userid']) > 0)
		{
			setcookie($this->data['cookie'].'_data', '', 1);
			setcookie($this->data['cookie'].'_sid', '', 0);

			$this->db->query("UPDATE ".$this->data['prefix']."users SET user_lastvisit = ".NEWTIME." WHERE user_id = '".$this->usermain['userid']."'");

			$this->db->query("DELETE FROM ".$this->data['prefix']."sessions_keys WHERE user_id = '".$this->usermain['userid']."'");
			$this->db->query("DELETE FROM ".$this->data['prefix']."sessions WHERE session_user_id = '".$this->usermain['userid']."'");
		}
	}

	function group()
	{
		$associat = array();
		$inq = $this->db->query("SELECT * FROM ".$this->data['prefix']."groups");
		while ($item = $this->db->fetchassoc($inq)) {
			$associat[$item['group_id']] = $item['group_name'];
		}
		return $associat;
	}

	function userlist($sf, $nu, $p, $sess, $sql)
	{
        global $lang;

		if ($this->db->tables($this->data['prefix']."users"))
		{
			$inq = $this->db->query("SELECT u.*, b.ban_id FROM ".$this->data['prefix']."users AS u LEFT JOIN ".$this->data['prefix']."banlist AS b ON b.ban_userid = u.user_id WHERE u.user_id > 1 AND u.user_type <> 2".$sql." ORDER BY u.user_id DESC LIMIT ".$sf.", ".$nu);
			while ($item = $this->db->fetchassoc($inq))
			{
				$style = (intval($item['ban_id']) > 0) ? 'noactive' : '';
				echo '	<tr>
							<td class="'.$style.' al site">'.$item['username'].'</td>
							<td class="'.$style.'">'.format_time($item['user_regdate'],0,1).'</td>
							<td class="'.$style.'">'.(($item['user_lastvisit'] != 0) ? format_time($item['user_lastvisit'],1,1) : '&#8212;').'</td>
							<td class="'.$style.'"><a href=mailto:'.$item['user_email'].'>'.$item['user_email'].'</a></td>
							<td class="'.$style.'">';
				if ($item['user_id'] > 1)
				{
					echo '		<a href="user.php?dn=edit&amp;uid='.$item['user_id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="template/images/edit.gif" alt="'.$lang['all_edit'].'" /></a>';
				}
				echo '		</td>
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
			$item = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."users WHERE user_id = '".$uid."'"));
			echo '	<table class="work">
						<caption>'.$lang['edit_user'].'</caption>
						<tr>
							<td>'.$lang['all_user'].'</td>
							<td><strong>'.$item['username'].'</strong></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><input name="edit[mail]" size="50" maxlength="50" type="text" value="'.$item['user_email'].'"></td>
						</tr>
						<tr>
							<td>URL</td>
							<td><input name="edit[www]" size="50" maxlength="50" type="text" value="'.$item['user_website'].'"></td>
						</tr>';
		}
	}

	function usersave($uid, $edit)
	{
		if ($uid > 0 AND is_array($edit))
		{
			if (isset($edit['mail']) AND ! empty($edit['mail']) AND $this->is_umail($uid, $edit['mail']) == 0) {
				$this->db->query("UPDATE ".$this->data['prefix']."users SET user_email = '".$this->db->escape($edit['mail'])."' WHERE user_id = '".$uid."'");
			}
		}
	}

	function is_umail($uid, $umail)
	{
		return $this->db->numrows
				(
					$this->db->query
					(
						"SELECT user_id FROM ".$this->data['prefix']."users
						 WHERE user_email = '".$this->db->escape($umail)."'
						 AND user_id <> ".$uid
					)
				);
	}

	function messagelast($bs)
	{
		global $api, $lang;

		$not = $re = null;
		$ignore = array();

		$pass = $this->db->query("SELECT forum_id, forum_password FROM ".$this->data['prefix']."forums");
		while ($item = $this->db->fetchassoc($pass))
		{
			if ( ! empty($item['forum_password'])) {
				$ignore[] = $item['forum_id'];
			}
		}

		if (count($ignore) > 0)
		{
			$not = " AND forum_id NOT IN (".implode(',', $ignore).") ";
		}

		$bs['order'] = (isset($bs['order']) AND $bs['order'] == 'desc') ? 'desc' : 'asc';
		$inq = $this->db->query
					(
						"SELECT * FROM ".$this->data['prefix']."topics WHERE topic_moved_id = 0
						".$not."ORDER BY topic_last_post_id ".$bs['order']." LIMIT 0, ".$bs['col']
					);

		if ($this->db->numrows($inq) > 0)
		{
			$target = ( ! empty($bs['target'])) ? ' target="'.$bs['target'].'"' : '';
			$re.= '<table class="forum">
					<tbody>
						<tr>
							<th>'.$lang['subject'].'</th>';
			if ($bs['author'] == 'yes') {
				$re.= '		<th>'.$lang['author'].'</th>';
			}
			if ($bs['replie'] == 'yes') {
				$re.= '		<th>'.$lang['of_replies'].'</th>';
			}
			if ($bs['hits'] == 'yes') {
				$re.= '		<th>'.$lang['all_hits'].'</th>';
			}
			if ($bs['last'] == 'yes') {
				$re.= '		<th>'.$lang['last_replies'].'</th>';
			}
			$re.= '		</tr>
					</tbody>
					<tbody>';
			while ($item = $this->db->fetchassoc($inq))
			{
				$time = ($bs['time'] == 'yes') ? $api->sitetime($item['topic_last_post_time'], 'hm') : '';
				$title = str_word(deltags($item['topic_title']), $bs['wrap']);

				$re.= '	<tr class="topic">
							<td>
								<a href="'.SITE_URL.'/forum/viewtopic.php?f='.$item['forum_id'].'&t='.$item['topic_id'].'&p='.$item['topic_last_post_id'].'#p'.$item['topic_last_post_id'].'"'.$target.'>'.$title.'</a>
							</td>';
				if ($bs['author'] == 'yes') {
					$re.= '	<td>
								<a href="'.SITE_URL.'/forum/memberlist.php?mode=viewprofile&amp;u='.$item['topic_poster'].'">'.$item['topic_first_poster_name'].'</a>
							</td>';
				}
				if ($bs['replie'] == 'yes') {
					$re.= '	<td>
								'.$item['topic_posts_approved'].'
							</td>';
				}
				if ($bs['hits'] == 'yes') {
					$re.= '	<td>'.$item['topic_views'].'</td>';
				}
				if ($bs['last'] == 'yes') {
					$re.= '	<td>
								'.$api->sitetime($item['topic_last_post_time'], 'ru').' '.$time.' &nbsp;&#8260;&nbsp;
								<a href="'.SITE_URL.'/forum/memberlist.php?mode=viewprofile&amp;u='.$item['topic_last_poster_id'].'">'.$item['topic_last_poster_name'].'</a>
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
