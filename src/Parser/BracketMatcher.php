<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class BracketMatcher {
	private $str;
	private $startPos = 0;
	private $endPos = 0;

	public function __construct($str) {
		$this->str = $str;
	}
	
	public function match($openChr, $closingChr, $start = 0) {
		$open = strpos($this->str, $openChr, $start);
		$close = strpos($this->str, $closingChr, $open);

		$cPos = $open+1;
		while (($cPos = strpos($this->str, $openChr, $cPos+1)) !== false && $cPos < $close) $close = strpos($this->str, $closingChr, $close+1);

		$this->startPos = $open;
		$this->endPos = $close;
		return substr($this->str, $open+1, $close-$open-1);
	}

	public function getOpenPos() {
		return $this->startPos;
	}

	public function getClosePos() {
		return $this->endPos;
	}
}