<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
//Autoloader for Vision classes
spl_autoload_register(function($class) {
	$parts = explode('\\', ltrim($class, '\\'));
	if ($parts[0] === 'Transphporm') {
		array_shift($parts);
		require_once 'src/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
});
