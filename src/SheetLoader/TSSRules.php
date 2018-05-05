<?php
namespace Transphporm\SheetLoader;
interface TSSRules {
	public function updateRequired($data);
/*	public function getCacheKey($data);
	public function write($file, $rules, $imports = []);
	public function getRules($tss, $cssToXpath, $valueParser, $indexStart = 0);*/
}