<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
use \Transphporm\Parser\Tokenizer;
/** Determines whether $element matches the pseudo rule such as nth-child() or [attribute="value"] */
class PseudoMatcher {
	private $pseudo;
	private $valueParser;
	private $functionSet;
	private $functions = [];

	public function __construct($pseudo, \Transphporm\Parser\Value $valueParser, \Transphporm\FunctionSet $functionSet) {
		$this->pseudo = $pseudo;
		$this->valueParser = $valueParser;
		$this->functionSet = $functionSet;
	}

	public function registerFunction(\Transphporm\Pseudo $pseudo) {
		$this->functions[] = $pseudo;
	}

	public function matches($element) {
		$matches = true;
		$this->functionSet->setElement($element);

		foreach ($this->pseudo as $tokens) {
			foreach ($this->functions as $function) {
				$parts = $this->getFuncParts($tokens);
				$matches = $matches && $function->match($parts['name'], $parts['args'], $element);
			}
		}
		return $matches;
	}

	private function getFuncParts($tokens) {
		$parts = [];
		$parts['name'] = $this->getFuncName($tokens);
		if ($parts['name'] === null || in_array($parts['name'], ['data', 'iteration', 'root'])) {
			$parts['args'] = $this->valueParser->parseTokens($tokens, $this->functionSet);
		}
		elseif (isset($tokens[1])) $parts['args'] = $this->valueParser->parseTokens($tokens[1]['value'], $this->functionSet);
		else $parts['args'] = [['']];
		return $parts;
	}

	private function getFuncName($tokens) {
		if ($tokens[0]['type'] === Tokenizer::NAME) return $tokens[0]['value'];
		return null;
	}

	public function hasFunction($name) {
		foreach ($this->pseudo as $tokens) {
			if ($name === $this->getFuncName($tokens)) return true;
		}
	}

	// TODO: Improve the functionality of getFuncArgs and make it similar to when using `match`
	public function getFuncArgs($name, $element) {
		$this->functionSet->setElement($element);

		foreach ($this->pseudo as $tokens) {
			$parts = $this->getFuncParts($tokens);
			if ($name === $parts['name']) return $parts['args'];
		}
	}
}
