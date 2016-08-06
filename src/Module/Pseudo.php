<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Module;
/** Module for loading a formatter with a locale */
class Pseudo implements \Transphporm\Module {

	public function load(\Transphporm\Config $config) {
		$config->registerPseudo(new \Transphporm\Pseudo\Attribute());
		$config->registerPseudo(new \Transphporm\Pseudo\Nth());
		$config->registerPseudo(new \Transphporm\Pseudo\Not($config->getCssToXpath()));
	}
}
