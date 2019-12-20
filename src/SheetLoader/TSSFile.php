<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\SheetLoader;
class TSSFile implements TSSRules {
	private $fileName;
	private $filePath;
	private $cacheName;
	private $cacheKey;
	private $cache;
	private $time;

	public function __construct($fileName, \Transphporm\FilePath $filePath, $cache, $time) {
		$this->fileName = $fileName;
		$this->filePath = $filePath;
		$this->cache = $cache;
	    $this->time = isset($time) ? $time : time();
	    $this->cacheName = $this->fileName;
	}

	private function getRulesFromCache($file) {
		//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
		$rules = $this->cache->load($this->cacheName, filemtime($file));

		if (!isset($this->cacheKey) && isset($rules['cacheKey'])) {
			$this->cacheKey = $rules['cacheKey'];
		}

		if ($rules) {
			foreach ($rules['import'] as $file) {
				//Check that the import file hasn't been changed since the cache was written
				if (filemtime($file) > $rules['ctime']) return false;
			}
		}

		return $rules;
	}

	public function setCacheKey($tokens) {
		$this->cacheKey = $tokens;
	}

	public function updateRequired($data) {
		$this->cacheName = $this->getCacheKey($data);

		$rules = $this->getRulesFromCache($this->fileName);
		//Nothing was cached or the TSS file has changed, update is required
		if (empty($rules)) return true;

		//Find the sheet's minimum update-frequency, if it hasn't passed then no updates are required
		if ($rules['ctime']+$rules['minFreq'] <= $this->time) return true;

		return false;
	}

	public function getCacheKey($data) {
		$this->getRulesFromCache($this->fileName);
		if ($this->cacheKey) {
			$parser = new \Transphporm\Parser\Value($data);
			$cacheKey = $parser->parseTokens($this->cacheKey)[0];
			$this->cacheName = $cacheKey . $this->fileName;
			return $cacheKey;
		}
		else return $this->fileName;
	}

	public function getRules($cssToXpath, $valueParser, $sheetLoader, $indexStart) {
		$rules = $this->getRulesFromCache($this->fileName);
		$this->filePath->addPath(dirname(realpath($this->fileName)));
		if (empty($rules)) $tss = file_get_contents($this->fileName);
		else return $rules['rules'];

		return $tss == null ? [] : (new \Transphporm\Parser\Sheet($tss, $cssToXpath, $valueParser, $this->filePath, $sheetLoader))->parse($indexStart);
	}

	//write the sheet to cache
    public function write($rules, $imports = []) {
		$existing = $this->cache->load($this->fileName, filemtime($this->fileName));
		if (isset($existing['import']) && empty($imports)) $imports = $existing['import'];
		$this->cache->write($this->cacheName, ['rules' => $rules, 'import' => $imports, 'minFreq' => $this->getMinUpdateFreq($rules), 'ctime' => $this->time, 'cacheKey' => $this->cacheKey]);

		return $rules;
    }

    //Gets the minimum update-frequency for a sheet's rules
	private function getMinUpdateFreq($rules) {
		$min = \PHP_INT_MAX;

		foreach ($rules as $rule) {
			$ruleFreq = $rule->getUpdateFrequency();
			if ($ruleFreq < $min) $min = $ruleFreq;
		}

		return $min;
	}
}