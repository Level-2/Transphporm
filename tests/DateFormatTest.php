<?php
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
		$date = new \DateTime();
		$date->modify($modify);

		$formatter = empty($formatter) ? $this->getFormatter() : $formatter;

		return $formatter->relative($date);
	}

	public function testYesterday() {
		$this->assertEquals('Yesterday', $this->relative('-1 Day'));
	}

	public function testTomorrow() {
		$this->assertEquals('Tomorrow', $this->relative('+1 Day'));
	}

	public function testSecondsAgo() {
		$this->assertEquals('28 seconds ago', $this->relative('-28 seconds'));	
	}

	public function testSecondsIn() {
		$this->assertEquals('In 33 seconds', $this->relative('+33 seconds'));	
	}

	public function testMinutesAgo() {
		$this->assertEquals('13 minutes ago', $this->relative('-13 minutes'));	
	}

	public function testMinutesIn() {
		$this->assertEquals('In 40 minutes', $this->relative('+40 minutes'));	
	}

	public function testHoursAgo() {
		$this->assertEquals('22 hours ago', $this->relative('-22 hours'));	
	}

	public function testHoursIn() {
		$this->assertEquals('In 3 hours', $this->relative('+3 hours'));	
	}

	public function testDaysAgo() {
		$this->assertEquals('6 days ago', $this->relative('-6 days'));	
	}

	public function testDaysIn() {
		$this->assertEquals('In 3 days', $this->relative('+3 days'));	
	}

	public function testWeeksAgo() {
		$this->assertEquals('3 weeks ago', $this->relative('-3 weeks'));	
	}

	public function testWeeksIn() {
		$this->assertEquals('In 2 weeks', $this->relative('+2 weeks'));	
	}

	public function testMonthsAgo() {
		$this->assertEquals('5 months ago', $this->relative('-5 months'));	
	}

	public function testMonthsIn() {
		$this->assertEquals('In 2 months', $this->relative('+2 months'));
	}

	public function testYearsAgo() {
		$this->assertEquals('10 years ago', $this->relative('-10 years'));
	}

	public function testYearsIn() {
		$this->assertEquals('In 15 years', $this->relative('+15 years'));
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
		$this->assertEquals('In 2 days', $this->relative('+2 days'));
	}

	public function testDayAfterTomorrowSet() {
		$locale = json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true);
		$locale['offset_strings']['day_after_tomorrow'] = 'custom day after tomorrow string';
		$formatter = new \Transphporm\Formatter\Date($locale);	

		$this->assertEquals('custom day after tomorrow string', $this->relative('+2 days', $formatter));
	}
}