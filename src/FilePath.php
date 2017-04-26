<?php
namespace Transphporm;
class FilePath {
	private $paths = ['.'];

	public function addPath($path) {
		$this->paths[] = rtrim($path, DIRECTORY_SEPARATOR);
	}

	public function getFilePath($filePath) {
		if (is_file($filePath)) return $filePath;
		else {
			foreach ($this->paths as $path) {
				if (is_file($path . DIRECTORY_SEPARATOR . $filePath)) return $path . DIRECTORY_SEPARATOR . $filePath;

			}
		}

		throw new \Exception($filePath . ' not found in include path: ' . implode(';', $this->paths));
	}
}
