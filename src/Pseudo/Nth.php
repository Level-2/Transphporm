<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
class Nth implements \Transphporm\Pseudo {
	
	public function match($pseudo, \DomElement $element) {
		if (strpos($pseudo, 'nth-child') === 0) {
			$tokenizer = new \Transphporm\Parser\Tokenizer($pseudo);
			$tokens = $tokenizer->getTokens();
			$criteria = $tokens[1]['value'][0]['value'];
		
			$nodePath = $element->getNodePath();
			$tokenizer = new \Transphporm\Parser\Tokenizer($nodePath);
			$tokens = $tokenizer->getTokens();
			$num = end($tokens)['value'][0]['value'];
			
			if (is_callable([$this, $criteria])) return $this->$criteria($num);
			else return $num == $criteria;			
		}
		return true;
	}

	private function odd($num) {
		return $num % 2 === 1;
	}

	private function even($num) {
		return $num % 2 === 0;
	}
}
