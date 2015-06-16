<?php

/**
 * Description of RecordNotFound
 *
 * @author mageshravi
 */
class Exception_RecordNotFound extends Exception_App {
    
    function __construct($message="Record not found!", $code=NULL, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
