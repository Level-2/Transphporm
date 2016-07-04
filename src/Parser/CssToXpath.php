<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
class CssToXpath {
	private $specialChars = [' ', '.', '>', '~', '#', ':', '[', ']'];
	private $translators = [];
	private $css;
	private $valueParser;
	private static $instance;
	private $functionSet;


	public function __construct(Value $valueParser, \Transphporm\FunctionSet $functionSet, $prefix = '') {
		$this->registerInstance();
		$this->valueParser = $valueParser;
		$this->functionSet = $functionSet;

		$this->translators = [
			' ' => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			'>' => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			'#' => function($string) { return '[@id=\'' . $string . '\']'; },
			'.' => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; },
			'[' => function($string) { return '[' .'php:function(\'\Transphporm\Parser\CssToXpath::processAttr\', \'' . $string . '\', .)' . ']';	},
			']' => function() {	return ''; }
		];
	}

	private function registerInstance() {
		self::$instance = $this;
	}

	private function createSelector() {
		$selector = new \stdclass;
		$selector->type = '';
		$selector->string = '';
		return $selector;
	}

	//XPath only allows registering of static functions... this is a hacky workaround for that
	public static function processAttr($attr, $element) {
		$comparators = ['!=', '='];
		$valueParser = self::$instance->valueParser;
		self::$instance->functionSet->setElement($element[0]);

		foreach ($comparators as $comparator) {
			if (strpos($attr, $comparator) !== false) {
				$parts = explode($comparator, $attr);
				$parts = array_map(function($val) use ($valueParser) {
						return $valueParser->parse($val)[0];
				}, $parts);

				return self::compare($comparator, $element[0]->getAttribute($parts[0]), $parts[1]);
			}
		}
		return $attr;
	}

	private static function compare($comparator, $a, $b) {
		if ($comparator == '=') return $a == $b;
		else if ($comparator == '!=') return $a != $b;
	}

	//split the css into indivudal functions
	private function split($css) {
		$selectors = [];
		$selector = $this->createSelector();
		$selectors[] = $selector;

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
		$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		$css = explode(':', $this->css)[0];
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

	public function getPseudo() {
		$parts = explode(':', $this->css);
		array_shift($parts);
		return $parts;
	}
}
