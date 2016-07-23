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

		foreach ($this->pseudo as $pseudo) {
			$tokenizer = new \Transphporm\Parser\Tokenizer($pseudo);
			$tokens = $tokenizer->getTokens();
			foreach ($this->functions as $function) {
				$parts = $this->getFuncParts($tokens);
				$matches = $matches && $function->match($parts['name'], $parts['args'], $element);
			}
		}
		return $matches;
	}

	private function getFuncParts($tokens) {
		$parts = [];
		if ($tokens[0]['type'] === Tokenizer::NAME) $parts['name'] = $tokens[0]['value'];
		else $parts['name'] = null;
		if ($parts['name'] === null || (isset($tokens[1]) && $tokens[1]['type'] === Tokenizer::OPEN_SQUARE_BRACKET)) {
			$parts['name'] = null;
			$parts['args'] = $this->valueParser->parseTokens($tokens, $this->functionSet);
		}
		elseif (isset($tokens[1])) $parts['args'] = $this->valueParser->parseTokens($tokens[1]['value'], $this->functionSet);
		else $parts['args'] = [];
		return $parts;
	}

	public function hasFunction($name) {
		foreach ($this->pseudo as $pseudo) {
			if (strpos($pseudo, $name) === 0) return true;
		}
	}

	// TODO: Improve the functionality of getFuncArgs and make it similar to when using `match`
	public function getFuncArgs($name) {
		//$this->functionSet->setElement($element);
		foreach ($this->pseudo as $pseudo) {
			if (strpos($pseudo, $name) === 0) {
				$tokenizer = new \Transphporm\Parser\Tokenizer($pseudo);
				$tokens = $tokenizer->getTokens();
				return isset($tokens[1]) ? $tokens[1]['value'][0]['value'] : '';
			}
		}
	}
}
