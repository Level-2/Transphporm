<?php
namespace Transphporm;
/** Builds a Transphorm instance from the 3 constituent parts. XML template string, TSS string and data */
class Builder {
	private $template;
	private $tss;
	private $registeredProperties = [];
	private $mode;

	const FILE = 1;
	const STRING = 2;

	public function __construct($template, $tss = '', $mode = self::FILE) {
		if (self::FILE & $mode) {
			$this->template = file_get_contents($template);
			if ($tss) $this->tss = file_get_contents($tss);
		}
		else {
			$this->template =  $template;
			$this->tss = $tss;
		} 
	
		$this->mode = $mode;
		
	}

	public function output($data = null, $document = false) {
		$data = new Hook\DataFunction(new \SplObjectStorage(), $data);
		$this->registerBaseProperties($data);

		if (self::STRING & $this->mode)  $template = new Template('<template>' . $this->template . '</template>');
		else $template = new Template($this->template);

		$tss = new Sheet($this->tss);
		$rules = $tss->parse();

		foreach ($rules as $rule) {
			$pseudoMatcher = new Hook\PseudoMatcher($rule->pseudo, $data);
			$hook = new Hook\Rule($rule->rules, $pseudoMatcher, $data);
			foreach ($this->registeredProperties as $name => $closure) $hook->registerProperty($name, $closure);
			$template->addHook($rule->query, $hook);	
		}
		
		return $template->output($document);
	}

	public function registerProperty($name, $closure) {
		$this->registeredProperties[$name] = $closure;
	}

	private function registerBaseProperties($data) {
		$basicProperties = new Hook\BasicProperties($data);

		$this->registerProperty('content', [$basicProperties, 'content']);
		$this->registerProperty('repeat', [$basicProperties, 'repeat']);
		$this->registerProperty('display', [$basicProperties, 'display']);
	}
}
