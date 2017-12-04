<?php
namespace Transphporm\Parser\Tokenizer;
use Transphporm\Parser\Tokenizer;

class TokenizedString {
	private $str;
	public $pos = 0;

	private $chars = [
		'"' => Tokenizer::STRING,
		'\'' => Tokenizer::STRING,
		'(' => Tokenizer::OPEN_BRACKET,
		')' => Tokenizer::CLOSE_BRACKET,
		'[' => Tokenizer::OPEN_SQUARE_BRACKET,
		']' => Tokenizer::CLOSE_SQUARE_BRACKET,
		'+' => Tokenizer::CONCAT,
		',' => Tokenizer::ARG,
		'.' => Tokenizer::DOT,
		'!' => Tokenizer::NOT,
		'=' => Tokenizer::EQUALS,
		'{' => Tokenizer::OPEN_BRACE,
		'}' => Tokenizer::CLOSE_BRACE,
		':' => Tokenizer::COLON,
		';' => Tokenizer::SEMI_COLON,
		'#' => Tokenizer::NUM_SIGN,
		'>' => Tokenizer::GREATER_THAN,
		'@' => Tokenizer::AT_SIGN,
		'-' => Tokenizer::SUBTRACT,
		'*' => Tokenizer::MULTIPLY,
		'/' => Tokenizer::DIVIDE,
		' ' => Tokenizer::WHITESPACE,
		"\n" => Tokenizer::NEW_LINE,
		"\r" => Tokenizer::WHITESPACE,
		"\t" => Tokenizer::WHITESPACE
	];

	public function __construct($str) {
		$this->str = $str;
	}

	public function move($n) {
		if ($n == false) $this->pos = strlen($this->str)-1;
		else $this->pos += $n;
	}

	public function next() {
		$this->pos++;
		return $this->pos > strlen($this->str) ? false : $this->str[$this->pos];
	}

	public function identifyChar($offset = 0) {
		if (!isset($this->str[$this->pos + $offset])) return false;

		$chr = $this->str[$this->pos + $offset];
		if (isset($this->chars[$chr])) return $this->chars[$chr];
		else return Tokenizer::NAME;
	}

	public function count() {
		return strlen($this->str);
	}

	public function pos($str) {
		$pos = strpos($this->str,  $str, $this->pos);
		return $pos ? $pos-$this->pos : false;
	}

}