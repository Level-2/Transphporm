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

		while ($next = strpos($css, '{', $pos)) {
			$rule = new \stdclass;
			$selector = trim(substr($css, $pos, $next-$pos));
			$x = new CssToXpath($selector);

			$rule->query = $x->getXpath();
			$rule->pseudo = $x->getPseudo();
		

			$pos =  strpos($css, '}', $next)+1;
			$rule->rules = $this->getRules(trim(substr($css, $next+1, $pos-2-$next)));
			$rules[] = $rule;
		}

		return $rules;
	}

	private function getRules($str) {
		$rules = explode(';', $str);
		$return = [];
		
		foreach ($rules as $rule) {
			if (trim($rule) == '') continue;
			$parts = explode(':', $rule, 2);
			$return[trim($parts[0])] = isset($parts[1]) ? trim($parts[1]) : '';
		}

		return $return;
	}
}
