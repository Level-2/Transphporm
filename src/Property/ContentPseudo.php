<?php
namespace Transphporm\Property;

interface ContentPseudo {
    public function run($value, $pseudoArgs, $element, \Transphporm\Hook\PseudoMatcher $pseudoMatcher);
}
