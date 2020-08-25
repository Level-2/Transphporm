<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class Literals implements \Transphporm\Parser\Tokenizable {

	public function tokenize(TokenizedString $str, Tokens $tokens) {
		$char = $str->identifyChar();
		if ($char === Tokenizer::NAME) {

			$name = $str->read();
			$j = 0;

			while ($this->isLiteral($j+1, $str)) {
				$name .= $str->read($j+1);
				$j++;
			}

			$str->move($j);

			$this->processLiterals($tokens, $name, $str);
		}
	}

	private function isRealSubtract($n, $str) {
		$n--;
		// allow foo(2-5)
		//but not data(foo2-5)
		while (is_numeric($str->read($n))) {
			$n--;
		}

		if ($n == 0) return false;
		if (in_array($str->read($n), ['(', "\n", ' ', '['])) return true;

		return false;
	}

	private function isLiteral($n, $str) {
		//Is it a normal literal character
		return ($str->has($n) && ($str->identifyChar($n, $str) === Tokenizer::NAME
		//but a subtract can be part of a class name or a mathematical operation
				|| $str->identifyChar($n) == Tokenizer::SUBTRACT && !$this->isRealSubtract($n, $str))
			);
	}

	private function processLiterals($tokens, $name, $str) {
		if (is_numeric($name)) $tokens->add(['type' => Tokenizer::NUMERIC, 'value' => $name]);
		else if (method_exists($this, $name)) $this->$name($tokens);
		else $tokens->add(['type' => Tokenizer::NAME, 'value' => $name, 'line' => $str->lineNo()]);
	}

	private function true($tokens) {
		$tokens->add(['type' => Tokenizer::BOOL, 'value' => true]);
	}

	private function false($tokens) {
		$tokens->add(['type' => Tokenizer::BOOL, 'value' => false]);
	}

	private function in($tokens) {
		$tokens->add(['type' => Tokenizer::IN, 'value' => 'in']);
	}
}