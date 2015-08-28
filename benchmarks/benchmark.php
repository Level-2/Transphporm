<?php 

//Autoloader for Vision classes
spl_autoload_register(function($class) {
	$parts = explode('\\', ltrim($class, '\\'));
	if ($parts[0] === 'CDS') {
		array_shift($parts);
		require_once 'src/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
});
