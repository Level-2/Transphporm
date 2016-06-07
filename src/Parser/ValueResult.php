<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class ValueResult implements \ArrayAccess {
	private $result = [];

	/*
		The next operation to perform. Will be one of the following:
			ARG - A new value e.g,  "a","b"  becomes ["a", "b"]
			CONCAT - Concat onto the current arg e.g "a" + "b" becomes ["ab"]
			NOT - Boolean operation "a" != "b" becomes [true]
			EQUALS - Boolean operation "a" = "b" becomes [false]
	*/
	private $mode = Tokenizer::ARG;
	
	//Processes $newValue using $mode. Either concats to the current argument, adds a new argument
	//Or usess the two arguments for a boolean comparison
	public function processValue($newValue) {
		$funcs = [
			Tokenizer::ARG => 'arg',
			Tokenizer::CONCAT => 'concat',
			Tokenizer::EQUALS => 'equals',
			Tokenizer::NOT => 'not' 
		];

		$this->{$funcs[$this->mode]}($newValue);
	}

	public function arg($value) {
		$this->result[] = $value;
	}

	public function concat($value) {
		$this->result[count($this->result)-1] .= $value;
	}

	public function not($value) {
		$this->result[count($this->result)-1] = $this->result[count($this->result)-1] != $value;
	}
	
	public function equals($value) {
		$this->result[count($this->result)-1] = $this->result[count($this->result)-1] == $value;
	}

	
	public function setMode($mode) {
		$this->mode = $mode;
	}

	public function getMode() {
		return $this->mode;
	}

	public function getResult() {
		return $this->result;
	}

	public function pop() {
		return array_pop($this->result);
	}

	public function offsetSet($key, $value) {
		$this->result[$key] = $value;
	}

	public function offsetGet($key) {
		return $this->result[$key];
	}

	public function offsetUnset($key) {
		unset($this->result[$key]);
	}

	public function offsetExists($key) {
		return isset($this->result[$key]);
	}

	public function clear() {
		$this->result = [];
	}
}