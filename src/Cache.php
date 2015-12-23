<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
class Cache {
	private $cache;

	public function __construct(\ArrayAccess $cache) {
		$this->cache = $cache;
	}

	public function write($key, $content) {
		$this->cache[md5($key)] = ['content' => $content, 'timestamp' => time()];		
		return $content;
	}

	public function load($key, $modified = 0) {
		$key = md5($key);
		if (isset($this->cache[$key]) && $this->cache[$key]['timestamp'] >= $modified) {
			return $this->cache[$key]['content'];
		}
		else return false;
	}
}