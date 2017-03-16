<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
use \Transphporm\Parser\Tokenizer;
class Nth implements \Transphporm\Pseudo {
	private $count = 0;
	private $lastParentNode;

	public function match($name, $args, \DomElement $element) {
		if ($element->parentNode !== $this->lastParentNode) $this->count = 0;

		$this->lastParentNode = $element->parentNode;



		if ($name !== 'nth-child') return true;

		$this->count++;
		$criteria = $args[0];

		if (is_callable([$this, $criteria])) return $this->$criteria($this->count);
		$this->assert(is_numeric($criteria), "Argument passed to 'nth-child' must be 'odd', 'even', or of type int");
		return $this->count == $criteria;
	}

	//TODO: Abstract assertions throughout
	private function assert($condition, $error) {
		if (!$condition) throw new \Exception($error);
	}

	private function odd($num) {
		return $num % 2 === 1;
	}

	private function even($num) {
		return $num % 2 === 0;
	}
}
