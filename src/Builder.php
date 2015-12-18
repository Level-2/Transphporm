<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $registeredProperties = [];
	private $formatters = [];
	private $locale;
	private $baseDir;
	private $cache;
	private $time;

	public function __construct($template, $tss = '') {
		$this->template = $template;
		$this->tss = $tss;
		$this->cache = new Cache(new \ArrayObject());
	}

	//Allow setting the time used by Transphporm for caching. This is for testing purposes
	//Would be better if PHP allowed setting the script clock, but this is the simplest way of overriding it
	public function setTime($time) {
		$this->time = $time;
	}

	public function output($data = null, $document = false) {
		$locale = $this->getLocale();
		$data = new Hook\DataFunction(new \SplObjectStorage(), $data, $locale, $this->baseDir);
		$headers = [];
		$this->registerProperty('content', $this->getContentProperty($data, $locale, $headers));
		$this->registerProperty('repeat', new Property\Repeat($data));
		$this->registerProperty('display', new Property\Display);
		$this->registerProperty('bind', new Property\Bind($data));

		$cachedOutput = $this->loadTemplate();
		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isValidDoc($cachedOutput['body']) ? $cachedOutput['body'] : '<template>' . $cachedOutput['body'] . '</template>' );

		//Allow $time to be set via arguments to spoof time passage during tests
		foreach ($this->getRules($template) as $rule) {
			if ($rule->shouldRun($this->time)) $this->executeTssRule($rule, $template, $data);			
		}
		
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

	//Process a TSS rule e.g. `ul li {content: "foo"; format: bar}
	private function executeTssRule($rule, $template, $data) {
		$rule->touch();
		$hook = new Hook\Rule($rule->properties, new Hook\PseudoMatcher($rule->pseudo, $data), $data);
		foreach ($this->registeredProperties as $name => $property) $hook->registerProperty($name, $property);
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
	private function getRules($template) {		
		if (is_file($this->tss)) {
			$this->baseDir = dirname(realpath($this->tss)) . DIRECTORY_SEPARATOR;
			//The cache for the key: the filename and template prefix
			//Each template may have a different prefix which changes the parsed TSS,
			//Because of this the cache needs to be generated for each template prefix.
			$key = $this->tss . $template->getPrefix() . $this->baseDir;
			//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
			$rules = $this->cache->load($key, filemtime($this->tss));
			if (!$rules) return $this->cache->write($key, (new Sheet(file_get_contents($this->tss), $this->baseDir, $template->getPrefix()))->parse());
			else return $rules;
		}
		else return (new Sheet($this->tss, $this->baseDir, $template->getPrefix()))->parse();
	}

	private function getContentProperty($data, $locale, &$headers) {
		$formatter = new Hook\Formatter();
		$formatter->register(new Formatter\Number($locale));
		$formatter->register(new Formatter\Date($locale));
		$formatter->register(new Formatter\StringFormatter());
		
		foreach ($this->formatters as $format) $formatter->register($format);

		$basicProperties = new Property\Content($data, $headers, $formatter);

		return $basicProperties;
	}

	public function setCache(\ArrayAccess $cache) {
		$this->cache = new Cache($cache);
	}

	private function getLocale() {
		if (is_array($this->locale)) return $this->locale;
		else if (strlen($this->locale) > 0) return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . $this->locale . '.json'), true);
		else return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . 'enGB.json'), true);
	}

	public function registerProperty($name, Property $property) {
		$this->registeredProperties[$name] = $property;
	}

	public function registerFormatter($formatter) {
		$this->formatters[] = $formatter;
	}

	public function setLocale($locale) {
		$this->locale = $locale;
	}

	private function isValidDoc($xml) {
		return strpos($xml, '<!') === 0 || strpos($xml, '<?') === 0;
	}
}
