<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/* Maps which data is applied to which element */
class ElementData {
	private $data;
	private $elementMap;

	public function __construct(\SplObjectStorage $elementMap, $data) {
		$this->elementMap = $elementMap;
		$this->data = $data;
	}

	/** Binds data to an element */
	public function bind(\DomNode $element, $data, $type = 'data') {
		$content = isset($this->elementMap[$element]) ? $this->elementMap[$element] : [];
		$content[$type] = $data;
		$this->elementMap[$element] = $content;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	public function getData(\DomElement $element = null, $type = 'data') {
		while ($element) {
			if (isset($this->elementMap[$element]) && isset($this->elementMap[$element][$type])) return $this->elementMap[$element][$type];
			$element = $element->parentNode;
		}
		return $this->data;
	}
}
