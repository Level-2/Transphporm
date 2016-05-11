<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses "string" and function(args) e.g. data(foo) or iteration(bar) */ 
class Value {
	private $data;
	private $autoLookup;
	private $tokens;
	
	public function __construct($data, $autoLookup = false) {
		$this->data = $data;
		$this->autoLookup = $autoLookup;
	}

	public function parse($str, $element = null, $returnTokens = false) {
		$tokenizer = new Tokenizer($str);
		$tokens = $tokenizer->getTokens();
		if ($returnTokens) return $tokens;
		$result = $this->parseTokens($tokens, $element, $this->data);
		return $result;
	}

	public function parseTokens($tokens, $element, $data) {
		$result = [];
		$mode = Tokenizer::ARG;
		$last = null;

		if (empty($tokens)) return [$data];

		foreach ($tokens as $token) {
		if (is_string($token)) throw new \Exception($token);

			if (in_array($token['type'], [Tokenizer::NOT, Tokenizer::EQUALS])) {
				// ($last !== null) $result = $this->processValue($result, $mode, $last);
				$result = $this->processLast($last, $result, $mode, $data);

				if ($mode == Tokenizer::NOT && $token['type'] == Tokenizer::EQUALS) {
					$mode = Tokenizer::NOT;
				}
				else $mode = $token['type'];
			}

			if ($token['type'] === Tokenizer::DOT) {
				if ($last !== null) {
					$data = $data->$last;
				}
				else $data = array_pop($result);

				$last = null;
			}

			if (in_array($token['type'], [Tokenizer::ARG, Tokenizer::CONCAT])) {
				$mode = $token['type'];
				//if ($last !== null) $result = $this->processValue($result, $mode, $last);
				$result = $this->processLast($last, $result, $mode, $data);
			}

			if ($token['type'] === Tokenizer::STRING) {
				$result = $this->processValue($result, $mode, $token['value']);
			}
			
			if ($token['type'] === Tokenizer::NAME || $token['type'] == Tokenizer::NUMERIC) {
				$last = $token['value'];		
			}

			if ($token['type'] == Tokenizer::OPEN_BRACKET) {
				
				if ($this->data instanceof \Transphporm\Functionset && ($last == 'data' || $last == 'iteration' || $last == 'attr')) {
					$result = $this->processValue($result, $mode, $this->data->$last($token['value'], $element));
					
					foreach ($result as $i => $value) {
						if (is_array($data)) {
							if (isset($data[$value])) $result[$i] = $data[$value];
						}
						else if (is_scalar($value) && isset($data->$value)) $result[$i] = $data->$value;
					}	
					$last = null;
				}
				else if ($data instanceof \Transphporm\Functionset) {
					$result = $this->processValue($result, $mode, $data->$last($token['value'], $element));
					$last = null;
				}
				else {
					$args = $this->parseTokens($token['value'], $element, $data);
					$funcResult = $this->callFunc($last, $args, $element, $data);
					$result = $this->processValue($result, $mode, $funcResult);
					$last = null;	
				}
			}

			if ($token['type'] == Tokenizer::OPEN_SQUARE_BRACKET) {
				if ($this->autoLookup === true) {
					$result = $this->processValue($result, $mode, $data->$last($token['value'], $element));
					$last = null;

				}
			}		
		}

		return $this->processLast($last, $result, $mode, $data);
	}

	private function processLast($last, $result, $mode, $data) {
		if ($last !== null) {
			if ($this->autoLookup && isset($data->$last)) {
				$result = $this->processValue($result, $mode, $data->$last);
			}
			else if (is_array($data) && isset($data[$last])) {
				$result = $this->processValue($result, $mode, $data[$last]);	
			}
			else $result = $this->processValue($result, $mode, $last);
		}
		return $result;
	}

	private function processValue($result, $mode, $newValue) {
		if ($mode == Tokenizer::ARG) {
			$result[] = $newValue;
		}
		else if ($mode == Tokenizer::CONCAT) {
				$result[count($result)-1] .= $newValue;
		}
		else if ($mode == Tokenizer::NOT) {
			$result[count($result)-1] = $result[count($result)-1] != $newValue;
		}
		else if ($mode == Tokenizer::EQUALS) {
			$result[count($result)-1] = $result[count($result)-1] == $newValue;	
		}

		return $result;
	}

	private function callFunc($name, $args, $element, $data) {
		if ($data instanceof \Transphporm\FunctionSet) return $data->$name($args, $element);	
		else return $this->callFuncOnObject($data, $name, $args, $element);
	}

	private function callFuncOnObject($obj, $func, $args, $element) {
		if (isset($obj->$func) && is_callable($obj->$func)) return call_user_func_array($obj->$func, $args);
		else if (isset($obj->$func) && is_array($obj->$func))  {

		}
		else return call_user_func_array([$obj, $func], $args);
	}
}