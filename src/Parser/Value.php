<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses "string" and function(args) e.g. data(foo) or iteration(bar) */ 
class Value {
	private $dataFunction;
	private $callParamsAsArray;
	private $parent;
	const IS_NOT_FUNCTION = 'isNotFunction';

	public function __construct($dataFunction, Value $parent = null, $callParamsAsArray = true) {
		$this->dataFunction = $dataFunction;
		$this->callParamsAsArray = $callParamsAsArray;
		$this->parent = $parent;
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
			$name = substr($function, 0, $open);
			$bracketMatcher = new BracketMatcher($function);
			$params = $bracketMatcher->match('(', ')');
			
			return ['name' => $name, 'params' => $params, 'endPoint' => $bracketMatcher->getClosePos()];
		}
		else return ['name' => null, 'params' => $function, 'endPoint' => strlen($function)];
	}

	public function parse($function, \DomElement $element = null) {
		$stringExtractor = new StringExtractor($function);
		$parts = explode('+', $stringExtractor);

		$result = [];
		foreach ($parts as $part) {
			$part = $stringExtractor->rebuild($part);
			$result = array_merge($result, $this->parseString(trim($part), $element));
		}

		return $result;	
	}

	private function parseString($function, $element) {
		$result = [];
		if ($function && in_array($function[0], ['\'', '"'])) {
			$finalPos = $this->findMatchingPos($function, $function[0]);
			$result[] = $this->extractQuotedString($function[0], $function);
		}
		else {
			$func = $this->parseFunction($function);
			$finalPos = $func['endPoint'];			
			if (($data = $this->getFunctionValue($func['name'], $func['params'], $element)) !== self::IS_NOT_FUNCTION) $result = $this->appendToArray($result, $data);
			else $result[] = trim($function);
		}
		$remaining = trim(substr($function, $finalPos+1));
		return $this->parseNextValue($remaining, $result, $element);
	}

	private function getFunctionValue($name, $params, $element) {
		if (($data = $this->callFunc($name, $params, $element)) !== self::IS_NOT_FUNCTION) {
			return $data;
		}
		else if ($this->parent != null && ($data = $this->parent->callFunc($name, $params, $element)) !== self::IS_NOT_FUNCTION) {
			return $data;
		}
		else return self::IS_NOT_FUNCTION;
	}

	private function appendToArray($array, $value) {
		if (is_array($value)) $array += $value;
		else $array[] = $value;
		return $array;
	}

	private function callFunc($name, $params, $element) {
		if ($name && $this->isCallable($this->dataFunction, $name)) {
			if ($this->callParamsAsArray) return $this->dataFunction->$name($this->parse($params, $element), $element);	
			else {
				return $this->callFuncOnObject($this->dataFunction, $name, $this->parse($params, $element));
			}
		}
		return self::IS_NOT_FUNCTION;
	}

	//is_callable does not detect closures on properties, only methods defined in the class!
	private function isCallable($obj, $func) {
		return (isset($obj->$func) && is_callable($obj->$func)) || is_callable([$obj, $func]);
	}

	private function callFuncOnObject($obj, $func, $params) {
		$args = [];
		foreach ($params as $param) {
			$stringExtractor = new StringExtractor($param);
			$parts = explode(',', $stringExtractor);
			foreach ($parts as $part) $args[] = $stringExtractor->rebuild($part);
		}
		return $this->callFuncOrClosure($obj, $func, $args);
	}

	private function callFuncOrClosure($obj, $func, $args) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else return call_user_func_array([$obj, $func], $args);
	}

	private function parseNextValue($remaining, $result, $element) {
		if (strlen($remaining) > 0 && $remaining[0] == ',') $result = array_merge($result, $this->parse(trim(substr($remaining, 1)), $element));
		return $result;
	}
	
	private function findMatchingPos($string, $char, $start = 0, $escape = '\\') {
		$pos = $start+1;

		while ($end = strpos($string, $char, $pos)) {
			if ($string[$end-1] === $escape) $pos = $end+1;
			else {
				break;
			}
		}
		return $end;
	}
}