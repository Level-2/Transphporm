<?php
namespace Transphporm\Formatter;
class Nl2br {
    public function nl2br($var) {
        $parts = explode("\n", $var);
        $doc = new \DomDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);

        foreach ($parts as $key => $part) {
            $new = $doc->createTextNode($part);
            $doc->documentElement->appendChild($new);
            if ($key !== count($parts)-1) {
                $br = $doc->createElement('br');
                $doc->documentElement->appendChild($br);
            }
        }

        return $this->getContent($doc);
    }

    private function getContent($document) {
		$newNode = $document->documentElement;
		$result = [];
		if ($newNode->tagName === 'root') {
			foreach ($newNode->childNodes as $node) {
				$result[] = $this->getClonedElement($node);
			}
		}
		return $result;
	}

	private function getClonedElement($node) {
		$clone = $node->cloneNode(true);
		return $clone;
	}
}
