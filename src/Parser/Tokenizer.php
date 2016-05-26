<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class Tokenizer {
	private $str;
	const NAME = 1;
	const STRING = 2;
	const STRING2 = 3;
	const OPEN_BRACKET = 4;
	const CLOSE_BRACKET = 5;
	const OPEN_SQUARE_BRACKET = 6;
	const CLOSE_SQUARE_BRACKET = 7;
	const CONCAT = 8;
	const ARG = 9;
	const WHITESPACE = 10;
	const DOT = 11;
	const NUMERIC = 12;
	const EQUALS = 13;
	const NOT = 14;
	const OPEN_BRACE = 15;
	const CLOSE_BRACE = 16;
	const BOOL = 17;

	public $chars = [
		'"' => self::STRING,
		'\'' => self::STRING2,
		'(' => self::OPEN_BRACKET,
		')' => self::CLOSE_BRACKET,
		'[' => self::OPEN_SQUARE_BRACKET,
		']' => self::CLOSE_SQUARE_BRACKET,
		'+' => self::CONCAT,
		',' => self::ARG,
		'.' => self::DOT,
		'!' => self::NOT,
		'=' => self::EQUALS,
		'{' => self::OPEN_BRACE,
		'}' => self::CLOSE_BRACE,
		' ' => self::WHITESPACE,
		"\n" => self::WHITESPACE,
		"\r" => self::WHITESPACE,
		"\t" => self::WHITESPACE
	];

	public function __construct($str) {
		$this->str = $str;
	}

	public function getTokens() {
		$tokens = [];

		for ($i = 0; $i < strlen($this->str); $i++) {
			$char = $this->identifyChar($this->str[$i]);

			$this->doSimpleTokens($tokens, $char);
			$this->doLiterals($tokens, $char, $i);
			$i += $this->doStrings($tokens, $char, $i);
			$i += $this->doBrackets($tokens, $char, $i);
		}
		return $tokens;
	}

	private function doSimpleTokens(&$tokens, $char) {
		if (in_array($char, [Tokenizer::ARG, Tokenizer::CONCAT, Tokenizer::DOT, Tokenizer::NOT, Tokenizer::EQUALS])) {
			$tokens[] = ['type' => $char];
		}
	}

	private function doLiterals(&$tokens, $char, &$i) {
		if ($char === self::NAME) {
			$name = $this->str[$i];
			while (isset($this->str[$i+1]) && $this->identifyChar($this->str[$i+1]) == self::NAME) {
				$name .= $this->str[$i+1];
				$i++;
			}
			if (is_numeric($name)) $tokens[] = ['type' => self::NUMERIC, 'value' => $name];
			else if ($name == 'true') $tokens[] = ['type' => self::BOOL, 'value' => true];
			else if ($name == 'false') $tokens[] = ['type' => self::BOOL, 'value' => false];
			else $tokens[] = ['type' => self::NAME, 'value' => $name];
		}
	}

	private function doBrackets(&$tokens, $char, $i) {
		$types = [
			self::OPEN_BRACKET => ['(', ')'],
			self::OPEN_BRACE => ['{', '}'],
			self::OPEN_SQUARE_BRACKET => ['[', ']']
		];

		foreach ($types as $type => $brackets) {
			if ($char === $type) {
				$contents = $this->extractBrackets($i, $brackets[0], $brackets[1]);
				$tokenizer = new Tokenizer($contents);
				$tokens[] = ['type' => $type, 'value' => $tokenizer->getTokens()];
				return strlen($contents);
			}
		}
	}

	private function doStrings(&$tokens, $char, $i) {
		if (in_array($char, [self::STRING, self::STRING2])) {
			$string = $this->extractString($i);
			$length = strlen($string)+1;
			$char = $this->getChar($char);
			$string = str_replace('\\' . $char, $char, $string);
			$tokens[] = ['type' => self::STRING, 'value' => $string];
			return $length;
		}
	}

	private function extractString($pos) {
		$char = $this->str[$pos];
		$end = strpos($this->str, $char, $pos+1);
		while ($end !== false && $this->str[$end-1] == '\\') $end = strpos($this->str, $char, $end+1);

		return substr($this->str, $pos+1, $end-$pos-1);
	}

	private function extractBrackets($open, $startBracket = '(', $closeBracket = ')') {
		$close = strpos($this->str, $closeBracket, $open);

		$cPos = $open+1;
		while (($cPos = strpos($this->str, $startBracket, $cPos+1)) !== false && $cPos < $close) $close = strpos($this->str, $closeBracket, $close+1);
		return substr($this->str, $open+1, $close-$open-1);
	}

	private function identifyChar($chr) {
		if (isset($this->chars[$chr])) return $this->chars[$chr];
		else return self::NAME;
	}

	private function getChar($num) {
		$chars = array_reverse($this->chars);
		if (isset($chars[$num])) return $chars[$num];
		else return false;
	}
}
