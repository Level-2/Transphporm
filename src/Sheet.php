<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
/** Parses a .cds file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; data: iteration(id);` */
class Sheet {
	private $tss;
	private $baseDir;
	private $prefix;

	public function __construct($tss, $baseDir, $prefix = '') {
		$this->tss = $this->stripComments($tss);
		$this->baseDir = $baseDir;
		$this->prefix = $prefix;
	}

	public function parse($pos = 0, $rules = []) {
		while ($next = strpos($this->tss, '{', $pos)) {
			if ($processing = $this->processingInstructions($this->tss, $pos, $next)) {
				$pos = $processing['endPos']+1;
				$rules = array_merge($processing['rules'], $rules);
			}

			$selector = trim(substr($this->tss, $pos, $next-$pos));
			$rule = $this->cssToRule($selector, count($rules));	
			$pos =  strpos($this->tss, '}', $next)+1;
			$rule->properties = $this->getProperties(trim(substr($this->tss, $next+1, $pos-2-$next)));	
			$rules = $this->writeRule($rules, $selector, $rule);
		}
		//there may be processing instructions at the end
		if ($processing = $this->processingInstructions($this->tss, $pos, strlen($this->tss))) $rules = array_merge($processing['rules'], $rules);
		usort($rules, [$this, 'sortRules']);
		return $rules;
	}

	private function CssToRule($selector, $index) {
		$xPath = new CssToXpath($selector, $this->prefix);
		$rule = new Rule($xPath->getXpath(), $xPath->getPseudo(), $xPath->getDepth(), $index++);
		return $rule;
	}

	private function writeRule($rules, $selector, $newRule) {
		if (isset($rules[$selector])) $newRule->properties = array_merge($rules[$selector], $newRule->properties);
		$rules[$selector] = $newRule;
		
		return $rules;
	}

	private function processingInstructions($tss, $pos, $next) {
		$rules = [];
		while (($atPos = strpos($tss, '@', $pos)) !== false) {
			if ($atPos  <= (int) $next) {
				$spacePos = strpos($tss, ' ', $atPos);
				$funcName = substr($tss, $atPos+1, $spacePos-$atPos-1);
				$pos = strpos($tss, ';', $spacePos);
				$args = substr($tss, $spacePos+1, $pos-$spacePos-1);
				$rules = array_merge($rules, $this->$funcName($args));
			}
			else {
				break;	
			} 
		}

		return empty($rules) ? false : ['endPos' => $pos, 'rules' => $rules];
	}

	private function import($args) {
		$sheet = new Sheet(file_get_contents($this->baseDir . trim($args, '\'" ')), $this->baseDir);
		return $sheet->parse();
	}

	private function sortRules($a, $b) {
		//If they have the same depth, compare on index
		if ($a->depth === $b->depth) return $a->index < $b->index ? -1 : 1;

		return ($a->depth < $b->depth) ? -1 : 1;
	}

	private function stripComments($str) {
		$pos = 0;
		while (($pos = strpos($str, '/*', $pos)) !== false) {
			$end = strpos($str, '*/', $pos);
			$str = substr_replace($str, '', $pos, $end-$pos+2);
		}

		return $str;
	}

	private function getProperties($str) {
		$stringExtractor = new \Transphporm\StringExtractor($str);
		$rules = explode(';', $stringExtractor);
		$return = [];

		foreach ($rules as $rule) {
			if (trim($rule) === '') continue;
			$parts = explode(':', $rule, 2);

			$parts[1] = $stringExtractor->rebuild($parts[1]);
			$return[trim($parts[0])] = isset($parts[1]) ? trim($parts[1]) : '';
		}

		return $return;
	}
}
