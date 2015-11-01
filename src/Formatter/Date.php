<?php
namespace Transphporm\Formatter;
class Date {
	private $locale;

	public function __construct($locale) {
		$this->locale = $locale;
	}

	private function getDate($val) {
		$date =  $val instanceof \DateTime ? $val : new \DateTime($val);
		$date->setTimeZone(new \DateTimeZone($this->locale['timezone']));
		return $date;
	}

	public function date($val, $format = null) {
		$format = $format ? $format : $this->locale['date_format'];

		return $this->getDate($val)->format($format);
	}

	public function time($val, $format = null) {
		$format = $format ? $format : $this->locale['time_format'];
		return $this->getDate($val)->format($format);
	}

	public function dateTime($val) {
		return $this->date($val, $this->locale['date_format'] . ' ' . $this->locale['time_format']);
	}

	public function relative($val) {
		$now = $this->getDate(null);
		$date = $this->getDate($val);

		$diff = $now->diff($date);


		$diffDays = $diff->invert === 1 ? $diff->days : 0- $diff->days;

		if ($diffDays !== 0) return $this->dayOffset($diffDays);
		else return $this->timeOffset($diff);
	}

	private function timeOffset($diff) {
		$strings = $this->locale['offset_strings'];

		$str = $diff->invert === 1 ? $strings['past'] : $strings['future'];

		$parts = ['h' => 'hours', 'i' => 'minutes', 's'  => 'seconds'];

		foreach ($parts as $l => $time) {
			if ($diff->$l > 0) {
				$plural = $diff->$l === 1 ? '_singular' : '_plural';
				$result = sprintf($str, $diff->$l, $strings[$time . $plural]);
				break;
			}
		}

		return $result;
	}

	private function getRanges($strings) {
		return [
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
	}

	private function dayOffset($diffDays) {
		$strings = $this->locale['offset_strings'];

		foreach ($this->getRanges($strings) as list($lower, $upper, $str, $divisor, $plural)) {
			if ($diffDays >= $lower && $diffDays <= $upper) {
				$num = abs(round($diffDays / $divisor));
				if ($plural !== '') $result = sprintf($str, $num, $num == 1 ? $strings[$plural . '_singular'] : $strings[$plural . '_plural']);
				else $result = sprintf($str, $num / $divisor);
				break;
			}
		}

		return $result;
	}
}
