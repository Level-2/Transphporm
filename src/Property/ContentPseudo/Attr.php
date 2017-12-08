<?php
namespace Transphporm\Property\ContentPseudo;
class Attr implements \Transphporm\Property\ContentPseudo {
	public function run($value, $pseudoArgs, $element, \Transphporm\Hook\PseudoMatcher $pseudoMatcher) {
		$implodedValue = implode('', $value);

		if ($pseudoMatcher->hasFunction('before')) {
			$attrValue = $implodedValue . $element->getAttribute($pseudoArgs);
		}
		else if ($pseudoMatcher->hasFunction('after')) {
			$attrValue = $element->getAttribute($pseudoArgs) . $implodedValue;
		}
		else {
			$attrValue = implode('', $value);
		}

		$element->setAttribute($pseudoArgs, $attrValue);
	}
}
