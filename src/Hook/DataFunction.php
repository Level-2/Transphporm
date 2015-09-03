<?php
namespace CDS\Hook;
class DataFunction {
	private $dataStorage;
	private $data;

	public function __construct($objectStorage, $data) {
		$this->dataStorage = $objectStorage;
		$this->data = $data;
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
			$obj = $obj->$part;
		}

		return $obj;
	}
}