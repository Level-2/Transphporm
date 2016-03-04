<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Module;
/** Assigns all the basic properties repeat, comment, etc to a $builder instance */
class Basics implements \Transphporm\Module {


	public function load(\Transphporm\Config $config) {
		$data = $config->getFunctionSet();
		$headers = &$config->getHeaders();

		$config->registerProperty('content', new \Transphporm\Property\Content($data, $headers, $config->getFormatter()));
		$config->registerProperty('repeat', new \Transphporm\Property\Repeat($data, $config->getElementData()));
		$config->registerProperty('display', new \Transphporm\Property\Display);
		$config->registerProperty('bind', new \Transphporm\Property\Bind($config->getElementData()));
	}
}