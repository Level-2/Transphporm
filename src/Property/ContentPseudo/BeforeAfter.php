<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Property\ContentPseudo;
class BeforeAfter implements \Transphporm\Property\ContentPseudo {
	private $insertLocation;
	private $content;

	public function __construct($insertLocation, \Transphporm\Property\Content $content) {
		$this->insertLocation = $insertLocation;
		$this->content = $content;
	}

	public function run(\Transphporm\Document $document, $value, $pseudoArgs, $element, \Transphporm\Hook\PseudoMatcher $pseudoMatcher): \Transphporm\Document {
		$currentFirst = $element->firstChild;

		foreach ($this->content->getNode($value, $element->ownerDocument) as $node) {
			$this->{$this->insertLocation}($node, $element, $currentFirst);
		}
		//TODO, all changes should be made in $document
		return clone $document;
	}

	private function before($node, $element, $currentFirst) {
		$element->insertBefore($node, $currentFirst);
	}

	private function after($node, $element, $currentFirst) {
		$element->appendChild($node);
	}
}
