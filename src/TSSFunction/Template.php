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
	private $xPath;

	public function __construct(\Transphporm\Hook\ElementData $elementData, \Transphporm\Parser\CssToXpath $xPath, \Transphporm\FilePath $filePath) {
		$this->filePath = $filePath;
		$this->elementData = $elementData;
		$this->xPath = $xPath;
	}

	private function readArray($array, $index) {
		return isset($array[$index]) ? $array[$index] : null;
	}

	public function run(array $args, \DomElement $element = null) {
		$selector = $this->readArray($args, 1);
		$tss = $this->readArray($args, 2);

		if (trim($args[0])[0] === '<') $xml = $args[0];
		else $xml = $this->filePath->getFilePath($args[0]);

		$newTemplate = new \Transphporm\Builder($xml, $tss ? $this->filePath->getFilePath($tss) : null);

		$doc = $newTemplate->output($this->elementData->getData($element), true)->body;
		if ($selector != '') return $this->templateSubsection($doc, $selector);

		return $this->getTemplateContent($doc->documentElement, $tss);

	}

	private function getTemplateContent($newNode, $tss) {
		$result = [];
		foreach ($newNode->childNodes as $node) {
            if (isset($node->tagName) && $node->tagName === 'template') $result[] = $this->getTemplateContent($node, $tss);
			else $result[] = $this->getClonedElement($node, $tss);
		}
		return $result;
	}

	private function templateSubsection($doc, $selector) {
		$tokenizer = new \Transphporm\Parser\Tokenizer($selector);
		$xpathStr = $this->xPath->getXpath($tokenizer->getTokens());
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
