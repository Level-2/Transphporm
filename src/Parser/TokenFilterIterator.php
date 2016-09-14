<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class TokenFilterIterator implements \Iterator {
    private $ignore;
    private $tokens;

    public function __construct(Tokens $tokens, array $ignore) {
        $this->ignore = $ignore;
        $this->tokens = $tokens;
    }

    public function current() {
        return $this->tokens->current();
    }

    public function key() {
        return $this->tokens->key();
    }

    public function valid() {
        return $this->tokens->valid();
    }

    public function next() {
        do {
            $this->tokens->next();
        }
        while ($this->tokens->valid() && in_array($this->tokens->current()['type'], $this->ignore));
    }

    public function rewind() {
        $this->tokens->rewind();
        while ($this->tokens->valid() && in_array($this->tokens->current()['type'], $this->ignore)) $this->tokens->next();
    }
}