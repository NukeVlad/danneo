<?php
/**
 * File:        /core/classes/RSS.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class RSS
 */
class RSS
{
	public $out = '';
	public $pub = 'D, d M Y H:i:s O';

	public function __construct()
	{
	}

	public function pubformat($gtm)
	{
		return date($this->pub, $gtm);
	}

	public function additem($arr)
	{
		foreach ($arr as $k => $v)
		{
			$this->out.= "    <item>".PHP_EOL
                        ."        <title>".$v['title']."</title>".PHP_EOL
                        ."        <guid>".$v['link']."</guid>".PHP_EOL
                        ."        <link>".$v['link']."</link>".PHP_EOL
                        ."        <description><![CDATA[".$v['description']."]]></description>".PHP_EOL
                        ."        <pubDate>".$this->pubformat($v['pubdate'])."</pubDate>".PHP_EOL;
			if (isset($v['enclosure']))
			{
				$this->out.= "        <enclosure url=\"".$v['enclosure']['url']."\" length=\"".$v['enclosure']['length']."\" type=\"".$v['enclosure']['type']."\" />".PHP_EOL;
			}
			$this->out.= "    </item>".PHP_EOL;
		}
	}

	public function headers
	(
		$title,
		$description,
		$link,
		$last
	) {
		global $config, $global;

		header("Content-type: text/xml");

		$this->out = '<?xml version="1.0" encoding="'.$config['langcharset'].'"?>'.PHP_EOL;
		if ($global['full'] == 1)
		{
			$this->out.= "<rss version=\"2.0\" xmlns=\"backend.userland.com/rss2\" xmlns:yandex=\"news.yandex.ru\">".PHP_EOL;
		}
		else
		{
			$this->out.= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\">".PHP_EOL;
		}
		$this->out.= "<channel>".PHP_EOL
                    ."    <language>ru</language>".PHP_EOL
                    ."    <title>".$title."</title>".PHP_EOL
                    ."    <description>".$description."</description>".PHP_EOL
                    ."    <link>".$link."</link>".PHP_EOL;
		if ( ! empty($last))
		{
			$this->out.= "    <lastBuildDate>".$this->pubformat($last)."</lastBuildDate>".PHP_EOL;
		}
	}

	public function closers()
	{
		$this->out.= "</channel>".PHP_EOL."</rss>";
	}

	public function content()
	{
		return $this->out;
	}
}
