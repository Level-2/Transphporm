<?php
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $registeredProperties = [];
	private $isFile = false;

	public function __construct($template, $tss = '') {
		if (trim($template)[0] !== '<') {
			$this->template = file_get_contents($template);
			if ($tss) $this->tss = file_get_contents($tss);
			$this->isFile = true;
		}
		else {
			$this->template =  $template;
			$this->tss = $tss;
		}	
	}

	public function output($data = null, $document = false) {
		$data = new Hook\DataFunction(new \SplObjectStorage(), $data);
		$this->registerProperties(new Hook\BasicProperties($data));

		//To be a valid XML document it must have a root element, automatically wrap it in <template> to ensure it does
		//If it's a file, don't assume template partials and don't wrap in <template>
		if (!$this->isFile) $template = new Template('<template>' . $this->template . '</template>');
		else $template = new Template($this->template);

		$tss = new Sheet($this->tss);
		$rules = $tss->parse();

		foreach ($rules as $rule) {
			$pseudoMatcher = new Hook\PseudoMatcher($rule->pseudo, $data);
			$hook = new Hook\Rule($rule->rules, $pseudoMatcher, $data);
			foreach ($this->registeredProperties as $properties) $hook->registerProperties($properties);
			$template->addHook($rule->query, $hook);	
		}
		
		return $template->output($document);
	}

	public function registerProperties($object) {
		$this->registeredProperties[] = $object;
	}
}
