<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser;
class TokenFilterIterator implements \Iterator {
    private $ignore;
    private $tokens;

    public function __construct(Tokens $tokens, array $ignore) {
        $this->ignore = $ignore;
        $this->tokens = $tokens;
    }

    public function current(): mixed {
        return $this->tokens->current();
    }

    public function key(): mixed {
        return $this->tokens->key();
    }

    public function valid(): bool {
        return $this->tokens->valid();
    }

    public function next(): void {
        do {
            $this->tokens->next();
        }
        while ($this->shouldContinue());
    }

    public function rewind(): void {
        $this->tokens->rewind();
        while ($this->shouldContinue()) $this->tokens->next();
    }

    private function shouldContinue() {
        return $this->tokens->valid() && in_array($this->tokens->current()['type'], $this->ignore);
    }
}
