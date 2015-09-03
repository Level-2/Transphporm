<?php
namespace CDS\Hook;
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

		foreach ($this->pseudo as $pseudo) {			
			$matches = $matches && $this->attribute($pseudo, $element) && $this->nth($pseudo, $element);			
		}		
		return $matches;
	}

	private function attribute($pseudo, $element) {
		$pos = strpos($pseudo, '[');
		if ($pos === false) return true;
		$end = strpos($pseudo, ']', $pos);

		$name = substr($pseudo, 0, $pos);
		$criteria = substr($pseudo, $pos+1, $end-$pos-1);
		list ($field, $value) = explode('=', $criteria);

		$value = trim($value, '"');

		$lookupValue = $this->dataFunction->$name($field, $element);

		if ($lookupValue != $value) return false;
		else return true;
	}

	private function nth($pseudo, $element) {
		if (strpos($pseudo, 'nth-child') === 0) {		

			$criteria = $this->getBetween($pseudo, '(', ')');
			$num = $this->getBetween($element->getNodePath(), '[', ']');
			if (is_numeric($criteria)) {				
				if ($num == $criteria) return true;
				else return false;
			}
			else if ($criteria == 'odd') return $num % 2 === 1;
			else if ($criteria === 'even') return $num % 2 === 0;
		}
		return true;
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