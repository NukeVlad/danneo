<?php
/**
 * File:        /core/shop/payment/webmoney.php
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
	public $data = array
	(
		'merchantr'	=> array
		(
			'field' => 'input',
			'lang'  => 'merchantr',
			'hint'  => '',
			'preg'  => '#R[0-9]{12}#'
		),
		'merchantkey' => array
		(
			'field' => 'input',
			'lang'  => 'Secret Key',
			'hint'  => '',
			'preg'  => ''
		),
		'testmode' => array
		(
			'field' => 'select',
			'lang'  => 'testmode',
			'hint'  => '',
			'preg'  => '/^[0-1]+$/i',
			'value' => array(0 => 'all_no', 1 => 'all_yes')
		)
	);

	function save($arr)
	{
		$e = 0;
		foreach ($this->data as $k => $v)
		{
			if (isset($arr[$k]) AND ! empty($arr[$k]) OR isset($arr[$k]) AND $arr[$k] == 0)
			{
				$preg = $v['preg'];
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
		return isset($_POST['LMI_PAYMENT_NO']) ? intval($_POST['LMI_PAYMENT_NO']) : 0;
	}

	function confirm($val, $detail, $info)
	{
		global $lang, $config, $tm, $api, $global, $ro;

		$t = $tm->parsein($tm->create('mod/'.WORKMOD.'/checkout.form'));

		$href = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=edit&amp;id='.$info['oid']);
		$result = $ro->seo('index.php?dn='.WORKMOD.'&re=gateway&id='.$info['oid']);
		$success = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=success');
		$failure = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=failure');

		$v = Json::decode($val);
		$wa = array
				(
					'did' => $info['oid'],
					'LMI_PAYMENT_NO'     => $info['oid'],
					'LMI_PAYMENT_AMOUNT' => ($info['price'] + $info['delivprice']),
					'LMI_PAYEE_PURSE'    => $v['merchantr'],
					'LMI_RESULT_URL'     => $result,
					'LMI_SUCCESS_URL'    => $success,
					'LMI_FAIL_URL'       => $failure
				);

		if ($v['testmode'] == 1)
		{
			$wa['LMI_MODE'] = 1;
		}

		$data = null;
		foreach ($wa as $k => $v)
		{
			$data.= '<input name="'.$k.'" value="'.$v.'" type="hidden">';
		}

		$tm->parseprint
		(
			array
			(
				'action'  => 'https://merchant.webmoney.ru/lmi/payment.asp',
				'method'  => 'post',
				'charset' => ' accept-charset="utf-8"',
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
		$LMI_PAYMENT_NO = isset($_POST['LMI_PAYMENT_NO']) ? intval($_POST['LMI_PAYMENT_NO']) : 0;

		$hash = strtoupper(md5(
			// Кошелек продавца
			($LMI_PAYEE_PURSE = isset($_POST['LMI_PAYEE_PURSE']) ? $_POST['LMI_PAYEE_PURSE'] : '')

			// Сумма платежа
			.($LMI_PAYMENT_AMOUNT = isset($_POST['LMI_PAYMENT_AMOUNT']) ? $_POST['LMI_PAYMENT_AMOUNT'] : '')

			// Внутренний номер покупки
			.$LMI_PAYMENT_NO

			// Флаг тестового режима
			.($LMI_MODE = isset($_POST['LMI_MODE']) ? $_POST['LMI_MODE'] : '')

			// Внутренний номер счета в системе WebMoney Transfer
			.($LMI_SYS_INVS_NO = isset($_POST['LMI_SYS_INVS_NO']) ? $_POST['LMI_SYS_INVS_NO'] : '')

			// Внутренний номер платежа в системе WebMoney Transfer
			.($LMI_SYS_TRANS_NO = isset($_POST['LMI_SYS_TRANS_NO']) ? $_POST['LMI_SYS_TRANS_NO'] : '')

			// Дата и время выполнения платежа
			.($LMI_SYS_TRANS_DATE = isset($_POST['LMI_SYS_TRANS_DATE']) ? $_POST['LMI_SYS_TRANS_DATE'] : '')

			// Secret Key
			.$s['merchantkey']

			// Кошелек покупателя
			.($LMI_PAYER_PURSE = isset($_POST['LMI_PAYER_PURSE']) ? $_POST['LMI_PAYER_PURSE'] : '')

			// WMId покупателя
			.($LMI_PAYER_WM = isset($_POST['LMI_PAYER_WM']) ? $_POST['LMI_PAYER_WM'] : '')
		));

		if(isset($_POST['LMI_HASH']) AND $hash == $_POST['LMI_HASH'])
		{
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_payment WHERE payact = '1' ORDER BY payposit");
			$payment = array();
			while ($items = $db->fetchrow($inq))
			{
				$payment[$items['payid']] = $items;
			}
			if (sizeof($payment) > 0 AND isset($payment[$p['paystatus']]))
			{
				$db->query("UPDATE ".$basepref."_".PERMISS."_order SET statusid = '".intval($p['paystatus'])."' WHERE oid = '".$db->escape($LMI_PAYMENT_NO)."'");
				$inq = $db->query("SELECT orders FROM ".$basepref."_".PERMISS."_order WHERE oid = '".$db->escape($LMI_PAYMENT_NO)."'");
				if ($db->numrows($inq) > 0)
				{
					$i = $db->fetchrow($inq);
					$s = Json::decode($i['orders']);
					if(sizeof($s) > 0)
					{
						$ins = array();
						foreach ($s as $k => $v)
						{
							$ins[$k] = $k;
						}
						if(sizeof($ins) > 0)
						{
							$in = implode(',', $ins);
							$db->query("UPDATE ".$basepref."_".PERMISS." SET buyhits = buyhits + 1 WHERE id IN (".$db->escape($in).")");
						}
					}
				}
			}
		}
	}
}
