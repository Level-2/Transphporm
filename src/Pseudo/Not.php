<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
use \Transphporm\Parser\Tokenizer;
class Not implements \Transphporm\Pseudo {
	private $functionSet;
	private $cssToXpath;

	public function __construct(\Transphporm\FunctionSet $functionSet, \Transphporm\Parser\CssToXpath $cssToXpath) {
		$this->functionSet = $functionSet;
		$this->cssToXpath = $cssToXpath;
	}

	public function match($pseudo, \DomElement $element) {
		if ($pseudo[0]['type'] === Tokenizer::NAME && $pseudo[0]['value'] === 'not') {
			$parser = new \Transphporm\Parser\Value($this->functionSet);
			$this->functionSet->setElement($element);

			$css = $parser->parse($pseudo[1]['string']);


			$xpath = new \DomXpath($element->ownerDocument);
			return $this->notElement($css, $xpath, $element);
		}
		return true;
	}

	private function notElement($css, $xpath, $element) {

		foreach ($css as $selector) {
			$xpathString = $this->cssToXpath->getXpath($selector);
			//Find all nodes matched by the expressions in the brackets :not(EXPR)
			foreach ($xpath->query($xpathString) as $matchedElement) {
				//Check to see whether this node was matched by the not query
				if ($element->isSameNode($matchedElement)) return false;
			}
		}
		return true;
	}
}
