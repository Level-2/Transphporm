<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Module;
/** Assigns all the basic functions, data(), key(), iteration(), template(), etc    */
class Functions implements \Transphporm\Module {

	public function load(\Transphporm\Config $config) {
		$functionSet = $config->getFunctionSet();
		$baseDir = &$config->getBaseDir();

		$functionSet->addFunction('attr', new \Transphporm\TSSFunction\Attr());
		$functionSet->addFunction('data', new \Transphporm\TSSFunction\Data($config->getElementData(), $functionSet, 'data'));
		$functionSet->addFunction('key', new \Transphporm\TSSFunction\Data($config->getElementData(), $functionSet, 'key'));
		$functionSet->addFunction('iteration', new \Transphporm\TSSFunction\Data($config->getElementData(), $functionSet, 'iteration'));
		$functionSet->addFunction('template', new \Transphporm\TSSFunction\Template($config->getElementData(), $functionSet, $baseDir));
		$functionSet->addFunction('json', new \Transphporm\TSSFunction\Json($baseDir));
	}
}
