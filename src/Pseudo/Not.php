<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Not implements \Transphporm\Pseudo {
	private $functionSet;

	public function __construct(\Transphporm\FunctionSet $functionSet) {
		$this->functionSet = $functionSet;
	}

	public function match($pseudo, \DomElement $element) {
		if (strpos($pseudo, 'not') === 0) {
			$valueParser = new \Transphporm\Parser\Value($this->functionSet);
			$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
			$css = explode(',', $bracketMatcher->match('(', ')'));
			$xpath = new \DomXpath($element->ownerDocument);
			return $this->notElement($css, $valueParser, $xpath, $element);
		}
		return true;
	}

	private function notElement($css, $valueParser, $xpath, $element) {
		foreach ($css as $selector) {
			$cssToXpath = new \Transphporm\Parser\CssToXpath($selector, $valueParser);
			$xpathString = $cssToXpath->getXpath();					
			//Find all nodes matched by the expressions in the brackets :not(EXPR)
			foreach ($xpath->query($xpathString) as $matchedElement) {
				//Check to see whether this node was matched by the not query
				if ($element->isSameNode($matchedElement)) return false;
			}
		}
		return true;
	}
}