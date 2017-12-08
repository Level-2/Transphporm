<?php
namespace Transphporm\Property\ContentPseudo;
class BeforeAfter implements \Transphporm\Property\ContentPseudo {
	private $insertLocation;
	private $content;

	public function __construct($insertLocation, \Transphporm\Property\Content $content) {
		$this->insertLocation = $insertLocation;
		$this->content = $content;
	}

	public function run($value, $pseudoArgs, $element) {
		$this->{$this->insertLocation}($value, $element);
	}

	private function before($value, $element) {
		$currentFirst = $element->firstChild;

		foreach ($this->content->getNode($value, $element->ownerDocument) as $node) {
			$element->insertBefore($node, $currentFirst);
		}
	}

	private function after($value, $element) {
		foreach ($this->content->getNode($value, $element->ownerDocument) as $node) {
			$element->appendChild($node);
		}
	}
}
