<?php
namespace Transphporm\SheetLoader;
interface TSSRules {
	public function updateRequired($data);
	public function getCacheKey($data);
	public function setCacheKey($tokens);
	public function write($rules, $imports = []);
	public function getRules($cssToXpath, $valueParser, $sheetLoader, $indexStart);
}