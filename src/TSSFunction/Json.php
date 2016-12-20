<?php
namespace Transphporm\TSSFunction;
class Json implements \Transphporm\TSSFunction {
    private $filePath;

    public function __construct(\Transphporm\FilePath $filePath) {
        $this->filePath = $filePath;
    }

    public function run(array $args, \DomElement $element = null) {
        $json = $args[0];

        if ($this->isJsonFile($json)) {
            $path = $this->filePath->getFilePath($json);
            if (!file_exists($path)) throw new \Exception('File does not exist at: ' . $path);
            $json = file_get_contents($path);
        }

        $map = json_decode($json, true);

        if (!is_array($map)) throw new \Exception('Could not decode json: ' . json_last_error_msg());

        return $map;
    }

    private function isJsonFile($json) {
        return trim($json)[0] != '{' && trim($json)[0] != '[';
    }
}
