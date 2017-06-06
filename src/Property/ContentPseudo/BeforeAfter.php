<?php
namespace Transphporm\Property\ContentPseudo;
class BeforeAfter implements \Transphporm\Property\ContentPseudo {
    private $insertLocation;
    private $content;

    public function __construct($insertLocation, \Transphporm\Property\Content $content) {
        $this->insertLocation = $insertLocation;
		$this->content = $content;
	}

    public function run($value, $pseudoArgs, $element) {
        foreach ($this->content->getNode($value, $element->ownerDocument) as $node) {
			if ($this->insertLocation === "before") $element->insertBefore($node, $element->firstChild);
            else if ($this->insertLocation === "after") $element->appendChild($node);
		}
    }
}
