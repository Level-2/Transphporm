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
			$bracketMatcher = new \Transphporm\Parser\BracketMatcher($pseudo);
			$criteria = $bracketMatcher->match('(', ')');
		
			$node_path = explode('/', $element->getNodePath());

			$bracketMatcher = new \Transphporm\Parser\BracketMatcher(array_pop($node_path));
			$num = $bracketMatcher->match('[', ']');
			
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
