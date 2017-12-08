<?php
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class Literals implements \Transphporm\Parser\Tokenize {
	private $c = 0;

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

	private function isLiteral($n, $str) {
		//Is it a normal literal character
		return ($str->has($n) && $str->identifyChar($n, $str) === Tokenizer::NAME
		//but a subtract can be part of a class name or a mathematical operation
				|| ($str->has($n) && $str->identifyChar($n) == Tokenizer::SUBTRACT && !is_numeric($str->read($n-1)))
			);
	}

	private function processLiterals($tokens, $name, $str) {
		if (is_numeric($name)) $tokens->add(['type' => Tokenizer::NUMERIC, 'value' => $name]);
		else if ($name == 'true') $tokens->add(['type' => Tokenizer::BOOL, 'value' => true]);
		else if ($name == 'false') $tokens->add(['type' => Tokenizer::BOOL, 'value' => false]);
		else $tokens->add(['type' => Tokenizer::NAME, 'value' => $name, 'line' => $str->lineNo()]);
	}
}