<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
 namespace Transphporm\Property\ContentPseudo;
class Attr implements \Transphporm\Property\ContentPseudo {
    public function run($value, $pseudoArgs, $element) {
        $element->setAttribute($pseudoArgs, implode('', $value));
    }
}
