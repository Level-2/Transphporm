<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/** Determines whether $element matches the pseudo rule such as nth-child() or [attribute="value"] */
class PseudoMatcher {
	private $pseudo;
	private $functions = [];

	public function __construct($pseudo) {
		$this->pseudo = $pseudo;
	}

	public function registerFunction(\Transphporm\Pseudo $pseudo) {
		$this->functions[] = $pseudo;
	}

	public function matches($element) {
		$matches = true;

		foreach ($this->pseudo as $pseudo) {			
			foreach ($this->functions as $function) {
				$matches = $matches && $function->match($pseudo, $element);
			}
		}		
		return $matches;
	}
	
	public function hasFunction($name) {
		foreach ($this->pseudo as $pseudo) {
			if (strpos($pseudo, $name) === 0) return true;
		}
	}

	public function getFuncArgs($name) {
		foreach ($this->pseudo as $pseudo) {
			if (strpos($pseudo, $name) === 0) {
				$brackets = $this->getBracketType($pseudo);
				$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
				$ret = $bracketMatcher->match($brackets[0], $brackets[1]);
				return $ret;
			}
		}
	}

	private function getBracketType($pseudo) {
		$parenthesis = strpos($pseudo, '(');
		$square = strpos($pseudo, ']');

		if ($parenthesis === false) $parenthesis = 999;
		if ($square === false) $square = 999;

		if ($parenthesis < $square) return ['(', ')'];
		return ['[', ']'];
	}
	
}