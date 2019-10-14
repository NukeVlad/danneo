<?php
/**
 * File:        /core/userbase/danneo/danneo.user.php
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
	public $error = 1;

	public $data = array(
		'table'        => '_user',
		'userid'       => 'userid',
		'cookie'       => '',
		'cookieexp'    => 7200,
		'linkreg'      => 'index.php?dn=user&amp;re=register',
		'linklost'     => 'index.php?dn=user&amp;re=lost',
		'linkprivmess' => '',
		'linkprofile'  => 'index.php?dn=user&amp;re=profile&amp;id=',
		'avatarpath'   => '/up/avatar/',
		'noavatar'     => 'clear.gif'
	);

	public $usermain = array(
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

	public $userfilter = array(
		'login'    => array('uname', 'login', 'input'),
		'register' => array('regdate', 'registr_date', 'date'),
		'visit'    => array('lastvisit', 'last_visit', 'date'),
		'email'    => array('umail', 'E-Mail', 'input')
	);

	public $assoc = array(
		'userid'  => 'userid',
		'uname'   => 'uname',
		'regdate' => 'regdate',
		'avatar'  => 'avatar',
		'city'    => 'city',
		'phone'   => 'phone',
		'skype'   => 'skype',
		'www'     => 'www'
	);

	function __construct($db, $logged = FALSE)
	{
		global $config, $basepref;

		$this->db = $db;
		$this->data['table'] = $basepref.$this->data['table'];

		if ($logged)
		{
			$this->data['cookie'] = 'user_'.$config['cookname'];
			$this->data['cookieexp'] = $config['cookexpire'];

			return $this->userarray();
		}
	}

	function avatar($str, $name, $re = FALSE)
	{
		global $config;

		$avatar = '';
		if ( ! empty($str))
		{
			$avatar = ($re) ? '<img src="'.SITE_URL.$this->data['avatarpath'].$str.'" alt="'.$name.'" />' : $this->data['avatarpath'].$str;
		}

		return $avatar;
	}

	function userarray()
	{
		global $config;

		if (isset($_COOKIE[$this->data['cookie']]))
		{
			list($idu, $pass, $name) = unserialize($_COOKIE[$this->data['cookie']]);
			$idu = preparse($idu,THIS_INT);
			$pass = substr($pass, 0, 32);
			$pass = preg_match('/^[a-z0-9]+$/', $pass) ? $pass : '';

			if ( ! empty($pass) AND $idu > 0)
			{
				$newuser = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['table']." WHERE userid = '".$idu."' AND active='1' LIMIT 1"));

				if (md5($newuser['uname'].$newuser['upass']) == $pass)
				{
					$this->usermain = array(
						'logged'    => 1,
						'userid'    => intval($newuser['userid']),
						'gid'       => intval($newuser['gid']),
						'uname'     => $newuser['uname'],
						'umail'     => $newuser['umail'],
						'regdate'   => $newuser['regdate'],
						'lastvisit' => $newuser['lastvisit'],
						'phone'     => $newuser['phone'],
						'city'      => $newuser['city'],
						'skype'     => $newuser['skype'],
						'www'       => $newuser['www'],
						'newmsg'    => '',
						'newmsgnr'  => '',
						'avatar'    => $this->avatar($newuser['avatar'],$newuser['uname'],1),
						'favatar'   => $newuser['avatar']
					);

					$cookie = serialize(array($newuser['userid'], md5($newuser['uname'].$newuser['upass']), $newuser['uname']));
					$urlpath = parse_url(SITE_URL);
					$cookpath = (isset($urlpath['path']) AND ! empty($urlpath['path'])) ? $urlpath['path'].'/' : '/';

					setcookie($this->data['cookie'], $cookie, NEWTIME +  $this->data['cookieexp'], $cookpath, '', 0, 1);
				}
			}
		}
	}

	function logout()
	{
		global $config;

		if ($this->usermain['logged'] == 1 AND intval($this->usermain['userid']) > 0)
		{
			$this->db->query("UPDATE ".$this->data['table']." SET lastvisit = '".NEWTIME."' WHERE userid = '".$this->usermain['userid']."'");
			$urlpath = parse_url(SITE_URL);
			$cookpath = (isset($urlpath['path']) AND ! empty($urlpath['path'])) ? $urlpath['path'].'/' : '/';

			setcookie($this->data['cookie'], '', time() - 3600, $cookpath);
		}
	}

	function checkpwd($passw)
	{
		global $config;

		if (
			preparse($passw, THIS_STRLEN) < $config['user']['minpass'] OR
			preparse($passw, THIS_STRLEN) > $config['user']['maxpass'] OR
			! preg_match('/^[a-zA-Z0-9]+$/D', $passw)
		) {
			return FALSE;
		}
		return TRUE;
	}

	function checklogin($login)
	{
		global $config;

		if (
			preparse($login, THIS_STRLEN) < $config['user']['minname'] OR
			preparse($login, THIS_STRLEN) > $config['user']['maxname'] OR
			! preg_match('/^[\p{L}\p{Nd}]+$/u', $login)
		) {
			return FALSE;
		}
		return TRUE;
	}

	function issetmail($mail)
	{
		$result =  $this->db->numrows
						(
							$this->db->query
								(
									"SELECT userid FROM ".$this->data['table']."
									 WHERE umail = '".$this->db->escape($mail)."'
									 AND userid <> ".$this->usermain['userid']
								)
						);
		return $result;
	}

	function addmail($mail)
	{
		$this->db->query
			(
				"UPDATE ".$this->data['table']." SET
				 umail = '".$this->db->escape($mail)."'
				 WHERE userid = ".$this->usermain['userid']
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
				"UPDATE ".$this->data['table']." SET
				 phone  = '".$this->db->escape($phone)."',
				 city   = '".$this->db->escape($city)."',
				 skype  = '".$this->db->escape($skype)."',
				 www    = '".$this->db->escape($www)."',
				 avatar = '".$this->db->escape($avatar)."'
				 WHERE userid = '".$this->usermain['userid']."'"
			);
	}

	function associat($in = FALSE)
	{
		$associat = array();
		if ($in)
		{
			$inq = $this->db->query("SELECT * FROM ".$this->data['table']." WHERE userid IN (".$this->db->escape($in).")");
			while ($item = $this->db->fetchassoc($inq))
			{
				$associat[$item['userid']] = array
					(
						'userid'  => $item['userid'],
						'uname'   => $item['uname'],
						'regdate' => $item['regdate'],
						'avatar'  => $this->avatar($item['avatar'], 0),
						'favatar' => $item['avatar'],
						'phone'   => $item['phone'],
						'city'    => $item['city'],
						'skype'   => $item['skype'],
						'www'     => $item['www']
					);
			}
		}
		return $associat;
	}

	function userdata($key, $val = FALSE)
	{
		$udata = array();
		if (isset($this->assoc[$key]) AND $val)
		{
			$udata = $this->db->fetchassoc
						(
							$this->db->query
							(
								"SELECT userid, gid, uname, umail, regdate, lastvisit, phone, city, www, skype, avatar
								 FROM ".$this->data['table']."
								 WHERE ".$this->db->escape($this->assoc[$key])." = '".$this->db->escape($val)."'"
							)
						);
		}
		return $udata;
	}

	function login($login, $pass)
	{
		global $config;

		if ($this->usermain['logged'] == 0 AND intval($this->usermain['userid']) == 0)
		{
			$newuser = $this->db->fetchassoc
							(
								$this->db->query
									(
										"SELECT * FROM ".$this->data['table']."
										 WHERE active = '1' AND uname = '".$this->db->escape($login)."'"
									)
							);
			if (intval($newuser['blocked']) > 0)
			{
				define('THIS_BANNED', 1);
			}

			if (intval($newuser['userid']) > 0 AND intval($newuser['blocked']) == 0)
			{
				if (md5($pass) == $newuser['upass'] AND $newuser['active'])
				{
					$cookie = serialize(array($newuser['userid'], md5($newuser['uname'].$newuser['upass']), $newuser['uname']));

					$urlpath = parse_url(SITE_URL);
					$cookpath = (isset($urlpath['path']) AND ! empty($urlpath['path'])) ? $urlpath['path'].'/' : '/';

					setcookie($this->data['cookie'], $cookie, NEWTIME + $this->data['cookieexp'], $cookpath, '', 0, 1);

					$this->usermain = array
						(
							'logged'    => 1,
							'userid'    => intval($newuser['userid']),
							'gid'       => intval($newuser['gid']),
							'uname'     => $newuser['uname'],
							'umail'     => $newuser['umail'],
							'regdate'   => $newuser['regdate'],
							'lastvisit' => $newuser['lastvisit'],
							'phone'     => $newuser['phone'],
							'city'      => $newuser['city'],
							'skype'     => $newuser['skype'],
							'www'       => $newuser['www'],
							'newmsg'    => '',
							'newmsgnr'  => '',
							'avatar'    => $this->avatar($newuser['avatar'],0),
							'favatar'   => $newuser['avatar']
						);
				}
			}
		}
	}

	function group()
	{
		global $basepref;

		$associat = array();
		$inq = $this->db->query("SELECT * FROM ".$basepref."_user_group");
		while ($item = $this->db->fetchassoc($inq))
		{
			$associat[$item['gid']] = $item['title'];
		}
		return $associat;
	}

	function userlist($sf, $nu, $p, $sess, $sql)
	{
		global $lang, $conf;

		$inq = $this->db->query("SELECT * FROM ".$this->data['table'].$sql." ORDER BY userid DESC LIMIT ".$sf.", ".$nu);
		$g   = $this->group();
		while ($item = $this->db->fetchassoc($inq))
		{
			if (intval($item['blocked']) > 0) {
				$style = 'no-active';
			} elseif (intval($item['active']) == 0) {
				$style = 'no-active';
			} else {
				$style = '';
			}
			echo '	<tr class="list">
						<td class="'.$style.' al site">'.$item['uname'].'</td>';
			if ($item['active'] == 0 OR $item['blocked'] == 1) {
				echo '	<td class="'.$style.'">'.(($item['active'] == 0) ? $lang['act_no_confirm'] : $lang['group_ban']).'</td>';
			} else {
				echo '	<td class="'.$style.'">'.(($item['gid'] != 0) ? $g[$item['gid']] : $lang['user']).'</td>';
			}
			echo '		<td class="'.$style.'">'.format_time($item['regdate'], 0, 1).'</td>
						<td class="'.$style.'">'.(($item['lastvisit'] != 0) ? format_time($item['lastvisit'], 1, 1) : '&#8212;').'</td>
						<td class="'.$style.'"><a href=mailto:'.$item['umail'].'>'.$item['umail'].'</a></td>
						<td class="'.$style.' gov">';
			if ($item['userid'] > 0)
			{
				echo '		<a href="index.php?dn=edit&amp;uid='.$item['userid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if (intval($item['active']) > 0 AND intval($item['blocked']) == 0) {
					echo '	<a href="index.php?dn=ban&amp;uid='.$item['userid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/skin/'.$sess['skin'].'/images/act.png" alt="'.$lang['ban_add'].'" /></a>';
				}
				if (intval($item['blocked']) > 0) {
					echo '	<a class="inact" href="index.php?dn=unban&amp;uid='.$item['userid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/skin/'.$sess['skin'].'/images/act.png" alt="'.$lang['ban_del'].'" /></a>';
				}
				echo '		<a href="index.php?dn=del&amp;uid='.$item['userid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMURL.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
			}
			echo '		</td>
					</tr>';
		}
	}

	function userdel($uid)
	{
		global $basepref;

		if ($uid > 0)
		{
			$this->db->query("DELETE FROM ".$this->data['table']." WHERE userid = '".$uid."'");
		}
	}

	function bandel($uid)
	{
		if ($uid > 0)
		{
			$this->db->query("UPDATE ".$this->data['table']." SET blocked='0' WHERE userid = '".$uid."'");
		}
	}

	function banadd($uid)
	{
		if ($uid > 0)
		{
			$this->db->query("UPDATE ".$this->data['table']." SET blocked='1' WHERE userid = '".$uid."'");
		}
	}

	function useredit($uid)
	{
		global $lang, $conf, $basepref;

		echo '	<script>
				$(document).ready(function() {
					$("#data-country").change(function() {
						var id = $(this).val();
						if (id > 0) {
							$.ajax({
								cache:false,
								url:"'.$conf['site_url'].'/index.php",
								data:"dn=user&re=ajax&to=region&id=" + id,
								error:function(msg){},
								success:function(data) {
									if (data.length > 0 && data.match(/option/)) {
										$("#data-state").html(data);
									}
									$("#data-state").prop("disabled", false );
								}
							});
						} else {
							$("#data-state").prepend(\'<option value="0">&#8212;</option>\');
							$("#data-state").find("option:not(:first)").remove().end().prop("disabled", true );
						}
					});
				});
				</script>';
		if ($uid > 0)
		{
			$item = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['table']." WHERE userid = '".$uid."'"));
			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['edit_user'].'</caption>
						<tr>
							<td class="site">'.$lang['all_user'].'</td>
							<td class="vm"><strong class="bold">'.$item['uname'].'</strong></td>
						</tr>
						<tr>
							<td>'.$lang['file_group'].'</td>
							<td>
								<select name="edit[group]" style="width: 273px;">';
			echo '					<option value="0"'.(($item['gid'] == 0) ? ' selected' : '').'>'.$lang['user'].'</option>';
			if ($conf['user']['groupact'] == 'yes')
			{
				$uinq = $this->db->query("SELECT * FROM ".$basepref."_user_group");
				while ($uitem = $this->db->fetchassoc($uinq)) {
					echo '			<option value="'.$uitem['gid'].'"'.(($uitem['gid'] == $item['gid']) ? ' selected' : '').'>'.$uitem['title'].'</option>';
				}
			}
			if ($item['active'] == 0) {
				echo '				<option value="noactive"'.(($item['active'] == 0) ? ' selected' : '').'>'.$lang['act_no_confirm'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>';
			echo '		<tr>
							<td>'.$lang['login'].'</td>
							<td><input type="text" name="edit[name]" size="40" value="'.$item['uname'].'"></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><input name="edit[mail]" size="40" type="text" value="'.$item['umail'].'"></td>
						</tr>
						<tr>
							<td>Skype</td>
							<td><input name="edit[skype]" size="40" type="text" value="'.$item['skype'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['phone'].'</td>
							<td><input name="edit[phone]" size="40" type="text" value="'.$item['phone'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['site_url'].'</td>
							<td><input name="edit[www]" size="40" type="text" value="'.$item['www'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['country'].'</td>
							<td>
								<select id="data-country" name="country" style="width: 273px;">';
			$cinq = $this->db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			echo '					<option value="0">&#8212;</option>';
			while ($citem = $this->db->fetchassoc($cinq)) {
				echo '				<option value="'.$citem['countryid'].'"'.(($citem['countryid'] == $item['countryid']) ? ' selected' : '').'>'.$citem['countryname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['state'].'</td>
							<td>
								<select id="data-state" name="region" style="width: 273px;">';
			$rinq = $this->db->query("SELECT * FROM ".$basepref."_country_region WHERE countryid = '".$item['countryid']."' ORDER BY posit ASC");
			echo '					<option value="0">&#8212;</option>';
			while ($ritem = $this->db->fetchassoc($rinq)) {
			echo '					<option value="'.$ritem['regionid'].'"'.(($ritem['regionid'] == $item['regionid']) ? ' selected' : '').'>'.$ritem['regionname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['city'].'</td>
							<td><input name="edit[city]" size="40" type="text" value="'.$item['city'].'"></td>
						</tr>
						<tr>
							<th></th><th class="site" colspan="2">'.$lang['chang_button_pass'].'</th>
						</tr>
						<tr>
							<td>'.$lang['new_pass'].'</td>
							<td><input name="edit[pass]" size="40" type="text"></td>
						</tr>';
		}
	}

	function usersave($uid, $edit)
	{
		global $conf, $basepref;

		if ($uid > 0 AND is_array($edit))
		{
			if (isset($edit['name']) AND ! empty($edit['name']) AND $this->is_uname($uid, $edit['name']) == 0)
			{
				$this->db->query("UPDATE ".$this->data['table']." SET uname = '".$this->db->escape($edit['name'])."' WHERE userid = '".$uid."'");
			}

			if (isset($edit['mail']) AND ! empty($edit['mail']) AND $this->is_umail($uid, $edit['mail']) == 0)
			{
				$this->db->query("UPDATE ".$this->data['table']." SET umail = '".$this->db->escape($edit['mail'])."' WHERE userid = '".$uid."'");
			}

			if (isset($edit['pass']) AND ! empty($edit['pass']))
			{
				$this->db->query("UPDATE ".$this->data['table']." SET upass = '".md5($edit['pass'])."' WHERE userid = '".$uid."'");
			}

			if ($conf['user']['groupact'] == 'yes')
			{
				if (isset($edit['group']))
				{
					if ($edit['group'] == 'noactive')
					{
						$this->db->query("UPDATE ".$this->data['table']." SET active = '0' WHERE userid = '".$uid."'");
					}
					else
					{
						$this->db->query("UPDATE ".$this->data['table']." SET gid = '".intval($edit['group'])."', active = '1' WHERE userid = '".$uid."'");
					}
				}
			}
			else
			{
				if ($edit['group'] == 'noactive')
				{
					$this->db->query("UPDATE ".$this->data['table']." SET active = '0' WHERE userid = '".$uid."'");
				}
				else
				{
					$this->db->query("UPDATE ".$this->data['table']." SET gid = '".intval($edit['group'])."', active = '1' WHERE userid = '".$uid."'");
				}
			}

			if (isset($edit['phone']))
			{
				$edit['phone'] = preg_match('/^[0-9\-,()+ ]+$/D', $edit['phone']) ? $edit['phone'] : '';
				$this->db->query("UPDATE ".$this->data['table']." SET phone = '".$this->db->escape($edit['phone'])."' WHERE userid = '".$uid."'");
			}

			if (isset($edit['www']))
			{
				$edit['www'] = preg_match('/^[http]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i', $edit['www']) ? $edit['www'] : '';
				$this->db->query("UPDATE ".$this->data['table']." SET www = '".$this->db->escape($edit['www'])."' WHERE userid = '".$uid."'");
			}

			if (isset($edit['city']))
			{
				$this->db->query("UPDATE ".$this->data['table']." SET city = '".$this->db->escape($edit['city'])."' WHERE userid = '".$uid."'");
			}

			if (isset($edit['skype']))
			{
				$edit['skype'] = preg_match('/^[a-zA-Z0-9_-]+$/D', $edit['skype']) ? $edit['skype'] : '';
				$this->db->query("UPDATE ".$this->data['table']." SET skype = '".$this->db->escape($edit['skype'])."' WHERE userid = '".$uid."'");
			}
		}
	}

	function is_uname($uid, $uname)
	{
		$result = $this->db->numrows
					(
						$this->db->query
							(
								"SELECT userid FROM ".$this->data['table']."
								 WHERE uname = '".$this->db->escape($uname)."'
								 AND userid <> ".$uid
							)
					);

		return $result;
	}

	function is_umail($uid, $umail)
	{
		$result = $this->db->numrows
					(
						$this->db->query
							(
								"SELECT userid FROM ".$this->data['table']."
								 WHERE umail = '".$this->db->escape($umail)."'
								 AND userid <> ".$uid
							)
					);

		return $result;
	}

	function messagelast($bs)
	{
		return FALSE;
	}

	function profile($id)
	{
		global $basepref, $tm, $ro, $config, $global, $lang, $api;

		$profile = '';

		$user = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$this->data['table']." WHERE active = '1' AND blocked = '0' AND userid = '".$this->db->escape($id)."'"));

		if (isset($user['userid']) AND intval($user['userid']) > 0)
		{
			/**
			 * Свой TITLE
			 */
			if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
			{
				define('CUSTOM', $config['mod'][WORKMOD]['custom'].' - '.$user['uname']);
			} else {
				$global['title'] = $global['modname'].' - '.$user['uname'];
			}

			/**
			 * Мета данные
			 */
			$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].', '.$lang['profile'].' - '.$user['uname'] : '';
			$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

			/**
			 * Мета данные Open Graph
			 */
			$global['og_title'] = (defined('CUSTOM')) ? CUSTOM : $global['title'];
			if ( ! empty($config['mod'][WORKMOD]['map'])) {
				$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['map']);
			} elseif ( ! empty($config['mod'][WORKMOD]['descript'])) {
				$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['descript']);
			}
			$global['og_image'] = ( ! empty($user['avatar'])) ? SITE_URL.$this->avatar($user['avatar'], 0) : SITE_URL.$this->data['avatarpath'].$this->data['noavatar'];

			/**
			 * Меню, хлебные крошки
			 */
			if (defined('USER_LOGGED'))
			{
				if (defined('USER_DANNEO')) {
					$linkprofile = $ro->seo('index.php?dn=user');
				} else {
					$linkprofile = $ro->seo($this->data['linkprofile'].$user['userid']);
				}

				$global['insert']['current'] = $global['modname'];
				$global['insert']['breadcrumb'] = array('<a href="'.$linkprofile.'">'.$lang['profile'].'</a>', $user['uname']);
			}
			else
			{
				$global['insert']['current'] = $global['modname'];
				$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['profile'], $user['uname']);
			}

			/**
			 * Вывод на страницу, шапка
			 */
			$tm->header();

			/**
			 * Шаблон
			 */
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/profile'));

			/**
			 * Аватар
			 */
			$avatar_src = ($user['avatar']) ? SITE_URL.$this->avatar($user['avatar'], 0) : SITE_URL.'/up/avatar/blank/guest.png';
			$profile.= $tm->parse(array
									(
										'title' => '<img class="uavatar" src="'.$avatar_src.'" alt="'.$user['uname'].'" /> ',
										'value' => '<strong>'.$user['uname'].'</strong>'
									),
									$tm->manuale['rows']);

			/**
			 * Группа пользователя
			 */
			if ($config['user']['groupact'] == 'yes' AND $user['gid'] > 0)
			{
				$group = $this->db->fetchassoc($this->db->query("SELECT * FROM ".$basepref."_user_group WHERE gid = '".$user['gid']."'"));
				if ( ! empty($group['title']))
				{
					$profile.= $tm->parse(array
											(
												'title' => $lang['file_group'],
												'value' => $group['title']
											),
											$tm->manuale['rows']);
				}
			}

			/**
			 * Массив данных по умолчанию
			 */
			$t = array
			(
				'regdate'   => 'registr_date',
				'lastvisit' => 'last_visit',
				'skype'     => 'Skype',
				'www'       => 'author_site'
			);

			foreach ($t as $k => $v)
			{
				if (isset($user[$k]) AND ! empty($user[$k]))
				{
					$val = $user[$k];
					if ($k == 'regdate') {
						$val = $api->sitetime($val, 1);
					}
					if ($k == 'lastvisit') {
						$val = $api->sitetime($val, 1, 1);
					}
					if ($k == 'skype') {
						$val = $val.' <a rel="nofollow" href="skype:'.$val.'?userinfo"><img src="'.SITE_URL.'/template/'.SITE_TEMP.'/images/icon/skype.png" alt="Skype" /></a>';
					}
					if ($k == 'www') {
						$val = '<a rel="nofollow" href="'.$val.'" target="_blank">'.$val.'</a> ';
					}
					$profile.= $tm->parse(array(
												'title' => (isset($lang[$v]) ? $lang[$v] : $v),
												'value' => $val
												),
												$tm->manuale['rows']);
				}
			}

			/**
			 * Доп. поля
			 */
			$inqure = $this->db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes' AND profile = 'yes' ORDER BY posit");

			if ($this->db->numrows($inqure) > 0)
			{
				$ui = $this->db->fetchassoc($this->db->query("SELECT userfield FROM ".$basepref."_user WHERE userid = '".$user['userid']."'"));
				$user = ( ! empty($ui['userfield'])) ? Json::decode($ui['userfield']) : '';
				while ($item = $this->db->fetchassoc($inqure))
				{
					$val = '';
					if (isset($user[$item['fieldid']]))
					{
						$val = (isset($user[$item['fieldid']])) ? $user[$item['fieldid']] : '';
						if ($item['fieldtype'] == 'radio' OR $item['fieldtype'] == 'select')
						{
							$list = Json::decode($item['fieldlist']);
							foreach ($list as $k => $v)
							{
								if ($k == $val) {
									$val = $v;
								}
							}
						}
						if ($item['fieldtype'] == 'date' AND ! empty($val))
						{
							$date = Json::decode(Json::encode($val));
							$time = gmmktime(0, 0, 0, $date['m'], $date['d'], $date['y']);
							$val = $api->sitetime($time, 1);
						}
						if ( ! empty($val))
						{
							$profile.= $tm->parse(array(
														'title' => $item['name'],
														'value' => $val
														),
														$tm->manuale['rows']);
						}
					}
				}
			}

			/**
			 * Вывод в шаблон
			 */
			$tm->parseprint(array('profile' => $profile), $ins['template']);

			/**
			 * Вывод на страницу, подвал
			 */
			$tm->footer();
		}
		else
		{
			$tm->noexistprint();
		}
	}
}
