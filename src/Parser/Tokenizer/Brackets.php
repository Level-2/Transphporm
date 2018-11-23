<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class Brackets implements \Transphporm\Parser\Tokenizable {

	private $types =  [
			Tokenizer::OPEN_BRACKET => ['(', ')'],
			Tokenizer::OPEN_BRACE => ['{', '}'],
			Tokenizer::OPEN_SQUARE_BRACKET => ['[', ']']
	];

	public function tokenize(TokenizedString $str, Tokens $tokens) {
		foreach ($this->types as $type => $brackets) {
			if ($str->has() && $str->identifyChar() === $type) {
				$contents = $str->extractBrackets($brackets[0], $brackets[1]);
				$tokenizer = new Tokenizer($contents);
				$tokens->add(['type' => $type, 'value' => $tokenizer->getTokens(), 'string' => $contents, 'line' => $str->lineNo()]);
				$str->move(strlen($contents));
			}
		}
	}
}