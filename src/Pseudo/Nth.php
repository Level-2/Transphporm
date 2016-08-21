<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
use \Transphporm\Parser\Tokenizer;
class Nth implements \Transphporm\Pseudo {

	public function match($name, $args, \DomElement $element) {
		if ($name !== 'nth-child') return true;

		$criteria = $args[0];

		$nodePath = $element->getNodePath();
		$tokenizer = new \Transphporm\Parser\Tokenizer($nodePath);
		$pseudo = $tokenizer->getTokens(false);
		$num = end($pseudo)['value'][0]['value'];

		if (is_callable([$this, $criteria])) return $this->$criteria($num);
		else return $num == $criteria;
	}

	private function odd($num) {
		return $num % 2 === 1;
	}

	private function even($num) {
		return $num % 2 === 0;
	}
}
