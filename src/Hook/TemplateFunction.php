<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/* Handles template() function calls from the stylesheet */
class TemplateFunction {
	private $xml;
	private $selector;
	private $tss;

	public function __construct($xml, $selector = null, $tss = null) {
		$this->xml = $xml;
		$this->selector = $selector;
		$this->tss = $tss;
	}

	private function templateSubsection($doc) {
		$xpathStr = (new \Transphporm\Parser\CssToXpath($this->selector, new \Transphporm\Parser\Value($this)))->getXpath();
		$xpath = new \DomXpath($doc);
		$nodes = $xpath->query($xpathStr);
		$result = [];
		foreach ($nodes as $node) {
			$result[] = $node;
		}
		return $result;
	}

	private function getTss($val) {
		return isset($val[2]) ? $val[2] : null;
	}

	private function getClonedElement($node) {
		$clone = $node->cloneNode(true);
		if ($this->tss !== null && $clone instanceof \DomElement) $clone->setAttribute('transphporm', 'includedtemplate');
		return $clone;
	}

	public function getTemplateNodes($data) {
		$newTemplate = new \Transphporm\Builder($this->xml, $this->tss);

		$doc = $newTemplate->output($data, true)->body;
		if ($this->selector != '') return $this->templateSubsection($doc);
		
		$newNode = $doc->documentElement;
		$result = [];
		if ($newNode->tagName === 'template') {
			foreach ($newNode->childNodes as $node) {
				$result[] = $this->getClonedElement($node);
			}
		}
		//else $result[] = $newNode;
		return $result;
	}
}