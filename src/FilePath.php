<?php
namespace Transphporm;

class FilePath {
    private $baseDir;
    private $customBase;

    public function __construct($customBase = null) {
        $this->baseDir = "";
        if ($customBase === null) $this->customBase = getcwd();
        else $this->customBase = rtrim($customBase, '/');
    }

    public function setBaseDir($baseDir) {
        $this->baseDir = $baseDir;
    }

    public function getFilePath($filePath = "") {
		if (isset($filePath[0]) && $filePath[0] == "/") return $this->customBase . $filePath;
		else return $this->baseDir . $filePath;
	}
}
