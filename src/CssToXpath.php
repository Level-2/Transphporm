<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
namespace Transphporm;
class CssToXpath {
	private $specialChars = [' ', '.', '>', '~', '#', ':', '[', ']'];
	private $translators = [];
	private $css;
	private $depth;

	public function __construct($css, $prefix = '') {
		$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		$this->translators = [
			' ' => function($string) use ($prefix) { return '//' . $prefix . $string;	},
			'' => function($string) use ($prefix) { return '/' . $prefix . $string;	},
			'>' => function($string) use ($prefix) { return '/' . $prefix  . $string; },
			'#' => function($string) { return '[@id=\'' . $string . '\']'; },
			'.' => function($string) { return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]'; }, 
			'[' => function($string) { return '[@' . $string . ']';	},
			']' => function() {	return ''; }
		];
	}

	private function createSelector() {
		$selector = new \stdclass;
		$selector->type = '';
		$selector->string = '';
		return $selector;
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