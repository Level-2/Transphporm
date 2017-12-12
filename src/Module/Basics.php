<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Module;
/** Assigns all the basic properties repeat, comment, etc to a $builder instance */
class Basics implements \Transphporm\Module {


	public function load(\Transphporm\Config $config) {
		$data = $config->getFunctionSet();
		$headers = &$config->getHeaders();

		$content = new \Transphporm\Property\Content($config->getFormatter());
		$config->registerProperty('content', $content);
		$config->registerProperty('repeat', new \Transphporm\Property\Repeat($data, $config->getElementData(), $config->getLine(), $config->getFilePath()));
		$config->registerProperty('display', new \Transphporm\Property\Display);
		$config->registerProperty('bind', new \Transphporm\Property\Bind($config->getElementData()));

		$config->registerContentPseudo('attr', new \Transphporm\Property\ContentPseudo\Attr());
		$config->registerContentPseudo('before', new \Transphporm\Property\ContentPseudo\BeforeAfter('before', $content));
		$config->registerContentPseudo("after", new \Transphporm\Property\ContentPseudo\BeforeAfter('after', $content));

		$config->registerContentPseudo('header', new \Transphporm\Property\ContentPseudo\Headers($headers));
	}
}
