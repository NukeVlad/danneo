<?php
/**
 * File:        /admin/mod/user/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Функция даты
 */
function helpdate()
{
	$sel = '<select name="day">';
	for ($i = 1; $i < 32; $i ++) {
		$sel.= '<option value="'.$i.'">'.$i.'</option>';
	}
	$sel.= '</select> &nbsp;';
	$sel.= '<select name="month">';
	for($i = 1; $i < 13; $i ++) {
		$sel.= '<option value="'.$i.'">'.$i.'</option>';
	}
	$sel.= '</select> &nbsp;';
	$sel.= '<select name="year">';
	for($i = 1977; $i < (NEWYEAR + 1); $i ++) {
		$sel.= '<option value="'.$i.'">'.$i.'</option>';
	}
	$sel.= '</select>';

	return $sel;
}

/**
 * Массив допустимых полей
 */
$feild = array(
	'text'     => $lang['input_texts'],
	'textarea' => $lang['input_textarea'],
	'radio'    => $lang['input_radio'],
	'select'   => $lang['input_select'],
	'date'     => $lang['input_date'],
	'apart'    => $lang['input_apart']
);

/**
 * Ланги помощи по полям
 */
$feildhelp = array(
	'text'     => $lang['input_text_info'],
	'textarea' => $lang['input_textarea_info'],
	'radio'    => $lang['input_radio_info'],
	'select'   => $lang['input_select_info'],
	'date'     => '<div class="infosexample"><p>'.$lang['example'].':</p>'.helpdate().'</div>',
	'apart'    => $lang['input_apart_info']
);

/**
 * Ланги методов проверки
 */
$method = array(
	'text'   => $lang['all_text'],
	'email'  => 'E-Mail',
	'number' => $lang['int_number'],
	'phone'  => $lang['phone']
);

/**
 * Userapi
 */
 /*
if ($conf['userbase'] == 'danneo') {
	require_once(WORKDIR.'/core/userbase/danneo/danneo.user.php');
} else {
	require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
}
$userapi = new userapi($db, false);
*/
