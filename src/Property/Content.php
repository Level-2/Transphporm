<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Property;
class Content implements \Transphporm\Property {
	private $contentPseudo = [];
	private $formatter;

	public function __construct(\Transphporm\Hook\Formatter $formatter) {
		$this->formatter = $formatter;
	}

	public function run(array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {


		if (!$this->shouldRun($element)) return false;

		$values = $this->formatter->format($values, $rules);

		if (!$this->processPseudo($values, $element, $pseudoMatcher)) {
			//Remove the current contents
			$this->removeAllChildren($element);
			//Now make a text node
			if ($this->getContentMode($rules) === 'replace') {
				$contentReplace = new ContentReplace($this);
				$contentReplace->replaceContent($element, $values);
			}
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

	public function addContentPseudo($name, ContentPseudo $contentPseudo) {
		$this->contentPseudo[$name] = $contentPseudo;
	}

	private function processPseudo($value, $element, $pseudoMatcher) {
		foreach ($this->contentPseudo as $pseudoName => $pseudoFunction) {
			if ($pseudoMatcher->hasFunction($pseudoName)) {
				$pseudoFunction->run($value, $pseudoMatcher->getFuncArgs($pseudoName, $element)[0], $element, $pseudoMatcher);
				return true;
			}
		}
		return false;
	}

	public function getNode($node, $document) {
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
		}
		else {
			if ($node instanceof \DomText) $node = $node->nodeValue;
			$new = $document->createElement('text');

			$new->appendChild($document->createTextNode($node));
			$new->setAttribute('transphporm', 'text');
		}
		return $new;
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