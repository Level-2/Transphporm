<?php
namespace CDS\Hook;
class Rule implements \CDS\Hook {
	private $rule;
	private $data;
	private $dataStorage;

	public function __construct($rule, $data, $objectStorage) {
		$this->rule = $rule;
		$this->data = $data;
		$this->dataStorage = $objectStorage;
	}

	public function run(\DomElement $element) {
		foreach ($this->rule->rules as $name => $value) {
			if ($this->$name($value, $element) === false) break;
		}
		return $element;
	}

	public function content($val, $element) {
		$value = $this->parseFunction($val, $element);
		if ($element instanceof \DomElement) {
			$element->firstChild->nodeValue = $value;
		}
	}

	public function repeat($val, $element) {		
		$data = $this->parseFunction($val, $element);
		//$this->dataStorage[$element] = $data;

		foreach ($data as $iteration) {
			$clone = $element->cloneNode(true);
			$this->dataStorage[$clone] = $iteration;
			$element->parentNode->insertBefore($clone, $element);

			//Re-run the hook on the new element, but use the iterated data
			$newRule = clone $this->rule;

			//Don't run repeat on the clones element or it will loop forever
			unset($newRule->rules['repeat']);

			$hook = new Rule($newRule, $iteration, $this->dataStorage);
			$hook->run($clone);

		}

		//Remove the original element so only the ones that have been looped over will show
		$element->parentNode->removeChild($element);

		return false;
	}


	public function iteration($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;
	}

	public function getData($element) {
		while ($element) {
			if (isset($this->dataStorage[$element])) return $this->dataStorage[$element];
			$element = $element->parentNode;
		}
		return $this->data;
	}

	private function parseFunction($function, $element) {
		$open = strpos($function, '(');
		$close = strpos($function, ')', $open);
		
		$name = substr($function, 0, $open);
		$params = substr($function, $open+1, $close-$open-1);

		return $this->$name($params, $element);
	}

	private function data($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;			
	}

	private function traverse($name, $data) {
		$parts = explode('.', $name);
		$obj = $data;
		foreach ($parts as $part) {
			if ($part == '') continue;
			$obj = $obj->$part;
		}

		return $obj;
	}
}


