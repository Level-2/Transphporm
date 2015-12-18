<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
namespace Transphporm\Hook;
class BasicProperties {
	private $data;
	private $headers;
	private $formatter; 

	public function __construct($data, &$headers, Formatter $formatter) {
		$this->data = $data;
		$this->headers = &$headers;
		$this->formatter = $formatter;
	}

	public function content($value, $element, $rule) {
		$value = $this->formatter->format($value, $rule->getRules());
		if (!$this->processPseudo($value, $element, $rule)) {
			//Remove the current contents
			$this->removeAllChildren($element);
			//Now make a text node
			if ($this->getContentMode($rule->getRules()) === 'replace') $this->replaceContent($element, $value);
			else $this->appendContent($element, $value);
		}
	}

	private function getContentMode($rules) {
		return (isset($rules['content-mode'])) ? $rules['content-mode'] : 'append';
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

	private function replaceContent($element, $content) {
		if (isset($content[0]) && $content[0] instanceof \DomNode) {
			foreach ($content as $node) {
				$node = $element->ownerDocument->importNode($node, true);
				$element->parentNode->appendChild($node);
			}
		}
		else $element->parentNode->appendChild($element->ownerDocument->createElement('span', implode('', $content)));

		$element->setAttribute('transphporm', 'remove');
	}

	private function appendContent($element, $content) {
		if (isset($content[0]) && $content[0] instanceof \DomNode) {
			foreach ($content as $node) {
				$node = $element->ownerDocument->importNode($node, true);
				$element->appendChild($node);
			}
		}
		else $element->appendChild($element->ownerDocument->createTextNode(implode('', $content)));		
	}

	private function removeAllChildren($element) {
		while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
	}

	private function createHook($newRules, $rule) {
		$hook = new Rule($newRules, $rule->getPseudoMatcher(), $this->data);
		foreach ($rule->getProperties() as $obj) $hook->registerProperties($obj);
		return $hook;
	}

	public function repeat($value, $element, $rule) {
		if ($element->getAttribute('transphporm') === 'added') return $element->parentNode->removeChild($element);

		foreach ($value as $key => $iteration) {
			$clone = $element->cloneNode(true);
			//Mark this node as having been added by transphporm
			$clone->setAttribute('transphporm', 'added');
			$this->data->bind($clone, $iteration, 'iteration');
			$this->data->bind($clone, $key, 'key');
			$element->parentNode->insertBefore($clone, $element);

			//Re-run the hook on the new element, but use the iterated data
			$newRules = $rule->getRules();
			//Don't run repeat on the clones element or it will loop forever
			unset($newRules['repeat']);

			$this->createHook($newRules, $rule)->run($clone);
		}
		//Flag the original element for removal
		$element->setAttribute('transphporm', 'remove');
		return false;
	}

	public function display($value, $element) {
		if (strtolower($value[0]) === 'none') $element->setAttribute('transphporm', 'remove');
		else $element->setAttribute('transphporm', 'show');
	}

	public function bind($value, $element) {
		$this->data->bind($element, $value);
	}

}