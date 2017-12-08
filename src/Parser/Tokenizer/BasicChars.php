<?php
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class BasicChars implements \Transphporm\Parser\Tokenize {

	public function tokenize(TokenizedString $str, Tokens $tokens) {
		$this->newLine($str, $tokens);
		$this->whitespace($str, $tokens);
		$this->simpleTokens($str, $tokens);
	}

	public function whitespace(TokenizedString $str, Tokens $tokens) {
		//Combine whitespace, this increases performance across the board: Anywhere tokens are iterated over, whitespace is only looped once 8 spaces of indentation = 1 iteration
		$char = $str->identifyChar();
		if ($char === Tokenizer::WHITESPACE) {
			$last = $tokens->end();
			if ($last['type'] !== Tokenizer::WHITESPACE) {
				$tokens->add(['type' => $char]);
			}
		}
	}

	private function newLine(TokenizedString $str, Tokens $tokens) {
		if ($str->identifyChar() == Tokenizer::NEW_LINE) {
			$tokens->add(['type' => Tokenizer::WHITESPACE, 'line' => $str->newLine()]);
		}
	}


	private function simpleTokens($str, $tokens) {
		$char = $str->identifyChar();
		if (in_array($char, [Tokenizer::ARG, Tokenizer::CONCAT, Tokenizer::DOT, Tokenizer::NOT, Tokenizer::EQUALS,
			Tokenizer::COLON, Tokenizer::SEMI_COLON, Tokenizer::NUM_SIGN,
			Tokenizer::GREATER_THAN, Tokenizer::LOWER_THAN, Tokenizer::AT_SIGN, Tokenizer::SUBTRACT, Tokenizer::MULTIPLY, Tokenizer::DIVIDE])) {
			$tokens->add(['type' => $char, 'line' => $str->lineNo()]);
		}
	}

}