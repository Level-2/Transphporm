<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser\Tokenizer;
use Transphporm\Parser\Tokenizer;

class TokenizedString {
	private $str;
	private $pos = -1;
	private $lineNo = 1;

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
		'<' => Tokenizer::LOWER_THAN,
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

	public function next() {
		$this->pos++;
		return $this->pos < strlen((string)$this->str);
	}

	public function reset() {
		$this->lineNo = 1;
		$this->pos = -1;
	}

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

	public function extractString($offset = 0) {
		$pos = $this->pos + $offset;
		$char = $this->str[$pos];
		$end = strpos($this->str, $char, $pos+1);
		while ($end !== false && $this->str[$end-1] == '\\') $end = strpos($this->str, $char, $end+1);

		return substr($this->str, $pos+1, $end-$pos-1);
	}

	public function extractBrackets($startBracket = '(', $closeBracket = ')', $offset = 0) {
		$open = $this->pos+$offset;
		$close = strpos($this->str, $closeBracket, $open);

		$cPos = $open+1;
		while (($cPos = strpos($this->str, $startBracket, $cPos+1)) !== false && $cPos < $close) $close = strpos($this->str, $closeBracket, $close+1);
		return substr($this->str, $open+1, $close-$open-1);
	}

}
