<?php
namespace Transphporm\Formatter;
class Nl2br {
	public function nl2br($var) {
		$parts = explode("\n", $var);
		$doc = new \DomDocument();
		$result = [];
		
		foreach ($parts as $key => $part) {
			$new = $doc->createTextNode($part);
			$result[] = $new;
			if ($key !== count($parts)-1) {
				$br = $doc->createElement('br');
				$result[] = $br;
			}
		}

		return $result;
	}
}
