<?php
namespace Transphporm\Parser\Tokenizer;
use Transphporm\Parser\Tokenizer;

class TokenizedString {
	private $str;
	public $pos = 0;
	public $lineNo = 1;

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
		if ($n === false) $this->pos = strlen($this->str)-1;
		else $this->pos += $n;
	}

	/*public function next() {
	//	$this->pos++;
	//	return $this->pos > strlen($this->str) ? false : $this->str[$this->pos];
	}*/

	public function read($offset = 0) {
		return $this->str[$this->pos + $offset];
	}

	public function identifyChar($offset = 0) {
		$chr = $this->str[$this->pos + $offset];
		if (!empty($this->chars[$chr])) return $this->chars[$chr];
		else return Tokenizer::NAME;
	}

	public function has($offset = 0) {
		return isset($this->str[$this->pos + $offset]);
	}

	private function identifyChar2($chr) {
		if (isset($this->chars[$chr])) return $this->chars[$chr];
		else return self::NAME;
	}


	public function count() {
		return strlen($this->str);
	}

	public function pos($str) {
		$pos = strpos($this->str,  $str, $this->pos);
		return $pos ? $pos-$this->pos : false;
	}

	public function newLine() {
		return $this->lineNo++;
	}

	public function lineNo() {
		return $this->lineNo;
	}

	public function undigested() {
		return substr($this->str, $this->pos);
	}

	public function extractString($offset = 0) {
		$pos = $this->pos + $offset;
		$char = $this->str[$pos];
		$end = strpos($this->str, $char, $pos+1);
		while ($end !== false && $this->str[$end-1] == '\\') $end = strpos($this->str, $char, $end+1);

		return substr($this->str, $pos+1, $end-$pos-1);
	}
}