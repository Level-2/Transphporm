<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Holds the data used by `ValueParser` */
class ValueData {
	private $data;

	public function __construct($data) {
		$this->data = $data;
	}

	//Read $key from array, $this->data = $this->data[$key] but also works for objects
	private function traverseInto($key) {
		if (isset($this->data->{$key})) $this->data = $this->data->{$key};
		else if ($this->isArray() && isset($this->data[$key])) $this->data = $this->data[$key];
	}

	public function traverse($key, $result) {
		if ($key !== null) $this->traverseInto($key);
		else {
			//But if the key is null, replace the data structure with the result of the last function call
			$lastResult = $result->pop();
			if ($lastResult) {
				$this->data = $lastResult;
				return $lastResult;
			}
		}
	}

	private function isArray() {
		return is_array($this->data) || $this->data instanceof \ArrayAccess;
	}

	public function read($value) {
		if ($this->isArray()) {
			if (isset($this->data[$value])) return $this->data[$value];
		}
		else if (isset($this->data->$value)) return $this->data->$value;
		else return null;
	}

	public function call($func, $args) {
		return $this->data->$func(...$args);
	}

	public function methodExists($name) {
		return method_exists($this->data, $name);
	}

	public function parseNested($parser, $token, $funcName) {
		$args = $parser->parseTokens($token['value'], $this->data);
		if ($args[0] == $this->data) $args = [];
		return $this->callFuncOnObject($this->data, $funcName, $args);
	}

	private function callFuncOnObject($obj, $func, $args) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else if (is_callable([$obj, $func])) return call_user_func_array([$obj, $func], $args);
		else return false;
	}

	public function extract($last, $autoLookup, $traversing) {
		$value = $this->read($last);
		if ($value && ($autoLookup || $traversing) ) {
			return $value;
		}
		throw new \UnexpectedValueException('Not found');
	}
}
