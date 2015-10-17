<?php
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $registeredProperties = [];
	private $formatters = [];
	private $isFile = false;
	private $locale;
	private $baseDir;

	public function __construct($template, $tss = '') {
		if (trim($template)[0] !== '<') {
			$this->template = file_get_contents($template);
			if ($tss) {
				$this->baseDir = dirname(realpath($tss)) . DIRECTORY_SEPARATOR;
				$this->tss = file_get_contents($tss);	
			} 
			$this->isFile = true;
		}
		else {
			$this->template =  $template;
			$this->tss = $tss;
		}	
	}

	public function output($data = null, $document = false) {
		$locale = $this->getLocale();
		$data = new Hook\DataFunction(new \SplObjectStorage(), $data, $locale, $this->baseDir);
		$headers = [];
		$this->registerProperties($this->getBasicProperties($data, $locale, $headers));

		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		$template = new Template($this->isFile ? $this->template : '<template>' . $this->template . '</template>');
		$rules = (new Sheet($this->tss, $this->baseDir))->parse();

		foreach ($rules as $rule) {
			$hook = new Hook\Rule($rule->properties, new Hook\PseudoMatcher($rule->pseudo, $data), $data);
			foreach ($this->registeredProperties as $properties) $hook->registerProperties($properties);
			$template->addHook($rule->query, $hook);	
		}

		
		$output = $template->output($document);	
		
		return ['headers' => $headers, 'body' => $output];
	}

	private function getBasicProperties($data, $locale, &$headers) {
		$basicProperties = new Hook\BasicProperties($data, $headers);
		$basicProperties->registerFormatter(new Formatter\Number($locale));
		$basicProperties->registerFormatter(new Formatter\String());
		return $basicProperties;
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
}
