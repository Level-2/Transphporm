<?php
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer as Tokenizer;

class Comments implements \Transphporm\Parser\Tokenize {
	public function tokenize(TokenizedString $str, $tokens, $char) {
		return $this->singleLineComments($str) + $this->multiLinecomments($str);
	}

	private function singleLineComments($str) {
		if ($str->identifyChar() == Tokenizer::DIVIDE && $str->identifyChar(1) == Tokenizer::DIVIDE) {
			$pos = $str->pos("\n");
			$str->move($pos !== false ? $pos : false);
		}
	}

	public function multiLinecomments($str) {
		if ($str->identifyChar() == Tokenizer::DIVIDE && $str->identifyChar(1) == Tokenizer::MULTIPLY) {
			$pos = $str->pos('*/');
			$str->move($pos !== false ? $pos+2 : false);
		}
	}
}