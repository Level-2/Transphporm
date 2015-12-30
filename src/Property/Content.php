<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Content implements \Transphporm\Property {
	private $data;
	private $headers;
	private $formatter;


	public function __construct($data, &$headers, \Transphporm\Hook\Formatter $formatter) {
		$this->data = $data;
		$this->headers = &$headers;
		$this->formatter = $formatter;
	}

	public function run($value, \DomElement $element, \Transphporm\Hook\PropertyHook $rule) {
		if ($element->getAttribute('transphporm') === 'remove') return;
				
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

	private function getNode($node, $document) {
		foreach ($node as $n) {
			if ($n instanceof \DomElement) {
				$new = $document->importNode($n, true);
				$new->setAttribute('transphporm', 'added');
			}
			else {
				if ($n instanceof \DomText) $n = $n->nodeValue;
				if ($n == '') continue;

				$new = $document->createElement('text');
				$new->appendChild($document->createTextNode($n));
				$new->setAttribute('transphporm', 'text');
			}
			yield $new;
		}
	}

	private function pseudoBefore($value, $element, $rule) {
		if (in_array('before', $rule->getPseudoMatcher()->getPseudo())) {
			foreach ($this->getNode($value, $element->ownerDocument) as $node) {
				$element->insertBefore($node, $element->firstChild);	
			}
			return true;
		}
	}

	private function pseudoAfter($value, $element, $rule) {
		 if (in_array('after', $rule->getPseudoMatcher()->getPseudo())) {
		 	foreach ($this->getNode($value, $element->ownerDocument) as $node) {
		 		$element->appendChild($node);
			}
		 	return true;
		 }		 
	}

	private function removeAdded($e) {
		$remove = [];
		while ($e = $e->previousSibling && $e->getAttribute('transphporm') != null && $e->getAttribute('transphporm') != 'remove') {
			$remove[] = $e;
		}
		foreach ($remove as $r) $r->parentNode->removeChild($r);
	}

	private function replaceContent($element, $content) {
		//If this rule was cached, the elements that were added last time need to be removed prior to running the rule again.
		$this->removeAdded($element);
		foreach ($this->getNode($content, $element->ownerDocument) as $node) {
			$element->parentNode->insertBefore($node, $element);
		}		
		$element->setAttribute('transphporm', 'remove');
	}

	private function appendContent($element, $content) {
		foreach ($this->getNode($content, $element->ownerDocument) as $node) {
			$element->appendChild($node);
		}
	}

	private function removeAllChildren($element) {
		while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
	}
}