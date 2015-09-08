<?php
namespace Transphporm;
/** Loads an XML string into a DomDocument and allows searching for specific elements using xpath based hooks */
class Template {
	private $hooks = [];
	private $document;
	private $xpath;

	public function __construct($doc) {
		if ($doc instanceof \DomDocument) $this->document = $doc;
		else {
			$this->document = new \DomDocument;
			$this->document->loadXML($doc);
		}
	
		$this->xpath = new \DomXPath($this->document);
	}
	
	public function addHook($xpath, $hook) {
		$this->hooks[] = [$xpath, $hook];
	}

	private function processHook($query, $hook) {
		foreach ($this->xpath->query($query) as $element) $hook->run($element);
	}

	public function output() {
		//Process all hooks
		foreach ($this->hooks as list($query, $hook)) $this->processHook($query, $hook);

		//Generate the document by taking only the childnodes of the template, ignoring the <template> and </template> tags
		//TODO: Is there a faster way of doing this without string manipulation on the output or this loop through childnodes?
		$output = '';
		foreach ($this->document->documentElement->childNodes as $node) $output .= $this->document->saveXML($node, LIBXML_NOEMPTYTAG);

		//repair empty tags. Browsers break on <script /> and <div /> so can't avoid LIBXML_NOEMPTYTAG but they also break on <base></base> so repair them
		$output = str_replace(['></img>', '></br>', '></meta>', '></base>', '></link>', '></hr>', '></input>'], ' />', $output);
		return trim($output);
	}
}