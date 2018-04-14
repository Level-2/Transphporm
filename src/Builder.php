<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $cache;
	private $time;
	private $modules = [];
	private $config;
	private $filePath;
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

	public function output($data = null, $document = false) {
		$headers = [];

		$elementData = new \Transphporm\Hook\ElementData(new \SplObjectStorage(), $data);
		$functionSet = new FunctionSet($elementData);

		$cachedOutput = $this->loadTemplate();
		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isValidDoc($cachedOutput['body']) ? str_ireplace('<!doctype', '<!DOCTYPE', $cachedOutput['body']) : '<template>' . $cachedOutput['body'] . '</template>' );
		$tssCache = new SheetLoader($this->cache,  $this->filePath, $this->tss, $template->getPrefix(), $this->time);
		$valueParser = new Parser\Value($functionSet);
		$this->config = new Config($functionSet, $valueParser, $elementData, new Hook\Formatter(), new Parser\CssToXpath($functionSet, $template->getPrefix(), md5($this->tss)), $this->filePath, $headers);

		foreach ($this->modules as $module) $module->load($this->config);

		$tssCache->processRules($template, $this->config);

		$result = ['body' => $template->output($document), 'headers' => array_merge($cachedOutput['headers'], $headers)];
		$this->cache->write($this->template, $result);
		$result['body'] = $this->doPostProcessing($template)->output($document);
		return (object) $result;
	}


	//Add a postprocessing hook. This cleans up anything transphporm has added to the markup which needs to be removed
	private function doPostProcessing($template) {
		$template->addHook('//*[@transphporm]', new Hook\PostProcess());
		return $template;
	}


	//Load a template, firstly check if it's a file or a valid string
	private function loadTemplate() {
        $result = ['body' => $this->template, 'headers' => []];
		if (strpos($this->template, "\n") === false && is_file($this->template)) $result = $this->loadTemplateFromFile($this->template);
		return $result;
	}

    private function loadTemplateFromFile($file) {
        $xml = $this->cache->load($this->template, filemtime($this->template));
        return $xml ? $xml : ['body' => file_get_contents($this->template) ?: "", 'headers' => []];
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
