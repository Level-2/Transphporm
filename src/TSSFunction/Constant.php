<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\TSSFunction;
/* Handles constant() function in the TSS stlyesheet */
class Constant implements \Transphporm\TSSFunction {
	public function run(array $args, \DomElement $element) {
		$const_name = strtoupper(trim($args[0]));
		if (!defined($const_name)) {
			throw new \Exception($const_name . ' is not a defined constant');
		}
		return constant($const_name);
	}
}
