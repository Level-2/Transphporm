<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses "string" and function(args) e.g. data(foo) or iteration(bar) */
class Value {
	private $baseData;
	private $autoLookup;
	private $tokens;

	/*
		Stores the last value e.g. 
			"a" + "b"
		Will store "a" before reading the token for the + and perfoming the concatenate operation
	*/
	private $last;
	private $data;
	private $result;

	private $tokenFuncs = [
			Tokenizer::NOT => 'processComparator',
			Tokenizer::EQUALS => 'processComparator',
			Tokenizer::DOT => 'processDot',
			Tokenizer::OPEN_SQUARE_BRACKET => 'processSquareBracket',
			Tokenizer::ARG => 'processSeparator',
			Tokenizer::CONCAT => 'processSeparator',
			Tokenizer::NAME => 'processScalar',
			Tokenizer::NUMERIC => 'processScalar',
			Tokenizer::BOOL => 'processScalar',
			Tokenizer::STRING => 'processString',
			Tokenizer::OPEN_BRACKET => 'processBrackets'
	];

	public function __construct($data, $autoLookup = false) {
		$this->baseData = $data;
		$this->autoLookup = $autoLookup;
	}

	public function parse($str) {
		$tokenizer = new Tokenizer($str);
		$tokens = $tokenizer->getTokens();
		$this->result = $this->parseTokens($tokens, $this->baseData);
		return $this->result;
	}

	public function parseTokens($tokens, $data) {
		$this->result = new ValueResult;
		$this->data = $data;
		$this->last = null;

		if (empty($tokens)) return [$this->data];
		
		foreach ($tokens as $token) {
			$this->{$this->tokenFuncs[$token['type']]}($token);	
		}

		$this->processLast();
		return $this->result->getResult();
	}

	private function processComparator($token) {
		$this->result = $this->processLast();

		if ($this->result->getMode() == Tokenizer::NOT && $token['type'] == Tokenizer::EQUALS) {
			$this->result->setMode(Tokenizer::NOT);
		}
		else $this->result->setMode($token['type']);
	}


	//Reads the last selected value from $data regardless if it's an array or object and overrides $this->data with the new value
	private function moveLastToData() {
		if (isset($this->data->{$this->last})) $this->data = $this->data->{$this->last};
		else if (is_array($this->data) && isset($this->data[$this->last])) $this->data = $this->data[$this->last];
	}

	//Dot moves $data to the next object in $data foo.bar moves the $data pointer from `foo` to `bar`
	private function processDot($token) {
		if ($this->last !== null) $this->moveLastToData();
		else $this->data = $this->result->pop();

		$this->last = null;
	}

	private function processSquareBracket($token) {
		if ($this->last !== null) $this->moveLastToData();
		$parser = new Value($this->baseData, $this->autoLookup);
		$this->last = $parser->parseTokens($token['value'], null)[0];
	}

	private function processSeparator($token) {
		$this->result->setMode($token['type']);
		//if ($this->last !== null) $this->result = $this->processValue($this->result, $this->mode, $this->last);
		$this->result = $this->processLast();
	}

	private function processScalar($token) {
		$this->last = $token['value'];
	}

	private function processString($token) {
		$this->result->processValue($token['value']);
	}

	private function processBrackets($token) {
		if ($this->baseData instanceof \Transphporm\Functionset && $this->baseData->hasFunction($this->last)) {
			$this->callTransphpormFunctions($token);
		}
		else if ($this->data instanceof \Transphporm\Functionset) {
			$this->result = $this->result->processValue($this->data->{$this->last}($token['value']));
			$this->last = null;
		}
		else {
			$parser = new Value($this->baseData, $this->autoLookup);
			$args = $parser->parseTokens($token['value'], $this->data);
			if ($args[0] == $this->data) $args = [];
			$funcResult = $this->callFunc($this->last, $args, $this->data);
			$this->result->processValue($funcResult);
			$this->last = null;
		}
	}

	private function callTransphpormFunctions($token) {
		$this->result->processValue($this->baseData->{$this->last}($token['value']));
		foreach ($this->result->getResult() as $i => $value) {
			if (is_array($this->data)) {
				if (isset($this->data[$value])) $this->result[$i] = $this->data[$value];
			}
			else if (is_scalar($value) && isset($this->data->$value)) $this->result[$i] = $this->data->$value;
		}
		$this->last = null;
	}

	//Applies the current operation to whatever is in $last based on $mode
	private function processLast() {
		if ($this->last !== null) {
			try {
				$this->extractLast($this->result);
			}
			catch (\UnexpectedValueException $e) {
				if (!$this->autoLookup) {
					$this->result->processValue($this->last);
				}
				else {
					$this->result->clear();
					$this->result[0] = false;
				}
			}			
		}
		return $this->result;
	}

	//Extracts $last from $data. If "last" is "bar" from value "foo.bar",
	//$data contains "foo" and this function reads $data[$bar] or $data->$bar
	private function extractLast($result) {
		if ($this->autoLookup && isset($this->data->{$this->last})) {
			return $this->result->processValue($this->data->{$this->last});
		}
		else if (is_array($this->data) && isset($this->data[$this->last])) {
			return $this->result->processValue($this->data[$this->last]);
		}
		throw new \UnexpectedValueException('Not found');
	}	

	private function callFunc($name, $args, $data) {
		return $this->callFuncOnObject($this->data, $name, $args);
	}

	private function callFuncOnObject($obj, $func, $args) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else return call_user_func_array([$obj, $func], $args);
	}
}