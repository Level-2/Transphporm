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
	private $userCache;

	public function __construct($template, $tss = '') {
		$this->template = $template;
		$this->tss = $tss;
		$this->cache = new FileCache(new \ArrayObject());
	}

	public function output($data = null, $document = false) {
		$locale = $this->getLocale();
		$data = new Hook\DataFunction(new \SplObjectStorage(), $data, $locale, $this->baseDir);
		$headers = [];
		$this->registerProperties($this->getBasicProperties($data, $locale, $headers));

		$cachedOutput = $this->loadTemplate();
		$xml = $cachedOutput['body'];
		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isValidDoc($xml) ? $xml : '<template>' . $xml . '</template>' );
		$time = time();

		foreach ($this->getRules($template) as $rule) {
			if ($rule->shouldRun($time)) {
				$rule->touch();
				$hook = new Hook\Rule($rule->properties, new Hook\PseudoMatcher($rule->pseudo, $data), $data);
				foreach ($this->registeredProperties as $properties) $hook->registerProperties($properties);
				$template->addHook($rule->query, $hook);
			}			
		}
		
		$output = $template->output($document);	
		$result = ['headers' => array_merge($cachedOutput['headers'], $headers), 'body' => $output];
		$this->cache->write($this->template, $result);
		return (object) $result;
	}

	private function loadTemplate() {
		if (trim($this->template)[0] !== '<') {			
			$xml = $this->cache->load($this->template, filemtime($this->template));
			return $xml ? $xml : $this->cache->write($this->template, ['body' => file_get_contents($this->template), 'headers' => []]);
		}
		else return ['body' => $this->template, 'headers' => []];	
	}

	private function getRules($template) {		
		if (is_file($this->tss)) {
			$this->baseDir = dirname(realpath($this->tss)) . DIRECTORY_SEPARATOR;
			$key = $this->tss . $template->getPrefix() . $this->baseDir;
			$rules = $this->cache->load($key, filemtime($this->tss));
			if (!$rules) return $this->cache->write($key, (new Sheet(file_get_contents($this->tss), $this->baseDir, $template->getPrefix()))->parse());
			else return $rules;
		}
		else return (new Sheet($this->tss, $this->baseDir, $template->getPrefix()))->parse();
	}

	private function getBasicProperties($data, $locale, &$headers) {
		$basicProperties = new Hook\BasicProperties($data, $headers);
		$basicProperties->registerFormatter(new Formatter\Number($locale));
		$basicProperties->registerFormatter(new Formatter\Date($locale));
		$basicProperties->registerFormatter(new Formatter\StringFormatter());
		foreach ($this->formatters as $formatter) $basicProperties->registerFormatter($formatter);

		return isset($this->userCache) ? new Hook\Cache($basicProperties, $this->userCache) : $basicProperties;
	}

	public function setCache(\ArrayAccess $cache) {
		$this->cache = new FileCache($cache);
	}

	private function getLocale() {
		if (is_array($this->locale)) return $this->locale;
		else if (strlen($this->locale) > 0) return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . $this->locale . '.json'), true);
		else return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . 'enGB.json'), true);
	}

	public function registerProperties($object) {
		$this->registeredProperties[] = $object;
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
