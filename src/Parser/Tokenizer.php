<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class Tokenizer {
	private $str;
	const NAME = 'LITERAL';
	const STRING = 'STRING';
	const OPEN_BRACKET = 'OPEN_BRACKET';
	const CLOSE_BRACKET = 'CLOSE_BRACKET';
	const OPEN_SQUARE_BRACKET = 'SQUARE_BRACKET';
	const CLOSE_SQUARE_BRACKET = 'CLOSE_SQUARE_BRACKET';
	const CONCAT = 'CONCAT';
	const ARG = 'ARG';
	const WHITESPACE = 'WHITESPACE';
	const NEW_LINE = 'NEW_LINE';
	const DOT = 'DOT';
	const NUMERIC = 'NUMERIC';
	const EQUALS = 'EQUALS';
	const NOT = 'NOT';
	const OPEN_BRACE = 'OPEN_BRACE';
	const CLOSE_BRACE = 'CLOSE_BRACE';
	const BOOL = 'BOOL';
	const COLON = 'COLON';
	const SEMI_COLON = 'SEMI_COLON';
	const NUM_SIGN = 'NUM_SIGN';
	const GREATER_THAN = 'GREATER_THAN';
	const AT_SIGN = 'AT_SIGN';
	const SUBTRACT = 'SUBTRACT';
	const MULTIPLY = 'MULTIPLY';
	const DIVIDE = 'DIVIDE';

	public function __construct($str) {
		$this->str = new Tokenizer\TokenizedString($str);

		$this->tokenizeRules = [
			new Tokenizer\Comments,
			new Tokenizer\BasicChars,
			new Tokenizer\Literals,
			new Tokenizer\Strings,
			new Tokenizer\Brackets
		];
	}

	public function getTokens() {
		$tokens = new Tokens;
		$this->str->reset();

		while ($this->str->next()) {
			foreach ($this->tokenizeRules as $tokenizer) {
				$tokenizer->tokenize($this->str, $tokens);
			}
		}

		return $tokens;
	}

}
