<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses "string" and function(args) e.g. data(foo) or iteration(bar) */
class Last {
	private $baseData;
	private $autoLookup;
	/*
		Stores the last value e.g.
			"a" + "b"
		Will store "a" before reading the token for the + and perfoming the concatenate operation
	*/
	private $last;
	private $data;
	private $result;
	private $traversing = false;


	public function __construct($data, $result, $autoLookup) {
		$this->data = $data;
		$this->result = $result;
		$this->autoLookup = $autoLookup;
	}


	public function traverse() {
		if ($this->last !== null) $this->data->traverse($this->last);
		else {
			$lastResult = $this->result->pop();
			if ($lastResult) {
				$this->data = new ValueData($lastResult);
				return $lastResult;
			}
		}
	}

	public function clear() {
		$this->last = null;
	}

	public function isEmpty() {
		return $this->last == null;
	}

	public function processNested($parser, $token) {		
		$funcResult = $this->data->parseNested($parser, $token, $this->last);
		$this->result->processValue($funcResult);
		$this->last = null;
	}

	public function read() {
		return $this->last;
	}

	public function set($value) {
		$this->last = $value;
	}


	//Applies the current operation to whatever is in $last based on $mode
	public function process() {
		if ($this->last !== null) {
			try {
				$value = $this->data->extract($this->last, $this->autoLookup, $this->traversing);
				$this->result->processValue($value);
			}
			catch (\UnexpectedValueException $e) {
				$this->processLastUnexpected();
			}
		}
	}

	private function processLastUnexpected() {
		if (!($this->autoLookup || $this->traversing)) {
			$this->result->processValue($this->last);
		}
		else {
			$this->result->clear();
			$this->result[0] = false;
		}
	}
}