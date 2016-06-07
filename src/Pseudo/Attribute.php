<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Attribute implements \Transphporm\Pseudo {
	private $functionSet;

	public function __construct(\Transphporm\FunctionSet $functionSet) {
		$this->functionSet = $functionSet;
	}

	public function match($pseudo, \DomElement $element) {

		$pos = strpos($pseudo, '[');
		if ($pos === false) return true;

		$name = substr($pseudo, 0, $pos);
		if (!$this->functionSet->hasFunction($name)) return true;

		$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
		$criteria = $bracketMatcher->match('[', ']');

		$valueParser = new \Transphporm\Parser\Value($this->functionSet);

		$criteria = $name . '(' . $criteria;

		$pos = strpos($pseudo, '!');
		if ($pos === false) $pos = strpos($pseudo, '=');
		if ($pos === false) {
			$criteria .= ')=true';
		}
		else $criteria = substr_replace($criteria, ')', $pos, 0);

		return $valueParser->parse($criteria, $element)[0];
	}
}
