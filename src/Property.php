<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
interface Property {
	public function run(Document $document, array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []): Document;
}