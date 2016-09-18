<?php
namespace Transphporm;
class RunException extends \Exception {
    public function __construct($operationType, $operationName, \Exception $previous) {
        $message = 'TSS Error: Problem carrying out ' . $operationType . ' \'' . $operationName . '\'';

        parent::__construct($message, 0, $previous);
    }
}
