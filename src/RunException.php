<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
class RunException extends \Exception {
    public function __construct($operationType, $operationName, \Exception $previous = null) {
        $message = 'TSS Error: Problem carrying out ' . $operationType . ' "' . $operationName . '"';

        parent::__construct($message, 0, $previous);
    }
}
