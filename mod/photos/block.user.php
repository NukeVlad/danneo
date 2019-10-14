<?php
/**
 * File:        /mod/photos/block.user.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Рабочий мод
 */
$WORKMOD = basename(__DIR__);

/**
 * Ссылки для блока block/b-User.php
 *
 * @param url => link
 * @return title => name link
 */
$groups = TRUE;
if (
	isset($config[$WORKMOD]['addit']) AND
	$config[$WORKMOD]['addit'] == 'yes' AND
	in_array($WORKMOD, $realmod)
)
{
	if (defined('GROUP_ACT') AND ! empty($config[$WORKMOD]['groups']))
	{
		$group = Json::decode($config[$WORKMOD]['groups']);
		if ( ! isset($group[$usermain['gid']]))
		{
			$groups = FALSE;
		}
	}
	if ($groups)
	{
		$added[] = array(
			'url'   => $ro->seo('index.php?dn='.$WORKMOD.'&amp;re=add'),
			'title' => $config['mod'][$WORKMOD]['name'],
			'css'   => $WORKMOD
		);
	}
}
