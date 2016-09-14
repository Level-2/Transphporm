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

	private $chars = [
		'"' => self::STRING,
		'\'' => self::STRING,
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
		':' => self::COLON,
		';' => self::SEMI_COLON,
		'#' => self::NUM_SIGN,
		'>' => self::GREATER_THAN,
		'@' => self::AT_SIGN,
		' ' => self::WHITESPACE,
		"\n" => self::WHITESPACE,
		"\r" => self::WHITESPACE,
		"\t" => self::WHITESPACE
	];

	public function __construct($str) {
		$this->str = $str;
	}

	public function getTokens($returnObj = true) {
		$tokens = [];

		for ($i = 0; $i < strlen($this->str); $i++) {
			$char = $this->identifyChar($this->str[$i]);

			$this->doSimpleTokens($tokens, $char);
			$this->doLiterals($tokens, $char, $i);
			$i += $this->doStrings($tokens, $char, $i);
			$i += $this->doBrackets($tokens, $char, $i);
		}
		if ($returnObj) return new Tokens($tokens);
		else return $tokens;
	}

	private function doSimpleTokens(&$tokens, $char) {
		if (in_array($char, [Tokenizer::ARG, Tokenizer::CONCAT, Tokenizer::DOT, Tokenizer::NOT,
			Tokenizer::EQUALS, Tokenizer::COLON, Tokenizer::SEMI_COLON, Tokenizer::WHITESPACE,
			Tokenizer::NUM_SIGN, Tokenizer::GREATER_THAN, Tokenizer::AT_SIGN])) {
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
			$this->processLiterals($tokens, $name);
		}
	}

	private function processLiterals(&$tokens, $name) {
		if (is_numeric($name)) $tokens[] = ['type' => self::NUMERIC, 'value' => $name];
		else if ($name == 'true') $tokens[] = ['type' => self::BOOL, 'value' => true];
		else if ($name == 'false') $tokens[] = ['type' => self::BOOL, 'value' => false];
		else $tokens[] = ['type' => self::NAME, 'value' => $name];
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
				$tokens[] = ['type' => $type, 'value' => $tokenizer->getTokens(), 'string' => $contents];
				return strlen($contents);
			}
		}
	}

	private function doStrings(&$tokens, $char, $i) {
		if ($char === self::STRING) {
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

	public function serialize($tokens) {
		$str = '';
		$chars = array_flip($this->chars);

		foreach ($tokens as $token) {
			if (isset($chars[$token['type']])) {
				$str .= $chars[$token['type']];
			}
			$str .= $this->serializeValue($token);
		}
		return $str;
	}

	private function serializeValue($token) {
		if (isset($token['value'])) {
			if ($token['value'] instanceof Tokens) return $this->serialize($token['value']);
			else return $token['value'];	
		}			
	}
}
