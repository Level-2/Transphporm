<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Repeat implements \Transphporm\Property {
	private $functionSet;
	private $elementData;
	private $line;
    private $filePath;

	public function __construct(\Transphporm\FunctionSet $functionSet, \Transphporm\Hook\ElementData $elementData, &$line, \Transphporm\FilePath $filePath) {
		$this->functionSet = $functionSet;
		$this->elementData = $elementData;
		$this->line = &$line;
        $this->filePath = $filePath;
	}

	public function run(array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {
		$values = $this->fixEmpty($values);
		if ($element->getAttribute('transphporm') === 'added') return $element->parentNode->removeChild($element);
		$max = $this->getMax($values);
		$count = 0;
		$repeat = $this->getRepeatValue($values, $max);
		//Don't run repeat on the cloned element or it will loop forever
		unset($rules['repeat']);
		$hook = $this->createHook($rules, $pseudoMatcher, $properties);

		foreach ($repeat as $key => $iteration) {
			if ($count+1 > $max) break;
			$clone = $this->cloneElement($element, $iteration, $key, $count++);
			//Re-run the hook on the new element, but use the iterated data
			$hook->run($clone);
		}
		//Remove the original element
		$element->parentNode->removeChild($element);
		return false;
	}

	private function getRepeatValue($values, &$max) {
		$mode = $this->getMode($values);
		if ($mode === 'each') $repeat = $values[0];
		else if ($mode === 'loop') {
			$repeat = range($values[0], $max);
			$max++;
		}
		return $repeat;
	}

	private function getMode($args) {
		return isset($args[2]) ? $args[2] : 'each';
	}

	private function fixEmpty($value) {
		if (empty($value[0])) $value[0] = [];
		return $value;
	}

	private function cloneElement($element, $iteration, $key, $count) {
		$clone = $element->cloneNode(true);
		$this->tagElement($clone, $count);

		$this->elementData->bind($clone, $iteration, 'iteration');
		$this->elementData->bind($clone, $key, 'key');
		$element->parentNode->insertBefore($clone, $element);
		return $clone;
	}

	private function tagElement($element, $count) {
		//Mark all but one of the nodes as having been added by transphporm, when the hook is run again, these are removed
		if ($count > 0) $element->setAttribute('transphporm', 'added');
	}

	private function getMax($values) {
		return isset($values[1]) ? $values[1] : PHP_INT_MAX;
	}

	private function createHook($newRules, $pseudoMatcher, $properties) {
		$hook = new \Transphporm\Hook\PropertyHook($newRules, $this->line, null, $this->line, $pseudoMatcher, new \Transphporm\Parser\Value($this->functionSet), $this->functionSet, $this->filePath);
		foreach ($properties as $name => $property) $hook->registerProperty($name, $property);
		return $hook;
	}
}
