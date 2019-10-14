<?php
/**
 * File:        /core/shop/payment/robokassa.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class payment
 */
class payment
{
	public $suf = '.html';

	public $data = array
	(
		'merchantlogin' => array
		(
			'field' => 'input',
			'lang'  => 'Login',
			'hint'  => '',
			'preg'  => ''
		),
		'merchantpassw1' => array
		(
			'field' => 'input',
			'lang'  => 'Password 1',
			'hint'  => '',
			'preg'  => ''
		),
		'merchantpassw2' => array
		(
			'field' => 'input',
			'lang'  => 'Password 2',
			'hint'  => '',
			'preg'  => ''
		),
		'testmode' => array
		(
			'field' => 'select',
			'lang'  => 'testmode',
			'hint'  => '',
			'preg'  => '/^[0-1]{1}+$/i',
			'value' => array(1 => 'all_no',2 => 'all_yes')
		),
		'result' => array
		(
			'field'  => 'input',
			'lang'   => 'Result URL',
			'hint'   => 'Metod POST',
			'value'  => '',
			'url'    => 'index.php?dn={mod}&re=gateway&id={id}',
			'urlcpu' => '{mod}/gateway-{id}{suf}'
		),
		'success' => array
		(
			'field'  => 'input',
			'lang'   => 'Success URL',
			'hint'   => '',
			'value'  => '',
			'url'    => 'index.php?dn={mod}&re=order&to=success',
			'urlcpu' => '{mod}/order-success{suf}'
		),
		'failure' => array
		(
			'field'  => 'input',
			'lang'   => 'Fail URL',
			'hint'   => '',
			'value'  => '',
			'url'    => 'index.php?dn={mod}&re=order&to=failure',
			'urlcpu' => '{mod}/order-failure{suf}'
		)
	);

	public function __construct()
	{
	}

	function save($arr)
	{
		$e = 0;
		foreach ($this->data as $k => $v)
		{
			if (isset($arr[$k]) AND ! empty($arr[$k]))
			{
				if ( ! empty($preg))
				{
					if ( ! preg_match($preg, $arr[$k]))
					{
						$e = 1;
					}
				}
			}
			else
			{
				$e = 1;
			}
		}
		return $e;
	}

	function findid()
	{
		global $_POST;
		return isset($_POST['InvId']) ? intval($_POST['InvId']) : 0;
	}

	function confirm($val, $detail, $info)
	{
		global $lang, $config, $tm, $api, $global, $ro;

    	$t = $tm->parsein($tm->create('mod/'.WORKMOD.'/checkout.form'));
		$href = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=edit&amp;id='.$info['oid']);
		$v = Json::decode($val);
		$data = null;
		$wa = array
				(
					'MrchLogin' => $v['merchantlogin'],
					'OutSum' => floatval($info['price'] + $info['delivprice']),
					'InvId' => $info['oid']
				);
		$wa['SignatureValue'] = md5(implode(':',$wa).':'.$v['merchantpassw1']);
		if ($v['testmode'] == 1)
		{
			$action = 'https://merchant.roboxchange.com/Index.aspx';
		}
		else
		{
			$action = 'http://test.robokassa.ru/Index.aspx';
		}
		foreach ($wa as $k => $v)
		{
			$data.= '<input name="'.$k.'" value="'.$v.'" type="hidden">';
		}
		$tm->parseprint
				(
					array
					(
						'action'  => $action,
						'method'  => 'post',
						'data'    => $data,
						'detail'  => $detail,
						'edit'    => $lang['all_edit'],
						'href'    => $href,
						'proceed' => $lang['proceed']
					), $t
				);
	}

	function check($p, $val)
	{
		global $db, $basepref, $lang, $config, $tm, $api, $global, $_POST;

		$s = Json::decode($p['paydata']);
		$sum = isset($_POST['OutSum']) ? floatval($_POST['OutSum']) : 0;
		$price = floatval($val['price']);
		if($sum == $price)
		{
			$oid = $val['oid'];
			$sign = strtolower(md5($price.':'.$oid.':'.$s['merchantpassw2']));
			$signrobo = isset($_POST['SignatureValue']) ? strtolower($_POST['SignatureValue']) : '';
			if($sign == $signrobo)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment WHERE payact = '1' ORDER BY payposit");
				$payment = array();
				while ($items = $db->fetchrow($inq))
				{
					$payment[$items['payid']] = $items;
				}
				if (sizeof($payment) > 0 AND isset($payment[$p['paystatus']]))
				{
					//$config['statuscheckout']$p['paystatus']
					$db->query("UPDATE ".$basepref."_".PERMISS."_order SET statusid = '".intval($p['paystatus'])."' WHERE oid = '".$db->escape($oid)."'");
					$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_order WHERE oid = '".$db->escape($oid)."'");
					if ($db->numrows($inq) > 0)
					{
						$i = $db->fetchrow($inq);
						$s = Json::decode($i['orders']);
						if(is_array($s) AND sizeof($s) > 0)
						{
							$ins = array();
							foreach ($s as $k => $v)
							{
								$ins[$k] = $k;
							}
							if(sizeof($ins) > 0)
							{
								$in = implode(',',$ins);
								$db->query("UPDATE ".$basepref."_".PERMISS." SET buyhits = buyhits + 1 WHERE id IN (".$db->escape($in).")");
							}
						}
					}
				}
			}
		}
	}
}
