<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Formatter;
class StringFormatter {
	public function uppercase($val) {
		return strtoupper($val);
	}

	public function lowercase($val) {
		return strtolower($val);
	}

	public function titlecase($val) {
		return ucwords($val);
	}

	public function html($val) {
		$doc = new \DomDocument();
		$doc->loadXML($val);
		return $doc->documentElement;
	}

	public function debug($val) {
		ob_start();
		var_dump($val);
		return $this->html('<pre>' . ob_get_clean() . '</pre>');
	}
}