<?php
namespace Transphporm\TSSFunction;
class Json implements \Transphporm\TSSFunction {
    private $baseDir;

    public function __construct(&$baseDir) {
        $this->baseDir = &$baseDir;
    }

    public function run(array $args, \DomElement $element = null) {
        $json = $args[0];

        if (trim($json)[0] != '{') {
            if (is_file($this->baseDir . $args[0])) $jsonFile = $this->baseDir . $json;
    		elseif (is_file($args[0])) $jsonFile = $json;
    		else throw new \Exception('JSON File "' . $json .'" does not exist');

            $json = file_get_contents($jsonFile);
        }

        $map = json_decode($json, true);

        if (!is_array($map)) throw new \Exception('Could not decode json: ' . json_last_error_msg());

        return $map;
    }
}
