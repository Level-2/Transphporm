<?php
namespace Transphporm;
/** Parses a .cds file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; data: iteration(id);` */
class Sheet {
	private $tss;

	public function __construct($tss) {
		$this->tss = $this->stripComments($tss);
	}

	public function parse() {
		$tss = $this->tss;
		$rules = [];
		$pos = 0;
		$count = 0;

		while ($next = strpos($tss, '{', $pos)) {
			if ($processing = $this->processingInstructions($tss, $pos, $next)) {
				$pos = $processing['endPos']+1;
				$rules = array_merge($processing['rules'], $rules);
			}

			$selector = trim(substr($tss, $pos, $next-$pos));
			$rule = $this->cssToRule($selector, $count++);	

			$pos =  strpos($tss, '}', $next)+1;
			$rule->rules = $this->getRules(trim(substr($tss, $next+1, $pos-2-$next)));	
			$rules = $this->writeRule($rules, $selector, $rule);
		}
		//Now sort $rules by depth, index
		usort($rules, [$this, 'sortRules']);
		return $rules;
	}

	private function CssToRule($selector, $index) {
		$rule = new \stdclass;
		$xPath = new CssToXpath($selector);
		$rule->query = $xPath->getXpath();
		$rule->pseudo = $xPath->getPseudo();
		$rule->depth = $xPath->getDepth();
		$rule->index = $index++;

		return $rule;
	}

	private function writeRule($rules, $selector, $newRule) {
		if (isset($rules[$selector])) $newRule->rules = array_merge($rules[$selector], $newRule->rules);
		$rules[$selector] = $newRule;
		
		return $rules;
	}

	private function processingInstructions($tss, $pos, $next) {
		$atPos = strpos($tss, '@', $pos);
		if ($atPos !== false && $atPos < $next) {
			$spacePos = strpos($tss, ' ', $atPos);
			$funcName = substr($tss, $atPos+1, $spacePos-$atPos-1);
			$endPos = strpos($tss, ';', $spacePos);
			$args = substr($tss, $spacePos+1, $endPos-$spacePos-1);
			return ['rules' => $this->$funcName($args), 'endPos' => $endPos];			
		}
		else return false;
	}

	private function import($args) {
		$sheet = new Sheet(file_get_contents(trim($args, '\'" ')));
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

	private function getRules($str) {
		$rules = explode(';', $str);
		$return = [];
		
		foreach ($rules as $rule) {
			if (trim($rule) === '') continue;
			$parts = explode(':', $rule, 2);
			$return[trim($parts[0])] = isset($parts[1]) ? trim($parts[1]) : '';
		}

		return $return;
	}
}
