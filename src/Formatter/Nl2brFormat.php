<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Formatter;
class Nl2brFormat {
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
