<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Formatter;
class Number {
	private $locale;

	public function __construct($locale) {
		$this->locale = $locale;
	}

	public function decimal($num, $decimals) {
		if (!is_numeric($num)) return $num;
		return number_format((float)$num, $decimals, $this->locale['decimal_separator'], $this->locale['thousands_separator']);
	}

	public function currency($num) {
		$num = $this->decimal($num, $this->locale['currency_decimals']);
		if ($this->locale['currency_position'] === 'before') return $this->locale['currency'] . $num;
		else return $num . $this->locale['currency'];
	}
}
