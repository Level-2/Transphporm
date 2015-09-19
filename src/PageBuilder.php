<?php
namespace Transphporm;
class PageBuilder {
	public function __construct($templateFile, $tss) {
		$this->template = new \DomDocument;
		$this->template->loadXml(str_ireplace('<!doctype html>', '', file_get_contents($file)));
		
	}
}