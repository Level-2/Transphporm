<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Display implements \Transphporm\Property {
	public function run(array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {
		if ($pseudoMatcher->hasFunction('attr')) $element->removeAttribute($pseudoMatcher->getFuncArgs('attr'));
		else if (strtolower($values[0]) === 'none') $element->setAttribute('transphporm', 'remove');
		else $element->setAttribute('transphporm', 'show');
	}
}