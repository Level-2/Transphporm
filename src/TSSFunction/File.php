<?php
/**
 * @description Insert the contents of a file into selected element.
 * @author      Chris Johnson <cxjohnson@gmail.com>
 * @copyright   2020 Chris Johnson <cxjohnson@gmail.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     1.0
 */
namespace Transphporm\TSSFunction;

class File implements \Transphporm\TSSFunction
{
    private $filePath;

    public function __construct(\Transphporm\FilePath $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param array $args
     * @param \DomElement|null $element
     *
     * @return array
     * @throws \Exception
     */
    public function run(array $args, \DomElement $element = null)
    {
        $fileContents = $args[0];

        $path = $this->filePath->getFilePath($fileContents);
        if (!file_exists($path)) {
            throw new \Exception('File does not exist at: ' . $path);
        }
        $fileContents = file_get_contents($path);

        return $fileContents;
    }
}
