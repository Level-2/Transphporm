<?php
namespace Transphporm;
/** Parses a .cds file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; data: iteration(id);` */
class Sheet {
	private $cds;

	public function __construct($cds) {
		$this->cds = $cds;
	}

	public function parse() {
		$css = $this->cds;
		$rules = [];
		$pos = 0;
		$count = 0;

		while ($next = strpos($css, '{', $pos)) {
			//Ignore comments... this will only work for comments that aren't inside { and }
			if (strpos($css, '/*', $pos) !== false && strpos($css, '/*', $pos) < $next) {
				$pos = strpos($css, '*/', $pos+1);
			}
			$rule = new \stdclass;
			$selector = trim(substr($css, $pos, $next-$pos));
			$x = new CssToXpath($selector);

			$rule->query = $x->getXpath();
			$rule->pseudo = $x->getPseudo();
			$rule->depth = $x->getDepth();
			$rule->index = $count++;

			

			$pos =  strpos($css, '}', $next)+1;
			$rule->rules = $this->getRules(trim(substr($css, $next+1, $pos-2-$next)));

			if (isset($rules[$selector])) $rule->rules = array_merge($rules[$selector], $rule->rules);
			$rules[$selector] = $rule;
		}

		//Now sort $rules by depth, index
		usort($rules, [$this, 'sortRules']);
		return $rules;
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
			$str = substr_replace($str, '', $pos, $end-$pos);
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
