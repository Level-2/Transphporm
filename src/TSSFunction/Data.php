<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\TSSFunction;
/* Handles data() and iteration() function calls from the stylesheet */
class Data implements \Transphporm\TSSFunction{
	private $data;
	private $dataKey;
	private $functionSet;

	public function __construct(\Transphporm\Hook\ElementData $data,  \Transphporm\FunctionSet $functionSet, $dataKey = 'data') {
		$this->data = $data;
		$this->dataKey = $dataKey;
		$this->functionSet = $functionSet;
	}

	private function traverse($name, $data, $element) {
		$name = str_replace(['[', ']'], ['.', ''], $name);
		$parts = explode('.', $name);
		$obj = $data;

		$valueParser = new \Transphporm\Parser\Value($this->functionSet);

		foreach ($parts as $part) {
			if ($part === '') continue;
			$part = $valueParser->parse($part, $element)[0];

			$funcResult = $this->traverseObj($part, $obj, $valueParser, $element);

			if ($funcResult !== false) $obj = $funcResult;
			
			else $obj = $this->ifNull($obj, $part);
		}
		return $obj;
	}

	private function traverseObj($part, $obj, $valueParser, $element) {
		if (strpos($part, '(') !== false) {
			$subObjParser = new \Transphporm\Parser\Value($obj, $valueParser, false);
			$value = $subObjParser->parse($part, $element);
			return isset($value[0]) ? $value[0] : null;
		}
		else if (method_exists($obj, $part)) return call_user_func([$obj, $part]); 
		else return false;
	}

	private function ifNull($obj, $key) {
		if (is_array($obj)) return isset($obj[$key]) ? $obj[$key] : null;
		else return isset($obj->$key) ? $obj->$key : null;
	}


	public function run(array $args, \DomElement $element = null) {
		$data = $this->data->getData($element, $this->dataKey);
		$value = $this->traverse($args[0], $data, $element);
		return $value;
	}
}