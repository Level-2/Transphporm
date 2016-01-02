<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Not implements \Transphporm\Pseudo {
	private $dataFunction;

	public function __construct(\Transphporm\Hook\DataFunction $dataFunction) {
		$this->dataFunction = $dataFunction;
	}

	public function match($pseudo, \DomElement $element) {
		if (strpos($pseudo, 'not') === 0) {
			$valueParser = new \Transphporm\Parser\Value($this->dataFunction);
			$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
			$css = explode(',', $bracketMatcher->match('(', ')'));

			foreach ($css as $selector) {
				$cssToXpath = new \Transphporm\Parser\CssToXpath($selector, $valueParser);
				$xpathString = $cssToXpath->getXpath();	
				$xpath = new \DomXpath($element->ownerDocument);
				
				foreach ($xpath->query($xpathString) as $matchedElement) {
					if ($element->isSameNode($matchedElement)) return false;
				}
			}
		}
		return true;
	}
}