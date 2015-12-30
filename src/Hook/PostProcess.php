<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/** Hooks into the template system, gets assigned as `ul li` or similar and `run()` is called with any elements that match */
class PostProcess implements \Transphporm\Hook {
	public function run(\DomElement $element) {
		$transphporm = $element->getAttribute('transphporm');
		if ($transphporm === 'remove') $element->parentNode->removeChild($element);
		else if ($transphporm === 'text') $element->parentNode->replaceChild($element->firstChild, $element);
		else $element->removeAttribute('transphporm');
	}

}