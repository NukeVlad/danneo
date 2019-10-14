<?php
/**
 * File:        /core/shop/delivery/courier.php
 *
 * Файл:        Каталог / Доставка курьером
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class courier
 */
class courier
{
    public $check = array('fix', 'percent', 'fixpercent');

	/**
	 * Добавить доставку курьером
	 ------------------------------*/
	function add()
	{
		global $db, $basepref, $lang, $conf, $sess, $tm;

		$country = $state = array();

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$country[$items['countryid']] = $items['countryname'];
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$state[$items['countryid']][$items['regionid']] = $items['regionname'];
		}

		echo '	<script>
				var all_name = "'.$lang['all_name'].'";
				var all_price = "'.$lang['price'].'";
				var fix_price = "'.$lang['fix_price'].'";
				var percent = "'.$lang['percent'].'";
				var delet = "'.$lang['delet_of_list'].'";
				var region = new Array();
				';
		foreach ($state as $k => $v)
		{
			echo 'region['.$k.'] = new Array();';
			foreach ($v as $sk => $sv)
			{
				echo 'region['.$k.']['.$sk.'] = \''.$sv.'\';';
			}
		}
		echo '	$(function() {
					if ($("body").has("div.adr")) {
						$("#delivery-area").append("<cite class=\'red\'>'.$lang['add_field'].'</cite>");
					}
				});
				</script>
				<script src="'.ADMURL.'/js/jquery.catalog.delivery.js"></script>
				<div class="section">
				<form action="index.php" method="post" id="total-form">
				<table class="work">
					<caption>'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_auto'].'</caption>
					<tr>
						<th class="ar vm site">'.$lang['all_file'].'&nbsp;</th>
						<th class="site" style="font: 1.2em/1.4 Courier New, Sans-serif; padding-left: 15px;">core/shop/delivery/courier.php</th>
					</tr>
					<tr>
						<td class="vm first"><span>*</span> '.$lang['all_name'].'</td>
						<td><input name="title" type="text" size="79" required="required"></td>
					</tr>
					<tr>
						<td class="vm">'.$lang['all_icon'].'</td>
						<td>
							<input name="icon" id="icon" size="52" type="text">&nbsp;
							<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
						</td>
					</tr>
					<tr>
						<td>'.$lang['all_decs'].'</td>
						<td>';
							$tm->textarea('descr', 5, 50, '', 1);
		echo '			</td>
					</tr>
					<tr>
						<th class="ar site">'.$lang['parent_view'].'&nbsp;</th><th class="site">&nbsp;'.$lang['geo'].'</th>
					</tr>
					<tr>
						<td class="first"><span>*</span> '.$lang['country'].'</td>
						<td>
							<select name="country" id="country" style="width:300px" onchange="$.courierselect(this);">';
		$i = 1;
		$n = 0;
		foreach ($country as $k => $v)
		{
			if ($i == 1)
			{
				$n = $k;
			}
			echo '				<option value="'.$k.'">'.$v.'</option>';
			$i ++;
		}
		echo '				</select>
						</td>
					</tr>
					<tr>
						<td class="first"><span>*</span> '.$lang['state'].'</td>
						<td>
							<select name="state" id="state" style="width:300px">';
		foreach ($state[$n] as $k => $v)
		{
			echo '				<option value="'.$k.'">'.$v.'</option>';
		}
		echo '				</select>
						</td>
					</tr>
					<tr>
						<td>'.$lang['adress'].'</td>
						<td>
							<input type="hidden" id="countid" value="0">
							<div id="delivery-area"></div>
						</td>
					</tr>
					<tr>
						<td>&uarr;</td>
						<td><a class="side-button sw100" href="javascript:$.courieradd(\'total-form\',\'delivery-area\');">'.$lang['all_submint'].'</a></td>
					</tr>
					<tr>
						<th class="ar vm site">'.$lang['status'].'&nbsp;</th>
						<th>&nbsp;<select name="actdel" class="sw150">
								<option value="0">'.$lang['not_included'].'</option>
								<option value="1">'.$lang['included'].'</option>
							</select>
						</th>
					</tr>
					<tr class="tfoot">
						<td colspan="2">
							<input type="hidden" name="dn" value="delivsaveext">
							<input type="hidden" name="id" value="courier">
							<input type="hidden" name="ops" value="'.$sess['hash'].'">
							<input class="main-button" value="'.$lang['all_save'].'" type="submit">
						</td>
					</tr>
				</table>
				</form>
				</div>';
    }

	/**
	 * Добавить доставку курьером, сохранение
	 ------------------------------------------*/
	function save()
	{
		global $lang, $conf, $sess, $tm, $opt, $db, $basepref, $title, $icon, $descr, $tm, $country, $state, $actdel;

		$country = intval($country);
		$cstate = intval($state);
		$data = $c = $r = array();
		$err = $i = 1;

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$c[$items['countryid']] = $items['countryid'];
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$r[$items['countryid']][$items['regionid']] = $items['regionid'];
		}

		$err = (isset($c[$country]) AND isset($r[$country][$state])) ? 1 : 0;
		$data['data'] = null;

		if (is_array($opt['title']) AND isset($opt['title']))
		{
			foreach ($opt['title'] as $k => $v)
			{
				$p = (isset($opt['price'][$k]) AND ceil($opt['price'][$k]) > 0) ? $opt['price'][$k] : 0;
				if (preparse($v, THIS_EMPTY) == 0 AND $p > 0)
				{
					$act = (isset($opt['action'][$k]) AND in_array($opt['action'][$k], $this->check)) ? $opt['action'][$k] : 'fix';
					if ($act == 'fixpercent')
					{
						$percent = (isset($opt['percent'][$k]) AND ceil($opt['percent'][$k]) > 0) ? $opt['percent'][$k] : 0;
						if ($percent == 0)
						{
							$act = 'fix';
						}
					}
					else
					{
						$percent = 0;
					}
					$data['data'][$i] = array('title' => $v, 'price' => formats($p, 2, '.', ''), 'action' => $act, 'percent' => formats($percent, 2, '.', ''));
				}
			}
		}

		if (sizeof($data['data']) > 0 AND preparse($title, THIS_EMPTY) == 0 AND $err)
		{
			$title = preparse($title, THIS_TRIM, 0, 255);
			$data['country'][$country] = $country;
			$data['state'][$state] = $state;
			$new = Json::encode($data);

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_delivery VALUES (
					 NULL,
					 '0.00',
					 '',
					 '".$db->escape($icon)."',
					 '".$db->escape($title)."',
					 '".$db->escape($descr)."',
					 '".$db->escape($new)."',
					 '0',
					 'auto',
					 'courier',
					 '".$db->escape($actdel)."'
					 )"
				);
		}
		else
		{
			$tm->header();
			$tm->error($lang['delivery'], $lang['all_add'], $lang['forgot_name']);
			$tm->footer();
		}
	}

	/**
	 * Редактировать доставку курьером
	 -----------------------------------*/
    function edit($val)
    {
        global $db, $basepref, $lang, $conf, $sess, $tm;

		$data = Json::decode($val['data']);
		$country = $state = array();

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$country[$items['countryid']] = $items['countryname'];
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$state[$items['countryid']][$items['regionid']] = $items['regionname'];
		}

		echo '	<script>
				var all_name = "'.$lang['all_name'].'";
				var all_price = "'.$lang['price'].'";
				var fix_price = "'.$lang['fix_price'].'";
				var percent = "'.$lang['percent'].'";
				var delet = "'.$lang['delet_of_list'].'";
				var region = new Array();
				';
		foreach ($state as $k => $v)
		{
			echo 'region['.$k.'] = new Array();';
			foreach ($v as $sk => $sv)
			{
				echo 'region['.$k.']['.$sk.'] = \''.$sv.'\';';
			}
		}
		echo '	</script>
				<script src="'.ADMURL.'/js/jquery.catalog.delivery.js"></script>
				<div class="section">
				<form action="index.php" method="post" id="total-form">
				<table class="work">
					<caption>'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_auto'].'</caption>
					<tr>
						<th class="ar vm site">'.$lang['all_file'].'&nbsp;</th>
						<th class="site" style="font: 1.2em/1.4 Courier New, Sans-serif; padding-left: 15px;">core/shop/delivery/courier.php</th>
					</tr>
					<tr>
						<td class="vm first"><span>*</span> '.$lang['all_name'].'</td>
						<td><input name="title" type="text" value="'.$val['title'].'" size="79" required="required"></td>
					</tr>
					<tr>
						<td class="vm">'.$lang['all_icon'].'</td>
						<td>
							<input name="icon" id="icon" size="52" type="text" value="'.$val['icon'].'">&nbsp;
							<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
						</td>
					</tr>
					<tr>
						<td>'.$lang['all_decs'].'</td>
						<td>';
							$tm->textarea('descr', 5, 50, $val['descr'], 1);
		echo '			</td>
					</tr>
					<tr>
						<th class="ar site">'.$lang['parent_view'].'&nbsp;</th><th class="site">&nbsp;'.$lang['geo'].'</th>
					</tr>
					<tr>
						<td class="first"><span>*</span> '.$lang['country'].'</td>
						<td>
							<select name="country" id="country" style="width:300px" onchange="$.courierselect(this);">';
		$n = 0;
		foreach ($country as $k => $v)
		{
			if (isset($data['country'][$k])) {
				$n = $k;
			}
			echo '				<option value="'.$k.'"'.((isset($data['country'][$k])) ? ' selected' : '').'> '.$v.' </option>';
		}
		echo '				</select>
						</td>
					</tr>
					<tr>
						<td class="first"><span>*</span> '.$lang['state'].'</td>
						<td>
							<select name="state" id="state" style="width:300px">';
		foreach ($state[$n] as $k => $v)
		{
			echo '				<option value="'.$k.'"'.((isset($data['state'][$k])) ? ' selected' : '').'> '.$v.' </option>';
		}
		echo '				</select>
						</td>
					</tr>
					<tr>
						<td>'.$lang['adress'].'</td>
						<td>
							<div id="delivery-area">';
		$courier = Json::decode($val['data']);
		$c = 0;
		if (is_array($courier['data']))
		{
			foreach ($courier['data'] as $k => $v)
			{
				$c = $k;
				$style = ($v['action'] == 'fixpercent') ? 'display: block;' : 'display: none;';
				echo '			<div id="cinput'.$k.'" class="section tag adr" style="display: block;">
									<table class="work">
										<tr>
											<td class="sw100 ar site">'.$lang['all_name'].'</td>
											<td>
												<input name="opt[title]['.$k.']" size="69" value="'.$v['title'].'" type="text" class="fl pw25" style="min-width: 313px;">
												<a class="side-button fr" href="javascript:$.removeinput(\'total-form\',\'delivery-area\',\'cinput'.$k.'\');" title="'.$lang['delet_of_list'].'">&#215;</a>
											</td>
										</tr>
										<tr>
											<td class="sw100 gray">'.$lang['price'].'</td>
											<td>
												<input name="opt[price]['.$k.']" size="18" value="'.$v['price'].'" type="text" class="pw15">
												<select name="opt[action]['.$k.']" id="action-'.$k.'" onchange="$.changecourier(this);" class="sw250">
													<option value="fix"'.(($v['action'] == 'fix') ? ' selected' : '').'>'.$lang['fix_price'].'</option>
													<option value="percent"'.(($v['action'] == 'percent') ? ' selected' : '').'>'.$lang['percent'].'</option>
													<option value="fixpercent"'.(($v['action'] == 'fixpercent') ? ' selected' : '').'>'.$lang['fix_price'].' + '.$lang['percent'].'</option>
												</select>
											</td>
										</tr>
									</table>';
				echo '				<div id="view-action-'.$k.'" style="'.$style.'">
										<table class="work">
											<tr>
												<td class="sw100 gray">'.$lang['percent'].'</td>
												<td><input name="opt[percent]['.$k.']" size="18" value="'.$v['percent'].'" type="text" class="pw15"></td>
											</tr>
										</table>
									</div>
								</div>';
			}
		}
		echo '					<input type="hidden" id="countid" value="'.$c.'">
							</div>
						</td>
					</tr>
					<tr>
						<td>'.$lang['add_field'].'</td>
						<td><a class="side-button sw100" href="javascript:$.courieradd(\'total-form\',\'delivery-area\');">'.$lang['all_submint'].'</a></td>
					</tr>
					<tr>
						<th class="ar vm site">'.$lang['status'].'&nbsp;</th>
						<th>&nbsp;<select name="actdel" class="sw150">
								<option value="1"'.(($val['act'] == 1) ? ' selected' : '').'>'.$lang['included'].'</option>
								<option value="0"'.(($val['act'] == 0) ? ' selected' : '').'>'.$lang['not_included'].'</option>
							</select>
						</th>
					</tr>
					<tr class="tfoot">
						<td colspan="2">
							<input type="hidden" name="dn" value="deliveditsaveext">
							<input type="hidden" name="id" value="'.$val['did'].'">
							<input type="hidden" name="ops" value="'.$sess['hash'].'">
							<input class="main-button" value="'.$lang['all_save'].'" type="submit">
						</td>
					</tr>
				</table>
				</form>
				</div>';
	}

	/**
	 * Редактировать доставку курьером, сохранение
	 -----------------------------------------------*/
	function editsave()
	{
		global $id, $lang, $conf, $sess, $tm, $opt, $db, $basepref, $title, $icon, $descr, $tm, $actdel, $country, $state;

		$country = intval($country);
		$cstate = intval($state);
		$data = $c = $r = array();
		$err = $i = 1;

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$c[$items['countryid']] = $items['countryid'];
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($items = $db->fetchrow($inq))
		{
			$r[$items['countryid']][$items['regionid']] = $items['regionid'];
		}

		$err = (isset($c[$country]) AND isset($r[$country][$state])) ? 1 : 0;
		$data = array();
		$id = preparse($id, THIS_INT);
		$data['data'] = null;

		if (is_array($opt['title']) AND isset($opt['title']))
		{
			foreach ($opt['title'] as $k => $v)
			{
				$p = (isset($opt['price'][$k]) AND $opt['price'][$k] > 0) ? $opt['price'][$k] : 0;
				if (preparse($v, THIS_EMPTY) == 0 AND $p > 0)
				{
					$act = (isset($opt['action'][$k]) AND in_array($opt['action'][$k], $this->check)) ? $opt['action'][$k] : 'fix';
					if ($act == 'fixpercent')
					{
						$percent = (isset($opt['percent'][$k]) AND $opt['percent'][$k] > 0) ? $opt['percent'][$k] : 0;
						if ($percent == 0)
						{
							$act = 'fix';
						}
					}
					else
					{
						$percent = 0;
					}
					$data['data'][$k] = array('title' => $v, 'price' => formats($p, 2, '.', ''), 'action' => $act, 'percent' => formats($percent, 2, '.', ''));
				}
			}
		}

		if (sizeof($data['data']) > 0 AND preparse($title, THIS_EMPTY) == 0 AND $err)
		{
			$actdel = ($actdel == 1) ? 1 : 0;
			$title = preparse($title, THIS_TRIM, 0, 255);
			$data['country'][$country] = $country;
			$data['state'][$state] = $state;
			$new = Json::encode($data);

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_delivery SET
					 icon  = '".$db->escape($icon)."',
					 title = '".$db->escape($title)."',
					 descr = '".$db->escape($descr)."',
					 data  = '".$db->escape($new)."',
					 act   = '".$db->escape($actdel)."'
					 WHERE did = '".$id."'"
				);
		}
		else
		{
			$tm->header();
			$tm->error($lang['delivery'], $lang['all_edit'], $lang['forgot_name']);
			$tm->footer();
		}
	}

    function addform($id, $cur, $val, $weight)
    {
		global $lang, $config, $tm;

		$courier = $val;
		$sel = '';
		if (is_array($courier['data']))
		{
			$sel.= '<select name="delivery['.$id.']">';
			foreach ($courier['data'] as $k => $v)
			{
				$sum = $tot = '';
				if ($v['action'] == 'fix')
				{
					$sum = ' + '.$cur['symbol_left'].formats($v['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
					$tot = $cur['symbol_left'].formats($courier['price'] + $v['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				}
				else if ($v['action'] == 'percent')
				{
					$c = round(($courier['price'] / 100) * $v['price']);
					$sum = ' + '.$cur['symbol_left'].formats($c, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
					$tot = $cur['symbol_left'].formats($courier['price'] + $c, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				}
				else if ($v['action'] == 'fixpercent')
				{
					$c = round(($v['price'] + ($courier['price'] / 100) * $v['percent']));
					$sum = ' + '.$cur['symbol_left'].formats($v['price'] + $c,$cur['decimal'],$cur['decimalpoint'],$cur['thousandpoint'],$cur['value']).$cur['symbol_right'];
					$tot = $cur['symbol_left'].formats($courier['price'] + $c,$cur['decimal'],$cur['decimalpoint'],$cur['thousandpoint'],$cur['value']).$cur['symbol_right'];
				}
				$sel.= '<option value="'.$k.'">'.$v['title'].' '.$sum.' ('.$tot.')</option>';
			}
			$sel.= '</select>';
		}
		return $sel;
	}

	function checkform($id, $cur, $val, $weight)
	{
		global $lang, $config, $tm, $api, $global;

		$courier = $val;
		$c = 0;
		$ids = '';
		$err = 1;
		if (is_array($courier['data']) AND isset($courier['delivery'][$id]))
		{
			$newid = $ids = $courier['delivery'][$id];
			if (isset($courier['data'][$newid]) AND is_array($courier['data'][$newid]))
			{
				$v = $courier['data'][$newid];
				if (isset($v['action']))
				{
					if ($v['action'] == 'fix')
					{
						$c = $v['price'];
					}
					else if ($v['action'] == 'percent')
					{
						$c = ($courier['price'] / 100) * $v['price'];
					}
					else if ($v['action'] == 'fixpercent')
					{
						$c = ($v['price'] + ($courier['price'] / 100) * $v['percent']);
					}
					$err = 0;
				}
			}
		}

		if ($err)
		{
			$tm->error($lang['noisset_delive']);
		}

		return array($c, array($id => $ids));
	}
}
