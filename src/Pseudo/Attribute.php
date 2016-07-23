<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Pseudo;
use \Transphporm\Parser\Tokenizer;
class Attribute implements \Transphporm\Pseudo {
	private $functionSet;

	public function __construct(\Transphporm\FunctionSet $functionSet) {
		$this->functionSet = $functionSet;
	}

	public function match($pseudo, \DomElement $element) {//var_dump(Tokenizer::OPEN_SQUARE_BRACKET); var_Dump($pseudo[0]['type']);var_Dump($pseudo[1]['type']);
		if ($pseudo[0]['type'] !== Tokenizer::OPEN_SQUARE_BRACKET
			&& (isset($pseudo[1]) && $pseudo[1]['type'] !== Tokenizer::OPEN_SQUARE_BRACKET)) return true;

		$this->functionSet->setElement($element);
		$valueParser = new \Transphporm\Parser\Value($this->functionSet);
		$valueParser->debug = true;
		return $valueParser->parseTokens($pseudo, $this->functionSet)[0];
	}
}
