<?php
namespace Transphporm\Formatter;
class Number {
	private $locale;

	public function __construct($locale) {
		$this->locale = $locale;
	}

	public function decimal($num, $decimals) {
		return number_format($num, $decimals, $this->locale['decimal_separator'], $this->locale['thousands_separator']);
	}

	public function currency($num) {
		$num = $this->decimal($num, $this->locale['currency_decimals']);		
	}
}