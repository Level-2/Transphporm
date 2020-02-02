<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Module;
/** Module for loading a formatter with a locale */
class Pseudo implements \Transphporm\Module {

	public function load(\Transphporm\Config $config) {
		$config->registerPseudo('data', new \Transphporm\Pseudo\Attribute());
		$config->registerPseudo('iteration', new \Transphporm\Pseudo\Attribute());
		$config->registerPseudo('root', new \Transphporm\Pseudo\Attribute());
		$config->registerPseudo('nth-child', new \Transphporm\Pseudo\Nth());
		$config->registerPseudo('not', new \Transphporm\Pseudo\Not($config->getCssToXpath(), $config));
	}
}
