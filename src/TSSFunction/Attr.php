<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\TSSFunction;
/* Handles attr() function in the TSS stlyesheet */
class Attr implements \Transphporm\TSSFunction {
	public function run(array $args, \DomElement $element) {
		$parser = new \Transphporm\Parser\Value(null);
		$args = $parser->parseTokens($args, $element, null);
		return $element->getAttribute(trim($args[0]));
	}
}
