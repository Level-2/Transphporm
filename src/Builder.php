<?php
namespace Transphporm;
/** Builds a CDS instance from the 3 constituent parts. XML template string, CDS string and data */
class Builder {
	private $template;
	private $cds;
	private $data;
	private $registeredProperties = [];

	public function __construct($template, $cds, $data = []) {
		$this->template = new Template('<template>' . $template . '</template>');
		$this->cds = new Sheet($cds);
		$this->data = new Hook\DataFunction(new \SplObjectStorage(), $data);
		$this->registerBaseProperties();
	}

	public function output() {
		$rules = $this->cds->parse();

		foreach ($rules as $rule) {
			$pseudoMatcher = new Hook\PseudoMatcher($rule->pseudo, $this->data);
			$hook = new Hook\Rule($rule->rules, $pseudoMatcher, $this->data);
			foreach ($this->registeredProperties as $name => $closure) $hook->registerProperty($name, $closure);
			$this->template->addHook($rule->query, $hook);	
		}
		
		return $this->template->output();
	}

	public function registerProperty($name, $closure) {
		$this->registeredProperties[$name] = $closure;
	}

	private function registerBaseProperties() {
		$basicProperties = new Hook\BasicProperties($this->data);

		$this->registerProperty('content', [$basicProperties, 'content']);
		$this->registerProperty('repeat', [$basicProperties, 'repeat']);
		$this->registerProperty('display', [$basicProperties, 'display']);
	}
}
