<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Repeat implements \Transphporm\Property {
	private $data;

	public function __construct($data) {
		$this->data = $data;
	}

	public function run($value, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {
		if ($element->getAttribute('transphporm') === 'added') return $element->parentNode->removeChild($element);

		$count = 0;
		foreach ($value as $key => $iteration) {
			$clone = $element->cloneNode(true);
			//Mark all but one of the nodes as having been added by transphporm, when the hook is run again, these are removed
			if ($count++ > 0) $clone->setAttribute('transphporm', 'added');
			$this->data->bind($clone, $iteration, 'iteration');
			$this->data->bind($clone, $key, 'key');
			$element->parentNode->insertBefore($clone, $element);

			//Re-run the hook on the new element, but use the iterated data
			//Don't run repeat on the clones element or it will loop forever
			unset($rules['repeat']);
			$this->createHook($rules, $pseudoMatcher, $properties)->run($clone);
		}
		//Remove the original element
		$element->parentNode->removeChild($element);
		return false;
	}

	private function createHook($newRules, $pseudoMatcher, $properties) {
		$hook = new \Transphporm\Hook\PropertyHook($newRules, $pseudoMatcher, new \Transphporm\Parser\Value($this->data));
		foreach ($properties as $name => $property) $hook->registerProperty($name, $property);
		return $hook;
	}
}