<?php
namespace CDS;
/** Builds a CDS instance from the 3 constituent parts. XML template string, CDS string and data */
class Builder {
	private $template;
	private $cds;
	private $data;

	public function __construct($template, $cds, $data) {
		$this->template = new Template($template);
		$this->cds = new Sheet($cds);
		$this->data = new Hook\DataFunction(new \SplObjectStorage(), $data);		
	}

	public function output() {
		$rules = $this->cds->parse();

		foreach ($rules as $rule) {
			$pseudoMatcher = new Hook\PseudoMatcher($rule->pseudo, $this->data);
			$this->template->addHook($rule->query, new Hook\Rule($rule->rules, $pseudoMatcher, $this->data));	
		}
		
		return $this->template->output();
	}
}
