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

	function userarray()
	{
		global $group;

		if (isset($_COOKIE[$this->data['cookie']]))
		{
			$_COOKIE[$this->data['cookie']] = stripslashes($_COOKIE[$this->data['cookie']]);

			if (preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$this->data['cookie']]) == 1)
			{
				list($smf['userid'], $smf['pass']) = unserialize($_COOKIE[$this->data['cookie']]);

				$smf['userid'] = ( ! empty($smf['userid']) AND mb_strlen($smf['pass']) > 0) ? intval($smf['userid']) : 0;
				if ($smf['userid'] > 0 AND mb_strlen($smf['pass']) < 41)
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
							$avatar = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."attachments WHERE id_member  = '".$newuser['id_member']."'"));
							$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
							while ($item = $this->db->fetchassoc($set))
							{
								$setting[$item['variable']] = $item['value'];
							}
							$url_avatar = $newuser['avatar'] == '' ? ($avatar['id_attach'] > 0 ? (empty($avatar['attachment_type']) ? SITE_URL.'/forum/index.php?action=dlattach;attach='.$avatar['id_attach'].';type=avatar' : $setting['custom_avatar_url'].'/'.$avatar['filename']) : '') : (stristr($newuser['avatar'], 'http://') ? $newuser['avatar'] : $setting['avatar_url'].'/'.$newuser['avatar']);

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
				$url_avatar = $item['avatar'] == '' ? ($avatar['id_attach'] > 0 ? (empty($avatar['attachment_type']) ? '/forum/index.php?action=dlattach;attach='.$avatar['id_attach'].';type=avatar' : $setting['custom_avatar_url'].'/'.$avatar['filename']) : '') : (stristr($item['avatar'], 'http://') ? $item['avatar'] : $setting['avatar_url'].'/'.$item['avatar']);

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
				$newpass = sha1(strtolower($newuser['member_name']).$pass);
				if ($newpass == $newuser['passwd'])
				{
					$printcookie = serialize(array($newuser['id_member'], sha1($newuser['passwd'].$newuser['password_salt']), NEWTIME + $this->data['cookieexp'], 0));
					setcookie($this->data['cookie'], $printcookie, NEWTIME + $this->data['cookieexp'], '/', '', 0);

					$avatar = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['prefix']."attachments WHERE id_member  = '".$newuser['id_member']."'"));
					$set = $this->db->query("SELECT * FROM ".$this->data['prefix']."settings");
					while ($item = $this->db->fetchassoc($set))
					{
						$setting[$item['variable']] = $item['value'];
					}
					$url_avatar = $newuser['avatar'] == '' ? ($avatar['id_attach'] > 0 ? (empty($avatar['attachment_type']) ? SITE_URL.'/forum/index.php?action=dlattach;attach='.$avatar['id_attach'].';type=avatar' : $setting['custom_avatar_url'].'/'.$avatar['filename']) : '') : (stristr($newuser['avatar'], 'http://') ? $newuser['avatar'] : $setting['avatar_url'].'/'.$newuser['avatar']);

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
