<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Property;
class Bind implements \Transphporm\Property {
	private $data;

	public function __construct(\Transphporm\Hook\ElementData $data) {
		$this->data = $data;
	}

	public function run(\Transphporm\Document $document, array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []): \Transphporm\Document {
		$document = new \Transphporm\Document($this->data->elementMap, new \DomDocument);
		$document = $document->bind($element, $values[0]);
		return $document;
	}
}