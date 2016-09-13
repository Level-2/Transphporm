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
    private $ignoreWhitespace = false;

    public function __construct(array $tokens, $ignoreWhitespace = false) {
        $this->tokens = $tokens;
        $this->ignoreWhitespace = $ignoreWhitespace;
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
        if ($this->ignoreWhitespace) {
            do {
                ++$this->iterator;
            }
            while (isset($this->tokens[$this->iterator]) && $this->tokens[$this->iterator]['type'] === Tokenizer::WHITESPACE);
        }
        else ++$this->iterator;
	}

	public function valid() {
		return isset($this->tokens[$this->iterator]);
	}

	public function rewind() {
		$this->iterator = 0;
        if ($this->ignoreWhitespace) {
            while (isset($this->tokens[$this->iterator]) && $this->tokens[$this->iterator]['type'] === Tokenizer::WHITESPACE) ++$this->iterator;
        }
	}

    // Helpful Functions
    public function ignoreWhitespace($ignore = false) {
        return new Tokens($this->tokens, $ignore);
    }

    private function getKeysOfTokenType($tokenType) {
        return array_keys(array_column($this->tokens, 'type'), $tokenType);
    }

    public function from($tokenType, $inclusive = false) {
        $keys = $this->getKeysOfTokenType($tokenType);
        if (count($keys) === 0) return new Tokens([]);
        $key = $keys[0];
        for ($i = 0; $key < $this->iterator; $i++) $key = $keys[$i];
        if (!$inclusive) $key++;
        return new Tokens(array_slice($this->tokens, $key));
    }

    public function to($tokenType, $inclusive = false) {
        $keys = $this->getKeysOfTokenType($tokenType);
        if (empty($keys)) return new Tokens([]);
        $key = $keys[0];
        for ($i = 0; $key < $this->iterator; $i++) $key = $keys[$i];
        if ($inclusive) $key++;
        return new Tokens(array_slice($this->tokens, 0, $key));
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
            return new Tokens($tokens, $this->ignoreWhitespace);
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

    public function getTokens() {
    //    throw new \Exception();
        $tokens = [];
        //Loop through $this to account for $ignoreWhitespace
        foreach ($this as $token) {
            $tokens[] = $token;
        }
        return $tokens;
    }

    public function read() {
        return isset($this->tokens[0]) ? $this->tokens[0]['value'] : false;
    }
}
