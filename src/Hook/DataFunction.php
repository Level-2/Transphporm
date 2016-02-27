<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/* Handles data() and iteration() function calls from the stylesheet */
class DataFunction {
	private $dataStorage;
	private $data;
	private $baseDir;

	public function __construct(\SplObjectStorage $objectStorage, $data, $baseDir, $tss) {
		$this->dataStorage = $objectStorage;
		$this->data = $data;
		$this->baseDir = $baseDir;
		$this->tss = $tss;
	}

	public function setBaseDir($dir) {
		$this->baseDir = $dir;
	}

	/** Binds data to an element */
	public function bind(\DomNode $element, $data, $type = 'data') {
		//This is a bit of a hack to workaround #24, might need a better way of doing this if it causes a problem
		if (is_array($data) && $this->isObjectArray($data)) $data = $data[0];
		$content = isset($this->dataStorage[$element]) ? $this->dataStorage[$element] : [];
		$content[$type] = $data;
		$this->dataStorage[$element] = $content;
	}

	private function isObjectArray(array $data) {
		return count($data) === 1 && isset($data[0]) && is_object($data[0]);
	}

	public function iteration($val, $element) {
		$data = $this->getData($element, 'iteration');
		$value = $this->traverse($val, $data, $element);
		return $value;
	}

	public function key($val, $element) {
		$data = $this->getData($element, 'key');
		return $data;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	public function getData(\DomElement $element = null, $type = 'data') {
		while ($element) {
			if (isset($this->dataStorage[$element]) && isset($this->dataStorage[$element][$type])) return $this->dataStorage[$element][$type];
			$element = $element->parentNode;
		}
		return $this->data;
	}

	public function data($val, \DomElement $element = null) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data, $element);
		return $value;
	}

	private function traverse($name, $data, $element) {
		$name[0] = str_replace(['[', ']'], ['.', ''], $name[0]);
		$parts = explode('.', $name[0]);
		$obj = $data;
		$valueParser = new \Transphporm\Parser\Value($this);

		foreach ($parts as $part) {
			if ($part === '') continue;
			$part = $valueParser->parse($part, $element)[0];
			$funcResult = $this->traverseObj($part, $obj, $valueParser, $element);

			if ($funcResult !== false) $obj = $funcResult;
			
			else $obj = $this->ifNull($obj, $part);
		}
		return $obj;
	}

	private function traverseObj($part, $obj, $valueParser, $element) {
		if (strpos($part, '(') !== false) {
			$subObjParser = new \Transphporm\Parser\Value($obj, $valueParser, false);
			return $subObjParser->parse($part, $element)[0];
		}
		else if (method_exists($obj, $part)) return call_user_func([$obj, $part]); 
		else return false;
	}

	private function ifNull($obj, $key) {
		if (is_array($obj)) return isset($obj[$key]) ? $obj[$key] : null;
		else return isset($obj->$key) ? $obj->$key : null;
	}

	public function attr($val, $element) {
		return $element->getAttribute(trim($val[0]));
	}

	private function templateSubsection($css, $doc, $element) {
		$xpathStr = (new \Transphporm\Parser\CssToXpath($css, new \Transphporm\Parser\Value($this)))->getXpath();
		$xpath = new \DomXpath($doc);
		$nodes = $xpath->query($xpathStr);
		$result = [];

		foreach ($nodes as $node) {
			$result[] = $element->ownerDocument->importNode($node, true);
		}

		return $result;
	}


	public function template($val, \DomElement $element) {
		//Check the nesting level... without this it will keep applying TSS to included templates forever in some cases
		if ($element->getAttribute('tssapplied') === 'true') $this->tss = '';
		//Create a document to mimic the structure of the parent template
		$newDocument = $this->createDummyTemplateDoc($element, $this->baseDir . $val[0]);		
	
		//Build a new template using the $newDocument
		$newTemplate = new \Transphporm\Builder($newDocument->saveXml(), $this->tss);
		$data = $this->getData($element);

		var_dump($newDocument->saveXml());
		//Output the template as a DomDocument
		$doc = $newTemplate->output($data, true)->body;
		var_dump($doc->saveXml());
		//Find the corresponding element in the new document that matches the position of the original $element
		//and read the contents as the result to import into the parent template
		$result = [];
		$xpath = new \DomXpath($doc);
		$correspondingElement = $xpath->query('//*[@tssapplied]')[0];
		if (!$correspondingElement) $correspondingElement = $doc->documentElement;		
		foreach ($correspondingElement->childNodes as $child) {
			$child = $child->cloneNode(true);
			if ($child instanceof \DomElement) $child->setAttribute('transphporm', 'includedtemplate');

			$result[] = $child;			
		}		
		return $result;
	}

	private function createDummyTemplateDoc(\DomElement $element, $templateFile) {		
		$newDocument = new \DomDocument;
		$root = $newDocument->createElement('template');
		$newDocument->appendChild($root);

		//Loop through all parent nodes of $element and import them into the new document.
		$el = $element;
		$baseElement = null;
		do {
			$firstChild = $root->firstChild;
			$el = $el->cloneNode();
			$newNode = $newDocument->importNode($el);
			if ($baseElement === null) $baseElement = $newNode;
			if ($firstChild) $newNode->appendChild($firstChild);
			$root->appendChild($newNode);
		}
		while (($el = $el->parentNode)  instanceof \DomElement);		
		$baseElement->setAttribute('tssapplied', 'true');
		$this->loadTemplate($baseElement, $templateFile);
		return $newDocument;
	}

	private function loadTemplate($baseElement, $templateFile) {
		$baseElement->setAttribute('transphpormbaselement', 'true');
		//Load the template XML
		$templateDoc = new \DomDocument();
	
		$templateDoc->loadXml(file_get_contents($templateFile));

		if ($templateDoc->documentElement->tagName == 'template') {
			foreach ($templateDoc->documentElement->childNodes as $node) {
				$node = $baseElement->ownerDocument->importNode($node, true);
				$baseElement->appendChild($node);		
			}
		}
	}



}
