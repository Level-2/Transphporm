<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
class CssToXpath {
	private $specialChars = [' ', '.', '>', '~', '#', ':', '[', ']'];
	private $translators = [];
	private $css;
	private $depth;
	private $valueParser;

	public function __construct($css, ValueParser $valueParser, $prefix = '') {
		$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		$this->valueParser = $valueParser;
		$this->translators = [
			' ' => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			'>' => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			'#' => function($string) { return '[@id=\'' . $string . '\']'; },
			'.' => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; }, 
			'[' => function($string) { return '[@' . $this->parseAttr($string) . ']';	},
			']' => function() {	return ''; }
		];
	}

	private function createSelector() {
		$selector = new \stdclass;
		$selector->type = '';
		$selector->string = '';
		return $selector;
	}

	private function parseAttr($attr) {
		$comparators = ['!=', '='];
		foreach ($comparators as $comparator) {
			if (strpos($attr, $comparator) !== false) {
				$parts = explode($comparator, $attr);
				$parts = array_map(function($val) {
					return implode($this->valueParser->parse($val));
				}, $parts);
				if (isset($parts[1])) $parts[1] = '"' . $parts[1] . '"';
				return implode($comparator, $parts);
			}
		}

		return $attr;
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
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string);
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