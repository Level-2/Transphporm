<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\TSSFunction;
/* Handles data() and iteration() function calls from the stylesheet */
class Data implements \Transphporm\TSSFunction{
	private $data;
	private $dataKey;
	private $functionSet;

	public function __construct(\Transphporm\Hook\ElementData $data, \Transphporm\FunctionSet $functionSet, $dataKey = 'data') {
		$this->data = $data;
		$this->dataKey = $dataKey;
		$this->functionSet = $functionSet;
	}

	public function run(array $args, \DomElement $element = null) {
		$data = $this->data->getData($element, $this->dataKey);
		$parser = new \Transphporm\Parser\Value($this->functionSet, true);
		$return = $parser->parseTokens($args, $data);
		return $return[0];
	}
}