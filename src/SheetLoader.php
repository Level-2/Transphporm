<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
//Separates out TSS file loading/caching from parsing
class SheetLoader {
    private $cache;
    private $sheet;
    private $time;
    private $import = [];
    private $cacheKey;
    private $rules;
    private $cacheName;

    public function __construct(Cache $cache, FilePath $filePath, $tss, $time) {
    	$this->cache = $cache;
        $this->filePath = $filePath;
        $this->tss = $tss;
        $this->time = $time ?? time();
        $this->cacheName = $tss;
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
	//Allows controlling whether any updates are required to the template
	//e.g. return false
	//	 1. If all update-frequencies  haven't expired
	//   2. If the data hasn't changed since the last run
	//If this function returns false, the rendered template is sent straight from the cache skipping 99% of transphporm's code
	public function updateRequired($data) {
		if (!is_file($this->tss)) return true;

		$this->cacheName = $this->getCacheKey($data) . $this->tss;

		$rules = $this->getRulesFromCache($this->tss, $data);
		//Nothing was cached or the TSS file has changed, update is required
		if (empty($rules)) return true;

		//Find the sheet's minimum update-frequency, if it hasn't passed then no updates are required
		if ($rules['ctime']+$rules['minFreq'] <= $this->time) return true;

		return false;
	}

	//Gets the minimum update-frequency for a sheet's rules
	public function getMinUpdateFreq($rules) {
		$min = \PHP_INT_MAX;

		foreach ($rules as $rule) {
			$ruleFreq = $rule->getUpdateFrequency();
			if ($ruleFreq < $min) $min = $ruleFreq;
		}

		return $min;
	}

	public function addImport($import) {
		$this->import[] = $import;
	}

	public function setCacheKey($tokens) {
		$this->cacheKey = $tokens;
	}

	public function getCacheKey($data) {
		if (is_file($this->tss)) $this->getRulesFromCache($this->tss);
		if ($this->cacheKey) {
			$parser = new Parser\Value($data);
			$x= $parser->parseTokens($this->cacheKey)[0];
			$this->cacheName = $x . $this->tss;
			return $x;
		}
		else return '';
	}

	//write the sheet to cache
    public function write($file, $rules, $imports = []) {
		if (is_file($file)) {
			$existing = $this->cache->load($file, filemtime($file));
			if (isset($existing['import']) && empty($imports)) $imports = $existing['import'];
			$this->cache->write($this->cacheName, ['rules' => $rules, 'import' => $imports, 'minFreq' => $this->getMinUpdateFreq($rules), 'ctime' => $this->time, 'cacheKey' => $this->cacheKey]);
		}
		return $rules;
    }

	public function processRules($template, \Transphporm\Config $config) {
		$rules = $this->getRules($this->tss, $config->getCssToXpath(), $config->getValueParser());

		usort($rules, [$this, 'sortRules']);
		foreach ($rules as $rule) {
			if ($rule->shouldRun($this->time)) $this->executeTssRule($rule, $template, $config);
		}

		if (is_file($this->tss)) $this->write($this->tss, $rules, $this->import);
	}

	//Load the TSS
	public function getRules($tss, $cssToXpath, $valueParser) {
		if (is_file($tss)) {
    		//$rules = $this->cache->load($tss);
    		$rules = $this->getRulesFromCache($tss)['rules'];
			$this->filePath->addPath(dirname(realpath($tss)));
			if (empty($rules)) $tss = file_get_contents($tss);
			else return $rules;
    	}
		return $tss == null ? [] : (new Parser\Sheet($tss, $cssToXpath, $valueParser, $this->filePath, $this))->parse();
	}
	//Process a TSS rule e.g. `ul li {content: "foo"; format: bar}
	private function executeTssRule($rule, $template, $config) {
		$rule->touch();

		$pseudoMatcher = $config->createPseudoMatcher($rule->pseudo);
		$hook = new Hook\PropertyHook($rule->properties, $config->getLine(), $rule->file, $rule->line, $pseudoMatcher, $config->getValueParser(), $config->getFunctionSet(), $config->getFilePath());
		$config->loadProperties($hook);
		$template->addHook($rule->query, $hook);
	}


	private function sortRules($a, $b) {
		//If they have the same depth, compare on index
		if ($a->query === $b->query) return $this->sortPseudo($a, $b);

		if ($a->depth === $b->depth) $property = 'index';
		else $property = 'depth';

		return ($a->$property < $b->$property) ? -1 : 1;
	}


	private function sortPseudo($a, $b) {
		return count($a->pseudo) < count($b->pseudo)  ? -1  :1;
	}
}
