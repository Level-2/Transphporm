<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Attribute implements \Transphporm\Pseudo {
	private $functionSet;

	public function __construct(\Transphporm\FunctionSet $functionSet) {
		$this->functionSet = $functionSet;
	}

	public function match($pseudo, \DomElement $element) {
		$pos = strpos($pseudo, '[');
		if ($pos === false) return true;
		
		$name = substr($pseudo, 0, $pos);
		if (!is_callable([$this->functionSet, $name])) return true;

		$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
		$criteria = $bracketMatcher->match('[', ']');

		if (strpos($pseudo, '=') === false) {
			$lookupValue = $this->functionSet->$name([$criteria], $element);
			return $lookupValue !== null;
		}
		list ($field, $value) = explode('=', $criteria);

		$operator = $this->getOperator($field);
		$lookupValue = $this->functionSet->$name([trim($field, $operator)], $element);
		return $this->processOperator($operator, $lookupValue, $this->parseValue(trim($value, '"')));
	}


	//Currently only not is supported, but this is separated out to support others in future
	private function processOperator($operator, $lookupValue, $value) {
		$matched = $lookupValue == $value;
		return $operator === '!' ? !$matched : $matched;
	}

	private function parseValue($value) {
		if ($value == 'true') return true;
		else if ($value == 'false') return false;
		else return $value;
	}

	private function getOperator($field) {
		if ($field[strlen($field)-1] == '!') {
			return '!';
		}
		else return '';
	}

}