<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class StringExtractor {
	private $str;
	private $stringTable;

	public function __construct($str) {
		$parts = $this->extractStrings($str);
		$this->str = $parts[0];
		$this->stringTable = $parts[1];
	}

	private function extractStrings($str) {
		$double = $this->extractStringsByType($str, '"');
		$single = $this->extractStringsByType($double[0], '\'', count($double[1]));
		return [$single[0], array_merge($double[1], $single[1])];
	}

	private function extractStringsByType($str, $type, $num = 0) {
		$pos = 0;
		$strings = [];
		while (isset($str[$pos]) && ($pos = strpos($str, $type, $pos)) !== false) {
			$end = strpos($str, $type, $pos+1);
			if (!$end) break;
			$end = $this->getNextPosEscaped($str, '\\', $type, $end);
			$strings['$___STR' . ++$num] = substr($str, $pos, $end-$pos+1);
			$str = substr_replace($str, '$___STR' . $num, $pos, $end-$pos+1);
		}
		return [$str, $strings];
	}

	private function getNextPosEscaped($str, $escape, $chr, $start) {
		while ($str[$start-1] == $escape) $start = strpos($str, $chr, $start+1);
		return $start;
	}

	public function rebuild($str) {
		return str_replace(array_keys($this->stringTable), array_values($this->stringTable), $str);
	}

	public function __toString() {
		return $this->str;
	}
}