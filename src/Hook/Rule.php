<?php
namespace Transphporm\Hook;
/** Hooks into the template system, gets assigned as `ul li` or similar and `run()` is called with any elements that match */
class Rule implements \Transphporm\Hook {
	private $rules;
	private $dataFunction;
	private $pseudoMatcher;
	private $properties = [];

	public function __construct(array $rules, PseudoMatcher $pseudoMatcher, DataFunction $dataFunction) {
		$this->rules = $rules;
		$this->dataFunction = $dataFunction;
		$this->pseudoMatcher = $pseudoMatcher;
	}

	public function run(\DomElement $element) {	
		//Don't run if there's a pseudo element like nth-child() and this element doesn't match it
		if (!$this->pseudoMatcher->matches($element)) return;

		foreach ($this->rules as $name => $value) {
			$result = $this->callProperty($name, $element, $this->parseValue(trim($value), $element));
			if ($result === false) break;
		}
	}

	public function getPseudoMatcher() {
		return $this->pseudoMatcher;
	}

	public function getRules() {
		return $this->rules;
	}

	public function registerProperties($object) {
		$this->properties[] = $object;
	}

	public function getProperties() {
		return $this->properties;
	}

	private function callProperty($name, $element, $value) {
		foreach ($this->properties as $obj) {
			if (is_callable([$obj, $name])) {
				return call_user_func([$obj, $name], $value, $element, $this);
			}
		}
		return false;
	}

	private function findMatchingPos($string, $char, $start = 0, $escape = '\\') {
		$pos = $start+1;

		while (true) {
			$end = strpos($string, $char, $pos);
			if ($string[$end-1] === $escape) $pos = $end+1;
			else return $end;
		}
	}

	private function extractQuotedString($marker, $str) {
		$finalPos = $this->findMatchingPos($str, $marker);
		$string = substr($str, 1, $finalPos-1);
		//Now remove escape characters
		return str_replace('\\' . $marker, $marker, $string);
	}

	private function parseFunction($function) {
		$open = strpos($function, '(');
		if ($open) {
			$close = strpos($function, ')', $open);

			//Count the number of fresh opening ( before $close
			$cPos = $open+1;
			while (($cPos = strpos($function, '(', $cPos+1)) !== false && $cPos < $close) $close = strpos($function, ')', $close+1);

			$name = substr($function, 0, $open);

			$params = substr($function, $open+1, $close-$open-1);
			return ['name' => $name, 'params' => $params, 'endPoint' => $close];
		}
		else return ['name' => null, 'params' => $function, 'endPoint' => strlen($function)];
		
	}

	private function parseValue($function, $element) {
		$result = [];
		if ($function && in_array($function[0], ['\'', '"'])) {
			$finalPos = $this->findMatchingPos($function, $function[0]);
			$result[] = $this->extractQuotedString($function[0], $function);
		}
		else {
			$func = $this->parseFunction($function);
			$finalPos = $func['endPoint'];			

			if (($data = $this->callFunc($func['name'], $func['params'], $element)) !== false) {
				$result = $this->appendToArray($result, $data);
			} 
			else $result[] = trim($function);
		}
		$remaining = trim(substr($function, $finalPos+1));
		return $this->parseNextValue($remaining, $result, $element);
	}

	private function appendToArray($array, $value) {
		if (is_array($value)) $array += $value;
		else $array[] = $value;
		return $array;
	}

	private function callFunc($name, $params, $element) {
		if ($name && is_callable([$this->dataFunction, $name])) {
			return $this->dataFunction->$name($this->parseValue($params, $element), $element);	
		}
		return false;
	}

	private function parseNextValue($remaining, $result, $element) {
		if (strlen($remaining) > 0 && $remaining[0] == ',') $result = array_merge($result, $this->parseValue(trim(substr($remaining, 1)), $element));
		return $result;
	}

}
