<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Hook;
use \Transphporm\Parser\Tokenizer;
/** Determines whether $element matches the pseudo rule such as nth-child() or [attribute="value"] */
class PseudoMatcher {
	private $pseudo;
	private $valueParser;
	private $functions = [];
	private $funcParts = [];

	public function __construct($pseudo, \Transphporm\Parser\Value $valueParser) {
		$this->pseudo = $pseudo;
		$this->valueParser = $valueParser;
	}

	public function registerFunction($name, \Transphporm\Pseudo $pseudo) {
		$this->functions[$name] = $pseudo;
	}

	public function matches($element) {
		foreach ($this->pseudo as $i => $tokens) {
			$parts = $this->getFuncParts($i, $tokens);
			if ($parts['name'] === null) $parts['name'] = 'data';
			if (!isset($this->functions[$parts['name']])) continue;
			if ($this->match($parts, $this->functions[$parts['name']], $element) === false) {
				return false;
			}

		}
		return true;
	}

	private function match($parts, $function, $element) {
		try {
			$matches = $function->match($parts['name'], $parts['args'], $element);
			if ($matches === false) return false;
		}
		catch (\Exception $e) {
			throw new \Transphporm\RunException(\Transphporm\Exception::PSEUDO, $parts['name'], $e);
		}
	}
	private function getFuncParts($i, $tokens) {
		if (isset($this->funcParts[$i])) return $this->funcParts[$i];
		$parts = [];
		$canCache = true;
		$parts['name'] = $this->getFuncName($tokens);
		if ($parts['name'] === null || in_array($parts['name'], ['data', 'iteration', 'root'])) {
			//If the args are dynamic, it can't be cached as it may change between calls
			$canCache = false;
			$parts['args'] = $this->valueParser->parseTokens($tokens);
		}
		else if (count($tokens) > 1) {
			$tokens->rewind();
			$tokens->next();
			$this->skipWhitespace($tokens);
			$parts['args'] = $this->valueParser->parseTokens($tokens->current()['value']);
		}
		else $parts['args'] = [['']];
		if ($canCache) $this->funcParts[$i] = $parts;
		return $parts;
	}

	private function skipWhitespace($tokens) {
		while ($tokens->current()['type'] === 'WHITESPACE' || $tokens->current()['type'] == 'NEWLINE') $tokens->next();
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
		foreach ($this->pseudo as $i => $tokens) {
			$parts = $this->getFuncParts($i, $tokens);
			if ($name === $parts['name']) return $parts['args'];
		}
	}
}
