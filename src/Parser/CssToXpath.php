<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class CssToXpath {
	private $specialChars = [Tokenizer::WHITESPACE, Tokenizer::DOT, Tokenizer::GREATER_THAN,
		'~', Tokenizer::NUM_SIGN, Tokenizer::COLON, Tokenizer::OPEN_SQUARE_BRACKET];
	private $translators = [];
	private static $instances = [];
	private $functionSet;


	public function __construct(\Transphporm\FunctionSet $functionSet, $prefix = '') {
		$hash = $this->registerInstance();
		$this->functionSet = $functionSet;

		$this->translators = [
			Tokenizer::WHITESPACE => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			Tokenizer::GREATER_THAN => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			Tokenizer::NUM_SIGN => function($string) { return '[@id=\'' . $string . '\']'; },
			Tokenizer::DOT => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; },
			Tokenizer::OPEN_SQUARE_BRACKET => function($string) use ($hash) { return '[' .'php:function(\'\Transphporm\Parser\CssToXpath::processAttr\', \'' . base64_encode(serialize($string)) . '\', ., "' . $hash . '")' . ']';	}
		];
	}

	private function registerInstance() {
		$hash = spl_object_hash($this);
		self::$instances[$hash] = $this;
		return $hash;
	}

	private function createSelector() {
		$selector = new \stdclass;
		$selector->type = '';
		$selector->string = '';
		return $selector;
	}

	//XPath only allows registering of static functions... this is a hacky workaround for that
	public static function processAttr($attr, $element, $hash) {
		$attr = unserialize(base64_decode($attr));
		$functionSet = self::$instances[$hash]->functionSet;
		$functionSet->setElement($element[0]);

		$attributes = array();
        foreach($element[0]->attributes as $attribute_name => $attribute_node) {
            $attributes[$attribute_name] = $attribute_node->nodeValue;
        }

        $parser = new \Transphporm\Parser\Value($functionSet, true);
		$return = $parser->parseTokens($attr, $attributes);

		return $return[0] === '' ? false : $return[0];
	}

	//split the css into indivudal functions
	private function split($css) {
		$selectors = [];
		$selector = $this->createSelector();
		$selectors[] = $selector;

		foreach ($css as $token) {
			if (in_array($token['type'], $this->specialChars)) {
				$selector = $this->createSelector();
				$selector->type = $token['type'];
				$selectors[] = $selector;
			}
			if (isset($token['value'])) $selectors[count($selectors)-1]->string = $token['value'];
		}
		return $selectors;
	}

	public function getXpath($css) {		
		$css = $this->removeSpacesFromDirectDecend($css)->splitOnToken(Tokenizer::COLON)[0];
		$selectors = $this->split($css);
		$xpath = '/';
		foreach ($selectors as $selector) {
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string, $xpath);
		}

		$xpath = str_replace('/[', '/*[', $xpath);

		return $xpath;
	}

	private function removeSpacesFromDirectDecend($css) {
		$tokens = [];
		foreach ($css->splitOnToken(Tokenizer::GREATER_THAN) as $token) {
			foreach ($token->trim() as $t) $tokens[]  = $t;
			$tokens[] = ['type' => Tokenizer::GREATER_THAN];
		}
		return new Tokens(array_slice($tokens, 0, -1));
	}


	public function getDepth($css) {
		return count($this->split($css));
	}

	public function getPseudo($css) {
		$parts = $css->splitOnToken(Tokenizer::COLON);
		array_shift($parts);
		return $parts;
	}
}
