<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokenizer;
use \Transphporm\Parser\Tokens;

class Comments implements \Transphporm\Parser\Tokenize {
	public function tokenize(TokenizedString $str, Tokens $tokens) {
		$this->singleLineComments($str);
		$this->multiLinecomments($str);
	}

	private function singleLineComments($str) {
		if ($str->identifyChar() == Tokenizer::DIVIDE && $str->identifyChar(1) == Tokenizer::DIVIDE) {
			$pos = $str->pos("\n");
			$str->move($pos !== false ? $pos : false);
		}
	}

	public function multiLinecomments($str) {
		if ($str->identifyChar() == Tokenizer::DIVIDE && $str->identifyChar(1) == Tokenizer::MULTIPLY) {
			$pos = $str->pos('*/');
			$str->move($pos !== false ? $pos+2 : false);
		}
	}
}