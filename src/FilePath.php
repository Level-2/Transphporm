<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
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
		else return $this->loadFromPaths($filePath);
	}

	private function loadFromPaths($filePath) {
		foreach ($this->paths as $path) {
			if (is_file($path . DIRECTORY_SEPARATOR . $filePath)) return $path . DIRECTORY_SEPARATOR . $filePath;
		}

		throw new \Exception('File ' . $filePath . ' not found in paths (' . implode(', ', $this->paths) . ')');
	}
}
