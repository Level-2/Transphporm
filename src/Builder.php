<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $baseDir;
	private $cache;
	private $time;
	private $modules = [];
	private $defaultModules = [
		'\\Transphporm\\Module\\Basics',
		'\\Transphporm\\Module\\Pseudo',
		'\\Transphporm\\Module\\Format',
		'\\Transphporm\\Module\\Functions'
	];

	public function __construct($template, $tss = '', $modules = null) {
		$this->template = $template;
		$this->tss = $tss;
		$this->cache = new Cache(new \ArrayObject());

		$modules = is_array($modules) ? $modules : $this->defaultModules;
		foreach ($modules as $module) $this->loadModule(new $module);
	}

	//Allow setting the time used by Transphporm for caching. This is for testing purposes
	//Would be better if PHP allowed setting the script clock, but this is the simplest way of overriding it
	public function setTime($time) {
		$this->time = $time;
	}

	public function loadModule(Module $module) {
		$this->modules[get_class($module)] = $module;
	}

	public function output($data = null, $document = false) {
		$headers = [];

		$elementData = new \Transphporm\Hook\ElementData(new \SplObjectStorage(), $data);

		$data = new FunctionSet($elementData);
		$config = new Config($data, $elementData, new Hook\Formatter(), $headers, $this->baseDir);

		foreach ($this->modules as $module) $module->load($config);

		$cachedOutput = $this->loadTemplate();
		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isValidDoc($cachedOutput['body']) ? str_ireplace('<!doctype', '<!DOCTYPE', $cachedOutput['body']) : '<template>' . $cachedOutput['body'] . '</template>' );

		$this->processRules($template, $data, $config, $data);

		$result = ['body' => $template->output($document), 'headers' => array_merge($cachedOutput['headers'], $headers)];
		$this->cache->write($this->template, $result);
		$result['body'] = $this->doPostProcessing($template)->output($document);

		return (object) $result;
	}

	private function processRules($template, $data, $config, $functionSet) {
		$valueParser = new Parser\Value($data);
		$rules = $this->getRules($template, $valueParser);

		foreach ($rules as $rule) {
			if ($rule->shouldRun($this->time)) $this->executeTssRule($rule, $template, $valueParser, $config, $functionSet);
		}
	}

	//Add a postprocessing hook. This cleans up anything transphporm has added to the markup which needs to be removed
	private function doPostProcessing($template) {
		$template->addHook('//*[@transphporm]', new Hook\PostProcess());
		return $template;
	}

	//Process a TSS rule e.g. `ul li {content: "foo"; format: bar}
	private function executeTssRule($rule, $template, $valueParser, $config, $functionSet) {
		$rule->touch();
		$pseudoMatcher = $config->createPseudoMatcher($rule->pseudo);

		$hook = new Hook\PropertyHook($rule->properties, $pseudoMatcher, $valueParser, $functionSet);
		$config->loadProperties($hook);
		$template->addHook($rule->query, $hook);
	}

	//Load a template, firstly check if it's a file or a valid string
	private function loadTemplate() {
		if (trim($this->template)[0] !== '<') {
			$xml = $this->cache->load($this->template, filemtime($this->template));
			return $xml ? $xml : ['body' => file_get_contents($this->template), 'headers' => []];
		}
		else return ['body' => $this->template, 'headers' => []];
	}

	//Load the TSS rules either from a file or as a string
	//N.b. only files can be cached
	private function getRules($template, $valueParser) {
		if (is_file($this->tss)) {
			$this->baseDir = dirname(realpath($this->tss)) . DIRECTORY_SEPARATOR;
			//The cache for the key: the filename and template prefix
			//Each template may have a different prefix which changes the parsed TSS,
			//Because of this the cache needs to be generated for each template prefix.
			$key = $this->tss . $template->getPrefix() . $this->baseDir;
			//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
			$rules = $this->cache->load($key, filemtime($this->tss));
			if (!$rules) return $this->cache->write($key, (new Parser\Sheet(file_get_contents($this->tss), $this->baseDir, $valueParser, $template->getPrefix()))->parse());
			else return $rules;
		}
		else return (new Parser\Sheet($this->tss, $this->baseDir, $valueParser, $template->getPrefix()))->parse();
	}

	public function setCache(\ArrayAccess $cache) {
		$this->cache = new Cache($cache);
	}

	private function isValidDoc($xml) {
		return strpos($xml, '<!') === 0 || strpos($xml, '<?') === 0;
	}
}
