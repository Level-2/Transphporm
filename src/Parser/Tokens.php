<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class Tokens implements \Iterator, \Countable {
    private $tokens;
    private $iterator = 0;

    public function __construct(array $tokens) {
        $this->tokens = $tokens;
    }

    public function count() {
        return count($this->tokens);
    }

    // Iterator Functions
    public function current() {
        return $this->tokens[$this->iterator];
    }

    public function key() {
        return $this->iterator;
    }

    public function next() {
        ++$this->iterator;
	}

	public function valid() {
		return isset($this->tokens[$this->iterator]);
	}

	public function rewind() {
		$this->iterator = 0;
	}

    private function getKeysOfTokenType($tokenType) {
        return array_keys(array_column($this->tokens, 'type'), $tokenType);
    }

    private function getKeyToSlice($tokenType) {
        $keys = $this->getKeysOfTokenType($tokenType);
        if (empty($keys)) return false;
        $key = $keys[0];
        for ($i = 0; $key < $this->iterator && isset($keys[$i]); $i++) $key = $keys[$i];
        return $key;
    }

    public function from($tokenType, $inclusive = false) {
        $key = $this->getKeyToSlice($tokenType);
        if ($key === false) return new Tokens([]);
        if (!$inclusive) $key++;
        return new Tokens(array_slice($this->tokens, $key));
    }

    public function to($tokenType, $inclusive = false) {
        $key = $this->getKeyToSlice($tokenType);
        if ($key === false) return new Tokens([]);
        if ($inclusive) $key++;
        return new Tokens(array_slice($this->tokens, $this->iterator, $key));
    }

    public function skip($count) {
        $this->iterator += $count;
    }

    public function splitOnToken($tokenType) {
        $splitTokens = [];
		$i = 0;
		foreach ($this->tokens as $token) {
			if ($token['type'] === $tokenType) $i++;
			else $splitTokens[$i][] = $token;
		}
        return array_map(function ($tokens) {
            return new Tokens($tokens);
        }, $splitTokens);
		//return $splitTokens;
    }

    public function trim() {
        $tokens = $this->tokens;
        // Remove end whitespace
        while (end($tokens)['type'] === Tokenizer::WHITESPACE) {
            array_pop($tokens);
        }
        // Remove begining whitespace
        while (isset($tokens[0]) && $tokens[0]['type'] === Tokenizer::WHITESPACE) {
            array_shift($tokens);
        }
        return new Tokens($tokens);
    }

    public function read($offset = 0) {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset]['value'] : false;
    }

    public function type($offset = 0) {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset]['type'] : false;
    }
}
