<?php
namespace Transphporm\Property\ContentPseudo;
class Attr implements \Transphporm\Property\ContentPseudo {
    public function run($value, $pseudoArgs, $element) {
        $element->setAttribute($pseudoArgs, implode('', $value));
    }
}
