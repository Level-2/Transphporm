<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Formatter;
/** Date/time formatting for use in formmat: property*/
class Date {
	private $locale;

	public function __construct($locale) {
		$this->locale = $locale;
	}

	/** Converts $val into a \DateTime object if it's not already */
	private function getDate($val) {
		$tz = new \DateTimeZone($this->locale['timezone']);
		$date =  $val instanceof \DateTime ? $val : new \DateTime($val, $tz);
		$date->setTimeZone($tz);
		return $date;
	}

	/** Formats date based on supplied $format or default format from $locale */
	public function date($val, $format = null) {
		$format = $format ? $format : $this->locale['date_format'];
		return $this->getDate($val)->format($format);
	}

	/** Formats \DateTime as time based on supplied $format or default format from $locale */
	public function time($val, $format = null) {
		$format = $format ? $format : $this->locale['time_format'];
		return $this->getDate($val)->format($format);
	}

	/** Formats \DateTime as Date and Time using formats from $locale */
	public function dateTime($val) {
		return $this->date($val, $this->locale['date_format'] . ' ' . $this->locale['time_format']);
	}

	/** Generates relative time offsets based on system clock. e.g "10 minutes ago" or "In 6 months"
		using strings from $locale	*/
	public function relative($val) {
		$now = $this->getDate(null);
		$date = $this->getDate($val);

		$diff = $now->diff($date);


		$diffDays = $diff->invert === 1 ? $diff->days : 0- $diff->days;

		if ($diffDays !== 0) return $this->dayOffset($diffDays);
		else return $this->timeOffset($diff);
	}

	/** Calculates offset in hours/minutes/seconds */
	private function timeOffset($diff) {
		$strings = $this->locale['offset_strings'];

		$str = $diff->invert === 1 ? $strings['past'] : $strings['future'];

		$parts = ['h' => 'hours', 'i' => 'minutes', 's'  => 'seconds'];

		$result = '';

		foreach ($parts as $l => $time) {
			if ($diff->$l > 0) {				
				$result = sprintf($str, $diff->$l, $this->getPlural($strings, $diff->$l, $time));
				break;
			}
		}

		return $result;
	}

	/** Gets date ranges to represent uses of weeks/months/days/etc */
	private function getRanges($strings) {
		$ranges =  [
			[1, 1, $strings['yesterday'], 1, ''],
			[1, 13, $strings['past'], 1, 'days'],
			[13, 28, $strings['past'], 7, 'weeks'],
			[28, 365, $strings['past'], 28, 'months'],
			[365, 99999999, $strings['past'], 365, 'years'],
			[-1, -1, $strings['tomorrow'], 1, ''],
			[-13, -1, $strings['future'], 1, 'days'],
			[-28, -13, $strings['future'], 7, 'weeks'],
			[-365, -28, $strings['future'], 28, 'months'],
			[-999999, -365, $strings['future'], 365, 'years'],
		];
		if (isset($strings['day_before_yesterday'])) array_unshift($ranges, [2, 2, $strings['day_before_yesterday'], 1, '']);
		if (isset($strings['day_after_tomorrow'])) array_unshift($ranges, [-2, -2, $strings['day_after_tomorrow'], 1, '']);
		return $ranges;
	}

	/** Converts "week" to "weeks", "month" to "months" etc when plural is required using language from $locale */
	private function getPlural($strings, $num, $interval) {
		if ($interval !== '') return $num == 1 ? $strings[$interval . '_singular'] : $strings[$interval . '_plural'];
		else return '';
	}

	/** Calculates offset in days/weeks/month/years */
	private function dayOffset($diffDays) {
		$strings = $this->locale['offset_strings'];

		$result = '';
		
		foreach ($this->getRanges($strings) as list($lower, $upper, $str, $divisor, $plural)) {
			if ($diffDays >= $lower && $diffDays <= $upper) {
				$num = abs(round($diffDays / $divisor));
				$result = sprintf($str, $num, $this->getPlural($strings, $num, $plural));				
				break;
			}
		}

		return $result;
	}
}
