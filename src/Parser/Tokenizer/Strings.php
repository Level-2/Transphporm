<?php
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class Strings implements \Transphporm\Parser\Tokenize {

	public function tokenize(TokenizedString $str, Tokens $tokens) {
		if ($str->identifyChar() === Tokenizer::STRING) {
			$chr = $str->read();
			$string = $str->extractString();
			$length = strlen($string)+1;
			$string = str_replace('\\' . $chr, $chr, $string);
			$tokens->add(['type' => Tokenizer::STRING, 'value' => $string, 'line' => $str->lineNo()]);
			$str->move($length);
		}
	}

}