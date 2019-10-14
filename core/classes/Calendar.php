<?php
/**
 * File:        /core/classes/Calendar.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Site Calendar
 */
class Calendar
{
	public $out = '';
	public $d;
	public $m;
	public $y;
	public $mm;
	public $totalday;
	public $month;

	function __construct()
	{
	}

	function CreateCalendar()
	{
		global $ye, $mo, $da;

		$ye = substr(preparse($ye, THIS_INT), 0, 4);
		$mo = substr(preparse($mo, THIS_INT), 0, 2);
		$da = substr(preparse($da, THIS_INT), 0, 2);

		$this->y = ($ye < 2000 OR $ye > NEWYEAR) ? NEWYEAR : $ye;
		$this->m = ($mo < 1 OR $mo > 12) ? preparse(NEWMONT,THIS_INT) : $mo;
		$this->d = ($da < 1 OR $da > 31) ? NEWDAY : $da;

		$dim = cal_days_in_month(CAL_GREGORIAN, $this->m, $this->y);
		if ($this->d > $dim) {
			$this->d = $dim;
		}
		if (is_array($this->month)) {
			return $this->month;
		}

		$this->mm = date('F', mktime(0, 0, 0, $this->m, $this->d, $this->y));
		$this->totalday = date('t', mktime(0, 0, 0, $this->m, $this->d, $this->y));

		$counts = 1;
		$local = 0;

		for ($i = 0; $i < 7; $i ++)
		{
			$weeks = date('w', mktime(0, 0, 0, $this->m, $counts, $this->y));
			$weeks = $weeks - 1;
			if ($weeks == -1) {
				$weeks = 6;
			}
			if ($weeks == $i) {
				$this->month[$local][$i] = $counts;
			$counts ++;
			} else {
				$week[$local][$i] = '';
			}
		}

		while ($this->totalday > $local)
		{
			$local ++;
			for ($i = 0; $i < 7; $i ++)
			{
				$this->month[$local][$i] = $counts;
				$counts ++;
				if ($counts > $this->totalday) {
					break;
				}
			}
			if ($counts > $this->totalday) {
				break;
			}
		}
	}

	function OutputCalendar($ifmod = FALSE, $sqlinq)
	{
		global $db, $ro, $langdate, $config;

		$mod = ($ifmod) ? $ifmod : 'news';
		$start_mont = mktime(0, 0, 0, $this->m, 1, $this->y);
		$end_mont = mktime(23, 59, 59, $this->m, $this->totalday, $this->y);

		$calendar_chek = $countdate = array();
		$inq = $db->query($sqlinq." AND public >= '".$start_mont."' AND public <= '".$end_mont."'");
		while ($item = $db->fetchassoc($inq))
		{
			$tempdate = '';
			$tempdate = date('d', $item['public']);
			if ($tempdate < 10) {
				$tempdate = str_replace('0', '', $tempdate);
			}
			$calendar_chek[].= $tempdate;
		}

		$countm = intval($this->m);
		$backm = ($countm == 1) ? 12 : ($countm - 1);
		$backy = ($backm == 12) ? ($this->y - 1) : $this->y;
		$nextm = ($countm == 12) ? 1 : ($countm + 1);
		$nexty = ($nextm == 1) ? ($this->y + 1) : $this->y;
		$title = $langdate[strtolower($this->mm)].' '.$this->y;

		$calendar = '	<div class="calendar-wrap">
						<table class="calendar">
							<tr>
								<td class="calendar-month">
									<a class="calendarlink" href="'.$ro->seo('index.php?dn='.$mod.'&amp;to=dat&amp;ye='.$backy.'&amp;mo='.$backm.'&amp;da=0').'">«</a>
								</td>
								<td class="calendar-month" colspan="5"><a href="'.$ro->seo('index.php?dn='.$mod.'&amp;to=dat&amp;ye='.$this->y.'&amp;mo='.$this->m.'&amp;da=0').'">'.$title.'</a></td>
								<td class="calendar-month">';
		if (($this->y <= NEWYEAR AND $nextm <= NEWMONT) OR $this->y < NEWYEAR)
		{
			$calendar.= '        <a class="calendarlink" href="'.$ro->seo('index.php?dn='.$mod.'&amp;to=dat&amp;ye='.$nexty.'&amp;mo='.$nextm.'&amp;da=0').'">»</a>';
		}
		$calendar.= '			</td>
							</tr>
							<tr class="calendar-title">
								<td>'.$langdate['mon'].'.</td>
								<td>'.$langdate['tue'].'.</td>
								<td>'.$langdate['wed'].'.</td>
								<td>'.$langdate['thu'].'.</td>
								<td>'.$langdate['fri'].'.</td>
								<td class="sun">'.$langdate['sat'].'.</td>
								<td class="sun">'.$langdate['sun'].'.</td>
							</tr>';
		for ($i = 0; $i < count($this->month); $i ++)
		{
			$calendar.= '	<tr>';
			for ($j = 0; $j < 7; $j ++)
			{
				if ( ! empty($this->month[$i][$j]))
				{
					$link = $this->month[$i][$j];
					if ( ! empty($calendar_chek) AND in_array($this->month[$i][$j], $calendar_chek))
					{
						$title = isset($countdate[$link]) ? @$config['mod'][$mod]['name'].' : '.$countdate[$link] : '';
						$link = '<a class="calendarlink" title="'.$title.'" href="'.$ro->seo('index.php?dn='.$mod.'&amp;to=dat&amp;ye='.$this->y.'&amp;mo='.$this->m.'&amp;da='.$link).'"><strong>'.$link.'</strong></a>';
					}
					$class = ($this->month[$i][$j] == $this->d) ? 'calendar-today' : 'calendar-allday';
					$calendar.= '<td class="'.$class.'">'.$link.'</td>';
				}
				else
				{
					$calendar.= '<td class="calendarempty">&nbsp;</td>';
				}
			}
			$calendar.= '	</tr>';
		}
		$calendar.= '	</table>';
		$calendar.= '	</div>';

		return $this->out = $calendar;
	}
}
