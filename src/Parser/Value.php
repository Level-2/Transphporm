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
			Tokenizer::NUMERIC => 'processString',
			Tokenizer::BOOL => 'processString',
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

	public function parseTokens($tokens, $data = null) {
		$this->result = new ValueResult;
		$this->data = new ValueData($data ? $data : $this->baseData);
		$this->last = null;

		if (count($tokens) <= 0) return [$data];

		foreach (new TokenFilterIterator($tokens, [Tokenizer::WHITESPACE]) as $token) {
			$this->{$this->tokenFuncs[$token['type']]}($token);
		}

		$this->processLast();
		return $this->result->getResult();
	}

	private function processComparator($token) {
		$this->processLast();

		if (!(in_array($this->result->getMode(), array_keys($this->tokenFuncs, 'processComparator')) && $token['type'] == Tokenizer::EQUALS)) {
			$this->result->setMode($token['type']);
			$this->last = null;
		}
	}

	//Reads the last selected value from $data regardless if it's an array or object and overrides $this->data with the new value
	//Dot moves $data to the next object in $data foo.bar moves the $data pointer from `foo` to `bar`
	private function processDot($token) {
		if ($this->last !== null) $this->data->traverse($this->last);
		else {
			//When . is not preceeded by anything, treat it as part of the string instead of an operator
			// foo.bar is treated as looking up `bar` in `foo` whereas .foo is treated as the string ".foo"
			$lastResult = $this->result->pop();
			if ($lastResult) $this->data = new ValueData($lastResult);
			else {
				$this->processString(['value' => '.']);
				$this->result->setMode(Tokenizer::CONCAT);
			}
		}

		$this->last = null;
	}

	private function processSquareBracket($token) {
		$parser = new Value($this->baseData, $this->autoLookup);
		if ($this->baseData instanceof \Transphporm\Functionset && $this->baseData->hasFunction($this->last)) {
			$this->callTransphpormFunctions($token);
		}
		else {
			if ($this->last !== null) $this->data->traverse($this->last);
			$this->last = $parser->parseTokens($token['value'], null)[0];
		}
	}

	private function processSeparator($token) {
		$this->result->setMode($token['type']);
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
		else {
			$this->processNested($token);
		}
	}

	private function processNested($token) {
		$parser = new Value($this->baseData, $this->autoLookup);
		$funcResult = $this->data->parseNested($parser, $token, $this->last);
		$this->result->processValue($funcResult);
		$this->last = null;
	}

	private function callTransphpormFunctions($token) {
		$this->result->processValue($this->baseData->{$this->last}($token['value']));
		foreach ($this->result->getResult() as $i => $value) {
			if (is_scalar($value)) {
				$val = $this->data->read($value);
				if ($val) $this->result[$i] = $val;
			}
		}
		$this->last = null;
	}

	//Applies the current operation to whatever is in $last based on $mode
	private function processLast() {
		if ($this->last !== null) {
			try {
				$value = $this->data->extract($this->last, $this->autoLookup);
				$this->result->processValue($value);
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
	}
}
