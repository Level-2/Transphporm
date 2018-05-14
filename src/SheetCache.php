<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
//Separates out TSS file loading/caching from parsing
class SheetCache {
	private $cacheKey;
	private $cacheName;
	private $cache;

	public function __construct(Cache $cache) {
		$this->cache = $cache;
	}

	private function getRulesFromCache($file) {
		//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
		$rules = $this->cache->load($this->cacheName, filemtime($file));

		$this->cacheKey = $this->cacheKey ?? $rules['cacheKey'] ?? null;

		if ($rules) {
			foreach ($rules['import'] as $file) {
				//Check that the import file hasn't been changed since the cache was written
				if (filemtime($file) > $rules['ctime']) return false;
			}
		}
		return $rules;
	}

	public function getCacheKey($tss, $data) {
		//Read the rules so that $this->cacheKey is set
		if (is_file($tss)) $this->getRulesFromCache($tss);
		if ($this->cacheKey) {
			$parser = new Parser\Value($data);
			$parsedKey = $parser->parseTokens($this->cacheKey)[0];
			$this->cacheName = $parsedKey . $this->tss;
			return $parsedKey;
		}
		else return '';
	}


	//write the sheet to cache
    public function write($file, $rules, $imports = []) {
		if (is_file($file)) {
			$existing = $this->cache->load($file, filemtime($file));
			if (isset($existing['import'])) $imports = $existing['import'];
			$this->cache->write($this->cacheName, ['rules' => $rules, 'import' => $imports, 'minFreq' => $this->getMinUpdateFreq($rules), 'ctime' => $this->time, 'cacheKey' => $this->cacheKey]);
		}
		return $rules;
    }

    public function setKey($key) {
    	$this->cacheKey = $key;
    }

}