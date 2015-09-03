<?php
namespace CDS;
class CssToXpath {
	private $specialChars = [' ', '.', '>', '~', '#', ':'];
	private $translators = [];
	private $css;

	public function __construct($css) {
		//Translation functions for css -> xpath conversion
		//Can't have functions with these names so make them closures.
		$this->css = str_replace([' >', '> '],['>', '>'], trim($css));
		$this->translators = [
			' ' => function($string) {
				return '//' . $string;
			},
			'' => function($string) {
				return '/' . $string;
			},

			'>' => function($string) {
				return '/' . $string;
			},
			'#' => function($string) {
				return '[@id=\'' . $string . '\']';
			},
			'.' => function($string) {
				return '[contains(concat(\' \', normalize-space(@class), \' \'), \' ' . $string . ' \')]';
			}
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
		$xpath = '/';
		foreach ($selectors as $selector) {
			if (isset($this->translators[$selector->type])) $xpath .= $this->translators[$selector->type]($selector->string);
		}

		$xpath = str_replace('/[', '/*[', $xpath);
		return $xpath;
	}

	public function getPseudo() {
		$parts = explode(':', $this->css);
		return isset($parts[1]) ? $parts[1] : '';
	}
}