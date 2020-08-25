<?php
/**
 * @file File.php
 * Replace with one line description.
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
