<?php

/**
 * Description of WaException
 *
 * @author Gandhi
 */
class Exception_TableNotFoundException extends Exception {
    public function __construct($message = 'Table not found!', $code = 404) {
        parent::__construct($message, $code);        
    }
}
