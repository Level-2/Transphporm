<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Attribute implements \Transphporm\Pseudo {
	public function match($name, $args, \DomElement $element) {
		if (!($name === null || in_array($name, ['data', 'iteration', 'root'])))  return true;
		return $args[0];
	}
}
