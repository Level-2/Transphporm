<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
class TSSCache {
    private $cache;
    private $prefix;

    public function __construct(Cache $cache, $prefix) {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

	private function getRulesFromCache($file) {
		//The cache for the key: the filename and template prefix
		//Each template may have a different prefix which changes the parsed TSS,
		//Because of this the cache needs to be generated for each template prefix.
		$key = $this->getCacheKey($file);
		//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
		$rules = $this->cache->load($key, filemtime($file));
		if ($rules) {
			foreach ($rules['import'] as $file) {
				if (!$this->cache->load($this->getCacheKey($file), filemtime($file))) return false;
			}
		}
		return $rules;
	}

	private function getCacheKey($file) {
		return $file . $this->prefix . dirname(realpath($file)) . DIRECTORY_SEPARATOR;
	}

    public function load($tss) {
        return $this->getRulesFromCache($tss);
    }

    public function write($file, $rules, $imports = []) {
        if (is_file($file)) {
        	$key = $this->getCacheKey($file);
        	$existing = $this->cache->load($key, filemtime($file));
        	if (isset($existing['import']) && empty($imports)) $imports = $existing['import'];
        	$this->cache->write($key, ['rules' => $rules, 'import' => $imports]);
        }
        return $rules;
    }
}
