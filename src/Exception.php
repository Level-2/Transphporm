<?php
namespace Transphporm;
class Exception extends \Exception {
    const PROPERTY = 'property';
    const TSS_FUNCTION = 'function';
    const PSEUDO = 'pseudo';
    const FORMATTER = 'formatter';

    public function __construct(RunException $runException, $file, $line) {
        $message = $runException->getMessage() . ' on Line ' . $line . ' of ' . ($file === null ? 'tss' : $file);

        parent::__construct($message, 0, $runException->getPrevious());
    }
}
