<?php
namespace CDS;
class Builder {
	private $template;
	private $cds;
	private $data;
	private $dataStorage;

	public function __construct($template, $cds, $data) {
		$this->template = new Template($template);
		$this->cds = new Sheet($cds);
		$this->data = $data;
		$this->dataStorage = new \SplObjectStorage();
	}

	public function output() {
		$rules = $this->cds->parse();
		foreach ($rules as $rule) {
			$this->template->addHook($rule->query, new Hook\Rule($rule, $this->data, $this->dataStorage));	
		}
		
		return $this->template->output();
	}
}
