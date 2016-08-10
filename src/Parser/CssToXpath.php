<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class CssToXpath {
	private $specialChars = [Tokenizer::WHITESPACE, Tokenizer::DOT, Tokenizer::GREATER_THAN, '~', Tokenizer::NUM_SIGN, Tokenizer::COLON, Tokenizer::OPEN_SQUARE_BRACKET];
	private $translators = [];
	private $css;
	private $valueParser;
	private static $instances = [];
	private $functionSet;


	public function __construct(Value $valueParser, \Transphporm\FunctionSet $functionSet, $prefix = '') {
		$hash = $this->registerInstance();
		$this->valueParser = $valueParser;
		$this->functionSet = $functionSet;

		$this->translators = [
			Tokenizer::WHITESPACE => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			Tokenizer::GREATER_THAN => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			Tokenizer::NUM_SIGN => function($string) { return '[@id=\'' . $string . '\']'; },
			Tokenizer::DOT => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; },
			Tokenizer::OPEN_SQUARE_BRACKET => function($string) use ($hash) { return '[' .'php:function(\'\Transphporm\Parser\CssToXpath::processAttr\', \'' . json_encode($string) . '\', ., "' . $hash . '")' . ']';	},
			//']' => function() {	return ''; }
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
		$attr = json_decode($attr, true);
		$valueParser = self::$instances[$hash]->valueParser;
		self::$instances[$hash]->functionSet->setElement($element[0]);

		$parts = self::$instances[$hash]->splitOnToken($attr, Tokenizer::EQUALS);

		if ($parts[0] === $attr) return $element[0]->getAttribute($valueParser->parseTokens($attr)[0]) !== '';

		if ($parts[0][count($parts[0])-1]['type'] === Tokenizer::NOT) {
			$attr = [
				['type' => Tokenizer::NAME, 'value' => 'attr'],
				['type' => Tokenizer::OPEN_BRACKET, 'value' => $parts[0]],
				['type' => Tokenizer::NOT],
				['type' => Tokenizer::EQUALS]
			];
			$attr = array_merge($attr, $parts[1]);
		}
		else {
			$attr = [
				['type' => Tokenizer::NAME, 'value' => 'attr'],
				['type' => Tokenizer::OPEN_BRACKET, 'value' => $parts[0]],
				['type' => Tokenizer::EQUALS]
			];
			$attr = array_merge($attr, $parts[1]);
		}
		return $valueParser->parseTokens($attr)[0];
	}

	private function splitOnToken($tokens, $splitOn) {
		$splitTokens = [];
		$i = 0;
		foreach ($tokens as $token) {
			if ($token['type'] === $splitOn) $i++;
			else $splitTokens[$i][] = $token;
		}
		return $splitTokens;
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

		for ($i = 0; $i < strlen($css); $i++) {
			if (in_array($css[$i], $this->specialChars)) {
				$selector = $this->createSelector();
				$selector->type = $css[$i];
				$selectors[] = $selector;
			}
			else $selector->string .= $css[$i];
		}
		return $selectors;
	}

	public function getXpath($css) {
		//$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		foreach ($css as $key => $token) {
			if ($token['type'] === Tokenizer::WHITESPACE &&
				(isset($css[$key+1]) && $css[$key+1]['type'] === Tokenizer::GREATER_THAN)) unset($css[$key]);
			else if ($token['type'] === Tokenizer::WHITESPACE &&
				(isset($css[$key-1]) && $css[$key-1]['type'] === Tokenizer::GREATER_THAN)) unset($css[$key]);
		}
		$css = $this->splitOnToken(array_values($css), Tokenizer::COLON)[0];
		$selectors = $this->split($css);
		$xpath = '/';
		foreach ($selectors as $selector) {
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string, $xpath);
		}

		$xpath = str_replace('/[', '/*[', $xpath);

		return $xpath;
	}

	public function getDepth($css) {
		return count($this->split($css));
	}

	public function getPseudo($css) {
		$parts = $this->splitOnToken($css, Tokenizer::COLON);
		array_shift($parts);
		return $parts;
	}
}
