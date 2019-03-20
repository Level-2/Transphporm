<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Hook;
/* Maps which data is applied to which element */
class ElementData {
	private $data;
	public $elementMap;

	public function __construct(\SplObjectStorage $elementMap, $data) {
		$this->elementMap = $elementMap;
		$this->data = $data;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	public function getData(\DomElement $element = null, $type = 'data') {
		//throw new \Exception('b');
		while ($element) {
			if (isset($this->elementMap[$element]) && array_key_exists($type, $this->elementMap[$element])) return $this->elementMap[$element][$type];
			$element = $element->parentNode;
		}
		return $this->data;
	}
}
