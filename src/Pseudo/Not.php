<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Pseudo;
class Not implements \Transphporm\Pseudo {
	private $cssToXpath;
    private $config;

	public function __construct(\Transphporm\Parser\CssToXpath $cssToXpath, \Transphporm\Config $config) {
		$this->cssToXpath = $cssToXpath;
        $this->config = $config;
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
            $pseudo = $this->cssToXpath->getPseudo($tokenizer->getTokens());
            $pseudoMatcher = $this->config->createPseudoMatcher($pseudo);
			if ($this->matches($xpath->query($xpathString), $element, $pseudoMatcher)) return false;
		}
		return true;
	}

    private function matches($foundElements, $element, $pseudoMatcher) {
        //Find all nodes matched by the expressions in the brackets :not(EXPR)
        foreach ($foundElements as $matchedElement) {
            //Check to see whether this node was matched by the not query
            if ($pseudoMatcher->matches($matchedElement) && $element->isSameNode($matchedElement)) return true;
        }
        return false;
    }
}
