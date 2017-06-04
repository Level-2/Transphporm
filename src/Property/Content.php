<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Content implements \Transphporm\Property {
	private $headers;
	private $formatter;

	public function __construct(&$headers, \Transphporm\Hook\Formatter $formatter) {
		$this->headers = &$headers;
		$this->formatter = $formatter;
	}

	public function run(array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {
		if (!$this->shouldRun($element)) return false;

		$values = $this->formatter->format($values, $rules);

		if (!$this->processPseudo($values, $element, $pseudoMatcher)) {
			//Remove the current contents
			$this->removeAllChildren($element);
			//Now make a text node
			if ($this->getContentMode($rules) === 'replace') $this->replaceContent($element, $values);
			else $this->appendContent($element, $values);
		}
	}

	private function shouldRun($element) {
		do {
			if ($element->getAttribute('transphporm') == 'includedtemplate') return false;
		}
		while (($element = $element->parentNode) instanceof \DomElement);
		return true;
	}

	private function getContentMode($rules) {
		return (isset($rules['content-mode'])) ? $rules['content-mode']->read() : 'append';
	}

	private function processPseudo($value, $element, $pseudoMatcher) {
		$pseudoContent = ['attr', 'header', 'before', 'after'];
		foreach ($pseudoContent as $pseudo) {
			if ($pseudoMatcher->hasFunction($pseudo)) {
				$this->$pseudo($value, $pseudoMatcher->getFuncArgs($pseudo, $element)[0], $element);
				return true;
			}
		}
		return false;
	}

	private function getNode($node, $document) {
		foreach ($node as $n) {
			if (is_array($n)) {
				foreach ($this->getNode($n, $document) as $new) yield $new;
			}
			else {
				yield $this->convertNode($n, $document);
			}
		}
	}

	private function convertNode($node, $document) {
		if ($node instanceof \DomElement || $node instanceof \DOMComment) {
			$new = $document->importNode($node, true);
			//Removing this might cause problems with caching...
			//$new->setAttribute('transphporm', 'added');
		}
		else {
			if ($node instanceof \DomText) $node = $node->nodeValue;
			$new = $document->createElement('text');

			$new->appendChild($document->createTextNode($node));
			$new->setAttribute('transphporm', 'text');
		}
		return $new;
	}

	/** Functions for writing to pseudo elements, attr, before, after, header */
	private function attr($value, $pseudoArgs, $element) {
		$element->setAttribute($pseudoArgs, implode('', $value));
	}

	private function header($value, $pseudoArgs, $element) {
		$this->headers[] = [$pseudoArgs, implode('', $value)];
	}

	private function before($value, $pseudoArgs, $element) {
		foreach ($this->getNode($value, $element->ownerDocument) as $node) {
			$element->insertBefore($node, $element->firstChild);
		}
		return true;
	}

	private function after($value, $pseudoArgs, $element) {
		 foreach ($this->getNode($value, $element->ownerDocument) as $node) {
		 		$element->appendChild($node);
		}
	}

	private function replaceContent($element, $content) {
		//If this rule was cached, the elements that were added last time need to be removed prior to running the rule again.
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
