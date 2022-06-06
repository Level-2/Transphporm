<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
class DateFormatTest extends PHPUnit_Framework_TestCase {

	private function getFormatter() {
		$locale = json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true);
		return new \Transphporm\Formatter\Date($locale);
	}

	public function testFormatDate() {
		$this->assertEquals('25/12/2014', $this->getFormatter()->date('2014-12-25'));
	}

	public function testFormatTime() {
		$this->assertEquals('13:34', $this->getFormatter()->time('2014-12-25 13:34'));
	}

	public function testFormatDateTime() {
		$this->assertEquals('25/12/2014 13:34', $this->getFormatter()->datetime('2014-12-25 13:34'));
	}

	private function relative($modify, $formatter = null) {
		$date = new \DateTime(json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true)['timezone']);
		$date = new \DateTime();
		$date->modify($modify);

		$formatter = empty($formatter) ? $this->getFormatter() : $formatter;

		return $formatter->relative($date);
	}

	public function testYesterday() {
		$this->assertEquals('yesterday', $this->relative('-1 Day'));
	}

	public function testTomorrow() {
		$this->assertEquals('tomorrow', $this->relative('+1 Day'));
	}

	public function testSecondsAgo() {
		$this->assertEquals('28 seconds ago', $this->relative('-28 seconds'));
	}

	public function testSecondsin() {
		$this->assertEquals('in 33 seconds', $this->relative('+33 seconds'));
	}

	public function testMinutesAgo() {
		$this->assertEquals('13 minutes ago', $this->relative('-13 minutes'));
	}

	public function testMinutesin() {
		$this->assertEquals('in 40 minutes', $this->relative('+40 minutes'));
	}

	public function testHoursAgo() {
		$this->assertEquals('22 hours ago', $this->relative('-22 hours'));
	}

	public function testHoursin() {
		$this->assertEquals('in 3 hours', $this->relative('+3 hours'));
	}

	public function testDaysAgo() {
		$this->assertEquals('6 days ago', $this->relative('-6 days'));
	}

	public function testDaysin() {
		$this->assertEquals('in 3 days', $this->relative('+3 days'));
	}

	public function testWeeksAgo() {
		$this->assertEquals('3 weeks ago', $this->relative('-3 weeks'));
	}

	public function testWeeksin() {
		$this->assertEquals('in 2 weeks', $this->relative('+2 weeks'));
	}

	public function testMonthsAgo() {
		$this->assertEquals('5 months ago', $this->relative('-5 months'));
	}

	public function testMonthsin() {
		$this->assertEquals('in 2 months', $this->relative('+2 months'));
	}

	public function testYearsAgo() {
		$this->assertEquals('10 years ago', $this->relative('-10 years'));
	}

	public function testYearsin() {
		$this->assertEquals('in 15 years', $this->relative('+15 years'));
	}

	public function testDayBeforeYesterdayNotSet() {
		$this->assertEquals('2 days ago', $this->relative('-2 days'));
	}

	public function testDayBeforeYesterdaySet() {
		$locale = json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true);
		$locale['offset_strings']['day_before_yesterday'] = 'custom day before yesterday string';
		$formatter = new \Transphporm\Formatter\Date($locale);

		$this->assertEquals('custom day before yesterday string', $this->relative('-2 days', $formatter));
	}

	public function testDayAfterTomorrowNotSet() {
		$this->assertEquals('in 2 days', $this->relative('+2 days'));
	}

	public function testDayAfterTomorrowSet() {
		$locale = json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true);
		$locale['offset_strings']['day_after_tomorrow'] = 'custom day after tomorrow string';
		$formatter = new \Transphporm\Formatter\Date($locale);

		$this->assertEquals('custom day after tomorrow string', $this->relative('+2 days', $formatter));
	}
}
