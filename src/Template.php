<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
/** Loads an XML string into a DomDocument and allows searching for specific elements using xpath based hooks */
class Template {
	private $hooks = [];
	private $document;
	private $xpath;
	private $prefix = '';

	/** Takes an XML string and loads it into a DomDocument object */
	public function __construct($doc) {
		$this->document = new \DomDocument;
		
		$this->loadDocument($doc);

		$this->xpath = new \DomXPath($this->document);
		$this->xpath->registerNamespace('php', 'http://php.net/xpath');
		$this->xpath->registerPhpFunctions();

		if ($this->document->documentElement->namespaceURI !== null) {
			$this->xpath->registerNamespace('nsprefix', $this->document->documentElement->namespaceURI);
			$this->prefix = 'nsprefix:';
		}
	}

	/** Loads a HTML or XML document */ 	
	private function loadDocument($doc) {
		libxml_use_internal_errors(true);
		if ($this->document->loadXml($doc) === false) {
				$this->document->loadHtml($doc);

				if (strpos($doc, '<!') !== 0) {
					$templateNode = $this->document->getElementsByTagName('template')[0];
					$this->document->replaceChild($templateNode, $this->document->documentElement);
				}
		}
		libxml_clear_errors();
	}

	/** Returns the document's XML prefix */
	public function getPrefix() {
		return $this->prefix;
	}
	
	/** Assigns a $hook which will be run on any element that matches the given $xpath query */
	public function addHook($xpath, $hook) {
		$this->hooks[] = [$xpath, $hook];
	}

	/** Loops through all assigned hooks, runs the Xpath query and calls the hook */
	private function processHooks() {
		foreach ($this->hooks as list($query, $hook)) {
			foreach ($this->xpath->query($query) as $element) $hook->run($element);
		}
		$this->hooks = [];
	}

	/** Prints out the current DomDocument as HTML */
	private function printDocument(\DomDocument $doc) {
		$output = '';
		foreach ($doc->documentElement->childNodes as $node) $output .= $doc->saveXML($node, LIBXML_NOEMPTYTAG);
		return $output;
	}

	/** Outputs the template's header/body. Returns an array containing both parts */
	public function output($document = false) {
		//Process all hooks
		 $this->processHooks();

		//Generate the document by taking only the childnodes of the template, ignoring the <template> and </template> tags
		//TODO: Is there a faster way of doing this without string manipulation on the output or this loop through childnodes?
		 //Either return a whole DomDocument or return the output HTML
		if ($document) return $this->document;


		$output = ($this->document->doctype) ? $this->document->saveXml($this->document->doctype) . "\n" : '';

		if ($this->document->documentElement->tagName !== 'template') $output .= $this->document->saveXml($this->document->documentElement, LIBXML_NOEMPTYTAG);
		else $output = $this->printDocument($this->document);

		//repair empty tags. Browsers break on <script /> and <div /> so can't avoid LIBXML_NOEMPTYTAG but they also break on <base></base> so repair them
		$output = str_replace(['></img>', '></br>', '></meta>', '></base>', '></link>', '></hr>', '></input>'], ' />', $output);
		return trim($output);
	}
}
