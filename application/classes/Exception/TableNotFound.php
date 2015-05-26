<?php

/**
 * Description of Exception_TableNotFound
 *
 * @author Gandhi
 * @version 1.1
 */
class Exception_TableNotFound extends Exception {
    public function __construct($message = 'Table not found!', $code = 404) {
        parent::__construct($message, $code);        
    }
}
