<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
/* Handles data() and iteration() function calls from the stylesheet */
class FunctionSet {
	private $elementData;
	private $functions = [];
	private $element;

	public function __construct(Hook\ElementData $elementData) {
		$this->elementData = $elementData;
	}

	public function __call($name, $args) {
		if (isset($this->functions[$name])) {
			return $this->functions[$name]->run($this->getArgs0($name, $args), $this->element);
		}
		
		return false;
	}

	private function getArgs0($name, $args) {
		if (isset($this->functions[$name]) && !($this->functions[$name] instanceof TSSFunction\Data)) {
			$tokens = $args[0];
			$parser = new \Transphporm\Parser\Value($this);
			return $parser->parseTokens($tokens, $this->elementData->getData($this->element));
		}
		else {
			return iterator_to_array($args[0]);
		}

	}

	public function addFunction($name, \Transphporm\TSSFunction $function) {
		$this->functions[$name] = $function;
	}

	public function hasFunction($name) {
		return isset($this->functions[$name]);
	}

	public function setElement(\DomElement $element) {
		$this->element = $element;
	}

}
