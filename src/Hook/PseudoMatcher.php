<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/** Determines whether $element matches the pseudo rule such as nth-child() or [attribute="value"] */
class PseudoMatcher {
	private $pseudo;
	private $dataFunction;

	public function __construct($pseudo, DataFunction $dataFunction) {
		$this->pseudo = $pseudo;
		$this->dataFunction = $dataFunction;
	}

	public function matches($element) {
		$matches = true;
		$functions = ['attribute', 'nth', 'not'];

		foreach ($this->pseudo as $pseudo) {			
			foreach ($functions as $func) {
				$matches = $matches && $this->$func($pseudo, $element);
			}
		}		
		return $matches;
	}

	private function betweenBrackets($string, $openChr, $closingChr, $start = 0) {
		$open = strpos($string, $openChr, $start);
		$close = strpos($string, $closingChr, $open);

		$cPos = $open+1;
		while (($cPos = strpos($string, $openChr, $cPos+1)) !== false && $cPos < $close) $close = strpos($string, $closingChr, $close+1);

		return substr($string, $open+1, $close-$open-1);
	}
	
	private function attribute($pseudo, $element) {
		$pos = strpos($pseudo, '[');
		if ($pos === false) return true;
		
		$name = substr($pseudo, 0, $pos);
		if (!is_callable([$this->dataFunction, $name])) return true;

		$criteria = $this->betweenBrackets($pseudo, '[', ']');

		if (strpos($pseudo, '=') === false) {
			$lookupValue = $this->dataFunction->$name([$criteria], $element);
			return $lookupValue !== null;
		}
		list ($field, $value) = explode('=', $criteria);

		$operator = $this->getOperator($field);
		$lookupValue = $this->dataFunction->$name([trim($field, $operator)], $element);

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

	private function nth($pseudo, $element) {
		if (strpos($pseudo, 'nth-child') === 0) {	
			$criteria = $this->getBetween($pseudo, '(', ')');
			$num = $this->getBetween($element->getNodePath(), '[', ']');
			
			if (is_callable([$this, $criteria])) return $this->$criteria($num);
			else return $num == $criteria;
			
		}
		return true;
	}

	private function not($pseudo, $element) {
		if (strpos($pseudo, 'not') === 0) {
			$valueParser = new \Transphporm\Parser\Value($this->dataFunction);
			$css = explode(',', $this->getBetween($pseudo, '(', ')'));

			foreach ($css as $selector) {
				$cssToXpath = new \Transphporm\Parser\CssToXpath($selector, $valueParser);
				$xpathString = $cssToXpath->getXpath();	
				$xpath = new \DomXpath($element->ownerDocument);
				
				foreach ($xpath->query($xpathString) as $matchedElement) {
					if ($element->isSameNode($matchedElement)) return false;
				}
			}
		}
		return true;
	}

	public function attr() {
		foreach ($this->pseudo as $pseudo) {
			if (strpos($pseudo, 'attr') === 0) {
				$criteria = trim($this->getBetween($pseudo, '(', ')'));
				return $criteria;
			}
		}

		return false;
	}

	public function header($element)  {
		if ($this->matches($element)) {
			foreach ($this->pseudo as $pseudo) {
				if (strpos($pseudo, 'header') === 0) return $this->getBetween($pseudo, '[', ']');
			}
		}
	}

	private function odd($num) {
		return $num % 2 === 1;
	}

	private function even($num) {
		return $num % 2 === 0;
	}

	private function getBetween($string, $start, $end) {
		$open = strpos($string, $start);
		if ($open === false) return false;
		$close = strpos($string, $end, $open);
		return substr($string, $open+1, $close-$open-1);
	}

	public function getPseudo() {
		return $this->pseudo;
	}
}