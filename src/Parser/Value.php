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
	private $traversing = false;
	private $allowNullResult;

	private $tokenFuncs = [
			Tokenizer::NOT => 'processComparator',
			Tokenizer::EQUALS => 'processComparator',
			Tokenizer::DOT => 'processDot',
			Tokenizer::OPEN_SQUARE_BRACKET => 'processSquareBracket',
			Tokenizer::ARG => 'processSeparator',
			Tokenizer::CONCAT => 'processSeparator',
			Tokenizer::SUBTRACT => 'processSeparator',
			Tokenizer::MULTIPLY => 'processSeparator',
			Tokenizer::DIVIDE => 'processSeparator',
			Tokenizer::NAME => 'processScalar',
			Tokenizer::NUMERIC => 'processString',
			Tokenizer::BOOL => 'processString',
			Tokenizer::STRING => 'processString',
			Tokenizer::OPEN_BRACKET => 'processBrackets'
	];

	public function __construct($data, $autoLookup = false, $allowNullResult = false) {
		$this->baseData = $data;
		$this->autoLookup = $autoLookup;
		$this->allowNullResult = $allowNullResult;
	}

	public function parse($str) {
		$tokenizer = new Tokenizer($str);
		$tokens = $tokenizer->getTokens();
		$this->result = $this->parseTokens($tokens, $this->baseData);
		return $this->result;
	}

	public function parseTokens($tokens, $data = null) {
		$this->result = new ValueResult();
		$this->data = new ValueData($data ? $data : $this->baseData);
		$this->last = new Last($this->data, $this->result, $this->autoLookup);
		$this->traversing = false;

		if (count($tokens) <= 0) return [$data];

		foreach (new TokenFilterIterator($tokens, [Tokenizer::WHITESPACE, Tokenizer::NEW_LINE]) as $token) {
			$this->{$this->tokenFuncs[$token['type']]}($token);
		}

		$this->last->process();
		return $this->result->getResult();
	}

	private function processComparator($token) {
		$this->last->process();

		if (!(in_array($this->result->getMode(), array_keys($this->tokenFuncs, 'processComparator')) && $token['type'] == Tokenizer::EQUALS)) {
			$this->result->setMode($token['type']);
			$this->last->clear();
		}
	}

	//Reads the last selected value from $data regardless if it's an array or object and overrides $this->data with the new value
	//Dot moves $data to the next object in $data foo.bar moves the $data pointer from `foo` to `bar`
	private function processDot($token) {
		$lastResult = $this->last->traverse();

		//When . is not preceeded by anything, treat it as part of the string instead of an operator
		// foo.bar is treated as looking up `bar` in `foo` whereas .foo is treated as the string ".foo"
		if ($lastResult) {
			$this->last->makeTraversing();
		}
		else if ($this->last->isEmpty())  {
			$this->processString(['value' => '.']);
			$this->result->setMode(Tokenizer::CONCAT);
		}

		$this->last->clear();
	}

	private function hasFunction($name) {
		return $this->baseData instanceof \Transphporm\Functionset && $this->baseData->hasFunction($name);
	}

	private function processSquareBracket($token) {
		if ($this->hasFunction($this->last->read())) {
			$this->callTransphpormFunctions($token);
		}
		else {
			$this->last->traverse();
			$this->last->set($this->getNewParser()->parseTokens($token['value'], null)[0]);
			if (!is_bool($this->last->read())) $this->last->makeTraversing();
		}
	}

	private function processSeparator($token) {
		$this->result->setMode($token['type']);
	}

	private function processScalar($token) {
		if (is_scalar($this->last->read())) {
			$this->result->processValue($this->last->read());
		}
		$this->last->set($token['value']);
	}

	private function processString($token) {
		$this->result->processValue($token['value']);
	}

	private function processBrackets($token) {
		if ($this->hasFunction($this->last->read())
			&& !$this->data->methodExists($this->last->read())) {
			$this->callTransphpormFunctions($token);
		}
		else {
			$this->last->processNested($this->getNewParser(), $token);
		}
	}

	private function getNewParser() {
		return new Value($this->baseData, $this->autoLookup);
	}

	private function callTransphpormFunctions($token, $parse = true) {
		$val = $this->baseData->{$this->last->read()}($token['value']);
		$this->result->processValue($val);

		if ($this->autoLookup) {
			$parser = new Value($this->data->getData());
			$parsedArr = $parser->parse($val);
			$parsedVal = isset($parsedArr[0]) ? $parsedArr[0] : null;
		}
		else $parsedVal = null;
        
        $this->result->postProcess($this->data, $val, $parsedVal, $this->allowNullResult);

		$this->last->clear();
	}
}
