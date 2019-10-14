<?php
/**
 * File:        /core/shop/delivery/rus.post.php
 *
 * Файл:        Каталог / Доставка почтой по России
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class ruspost
 */
class ruspost
{
    public $post1 = array
	(
		1 => '53.45',
		2 => '58.90',
		3 => '77.40',
		4 => '103.55',
		5 => '116.65'
    );
    public $post2 = array
	(
		1 => '4.40',
		2 => '4.70',
		3 => '5.90',
		4 => '7.55',
		5 => '8.30'
    );

	/**
	 * Добавить доставку почтой
	 -----------------------------*/
	function add()
	{
		global $db, $basepref, $lang, $conf, $sess, $tm;

		$inq = $db->query("SELECT * FROM ".$basepref."_country WHERE iso = '643'");
		if ($db->numrows($inq) == 0)
		{
			$tm->error($lang['delivery'], $lang['all_add'], $lang['deliv_not_geo']);
		}
		else
		{
			$item = $db->fetchrow($inq);
			$inq = $db->query("SELECT * FROM ".$basepref."_country_region WHERE countryid = '". $item['countryid']."' ORDER BY posit");
			$select = '';
			while ($item = $db->fetchrow($inq))
			{
				$select.= '<option value="'.$item['regionid'].'">'.$item['regionname'].'</option>';
			}
			echo "	<script>
						var all_name   = '".$lang['all_name']."';
						var all_time   = '".$lang['timeszone']."';
						var delet      = '".$lang['delet_of_list']."';
						var all_select = '".$select."';
						$(function() {
							if ($('body').has('div.adr')) {
								$('#delivery-area').append('<cite class=\"red\">".$lang['add_field']."</cite>');
							}
						});
					</script>";

			echo '	<script src="'.ADMURL.'/js/jquery.catalog.delivery.js"></script>
					<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_auto'].'</caption>
						<tr>
							<th class="ar vm site">'.$lang['all_file'].'&nbsp;</th>
							<th class="site" style="font: 1.2em/1.4 Courier New, Sans-serif; padding-left: 15px;">core/shop/delivery/rus.post.php</th>
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
							<th></th>
							<th class="site">&nbsp;'.$lang['order_detail'].'</th>
						</tr>
						<tr>
							<td>'.$lang['post_avia'].'</td>
							<td><input name="opt[avia]" type="text" value="0.00" size="25"></td>
						</tr>
						<tr>
							<td>'.$lang['post_max_weight'].'</td>
							<td><input name="opt[maxweight]" type="text" value="20.00" size="25">';
                              $tm->outhint($lang['post_max_help']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['post_price_value'].'</td>
							<td><input name="opt[pricevalue]" type="text" value="3.00" size="25">';
                              $tm->outhint($lang['post_price_help']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['post_tarif_weight'].'</td>
							<td><input name="opt[tarifweight]" type="text" value="10.00" size="25">';
                              $tm->outhint($lang['post_tarif_help']);
			echo '			</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">&nbsp;'.$lang['post_price_one'].'</th>
						</tr>';
			for ($i = 1; $i < 6; $i ++)
			{
				echo '	<tr>
							<td class="server">'.$lang['timeszone'].' '.$i.'</td>
							<td><input name="opt[priceone]['.$i.']" type="text" value="'.$this->post1[$i].'" size="25"></td>
						</tr>';
			}
			echo '		<tr>
							<th></th>
							<th class="site">&nbsp;'.$lang['post_price_two'].'</th>
						</tr>';
			for ($i = 1; $i < 6; $i ++)
			{
				echo '	<tr>
							<td class="alternative">'.$lang['timeszone'].' '.$i.'</td>
							<td><input name="opt[pricetwo]['.$i.']" type="text" value="'.$this->post2[$i].'" size="25"></td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['adress'].'</td>
							<td>
								<input type="hidden" id="countid" value="0">
								<div id="delivery-area"></div>
							</td>
						</tr>
						<tr>
							<td>&uarr;</td>
							<td><a class="side-button sw100" href="javascript:$.ruspostadd(\'total-form\',\'delivery-area\');">'.$lang['all_submint'].'</a></td>
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
								<input type="hidden" name="id" value="rus.post">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
		}
	}

	/**
	 * Редактировать доставку почтой
	 ---------------------------------*/
	function edit($val)
	{
		global $db, $basepref, $lang, $conf, $sess, $tm;

		$inq = $db->query("SELECT * FROM ".$basepref."_country WHERE iso = '643'");
		if ($db->numrows($inq) == 0)
		{
			$tm->error($lang['delivery'], $lang['all_edit'], $lang['deliv_not_geo']);
		}
		else
		{
			$data = Json::decode($val['data']);
			$c = array();
			foreach ($data['state'] as $k => $v)
			{
				$c[$v['id']] = $v['id'];
			}
			$item = $db->fetchrow($inq);
			$inq = $db->query("SELECT * FROM ".$basepref."_country_region WHERE countryid = '". $item['countryid']."' ORDER BY posit");
			$select = '';
			$view = 0;
			$region = array();
			while ($item = $db->fetchrow($inq))
			{
				if ( ! isset($c[$item['regionid']]))
				{
					$select.= '<option value="'.$item['regionid'].'">'.$item['regionname'].'</option>';
					$view = 1;
				}
				$region[$item['regionid']] = $item['regionname'];
			}
			echo "	<script>
						var all_name   = '".$lang['all_name']."';
						var all_time   = '".$lang['timeszone']."';
						var delet      = '".$lang['delet_of_list']."';
						var all_select = '".$select."';
					</script>";

			echo '	<script src="'.ADMURL.'/js/jquery.catalog.delivery.js"></script>
					<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['delivery'].': '.$lang['delivery_auto'].'</caption>
						<tr>
							<th class="ar vm site">'.$lang['all_file'].'&nbsp;</th>
							<th class="site" style="font: 1.2em/1.4 Courier New, Sans-serif; padding-left: 15px;">core/shop/delivery/rus.post.php</th>
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
							<th></th>
							<th class="site">&nbsp;'.$lang['order_detail'].'</th>
						</tr>
						<tr>
							<td>'.$lang['post_avia'].'</td>
							<td><input name="opt[avia]" type="text" value="'.$data['avia'].'" size="25"></td>
						</tr>
						<tr>
							<td>'.$lang['post_max_weight'].'</td>
							<td><input name="opt[maxweight]" type="text" value="'.$data['maxweight'].'" size="25">';
                              $tm->outhint($lang['post_max_help']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['post_price_value'].'</td>
							<td><input name="opt[pricevalue]" type="text" value="'.$data['pricevalue'].'" size="25">';
                              $tm->outhint($lang['post_price_help']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['post_tarif_weight'].'</td>
							<td><input name="opt[tarifweight]" type="text" value="'.$data['tarifweight'].'" size="25">';
                              $tm->outhint($lang['post_tarif_help']);
			echo '			</td>
						</tr>
						<tr>
							<th></th>
							<th class="site">&nbsp;'.$lang['post_price_one'].'</th>
						</tr>';
			for ($i = 1; $i < 6; $i ++)
			{
				echo '	<tr>
							<td class="server">'.$lang['timeszone'].' '.$i.'</td>
							<td><input name="opt[priceone]['.$i.']" type="text" value="'.$data['priceone'][$i].'" size="25"></td>
						</tr>';
			}
			echo '		<tr>
							<th></th>
							<th class="site">&nbsp;'.$lang['post_price_two'].'</th>
						</tr>';
			for ($i = 1; $i < 6; $i ++)
			{
				echo '	<tr>
							<td class="alternative">'.$lang['timeszone'].' '.$i.'</td>
							<td><input type="text" name="opt[pricetwo]['.$i.']" value="'.$data['pricetwo'][$i].'" size="25"></td>
						</tr>';
			}
			echo '		<tr>
							<th class="ar site">'.$lang['parent_view'].'&nbsp;</th><th class="site">&nbsp;'.$lang['geo'].'</th>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['state'].'</td>
							<td>
								<div id="delivery-area">';
			$c = 0; $r = '';
			if (is_array($data['state']))
			{
				foreach ($data['state'] as $k => $v)
				{
					$c = $k;
					echo '			<div id="cinput'.$k.'" class="section tag adr" style="display: block;">
										<table class="work">
											<tr>
												<td class="sw100 ar site">'.$lang['all_name'].'</td>
												<td>
													<select name="opt[state]['.$k.']" class="fl pw25" style="min-width: 313px;">';
					foreach ($region as $rk => $rv)
					{
						echo '							<option value="'.$rk.'"'.(($k == $rk) ? ' selected' : '').'>'.$rv.'</option>';
					}
					echo '							</select>
													<a class="side-button fr" href="javascript:$.removeinput(\'total-form\',\'delivery-area\',\'cinput'.$k.'\');" title="'.$lang['delet_of_list'].'">&#215;</a>
												</td>
											</tr>
											<tr>
												<td class="sw100 gray">'.$lang['timeszone'].'</td>
												<td>
													<select name="opt[zone]['.$k.']" class="sw150">
														<option value="0">  &#8212;  </option>';
					for ($i = 1; $i < 6; $i ++)
					{
						echo '							<option value="'.$i.'"'.(($v == $i) ? ' selected' : '').'>'.$lang['timeszone'].' '.$i.'</option>';
					}
					echo '							</select>
												</td>
											</tr>
										</table>
									</div>';
				}
			}
			echo '					<input type="hidden" id="countid" value="'.$c.'">
								</div>
							</td>
						</tr>';
			if ($view) {
			echo '		<tr>
							<td>'.$lang['add_field'].'</td>
							<td><a class="side-button sw100" href="javascript:$.ruspostadd(\'total-form\',\'delivery-area\');">'.$lang['all_submint'].'</a></td>
						</tr>';
			}
		echo '			<tr>
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
	}

	function save()
	{
		global $lang, $conf, $sess, $tm, $opt, $db, $basepref, $title, $icon, $descr, $tm, $currency, $actdel;

		$data = array
			(
				'avia'        => '0.00',
				'maxweight'   => '20.00',
				'pricevalue'  => '3.00',
				'tarifweight' => '10.00',
				'priceone'    => array(),
				'pricetwo'    => array(),
				'state'       => array(),
				'country'     => array()
			);

		if (isset($opt['state']) AND is_array($opt['state']))
		{
            foreach ($opt['state'] as $k => $v)
            {
                $z = (isset($opt['zone'][$k]) AND intval($opt['zone'][$k]) < 6) ? intval($opt['zone'][$k]) : 0;
                if (preparse($v, THIS_EMPTY) == 0)
                {
                    $data['state'][$v] = $z;
                }
            }
		}

		$data['avia'] = (isset($opt['avia']) AND ceil($opt['avia']) > 0) ? formats($opt['avia'], 2, '.', '') : '0.00';
		$data['maxweight'] = (isset($opt['maxweight']) AND ceil($opt['maxweight']) > 0 AND intval($opt['maxweight']) < 21) ? formats($opt['maxweight'], 2, '.', '') : '20.00';
		$data['pricevalue'] = (isset($opt['pricevalue']) AND ceil($opt['pricevalue']) > 0) ? formats($opt['pricevalue'], 2, '.', '') : '0.00';
		$data['tarifweight'] = (isset($opt['tarifweight']) AND ceil($opt['tarifweight']) > 0) ? formats($opt['tarifweight'], 2, '.', '') : '0.00';

		$err = 1;
		$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE iso = '643'"));
		if (intval($item['countryid']) > 0)
		{
			$data['country'][$item['countryid']] = $item['countryid'];
		}
		else
		{
			$data['country'] = array();
		}
		if (isset($opt['priceone']) AND is_array($opt['priceone']))
		{
			for ($i = 1; $i < 6; $i ++)
			{
				if (isset($opt['priceone'][$i]) AND ceil($opt['priceone'][$i]) > 0)
				{
					$data['priceone'][$i] = formats($opt['priceone'][$i], 2, '.', '');
				}
				else
				{
					$err = 0;
				}
			}
		}
		else
		{
			$err = 0;
		}
		if (isset($opt['pricetwo']) AND is_array($opt['pricetwo']))
		{
			for ($i = 1; $i < 6; $i++)
			{
				if (isset($opt['pricetwo'][$i]) AND ceil($opt['pricetwo'][$i]) > 0)
				{
					$data['pricetwo'][$i] = formats($opt['pricetwo'][$i], 2, '.', '');
				}
				else
				{
					$err = 0;
				}
			}
		}
		else
		{
			$err = 0;
		}
		if (sizeof($data['country']) > 0 AND sizeof($data['state']) > 0 AND preparse($title, THIS_EMPTY) == 0 AND $err)
		{
			$title = preparse($title, THIS_TRIM, 0, 255);
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
					 'rus.post',
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

	function editsave()
	{
		global $id, $lang, $conf, $sess, $tm, $opt, $db, $basepref, $title, $icon, $descr, $tm, $currency, $actdel, $country;

		$data = array
			(
				'avia'        => '0.00',
				'maxweight'   => '20.00',
				'pricevalue'  => '3.00',
				'tarifweight' => '10.00',
				'priceone'    => array(),
				'pricetwo'    => array(),
				'state'       => array(),
				'country'     => array()
			);

		if (isset($opt['state']) AND is_array($opt['state']))
		{
			foreach ($opt['state'] as $k => $v)
			{
				$z = (isset($opt['zone'][$k]) AND intval($opt['zone'][$k]) < 6) ? intval($opt['zone'][$k]) : 0;
				if (preparse($v, THIS_EMPTY) == 0)
				{
					$data['state'][$v] = $z;
				}
			}
		}

		$data['avia'] = (isset($opt['avia']) AND ceil($opt['avia']) > 0) ? formats($opt['avia'], 2, '.', '') : '0.00';
		$data['maxweight'] = (isset($opt['maxweight']) AND ceil($opt['maxweight']) > 0 AND intval($opt['maxweight']) < 21) ? formats($opt['maxweight'], 2, '.', '') : '20.00';
		$data['pricevalue'] = (isset($opt['pricevalue']) AND ceil($opt['pricevalue']) > 0) ? formats($opt['pricevalue'], 2, '.', '') : '0.00';
		$data['tarifweight'] = (isset($opt['tarifweight']) AND ceil($opt['tarifweight']) > 0) ? formats($opt['tarifweight'], 2, '.', '') : '0.00';

		$err = 1;
		$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE iso = '643'"));
		if (intval($item['countryid']) > 0)
		{
			$data['country'][$item['countryid']] = $item['countryid'];
		}
		else
		{
			$data['country'] = array();
		}
		if (isset($opt['priceone']) AND is_array($opt['priceone']))
		{
			for ($i = 1; $i < 6; $i ++)
			{
				if (isset($opt['priceone'][$i]) AND ceil($opt['priceone'][$i]) > 0)
				{
					$data['priceone'][$i] = formats($opt['priceone'][$i], 2, '.', '');
				}
				else
				{
					$err = 0;
				}
			}
		}
		else
		{
			$err = 0;
		}
		if (isset($opt['pricetwo']) AND is_array($opt['pricetwo']))
		{
			for ($i = 1; $i < 6; $i ++)
			{
				if (isset($opt['pricetwo'][$i]) AND ceil($opt['pricetwo'][$i]) > 0)
				{
					$data['pricetwo'][$i] = formats($opt['pricetwo'][$i], 2, '.', '');
				}
				else
				{
					$err = 0;
				}
			}
		}
		else
		{
			$err = 0;
		}
		if (sizeof($data['country']) > 0 AND sizeof($data['state']) > 0 AND preparse($title, THIS_EMPTY) == 0 AND $err)
		{
			$actdel = ($actdel == 1) ? 1 : 0;
			$title = preparse($title, THIS_TRIM, 0, 255);
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
		global $db, $basepref, $lang, $config, $tm, $api, $global;

		$post = $val;
		$sel = '';
		$weight = $weight / 1000;
		if ($post['maxweight'] >= $weight AND $weight !== 0)
		{
			$c = $post['countryid'];
			$s = $post['regionid'];
			if (isset($post['state'][$s]) AND isset($post['geo'][$c]['region'][$s]))
			{
				$v['zone'] = $post['state'][$s];
				$title = $post['geo'][$c]['region'][$s];
				$ground = $post['priceone'][$v['zone']] + $post['pricetwo'][$v['zone']] * ceil((($weight < 0.5 ? 0.5 : $weight) - 0.5) / 0.5);
				$air = $ground + $post['avia'];
				if ($weight > $post['tarifweight'])
				{
					$ground *= 1.3;
					$air *= 1.3;
				}
				$ground += ($post['pricevalue'] / 100);
				$totg = $cur['symbol_left'].formats(round($post['price'] + $ground), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$air += ($post['pricevalue'] / 100) ;
				$tota = $cur['symbol_left'].formats(round($post['price'] + $air), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$ground = ' + '.$cur['symbol_left'].formats($ground,$cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$air = ' + '.$cur['symbol_left'].formats($air, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];

				$sel .= '	<select name="delivery['.$id.']">
								<optgroup label="'.$title.'">
									<option value="ground">'.$lang['deliv_ground'].' '.$ground.' ('.$totg.')</option>
									'.(($post['avia'] > 0) ? '<option value="air">'.$lang['deliv_avia'].' '.$air.' ('.$tota.')</option>' : '').'
								</optgroup>
							</select>';
			}
		}
		return $sel;
	}

	function checkform($id, $cur, $val, $weight)
	{
		global $lang, $config, $tm, $api, $global;

		$post = $val;
		$c = 0;
		$ids = '';
		$err = $werr = 1;
		$weight = $weight / 1000;
		if ($post['maxweight'] >= $weight AND $weight !== 0)
		{
			$werr = 0;
			$co = $post['countryid'];
			$s = $post['regionid'];
			if (isset($post['state'][$s]) AND isset($post['geo'][$co]['region'][$s]) AND isset($post['delivery'][$id]))
			{
				$newid = $ids = $post['delivery'][$id];
				$v['zone'] = $post['state'][$s];
				$ground = $post['priceone'][$v['zone']] + $post['pricetwo'][$v['zone']] * ceil((($weight < 0.5 ? 0.5 : $weight) - 0.5) / 0.5);
				$air = $ground + $post['avia'];
				if ($weight > $post['tarifweight'])
				{
					$ground *= 1.3;
					$air *= 1.3;
				}
				$ground += ($post['pricevalue'] / 100);
				$air += ($post['pricevalue'] / 100) ;
				if ($newid == 'ground')
				{
					$c = $ground;
					$err = 0;
				}
				elseif($newid == 'air')
				{
					$c = $air;
					$err = 0;
				}
			}
		}

		if ($err OR $werr)
		{
			$tm->error(($werr) ? $lang['weight_error'] : $lang['noisset_delive']);
		}

		return array($c, array($id => $ids));
	}
}
