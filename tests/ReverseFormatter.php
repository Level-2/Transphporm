<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
class ReverseFormatter {
	public function reverse($str) {
		return strrev($str);
	}
}

class ReverseFormatterModule implements \Transphporm\Module {
	public function load(\Transphporm\Config $config) {
		$config->registerFormatter(new ReverseFormatter);
	}
}