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

	public function run($value, \DomElement $element, \Transphporm\Hook\Rule $rule) {
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
			//var_dump($value);
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

	private function appendToIfNode($element, $content, $appendTo) {
		if (isset($content[0]) && $content[0] instanceof \DomNode) {
			foreach ($content as $node) {
				$node = $element->ownerDocument->importNode($node, true);
				$appendTo->appendChild($node);
			}
			return true;
		}
		return false;
	}

	private function replaceContent($element, $content) {
		if (!$this->appendToIfNode($element, $content, $element->parentNode)) {
			$element->parentNode->appendChild($element->ownerDocument->createElement('span', implode('', $content)));
		}		
		$element->setAttribute('transphporm', 'remove');
	}

	private function appendContent($element, $content) {
		if (!$this->appendToIfNode($element, $content, $element)) {
			$element->appendChild($element->ownerDocument->createTextNode(implode('', $content)));
		}
	}

	private function removeAllChildren($element) {
		while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
	}
}