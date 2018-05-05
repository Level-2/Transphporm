<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\SheetLoader;
//Separates out TSS file loading/caching from parsing
class SheetLoader {
    private $tss;
    private $filePath;
    private $time;
    private $import = [];

    public function __construct(\Transphporm\Cache $cache, \Transphporm\FilePath $filePath, TSSRules $tss, $time) {
    	$this->cache = $cache;
        $this->filePath = $filePath;
        $this->tss = $tss;
        $this->time = $time ?? time();
    }

	//Allows controlling whether any updates are required to the template
	//e.g. return false
	//	 1. If all update-frequencies  haven't expired
	//   2. If the data hasn't changed since the last run
	//If this function returns false, the rendered template is sent straight from the cache skipping 99% of transphporm's code
	public function updateRequired($data) {
		return $this->tss->updateRequired($data);
	}

	public function addImport($import) {
		$this->filePath->addPath(dirname(realpath($this->filePath->getFilePath($import))));
		$this->import[] = $import;
	}

	public function setCacheKey($tokens) {
		$this->tss->setCacheKey($tokens);
	}

	public function getCacheKey($data) {
		return $this->tss->getCacheKey($data);
	}


	public function processRules($template, \Transphporm\Config $config) {
		$rules = $this->getRules($config->getCssToXpath(), $config->getValueParser());


		usort($rules, [$this, 'sortRules']);

		foreach ($rules as $rule) {
			if ($rule->shouldRun($this->time)) $this->executeTssRule($rule, $template, $config);
		}

		//if (is_file($this->tss)) $this->write($this->tss, $rules, $this->import);
		$this->tss->write($rules, $this->import);
	}

	//Load the TSS
	public function getRules($cssToXpath, $valueParser, $indexStart = 0) {
		return $this->tss->getRules($cssToXpath, $valueParser, $this, $indexStart);
	}

	//Process a TSS rule e.g. `ul li {content: "foo"; format: bar}
	private function executeTssRule($rule, $template, $config) {
		$rule->touch();

		$pseudoMatcher = $config->createPseudoMatcher($rule->pseudo);
		$hook = new \Transphporm\Hook\PropertyHook($rule->properties, $config->getLine(), $rule->file, $rule->line, $pseudoMatcher, $config->getValueParser(), $config->getFunctionSet(), $config->getFilePath());
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
		return count($a->pseudo) > count($b->pseudo)  ? 1 : -1;
	}
}
