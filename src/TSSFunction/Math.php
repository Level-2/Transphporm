<?php

namespace Transphporm\TSSFunction;

class Math implements \Transphporm\TSSFunction {
    private $mode;

    const ADD = 1;
    const SUBTRACT = 2;
    const MULTIPLY = 3;
    const DIVIDE = 4;

    public function __construct($mode) {
        $this->mode = $mode;
    }

    public function run(array $args, \DomElement $element) {
        $result = $args[0];
        for ($i = 1; $i < count($args); $i++) $result = $this->getModeResult($args[$i], $result);
        return $result;
    }

    private function getModeResult($val, $prev) {
        switch ($this->mode) {
            case Math::ADD:
                return $prev+$val;
            case Math::SUBTRACT:
                return $prev-$val;
            case Math::MULTIPLY:
                return $prev*$val;
            case MATH::DIVIDE:
                return $prev/$val;
        }
    }
}
