<?php
namespace Transphporm\Parser;

interface Tokenize {
	public function tokenize(Tokenizer\TokenizedString $str, $tokens, $char);
}