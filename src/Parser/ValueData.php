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

	public function traverse($key) {
		if (isset($this->data->{$key})) $this->data = $this->data->{$key};
		else if (is_array($this->data) && isset($this->data[$key])) $this->data = $this->data[$key];
	}

	public function read($value) {
		if (is_array($this->data)) {
			if (isset($this->data[$value])) return $this->data[$value];
		}
		else if (isset($this->data->$value)) return $this->data->$value;
		else return false;
	}

	public function call($func, $args) {
		return $this->data->$func(...$args);
	}

	public function parseNested($parser, $token, $funcName) {
		$args = $parser->parseTokens($token['value'], $this->data);
		if ($args[0] == $this->data) $args = [];
		return $this->callFunc($funcName, $args, $this->data);
	}

	private function callFunc($name, $args, $data) {
		return $this->callFuncOnObject($this->data, $name, $args);
	}

	private function callFuncOnObject($obj, $func, $args) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else return call_user_func_array([$obj, $func], $args);
	}

	public function extract($last, $autoLookup) {
		$value = $this->read($last);
		if ($value && ($autoLookup || is_array($this->data)) ) {
			return $value;
		}
		throw new \UnexpectedValueException('Not found');
	}
}