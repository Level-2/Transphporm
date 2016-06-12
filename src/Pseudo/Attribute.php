<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Attribute implements \Transphporm\Pseudo {
	private $functionSet;

	public function __construct(\Transphporm\FunctionSet $functionSet) {
		$this->functionSet = $functionSet;
	}

	public function match($pseudo, \DomElement $element) {
		if (strpos($pseudo, '[') === false) return true;

		$this->functionSet->setElement($element);
		$valueParser = new \Transphporm\Parser\Value($this->functionSet);
		$valueParser->debug = true;
		return $valueParser->parse($pseudo)[0];
	}
}
