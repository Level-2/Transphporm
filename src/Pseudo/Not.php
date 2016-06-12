<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Not implements \Transphporm\Pseudo {
	private $functionSet;
	private $cssToXpath;

	public function __construct(\Transphporm\FunctionSet $functionSet, \Transphporm\Parser\CssToXpath $cssToXpath) {
		$this->functionSet = $functionSet;
		$this->cssToXpath = $cssToXpath;
	}

	public function match($pseudo, \DomElement $element) {
		if (strpos($pseudo, 'not') === 0) {
			$parser = new \Transphporm\Parser\Value($this->functionSet);
			$tokenizer = new \Transphporm\Parser\Tokenizer($pseudo);
			$tokens = $tokenizer->getTokens();
			$this->functionSet->setElement($element);


			var_dump($tokens[1]);
			$css = $parser->parse($tokens[1]['string']);
		
	
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