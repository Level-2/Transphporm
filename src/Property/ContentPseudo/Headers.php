<?php
namespace Transphporm\Property\ContentPseudo;
class Headers implements \Transphporm\Property\ContentPseudo {
    private $headers;

    public function __construct(&$headers) {
		$this->headers = &$headers;
	}

    public function run($value, $pseudoArgs, $element) {
        $this->headers[] = [$pseudoArgs, implode('', $value)];
    }
}
