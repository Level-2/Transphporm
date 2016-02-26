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
	private $depth;
	private $valueParser;
	private static $instances = [];


	public function __construct($css, Value $valueParser, $prefix = '') {
		$hash = $this->registerInstance();
		$this->valueParser = $valueParser;
		$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		$this->translators = [
			' ' => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			'>' => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			'#' => function($string) { return '[@id=\'' . $string . '\']'; },
			'.' => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; }, 
			'[' => function($string) use ($hash) { return '[' .'php:function(\'\Transphporm\Parser\CssToXpath::processAttr\', \'' . $string . '\', ., "' . $hash . '")' . ']';	},
			']' => function() {	return ''; }
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
		$comparators = ['!=', '='];
		$valueParser = self::$instances[$hash]->valueParser;
		foreach ($comparators as $comparator) {
			if (strpos($attr, $comparator) !== false) {
				$parts = explode($comparator, $attr);
				$parts = array_map(function($val) use ($valueParser, $element) {
					return $valueParser->parse($val, $element[0])[0];
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

	public function getXpath() {
		$css = explode(':', $this->css)[0];
		$selectors = $this->split($css);
		$this->depth = count($selectors);
		$xpath = '/';
		foreach ($selectors as $selector) {
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string, $xpath);
		}

		$xpath = str_replace('/[', '/*[', $xpath);

		return $xpath;
	}

	public function getDepth() {
		return $this->depth;
	}
	
	public function getPseudo() {
		$parts = explode(':', $this->css);
		array_shift($parts);
		return $parts;
	}
}