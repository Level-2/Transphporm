<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
/* Handles data() and iteration() function calls from the stylesheet */
class FunctionSet {
	private $elementData;
	private $baseDir;
	private $functions = [];

	public function __construct(Hook\ElementData $elementData, &$baseDir) {
		$this->baseDir = &$baseDir;
		$this->elementData = $elementData;
	}

	public function __call($name, $args) {
		if (isset($this->functions[$name])) {
			return $this->functions[$name]->run($args[0], $args[1]);
		}
		else return \Transphporm\Parser\Value::IS_NOT_FUNCTION;
	}

	public function addFunction($name, \Transphporm\TSSFunction $function) {
		$this->functions[$name] = $function;
	}
}
