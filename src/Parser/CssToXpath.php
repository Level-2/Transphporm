<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser;
class CssToXpath {
	private $specialChars = [Tokenizer::WHITESPACE, Tokenizer::DOT, Tokenizer::GREATER_THAN,
		'~', Tokenizer::NUM_SIGN, Tokenizer::OPEN_SQUARE_BRACKET, Tokenizer::MULTIPLY];
	private $translators = [];
	private static $instances = [];
	private $functionSet;
	private $id;

	public function __construct(\Transphporm\FunctionSet $functionSet, $prefix = '', $id = null) {
		$this->id = $id;
		self::$instances[$this->id] = $this;
		$this->functionSet = $functionSet;

		$this->translators = [
			Tokenizer::WHITESPACE => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			Tokenizer::MULTIPLY => function () { return '*'; },
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			Tokenizer::GREATER_THAN => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			Tokenizer::NUM_SIGN => function($string) { return '[@id=\'' . $string . '\']'; },
			Tokenizer::DOT => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; },
			Tokenizer::OPEN_SQUARE_BRACKET => function($string) { return '[' .'php:function(\'\Transphporm\Parser\CssToXpath::processAttr\', \'' . base64_encode(serialize($string)) . '\', ., "' .  $this->id . '")' . ']';	}
		];
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

		$attributes = [];
		foreach($element[0]->attributes as $name => $node) {
			$attributes[$name] = $node->nodeValue;
		}

		$parser = new \Transphporm\Parser\Value($functionSet, true);
		$return = $parser->parseTokens($attr, $attributes);
		return is_array($return[0]) || $return[0] === '' ? false : $return[0];
	}

	public function cleanup() {
		unset(self::$instances[$this->id]);
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
		$css = $this->removeSpacesFromDirectDecend($css)->splitOnToken(Tokenizer::COLON)[0]->trim();
		$selectors = $this->split($css);
		$xpath = '/';
		foreach ($selectors as $selector) {
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string, $xpath);
		}

		$xpath = str_replace('/[', '/*[', $xpath);

		return $xpath;
	}

	private function removeSpacesFromDirectDecend($css) {
		$tokens = new Tokens;
		$split = $css->splitOnToken(Tokenizer::GREATER_THAN);
		$numSplits = count($split);

		if ($numSplits <= 1) return $css;

		for ($i = 0; $i < $numSplits; $i++) {
			$tokens->add($split[$i]->trim());
			if (isset($split[$i+1])) $tokens->add(['type' => Tokenizer::GREATER_THAN]);
		}

		return $tokens;
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
