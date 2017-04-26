<?php
namespace Transphporm;
class FilePath {
	private $paths = ['.'];
	private $baseDir;

	public function addPath($path) {
		$this->paths[] = rtrim($path, DIRECTORY_SEPARATOR);
	}

	public function setBaseDir($baseDir) {
		$this->baseDir = $baseDir;
	}

	public function getFilePath($filePath) {
		if (is_file($filePath)) return $filePath;
		else if (is_file($this->baseDir . DIRECTORY_SEPARATOR . $filePath)) return $this->baseDir . DIRECTORY_SEPARATOR . $filePath;
		else {
			foreach ($this->paths as $path) {
				if (is_file($path . DIRECTORY_SEPARATOR . $filePath)) return $path . DIRECTORY_SEPARATOR . $filePath;

			}
		}

		throw new \Exception($filePath . ' not found in include path: ' . implode(';', $this->paths));
	}
}
