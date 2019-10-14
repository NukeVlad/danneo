<?php
/**
 * File:        /core/userbase/smf20/danneo.user.php
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
			'prefix'        => 'smf_',
			'table'         => '',
			'userid'        => 'id_member',
			'forumpath'     => 'forum/',
			'cookie'        => 'RYz13lodkF5IznIgoC9a',
			'cookiehost'    => 'domain.ru',
			'cookieexp'     => 3600,
			'avatarpath'    => '/forum/avatars/',
			'avatargalpath' => '/forum/gallery/',
			'setting'       => array(),
			'linkreg'       => 'forum/index.php?action=register',
			'linklost'      => 'forum/index.php?action=reminder',
			'linkprivmess'  => 'forum/index.php?action=pm',
			'linksendmess'  => '/forum/index.php?action=pm;sa=send',
			'linkprofile'   => '/forum/index.php?action=profile;u='
		);

	public $set = array
		(
			'prefix'       => 'all_prefix',
			'forumpath'    => 'all_path',
			'cookie'       => 'Cookies',
			'cookiehost'   => 'Cookies domaine',
			'cookieexp'    => 'cookies_expire',
			'avatarpath'   => 'avatar_path',
			'linkreg'      => 'registr',
			'linklost'     => 'rest_pass',
			'linkprivmess' => 'private_message',
			'linksendmess' => 'link_send_mess',
			'linkprofile'  => 'your_profile'
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
			'login'    => array('member_name', 'login', 'input'),
			'register' => array('date_registered', 'registr_date', 'date'),
			'visit'    => array('last_login', 'last_visit', 'date'),
			'email'    => array('email_address', 'E-Mail', 'input'),
		);

	public $groups = array();
	public $error = 1;

	public $assoc = array
		(
			'userid'  => 'id_member',
			'uname'   => 'member_name',
			'regdate' => 'date_registered',
			'avatar'  => 'avatar',
			'www'     => 'website_url',
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
			if(is_array($n)){
				$this->data = $n;
			}
		}

		if (is_array($group))
		{
			foreach ($group as $k => $v)
			{
				if ($v['fid'] > 0) {
					$this->groups[$v['fid']] = array('gid'=>$v['gid'],'title'=>$v['title']);
				}
			}
		}

		$this->data['table'] = $this->data['prefix'].'members';
		if ($logged) {
			return $this->userarray();
		}

		$this->error = $this->error;
	}

	function avatar($scr, $link = FALSE)
	{
		$avatar = null;
		if ($scr)
		{
			$avatar = ($link) ? '<img src="'.$scr.'">' : $scr;
		}

		return $avatar;
	}

	function gravatar_url($email)
	{
		global $setting;

		$setting = array();
		$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
		while ($item = $this->db->fetchassoc($set))
		{
			$setting[$item['variable']] = $item['value'];
		}

		static $url_params = null;

		if ($url_params === null)
		{
			$ratings = array('G', 'PG', 'R', 'X');
			$defaults = array('mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank');

			$url_params = array();
			if ( ! empty($setting['gravatarMaxRating']) AND in_array($setting['gravatarMaxRating'], $ratings))
			{
				$url_params[] = 'rating=' . $setting['gravatarMaxRating'];
			}

			if ( ! empty($setting['gravatarDefault']) AND in_array($setting['gravatarDefault'], $defaults))
			{
				$url_params[] = 'default=' . $setting['gravatarDefault'];
			}

			if ( ! empty($setting['avatar_max_width_external']))
			{
				$size_string = (int) $setting['avatar_max_width_external'];
			}

			if ( ! empty($setting['avatar_max_height_external']) AND ! empty($size_string))
			{
				if ((int) $setting['avatar_max_height_external'] < $size_string)
				{
					$size_string = $setting['avatar_max_height_external'];
				}
			}

			if ( ! empty($size_string))
			{
				$url_params[] = 's=' . $size_string;
			}
		}

		$http_method = ( ! empty($setting['force_ssl']) AND $setting['force_ssl'] == 2) ? 'https://secure' : 'http://www';

		return $http_method . '.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . '?' . implode('&', $url_params);
	}

	function userarray()
	{
		global $group;

		if (isset($_COOKIE[$this->data['cookie']]))
		{
			if (preg_match('~^a:[34]:\{i:0;i:\d{1,7};i:1;s:(0|128):"([a-fA-F0-9]{128})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$this->data['cookie']]) === 1)
			{
				list($smf['userid'], $smf['pass']) = unserialize($_COOKIE[$this->data['cookie']]);
				$smf['userid'] = ( ! empty($smf['userid']) AND mb_strlen($smf['pass']) > 0) ? intval($smf['userid']) : 0;
				if ($smf['userid'] > 0)
				{
					if (
						$newuser = $this->db->fetchassoc
										(
											$this->db->query
												(
													"SELECT u.*, b.id_ban FROM ".$this->data['prefix']."members AS u LEFT JOIN ".$this->data['prefix']."ban_items AS b ON (b.id_member = u.id_member)
													 WHERE u.id_member  = '".$smf['userid']."' AND b.id_member IS NULL"
												)
										)
					) {
						if (intval($newuser['id_member']) > 0)
						{
							$setting = array();
							$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
							while ($item = $this->db->fetchassoc($set))
							{
								$setting[$item['variable']] = $item['value'];
							}
							$avatar = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."attachments WHERE id_member  = '".$newuser['id_member']."'"));

							if ( ! empty($setting['gravatarOverride']) OR ( ! empty($setting['gravatarEnabled']) AND stristr($newuser['avatar'], 'gravatar://')))
							{
								if ( ! empty($setting['gravatarAllowExtraEmail']) AND stristr($newuser['avatar'], 'gravatar://') AND strlen($newuser['avatar']) > 11)
								{
									$url_avatar = $this->gravatar_url($smcFunc['substr']($newuser['avatar'], 11));
								}
								else
								{
									$url_avatar = $this->gravatar_url($newuser['email_address']);
								}
							}
							else
							{
								if ( ! empty($newuser['avatar']))
								{
									$url_avatar = stristr($newuser['avatar'], 'http://') ? $newuser['avatar'] : $setting['avatar_url'] . '/' . $newuser['avatar'];
								}
								elseif ( ! empty($avatar['filename']))
								{
									$url_avatar = $setting['custom_avatar_url'] . '/' . $avatar['filename'];
								}
								else
								{
									$url_avatar = $setting['avatar_url'] . '/default.png';
								}
							}

							$gid = (isset($this->groups[$newuser['id_group']]['gid']) AND intval($this->groups[$newuser['id_group']]['gid']) > 0) ? $this->groups[$newuser['id_group']]['gid'] : 0;

							$this->usermain = array
								(
									'logged'    => 1,
									'userid'    => intval($newuser['id_member']),
									'gid'       => $gid,
									'uname'     => $newuser['member_name'],
									'umail'     => $newuser['email_address'],
									'regdate'   => $newuser['date_registered'],
									'lastvisit' => $newuser['last_login'],
									'phone'     => '',
									'city'      => '',
									'skype'     => '',
									'www'       => $newuser['website_url'],
									'newmsg'    => intval($newuser['new_pm']),
									'avatar'    => $this->avatar($url_avatar, TRUE)
								);
						}
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
		return $this->db->numrows
					(
						$this->db->query
							(
								"SELECT id_member FROM ".$this->data['prefix']."members
								 WHERE email_address = '".$this->db->escape($mail)."' AND id_member <> ".$this->usermain['userid'].""
							)
					);
	}

	function addmail($mail)
	{
		$this->db->query
			(
				"UPDATE ".$this->data['prefix']."members SET email_address = '".$this->db->escape($mail)."'
				 WHERE id_member = ".$this->usermain['userid'].""
			);
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
		$this->db->query
			(
				"UPDATE ".$this->data['prefix']."members SET website_url = '".$this->db->escape($www)."'
				 WHERE id_member = '".$this->usermain['userid']."'"
			);
	}

	function associat($in = FALSE)
	{
		$associat = array();

		if ($in)
		{
			$inq = $this->db->query
						(
							"SELECT id_member, member_name, date_registered, avatar, website_url, email_address FROM ".$this->data['prefix']."members
							 WHERE id_member IN (".$this->db->escape($in).")"
						);

			$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
			while ($items = $this->db->fetchassoc($set))
			{
				$setting[$items['variable']] = $items['value'];
			}

			while ($item = $this->db->fetchassoc($inq))
			{
				$avatar = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."attachments WHERE id_member  = '".$item['id_member']."'"));

				if ( ! empty($setting['gravatarOverride']) OR ( ! empty($setting['gravatarEnabled']) AND stristr($item['avatar'], 'gravatar://')))
				{
					if ( ! empty($setting['gravatarAllowExtraEmail']) AND stristr($item['avatar'], 'gravatar://') AND strlen($item['avatar']) > 11)
					{
						$url_avatar = $this->gravatar_url($smcFunc['substr']($item['avatar'], 11));
					}
					else
					{
						$url_avatar = $this->gravatar_url($item['email_address']);
					}
				}
				else
				{
					if ( ! empty($item['avatar']))
					{
						$url_avatar = stristr($item['avatar'], 'http://') ? $item['avatar'] : $setting['avatar_url'] . '/' . $item['avatar'];
					}
					elseif ( ! empty($avatar['filename']))
					{
						$url_avatar = $setting['custom_avatar_url'] . '/' . $avatar['filename'];
					}
					else
					{
						$url_avatar = $setting['avatar_url'] . '/default.png';
					}
				}

				$associat[$item['id_member']] = array
					(
						'userid'  => $item['id_member'],
						'uname'   => $item['member_name'],
						'regdate' => $item['date_registered'],
						'avatar'  => $this->avatar($url_avatar, FALSE),
						'www'     => $item['website_url'],
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
								"SELECT id_member, member_name, date_registered, avatar, website_url, email_address
								 FROM ".$this->data['prefix']."members
								 WHERE ".$this->db->escape($this->assoc[$key])." = '".$this->db->escape($val)."'"
							)
						);
			if ($udata)
			{
				$assoc = array
					(
						'userid'  => $udata['id_member'],
						'uname'   => $udata['member_name'],
						'regdate' => $udata['date_registered'],
						'www'     => $udata['website_url'],
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
										"SELECT * FROM ".$this->data['prefix']."members
										 WHERE member_name = '".$this->db->escape($login)."'"
									)
							);

			$newban = $this->db->fetchassoc
							(
								$this->db->query
									(
										"SELECT id_ban FROM ".$this->data['prefix']."ban_items
										 WHERE id_member = '".$this->db->escape($newuser['id_member'])."' LIMIT 1"
									)
							);

			if (intval($newban['id_ban']) > 0) {
				define('THIS_BANNED', 1);
			}

			if (intval($newuser['id_member']) > 0 AND intval($newban['id_ban']) == 0)
			{
				$post_pass = $this->un_htmlspecialchars($pass);
				if ($this->password_verify(strtolower($newuser['member_name']).$post_pass, $newuser['passwd']))
				{
					$data = $this->hash_salt($newuser['passwd'],$newuser['password_salt']);
					$printcookie = serialize(array(intval($newuser['id_member']), $data, NEWTIME + $this->data['cookieexp'], 0));
					setcookie($this->data['cookie'], $printcookie, NEWTIME + $this->data['cookieexp'], '/', '', 0, 1);

					$setting = array();
					$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
					while ($item = $this->db->fetchassoc($set))
					{
						$setting[$item['variable']] = $item['value'];
					}
					$avatar = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."attachments WHERE id_member  = '".$newuser['id_member']."'"));

					if ( ! empty($setting['gravatarOverride']) OR ( ! empty($setting['gravatarEnabled']) AND stristr($newuser['avatar'], 'gravatar://')))
					{
						if ( ! empty($setting['gravatarAllowExtraEmail']) AND stristr($newuser['avatar'], 'gravatar://') AND strlen($newuser['avatar']) > 11)
						{
							$url_avatar = $this->gravatar_url($smcFunc['substr']($newuser['avatar'], 11));
						}
						else
						{
							$url_avatar = $this->gravatar_url($newuser['email_address']);
						}
					}
					else
					{
						if ( ! empty($newuser['avatar']))
						{
							$url_avatar = stristr($newuser['avatar'], 'http://') ? $newuser['avatar'] : $setting['avatar_url'] . '/' . $newuser['avatar'];
						}
						elseif ( ! empty($avatar['filename']))
						{
							$url_avatar = $setting['custom_avatar_url'] . '/' . $avatar['filename'];
						}
						else
						{
							$url_avatar = $setting['avatar_url'] . '/default.png';
						}
					}

					$gid = (isset($this->groups[$newuser['id_group']]['gid']) AND intval($this->groups[$newuser['id_group']]['gid']) > 0) ? $this->groups[$newuser['id_group']]['gid'] : 0;
					$this->usermain = array
						(
							'logged'    => 1,
							'userid'    => intval($newuser['id_member']),
							'gid'       => $gid,
							'uname'     => $newuser['member_name'],
							'umail'     => $newuser['email_address'],
							'regdate'   => $newuser['date_registered'],
							'lastvisit' => $newuser['last_login'],
							'phone'     => '',
							'city'      => '',
							'skype'     => '',
							'www'       => $newuser['website_url'],
							'newmsg'    => intval($newuser['new_pm']),
							'avatar'    => $this->avatar($url_avatar, TRUE)
						);
				}
			}
		}
	}

	function password_verify($password, $hash)
	{
		if (!function_exists('crypt'))
		{
			trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
			return false;
		}

		if ($this->_strlen($password) > 72)
		{
			$password = $this->_substr($password, 0,72);
		}

		$ret = crypt($password, $hash);
		if (!is_string($ret) || $this->_strlen($ret) != $this->_strlen($hash) || $this->_strlen($ret) <= 13)
		{
			return false;
		}

		$status = 0;
		for ($i = 0; $i < $this->_strlen($ret); $i++)
		{
			$status |= (ord($ret[$i]) ^ ord($hash[$i]));
		}

		return $status === 0;
	}

	function hash_salt($password, $salt)
	{
		return hash('sha512', $password . $salt);
	}

	function _strlen($binary_string)
	{
		if (function_exists('mb_strlen'))
		{
			return mb_strlen($binary_string, '8bit');
		}
		return strlen($binary_string);
	}

	function _substr($binary_string, $start, $length)
	{
		if (function_exists('mb_substr'))
		{
			return mb_substr($binary_string, $start, $length, '8bit');
		}
		return substr($binary_string, $start, $length);
	}

	function un_htmlspecialchars($string)
	{
		global $context;

		static $translation = array();

		if (empty($context['character_set']))
			$charset = 'UTF-8';
		elseif (strpos($context['character_set'], 'ISO-8859-') !== false && !in_array($context['character_set'], array('ISO-8859-5', 'ISO-8859-15')))
			$charset = 'ISO-8859-1';
		else
			$charset = $context['character_set'];

		if (empty($translation))
			$translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES, $charset)) + array('&#039;' => '\'', '&#39;' => '\'', '&nbsp;' => ' ');

		return strtr($string, $translation);
	}

	function logout()
	{
		if ($this->usermain['logged'] == 1 AND intval($this->usermain['userid']) > 0)
		{
			setcookie($this->data['cookie'], '', NEWTIME - $this->data['cookieexp'], '/');

			if (isset($_COOKIE['PHPSESSID']))
			{
				setcookie('PHPSESSID', '', NEWTIME - $this->data['cookieexp'], '/');
			}
		}
	}

	function group()
	{
		$associat = array();

		$inq = $this->db->query("SELECT * FROM ".$this->data['prefix']."membergroups");
		while ($item = $this->db->fetchassoc($inq))
		{
			$associat[$item['id_group']] = $item['group_name'];
		}

		return $associat;
	}

	function userlist($sf, $nu, $p, $sess, $sql)
	{
		global $lang;

		if ($this->db->tables($this->data['prefix']."members"))
		{
			$inq = $this->db->query("SELECT u.*, b.id_ban FROM ".$this->data['prefix']."members AS u LEFT JOIN ".$this->data['prefix']."ban_items AS b ON b.id_member = u.id_member ".str_replace('email_address', 'u.email_address', $sql)." ORDER BY u.id_member DESC LIMIT ".$sf.", ".$nu);
			while ($item = $this->db->fetchassoc($inq))
			{
				$style = (intval($item['id_ban']) > 0) ? 'noactive' : '';
				echo '	<tr>
							<td class="'.$style.' al site">'.$item['member_name'].'</td>
							<td class="'.$style.'">'.format_time($item['date_registered'], 0, 1).'</td>
							<td class="'.$style.'">'.(($item['last_login'] != 0) ? format_time($item['last_login'], 1, 1) : '&#8212;').'</td>
							<td class="'.$style.'"><a href=mailto:'.$item['email_address'].'>'.$item['email_address'].'</a></td>
							<td class="'.$style.'">';
				if ($item['id_member'] > 0)
				{
					if (intval($item['id_ban']) > 0) {
						echo '	<a href="index.php?dn=unban&amp;uid='.$item['id_member'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/images/unblock.gif" alt="'.$lang['ban_del'].'" /></a>';
					} else {
						echo '	<a href="index.php?dn=edit&amp;uid='.$item['id_member'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/images/edit.gif" alt="'.$lang['all_edit'].'" /></a>';
					}
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
			$item = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."members WHERE id_member = '".$uid."'"));
			echo '	<table  class="work">
						<caption>'.$lang['edit_user'].'</caption>
						<tr>
							<td>'.$lang['all_user'].'</td>
							<td><strong>'.$item['member_name'].'</strong></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><input name="edit[mail]" size="50" maxlength="50" type="text" value="'.$item['email_address'].'"></td>
						</tr>
						<tr>
							<td>URL</td>
							<td><input name="edit[www]" size="50" maxlength="50" type="text" value="'.$item['website_url'].'"></td>
						</tr>';

		}
	}

	function usersave($uid, $edit)
	{
		if ($uid > 0 AND is_array($edit))
		{
			if (isset($edit['mail']) AND ! empty($edit['mail']) AND $this->is_umail($uid, $edit['mail']) == 0)
			{
				$this->db->query("UPDATE ".$this->data['prefix']."members SET email_address = '".$this->db->escape($edit['mail'])."' WHERE id_member = '".$uid."'");
			}

			if (isset($edit['www']))
			{
				$edit['www'] = preg_match('/^[http]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i',$edit['www']) ? $edit['www'] : '';
				$this->db->query("UPDATE ".$this->data['prefix']."members SET website_url = '".$this->db->escape($edit['www'])."' WHERE id_member = '".$uid."'");
			}
		}
	}

	function is_umail($uid, $umail)
	{
		return $this->db->numrows
				(
					$this->db->query
						(
							"SELECT id_member FROM ".$this->data['prefix']."members
							 WHERE email_address = '".$this->db->escape($edit['mail'])."'
							 AND id_member <> ".$uid
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
						"SELECT t.*, m.*, b.*, s.* FROM ".$this->data['prefix']."topics AS t
						 LEFT JOIN ".$this->data['prefix']."messages AS m ON (m.id_msg = t.id_last_msg)
						 LEFT JOIN ".$this->data['prefix']."members AS s ON (s.id_member = t.id_member_started)
						 LEFT JOIN ".$this->data['prefix']."boards AS b ON (m.id_board = b.id_board)
						 ORDER BY m.poster_time ".$bs['order']." LIMIT 0, ".$bs['col']
					);

		$user = $this->db->query("SELECT id_member, real_name FROM ".$this->data['prefix']."members");
		while ($p = $this->db->fetchassoc($user))
		{
			$poster[$p['id_member']] = $p;
		}

		if ($this->db->numrows($inq) > 0)
		{
			$re.= '	<table class="forum">
					<tbody>
						<tr>
							<th>'.$lang['subject'].'</th>';
			if ($bs['author'] == 'yes') {
				$re.= '		<th>'.$lang['author'].'</th>';
			}
			if ($bs['replie'] == 'yes') {
				$re.= '		<th>'.$lang['of_replies'].'</th>';
			}
			$re.= '			<th>'.$lang['all_hits'].'</th>';
			if ($bs['last'] == 'yes') {
				$re.= '		<th>'.$lang['last_replies'].'</th>';
			}
			$re.= '		</tr>
					</tbody>
					<tbody>';
			while ($item = $this->db->fetchassoc($inq))
			{
				$time = ($bs['time'] == 'yes') ? $api->sitetime($item['poster_time'], 'hm') : '';
				$subject = str_replace('Re: ', '', $item['subject']);
				$subject = str_word(deltags($subject), $bs['wrap']);

				$re.= '	<tr class="topic">
							<td><a href="'.SITE_URL.'/forum/index.php?topic='.$item['id_topic'].'.msg'.$item['id_msg'].'#new"'.$target.'>'.$subject.'</a></td>';
				if ($bs['author'] == 'yes') {
					$re.= '	<td><a href="'.$this->data['linkprofile'].$item['id_member_started'].'"'.$target.'>'.$item['real_name'].'</a></td>';
				}
				if ($bs['replie'] == 'yes') {
                  $re.= '	<td>'.$item['num_replies'].'</td>';
				}
				if ($bs['replie'] == 'yes') {
					$re.= '		<td>'.$item['num_views'].'</td>';
				}
				if ($bs['last'] == 'yes') {
					$re.= '	<td>
								'.$api->sitetime($item['poster_time'], 'ru').' '.$time.' &nbsp;&#8260;&nbsp;
								<a href="'.$this->data['linkprofile'].$item['id_member_updated'].'"'.$target.'>'.$poster[$item['id_member_updated']]['real_name'].'</a>
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
