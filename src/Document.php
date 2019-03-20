<?php
namespace Transphporm;
class Document {
	private $elementMap;
	public $document;
	private $data;

	public function __construct(\SplObjectStorage $elementMap, \DomDocument $document) {
		$this->elementMap = $elementMap;
		$this->document = $document;
	}

	/** Binds data to an element */
	public function bind(\DomNode $element, $data, $type = 'data'): self {
		$content = isset($this->elementMap[$element]) ? $this->elementMap[$element] : [];
		$content[$type] = $data;
		$this->elementMap[$element] = $content;
		return clone $this;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	public function getData(\DomElement $element = null, $type = 'data') {
		while ($element) {
			if (isset($this->elementMap[$element]) && array_key_exists($type, $this->elementMap[$element])) return $this->elementMap[$element][$type];
			$element = $element->parentNode;
		}
		return $this->data;
	}

	public function addHeader($header): self {
		return $this;
	}

	public function removeElement(\DomElement $element): self {
		$element->parentNode->removeChild($element);
		return clone $this;
	}

	public function removeAllChildren(\DomElement $element): self {
		while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
		return clone $this;
	}
}