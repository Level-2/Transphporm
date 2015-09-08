<?php
namespace Transphporm\Hook;
/* Handles data() and iteration() functions in the CDS */
class DataFunction {
	private $dataStorage;
	private $data;

	public function __construct(\SplObjectStorage $objectStorage, $data) {
		$this->dataStorage = $objectStorage;
		$this->data = $data;
	}

	/** Binds data to an element */
	public function bind(\DomElement $element, $data) {
		$this->dataStorage[$element] = $data;
	}


	public function iteration($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	private function getData(\DomElement $element) {
		while ($element) {
			if (isset($this->dataStorage[$element])) return $this->dataStorage[$element];
			$element = $element->parentNode;
		}
		return $this->data;
	}

	public function data($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;			
	}

	private function traverse($name, $data) {
		$parts = explode('.', $name);
		$obj = $data;
		foreach ($parts as $part) {
			if ($part == '') continue;
			$obj = is_array($obj) ? $obj[$part] : $obj->$part;
		}
		return $obj;
	}

	public function attr($val, $element) {
		return $element->getAttribute(trim($val));
	}
}
