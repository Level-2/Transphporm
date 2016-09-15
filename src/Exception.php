<?php
namespace Transphporm;
class Exception extends \Exception {
    const PROPERTY = 'property';
    const TSS_FUNCTION = 'function';
    const PSEUDO = 'pseudo';
    const FORMATTER = 'formatter';

    public function __construct($operationType, $operationName, $file = null, $line = 0, \Exception $previous) {
        $message = 'TSS Error: Problem carrying out ' . $operationType . ' \'' . $operationName
            . '\' on Line ' . $line . ' of ' . ($file === null ? 'tss' : $file);

        parent::__construct($message, 0, $previous);
    }
}
