<?php
/**
 * File:        /core/shop/payment/banktrans.php
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
		'company' => array
		(
			'field'=> 'input',
			'lang' => 'company',
			'hint' => '',
			'preg' => ''
		),
		'bank' => array
		(
			'field'=> 'input',
			'lang' => 'bank',
			'hint' => '',
			'preg' => ''
		),
		'giro' => array
		(
			'field'=> 'input',
			'lang' => 'giro',
			'hint' => '',
			'preg' => ''
		),
		'inn' => array
		(
			'field'=> 'input',
			'lang' => 'inn',
			'hint' => '',
			'preg' => ''
		),
		'kpp'	=> array
		(
			'field'=> 'input',
			'lang' => 'kpp',
			'hint' => '',
			'preg' => ''
		),
		'correspondent'	=> array
		(
			'field'=> 'input',
			'lang' => 'correspondent',
			'hint' => '',
			'preg' => ''
		),
		'bik' => array
		(
			'field'=> 'input',
			'lang' => 'bik',
			'hint' => '',
			'preg' => ''
		),
		'print' => array
		(
			'field'=> 'select',
			'lang' => 'print',
			'hint' => '',
			'preg' => '/^[0-1]+$/i',
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

	function confirm($val, $detail, $info)
	{
		global $lang, $config, $tm, $api, $global, $cou, $reg;

		$s = Json::decode($val);
    	$tm->unmanule['print'] = ($s['print']) ? 'yes' : 'no';

		$t = $tm->parsein($tm->create('mod/'.WORKMOD.'/checkout.text'));

		if (isset($config['arrcur'][$config['viewcur']]))
		{
			$cur = $config['arrcur'][$config['viewcur']];
		}
		else
		{
			$cur = array
				(
					'value'         => 1,
					'title'         => '',
					'symbol_left'   => '',
					'symbol_right'  => '',
					'decimal'       => 2,
					'decimalpoint'  => '.',
					'thousandpoint' => ','
				);
		}

		$tm->parseprint
		(
			array
			(
                'number_order'   => $lang['number_order'],
                'announcement'   => $lang['announcement'],
                'cashier'        => $lang['cashier'],
                'receipt'        => $lang['receipt'],
                'payer'          => $lang['payer'],
                'payee'          => $lang['payee'],
                'company'        => $s['company'],
                'langbank'       => $lang['bank'],
                'bank'           => $s['bank'],
                'langgiro'       => $lang['giro'],
                'giro'           => $s['giro'],
                'langinn'        => $lang['inn'],
                'inn'            => $s['inn'],
                'langkpp'        => $lang['kpp'],
                'kpp'            => $s['kpp'],
                'langbik'        => $lang['bik'],
                'bik'            => $s['bik'],
                'langcorres'     => $lang['correspondent'],
                'correspondent'  => $s['correspondent'],
                'langsum'        => $lang['sum'],
                'sum'            => $cur['symbol_left'].formats(($info['price'] + $info['delivprice']),$cur['decimal'],$cur['decimalpoint'],$cur['thousandpoint'],$cur['value']).$cur['symbol_right'],
                'langdata'       => $lang['sys_date'],
                'langpay'        => $lang['payment_orders'],
                'id'             =>  $info['oid'],
                'data'           =>  '',
                'firstname'      => $info['firstname'],
                'surname'        => $info['surname'],
                'country'        => $cou,
                'region'         => $reg,
                'details'        => $lang['details_payment'],
                'signpayer'      => $lang['signature_payer'],
                'langnomination' => $lang['nomination']
			),
            $t
		);
	}
}
