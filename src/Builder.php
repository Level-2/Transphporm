<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
/** Builds a Transphporm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $cache;
	private $time;
	private $modules = [];
	private $config;
	private $filePath;
	private $cacheKey;
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
		$this->filePath = new FilePath();
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

	public function setLocale($locale) {
        $format = new \Transphporm\Module\Format($locale);
        $this->modules[get_class($format)] = $format;
    }

	public function addPath($dir) {
		$this->filePath->addPath($dir);
	}

	private function getSheetLoader() {
		$tssRules = is_file($this->tss) ? new SheetLoader\TSSFile($this->tss, $this->filePath, $this->cache, $this->time) : new SheetLoader\TSSString($this->tss, $this->filePath);
		return new SheetLoader\SheetLoader($this->cache, $this->filePath, $tssRules, $this->time);
	}

	public function output($data = null, $document = false) {
		$headers = [];

		$tssCache = $this->getSheetLoader();
		$this->cacheKey = $tssCache->getCacheKey($data);
		$result = $this->loadTemplate();
		//If an update is required, run any rules that need to be run. Otherwise, return the result from cache
		//without creating any further objects, loading a DomDocument, etc
		if (empty($result['renderTime']) || $tssCache->updateRequired($data) === true) {
			$template = $this->createAndProcessTemplate($data, $result['cache'], $headers);
			$tssCache->processRules($template, $this->config);

			$result = ['cache' => $template->output($document),
			   'renderTime' => time(),
			   'headers' => array_merge($result['headers'], $headers),
			   'body' => $this->doPostProcessing($template)->output($document)
			];
			$this->cache->write($tssCache->getCacheKey($data) . $this->template, $result);
		}
		unset($result['cache'], $result['renderTime']);
		return (object) $result;
	}

	private function createAndProcessTemplate($data, $body, &$headers) {
		$elementData = new \Transphporm\Hook\ElementData(new \SplObjectStorage(), $data);
		$functionSet = new FunctionSet($elementData);
		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isValidDoc($body) ? str_ireplace('<!doctype', '<!DOCTYPE', $body) : '<template>' . $body . '</template>' );

		$valueParser = new Parser\Value($functionSet);
		$this->config = new Config($functionSet, $valueParser, $elementData, new Hook\Formatter(), new Parser\CssToXpath($functionSet, $template->getPrefix(), md5($this->tss)), $this->filePath, $headers);

		foreach ($this->modules as $module) $module->load($this->config);
		return $template;
	}

	//Add a postprocessing hook. This cleans up anything transphporm has added to the markup which needs to be removed
	private function doPostProcessing($template) {
		$template->addHook('//*[@transphporm]', new Hook\PostProcess());
		return $template;
	}


	//Load a template, firstly check if it's a file or a valid string
	private function loadTemplate() {
        $result = ['cache' => $this->template, 'headers' => []];
		if (strpos($this->template, "\n") === false && is_file($this->template)) $result = $this->loadTemplateFromFile($this->template);
		return $result;
	}

    private function loadTemplateFromFile($file) {
        $xml = $this->cache->load($this->cacheKey . $file, filemtime($file));
        return $xml ? $xml : ['cache' => file_get_contents($file) ?: "", 'headers' => []];
    }

	public function setCache(\ArrayAccess $cache) {
		$this->cache = new Cache($cache);
	}

	private function isValidDoc($xml) {
		return (strpos($xml, '<!') === 0 && strpos($xml, '<!--') !== 0) || strpos($xml, '<?') === 0 || strpos($xml, '<html') === 0;
	}

	public function __destruct() {
		//Required hack as DomXPath can only register static functions clear the statically stored instance to avoid memory leaks
		if (isset($this->config)) $this->config->getCssToXpath()->cleanup();
	}
}
