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

	public function __construct(\Transphporm\Hook\DataFunction $dataFunction) {
		$this->dataFunction = $dataFunction;
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

	public function parse($function, \DomElement $element) {
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
			return $this->dataFunction->$name($this->parse($params, $element), $element);	
		}
		return false;
	}

	private function parseNextValue($remaining, $result, $element) {
		if (strlen($remaining) > 0 && $remaining[0] == ',') $result = array_merge($result, $this->parse(trim(substr($remaining, 1)), $element));
		return $result;
	}
	
	private function findMatchingPos($string, $char, $start = 0, $escape = '\\') {
		$pos = $start+1;
		$end = 0;
		while ($end = strpos($string, $char, $pos)) {
			if ($string[$end-1] === $escape) $pos = $end+1;
			else {
				break;
			}
		}
		return $end;
	}
}