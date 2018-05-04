<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Property;
class ContentReplace {
	private $content;

	public function __construct(Content $content) {
		$this->content = $content;
	}

	public function replaceContent($element, $content) {
		if ($element->getAttribute('transphporm') == 'added') return;
		//If this rule was cached, the elements that were added last time need to be removed prior to running the rule again.
		if ($element->getAttribute('transphporm')) {
			$this->replaceCachedContent($element);
		}

		$this->insertNodes($element, $content);

		//Remove the original element from the final output
		$element->setAttribute('transphporm', 'remove');
	}

	private function insertNodes($element, $content) {
		foreach ($this->content->getNode($content, $element->ownerDocument) as $node) {
			if ($node instanceof \DomElement && !$node->getAttribute('transphporm'))  $node->setAttribute('transphporm', 'added');
			$element->parentNode->insertBefore($node, $element);
		}
	}

	private function replaceCachedContent($element) {
		$el = $element;
		while ($el = $el->previousSibling) {
			if ($el->nodeType == 1 && $el->getAttribute('transphporm') != 'remove') {
				$el->parentNode->removeChild($el);
			}
		}
		$this->fixPreserveWhitespaceRemoveChild($element);
	}

	// $doc->preserveWhiteSpace = false should fix this but it doesn't
	// Remove extra whitespace created by removeChild to avoid the cache growing 1 byte every time it's reloaded
	// This may need to be moved in future, anywhere elements are being removed and files are cached may need to apply this fix
	// Also remove any comments to avoid the comment being re-added every time the cache is reloaded
	private function fixPreserveWhitespaceRemoveChild($element) {
		if ($element->previousSibling instanceof \DomComment || ($element->previousSibling instanceof \DomText && $element->previousSibling->isElementContentWhiteSpace())) {
			$element->parentNode->removeChild($element->previousSibling);
		}
	}
}