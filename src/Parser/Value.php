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
	private $mode;
	private $last;
	private $data;
	private $result;
	private $element;

	public function __construct($data, $autoLookup = false) {
		$this->baseData = $data;
		$this->autoLookup = $autoLookup;
	}

	public function parse($str, $element = null, $returnTokens = false) {
		$tokenizer = new Tokenizer($str);
		$tokens = $tokenizer->getTokens();
		if ($returnTokens) return $tokens;
		$this->result = $this->parseTokens($tokens, $element, $this->baseData);
		return $this->result;
	}

	public function parseTokens($tokens, $element, $data) {
		$this->result = [];
		$this->mode = Tokenizer::ARG;
		$this->data = $data;
		$this->last = null;
		$this->element = $element;

		if (empty($tokens)) return [$this->data];

		$tokenFuncs = [
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

		foreach ($tokens as $token) {
			$this->{$tokenFuncs[$token['type']]}($token);	
		}

		return $this->processLast();
	}

	private function processComparator($token) {
		$this->result = $this->processLast();

		if ($this->mode == Tokenizer::NOT && $token['type'] == Tokenizer::EQUALS) {
			$this->mode = Tokenizer::NOT;
		}
		else $this->mode = $token['type'];
	}


	//Reads the last selected value from $data regardless if it's an array or object and overrides $this->data with the new value
	private function moveLastToData() {
		if (isset($this->data->{$this->last})) $this->data = $this->data->{$this->last};
		else if (is_array($this->data) && isset($this->data[$this->last])) $this->data = $this->data[$this->last];
	}

	//Dot moves $data to the next object in $data foo.bar moves the $data pointer from `foo` to `bar`
	private function processDot($token) {
		if ($this->last !== null) $this->moveLastToData();
		else $this->data = array_pop($this->result);

		$this->last = null;
	}

	private function processSquareBracket($token) {
		if ($this->last !== null) $this->moveLastToData();

		$parser = new Value($this->baseData, $this->autoLookup);
		$this->last = $parser->parseTokens($token['value'], $this->element, null)[0];
	}

	private function processSeparator($token) {
		$this->mode = $token['type'];
		//if ($this->last !== null) $this->result = $this->processValue($this->result, $this->mode, $this->last);
		$this->result = $this->processLast();
	}

	private function processScalar($token) {
		$this->last = $token['value'];
	}

	private function processString($token) {
		$this->result = $this->processValue($token['value']);
	}

	private function processBrackets($token) {
		if ($this->baseData instanceof \Transphporm\Functionset && $this->baseData->hasFunction($this->last)) {
			$this->callTransphpormFunctions($token);
		}
		else if ($this->data instanceof \Transphporm\Functionset) {
			$this->result = $this->processValue($this->data->{$this->last}($token['value'], $this->element));
			$this->last = null;
		}
		else {
			$parser = new Value($this->baseData, $this->autoLookup);
			$args = $parser->parseTokens($token['value'], $this->element, $this->data);
			if ($args[0] == $this->data) $args = [];
			$funcResult = $this->callFunc($this->last, $args, $this->element, $this->data);
			$this->result = $this->processValue($funcResult);
			$this->last = null;
		}
	}

	private function callTransphpormFunctions($token) {
		$this->result = $this->processValue($this->baseData->{$this->last}($token['value'], $this->element));
		foreach ($this->result as $i => $value) {
			if (is_array($this->data)) {
				if (isset($this->data[$value])) $this->result[$i] = $this->data[$value];
			}
			else if (is_scalar($value) && isset($this->data->$value)) $this->result[$i] = $this->data->$value;
		}
		$this->last = null;
	}

	//Processes the last entry down an object graph using foo.bar.baz and adds it to the result
	private function processLast() {
		if ($this->last !== null) {
			try {
				$this->result = $this->extractLast($this->result);
			}
			catch (\UnexpectedValueException $e) {
				if (!$this->autoLookup) {
					$this->result = $this->processValue($this->last);
				}
				else $this->result = [false];			
			}			
		}
		return $this->result;
	}

	private function extractLast($result) {
		if ($this->autoLookup && isset($this->data->{$this->last})) {
			return $this->processValue($this->data->{$this->last});
		}
		else if (is_array($this->data) && isset($this->data[$this->last])) {
			return $this->processValue($this->data[$this->last]);
		}
		throw new \UnexpectedValueException('Not found');
	}	

	private function processValue($newValue) {
		if ($this->mode == Tokenizer::ARG) {
			$this->result[] = $newValue;
		}
		else if ($this->mode == Tokenizer::CONCAT) {
				$this->result[count($this->result)-1] .= $newValue;
		}
		else if ($this->mode == Tokenizer::NOT) {
			$this->result[count($this->result)-1] = $this->result[count($this->result)-1] != $newValue;
		}
		else if ($this->mode == Tokenizer::EQUALS) {
			$this->result[count($this->result)-1] = $this->result[count($this->result)-1] == $newValue;
		}

		return $this->result;
	}

	private function callFunc($name, $args, $element, $data) {
		if ($this->data instanceof \Transphporm\FunctionSet) return $this->data->$name($args, $element);
		else return $this->callFuncOnObject($this->data, $name, $args, $element);
	}

	private function callFuncOnObject($obj, $func, $args, $element) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else if (isset($obj->$func) && is_array($obj->$func))  {

		}
		else return call_user_func_array([$obj, $func], $args);
	}
}
