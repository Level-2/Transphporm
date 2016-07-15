<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\TSSFunction;
/* Handles template() function calls from the stylesheet */
class Template implements \Transphporm\TSSFunction {
	private $elementData;
	private $baseDir;
	private $functionSet;
	private $xPath;

	public function __construct(\Transphporm\Hook\ElementData $elementData, \Transphporm\Parser\CssToXpath $xPath, \Transphporm\FunctionSet $functionSet, &$baseDir) {
		$this->baseDir = &$baseDir;
		$this->elementData = $elementData;
		$this->functionSet = $functionSet;
		$this->xPath = $xPath;
	}

	private function readArray($array, $index) {
		return isset($array[$index]) ? $array[$index] : null;
	}

	public function run(array $args, \DomElement $element) {
		$selector = $this->readArray($args, 1);
		$tss = $this->readArray($args, 2);

		if (is_file($this->baseDir . $args[0])) $xmlFile = $this->baseDir . $args[0];
		elseif (is_file($args[0])) $xmlFile = $args[0];
		else throw new \Exception('XML File "' . $args[0] .'" does not exist');

		$newTemplate = new \Transphporm\Builder($xmlFile, $tss ? $this->baseDir . $tss : null);

		$doc = $newTemplate->output($this->elementData->getData($element), true)->body;
		if ($selector != '') return $this->templateSubsection($doc, $selector);

		return $this->getTemplateContent($doc, $tss);

	}

	private function getTemplateContent($document, $tss) {
		$newNode = $document->documentElement;
		$result = [];
		if ($newNode->tagName === 'template') {
			foreach ($newNode->childNodes as $node) {
				$result[] = $this->getClonedElement($node, $tss);
			}
		}
		return $result;
	}

	private function templateSubsection($doc, $selector) {
		$xpathStr = $this->xPath->getXpath($selector);
		$xpath = new \DomXpath($doc);
		$nodes = $xpath->query($xpathStr);
		$result = [];
		foreach ($nodes as $node) {
			$result[] = $node;
		}
		return $result;
	}

	private function getClonedElement($node, $tss) {
		$clone = $node->cloneNode(true);
		if ($tss != null && $clone instanceof \DomElement) $clone->setAttribute('transphporm', 'includedtemplate');
		return $clone;
	}
}
