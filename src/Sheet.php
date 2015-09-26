<?php
namespace Transphporm;
/** Parses a .cds file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; data: iteration(id);` */
class Sheet {
	private $tss;

	public function __construct($tss) {
		$this->tss = $tss;
	}

	public function parse() {
		$tss = $this->tss;
		$rules = [];
		$pos = 0;
		$count = 0;

		while ($next = strpos($tss, '{', $pos)) {
			//Ignore comments... this will only work for comments that aren't inside { and }
			if ($comment = $this->skipComment($tss, $pos, $next)) $pos = $comment;			

			if ($processing = $this->processingInstructions($tss, $pos, $next)) {
				$pos = $processing['endPos']+1;
				$rules = array_merge($processing['rules'], $rules);
			}			

			$rule = new \stdclass;
			$selector = trim(substr($tss, $pos, $next-$pos));
			$x = new CssToXpath($selector);

			$rule->query = $x->getXpath();
			$rule->pseudo = $x->getPseudo();
			$rule->depth = $x->getDepth();
			$rule->index = $count++;
			

			$pos =  strpos($tss, '}', $next)+1;
			$rule->rules = $this->getRules(trim(substr($tss, $next+1, $pos-2-$next)));

		
			 $rules = $this->writeRule($rules, $selector, $rule);
		}

		//Now sort $rules by depth, index
		usort($rules, [$this, 'sortRules']);
		return $rules;
	}

	private function writeRule($rules, $selector, $newRule) {
		if (isset($rules[$selector])) $newRule->rules = array_merge($rules[$selector], $newRule->rules);
		$rules[$selector] = $newRule;
		
		return $rules;
	}

	private function skipComment($tss, $pos, $next) {
		if (strpos($tss, '/*', $pos) !== false && strpos($tss, '/*', $pos) < $next) {
			return strpos($tss, '*/', $pos+1);
		}
		return false;
	}

	private function processingInstructions($tss, $pos, $next) {
		$atPos = strpos($tss, '@', $pos);
		if ($atPos !== false && $atPos < $next) {
			$spacePos = strpos($tss, ' ', $atPos);
			$funcName = substr($tss, $atPos+1, $spacePos-$atPos-1);
			$endPos = strpos($tss, ';', $spacePos);
			$args = substr($tss, $spacePos+1, $endPos-$spacePos-1);
			//$rules = array_merge(, $rules);
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

	private function getRules($str) {
		//Strip comments from inside { and }
		$pos = 0;
		while (($pos = strpos($str, '/*', $pos)) !== false) {
			$end = strpos($str, '*/', $pos);
			$str = substr_replace($str, '', $pos, $end-$pos+2);
		}

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
