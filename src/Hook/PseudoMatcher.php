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
	private $functions = [];

	public function __construct($pseudo, \Transphporm\Parser\Value $valueParser) {
		$this->pseudo = $pseudo;
		$this->valueParser = $valueParser;
	}

	public function registerFunction(\Transphporm\Pseudo $pseudo) {
		$this->functions[] = $pseudo;
	}

	public function matches($element) {
		foreach ($this->pseudo as $tokens) {
			foreach ($this->functions as $function) {
				$matches = $this->match($tokens, $function, $element);
				if ($matches === false) return false;
			}
		}
		return true;
	}

	private function match($tokens, $function, $element) {
		try {
			$parts = $this->getFuncParts($tokens);
			$matches = $function->match($parts['name'], $parts['args'], $element);
			if ($matches === false) return false;
		}
		catch (\Exception $e) {
			throw new \Transphporm\RunException(\Transphporm\Exception::PSEUDO, $parts['name'], $e);
		}
	}
	private function getFuncParts($tokens) {
		$parts = [];
		$parts['name'] = $this->getFuncName($tokens);
		if ($parts['name'] === null || in_array($parts['name'], ['data', 'iteration', 'root'])) {
			$parts['args'] = $this->valueParser->parseTokens($tokens);
		}
		else if (count($tokens) > 1) {
			$tokens->rewind();
			$tokens->next();
			$parts['args'] = $this->valueParser->parseTokens($tokens->current()['value']);
		}
		else $parts['args'] = [['']];
		return $parts;
	}

	private function getFuncName($tokens) {
		if ($tokens->type() === Tokenizer::NAME) return $tokens->read();
		return null;
	}

	public function hasFunction($name) {
		foreach ($this->pseudo as $tokens) {
			if ($name === $this->getFuncName($tokens)) return true;
		}
	}

	public function getFuncArgs($name) {
		foreach ($this->pseudo as $tokens) {
			$parts = $this->getFuncParts($tokens);
			if ($name === $parts['name']) return $parts['args'];
		}
	}
}
