<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\SheetLoader;
//Strings do not support caching because we cannot know when the containing PHP script has been modified
class TSSString implements TSSRules {
	private $str;
	private $filePath;

	public function __construct($str, $filePath) {
		$this->str = $str;
		$this->filePath = $filePath;
	}

	public function updateRequired($data) {
		return true;
	}

	public function getCacheKey($data) {
		return '';
	}

	public function getRules($cssToXpath, $valueParser, $sheetLoader, $indexStart) {
		return (new \Transphporm\Parser\Sheet($this->str, $cssToXpath, $valueParser, $this->filePath, $sheetLoader))->parse($indexStart);
	}

	public function write($rules, $imports = []) {
		return;
	}

	public function setCacheKey($tokens) {
		return;
	}
}