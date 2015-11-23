<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
namespace Transphporm\Hook;
/* Handles data() and iteration() functions in the CDS */
class DataFunction {
	private $dataStorage;
	private $data;
	private $locale;
	private $baseDir;
	
	public function __construct(\SplObjectStorage $objectStorage, $data, $locale, $baseDir) {
		$this->dataStorage = $objectStorage;
		$this->data = $data;
		$this->locale = $locale;
		$this->baseDir = $baseDir;
	}

	/** Binds data to an element */
	public function bind(\DomElement $element, $data) {
		//This is a bit of a hack to workaround #24, might need a better way of doing this if it causes a problem
		if (is_array($data) && count($data) === 1 && is_object($data[0])) $data = $data[0];
		$this->dataStorage[$element] = $data;
	}

	public function iteration($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;
	}

	/** Returns the data that has been bound to $element, or, if no data is bound to $element climb the DOM tree to find the data bound to a parent node*/
	private function getData(\DomElement $element) {
		while ($element) {
			if (isset($this->dataStorage[$element])) return $this->dataStorage[$element];
			$element = $element->parentNode;
		}
		return $this->data;
	}

	public function data($val, $element) {
		$data = $this->getData($element);
		$value = $this->traverse($val, $data);
		return $value;			
	}

	private function traverse($name, $data) {
		$name[0] = str_replace(['[', ']'], ['.', ''], $name[0]);
		$parts = explode('.', $name[0]);
		$obj = $data;
		foreach ($parts as $part) {
			if ($part === '') continue;
			if (is_callable([$obj, $part])) $obj = call_user_func([$obj, $part]); 
			else $obj = $this->ifNull($obj, $part);
		}
		return $obj;
	}

	private function ifNull($obj, $key) {
		if (is_array($obj)) return isset($obj[$key]) ? $obj[$key] : null;
		else return isset($obj->$key) ? $obj->$key : null;
	}

	public function attr($val, $element) {
		return $element->getAttribute(trim($val[0]));
	}

	private function templateSubsection($css, $doc, $element) {
		$xpathStr = (new \Transphporm\CssToXpath($css))->getXpath();
		$xpath = new \DomXpath($doc);
		$nodes = $xpath->query($xpathStr);
		$result = [];

		foreach ($nodes as $node) {
			$result[] = $element->ownerDocument->importNode($node, true);
		}

		return $result;
	}

	public function template($val, $element) {
		$newTemplate = new \Transphporm\Builder($this->baseDir . $val[0]);
		$newTemplate->setLocale($this->locale);

		$doc = $newTemplate->output([], true)->body;

		if (isset($val[1])) return $this->templateSubsection($val[1], $doc, $element);
		
		$newNode = $element->ownerDocument->importNode($doc->documentElement, true);

		$result = [];

		if ($newNode->tagName === 'template') {
			foreach ($newNode->childNodes as $node) $result[] = $node->cloneNode(true);
		}		
		//else $result[] = $newNode;

		return $result;
	}
}
