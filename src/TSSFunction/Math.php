<?php

namespace Transphporm\TSSFunction;

class Math implements \Transphporm\TSSFunction {
    private $mode;

    const ADD = 'add';
    const SUBTRACT = 'sub';
    const MULTIPLY = 'mult';
    const DIVIDE = 'div';

    public function __construct($mode) {
        $this->mode = $mode;
    }

    public function run(array $args, \DomElement $element) {
        $result = $args[0];
        for ($i = 1; $i < count($args); $i++) $result = $this->getModeResult($args[$i], $result);
        return $result;
    }

    private function getModeResult($val, $prev) {
        return $this->{$this->mode}($val, $prev);
    }

    private function add($val, $prev) {
        return $prev+$val;
    }

    private function sub($val, $prev) {
        return $prev-$val;
    }

    private function mult($val, $prev) {
        return $prev*$val;
    }

    private function div($val, $prev) {
        return $prev/$val;
    }
}
