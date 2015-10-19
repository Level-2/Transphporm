<?php
namespace Transphporm\Hook;
class BasicProperties {
	private $data;
	private $formatters = [];
	private $headers;

	public function __construct($data, &$headers) {
		$this->data = $data;
		$this->headers = &$headers;
	}

	public function content($value, $element, $rule) {
		$value = $this->format($value, $rule->getRules());
		if (!$this->processPseudo($value, $element, $rule)) {
			//Remove the current contents
			$this->removeAllChildren($element);
			//Now make a text node
			$this->appendContent($element, $value);
		}
	}

	private function processPseudo($value, $element, $rule) {
		return $this->pseudoAttr($value, $element, $rule) || $this->pseudoHeader($value, $element, $rule) || $this->pseudoBefore($value, $element, $rule) || $this->pseudoAfter($value, $element, $rule);
	}

	private function pseudoAttr($value, $element, $rule) {
		if ($attr = $rule->getPseudoMatcher()->attr()) {
			$element->setAttribute($attr, implode('', $value));
			return true;
		}
	}

	private function pseudoHeader($value, $element, $rule) {
		if ($header = $rule->getPseudoMatcher()->header($element)) {
			$this->headers[] = [$header, implode('', $value)];
			return true;
		}
	}

	private function pseudoBefore($value, $element, $rule) {
		if (in_array('before', $rule->getPseudoMatcher()->getPseudo())) {
			$element->firstChild->nodeValue = implode('', $value) . $element->firstChild->nodeValue;
			return true;
		}
	}

	private function pseudoAfter($value, $element, $rule) {
		 if (in_array('after', $rule->getPseudoMatcher()->getPseudo())) {
		 	$element->firstChild->nodeValue .= implode('', $value);
		 	return true;
		 }		 
	}

	private function appendContent($element, $content) {
		if (isset($content[0]) && $content[0] instanceof \DomNode) {
			foreach ($content as $node) $element->appendChild($node);
		}
		else $element->appendChild($element->ownerDocument->createTextNode(implode('', $content)));		
	}

	private function removeAllChildren($element) {
		while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
	}

	public function registerFormatter($formatter) {
		$this->formatters[] = $formatter;
	}

	private function format($value, $rules) {
		if (!isset($rules['format'])) return $value;
		$format = explode(' ', $rules['format']);
		$functionName = array_shift($format);

		return $this->processFormat($format, $functionName, $value);		
	}

	private function processFormat($format, $functionName, $value) {
		foreach ($value as &$val) {
			foreach ($this->formatters as $formatter) {
				if (is_callable([$formatter, $functionName])) {
					$val = $formatter->$functionName($val, ...$format);
				}
			}
		}
		return $value;
	}

	public function repeat($value, $element, $rule) {
		foreach ($value as $iteration) {
			$clone = $element->cloneNode(true);
			$this->data->bind($clone, $iteration);
			$element->parentNode->insertBefore($clone, $element);

			//Re-run the hook on the new element, but use the iterated data
			$newRules = $rule->getRules();

			//Don't run repeat on the clones element or it will loop forever
			unset($newRules['repeat']);

			$hook = new Rule($newRules, $rule->getPseudoMatcher(), $this->data);
			foreach ($rule->getProperties() as $obj) $hook->registerProperties($obj);
			$hook->run($clone);
		}

		//Remove the original element so only the ones that have been looped over will show
		$element->parentNode->removeChild($element);

		return false;
	}

	public function display($value, $element) {
		if (strtolower($value[0]) === 'none') $element->parentNode->removeChild($element);
	}

	public function bind($value, $element) {
		$this->data->bind($element, $value);
	}

}