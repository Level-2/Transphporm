<?php
namespace Transphporm\Property\ContentPseudo;
class BeforeAfter implements \Transphporm\Property\ContentPseudo {
	private $insertLocation;
	private $content;

	public function __construct($insertLocation, \Transphporm\Property\Content $content) {
		$this->insertLocation = $insertLocation;
		$this->content = $content;
	}

	public function run($value, $pseudoArgs, $element, \Transphporm\Hook\PseudoMatcher $pseudoMatcher) {
		$currentFirst = $element->firstChild;

		foreach ($this->content->getNode($value, $element->ownerDocument) as $node) {
			$this->{$this->insertLocation}($node, $element, $currentFirst);
		}
	}

	private function before($node, $element, $currentFirst) {		
		$element->insertBefore($node, $currentFirst);
	}

	private function after($node, $element, $currentFirst) {
		$element->appendChild($node);
	}
}
