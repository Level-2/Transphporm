<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Not implements \Transphporm\Pseudo {
	private $cssToXpath;

	public function __construct(\Transphporm\Parser\CssToXpath $cssToXpath) {
		$this->cssToXpath = $cssToXpath;
	}

	public function match($name, $args, \DomElement $element) {
		if ($name !== 'not') return true;

		$xpath = new \DomXpath($element->ownerDocument);
		return $this->notElement($args, $xpath, $element);
	}

	private function notElement($css, $xpath, $element) {

		foreach ($css as $selector) {
			$tokenizer = new \Transphporm\Parser\Tokenizer($selector);
			$xpathString = $this->cssToXpath->getXpath($tokenizer->getTokens());
			//Find all nodes matched by the expressions in the brackets :not(EXPR)
			foreach ($xpath->query($xpathString) as $matchedElement) {
				//Check to see whether this node was matched by the not query
				if ($element->isSameNode($matchedElement)) return false;
			}
		}
		return true;
	}
}
