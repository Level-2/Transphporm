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
    private $prefix;
    private $sheet;
    private $time;
    private $import = [];

    public function __construct(Cache $cache, FilePath $filePath, $tss, $prefix, $time) {
        $this->cache = $cache;
        $this->filePath = $filePath;
        $this->prefix = $prefix;
        $this->tss = $tss;
        $this->time = $time;
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

	public function addImport($import) {
		$this->import[] = $import;
	}

	private function getCacheKey($file) {
		return $file . $this->prefix . dirname(realpath($file)) . DIRECTORY_SEPARATOR;
	}
	//write the sheet to cache
    public function write($file, $rules, $imports = []) {
		if (is_file($file)) {
			$key = $this->getCacheKey($file);
			$existing = $this->cache->load($key, filemtime($file));
			if (isset($existing['import']) && empty($imports)) $imports = $existing['import'];
			$this->cache->write($key, ['rules' => $rules, 'import' => $imports]);
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
		return (new Parser\Sheet($tss, $cssToXpath, $valueParser, $this->filePath, $this))->parse();
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
