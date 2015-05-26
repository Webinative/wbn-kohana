<?php

/**
 * Description of ValidationFailure
 *
 * @author mageshravi
 */
class Exception_ValidationFailure extends Exception_App {

    public $_errors;

    /**
     * 
     * @param array $_errors
     * @param int $code
     * @param Exception $previous
     */
    function __construct(array $_errors, $code = 400, $previous = NULL) {
        parent::__construct('Validation failed', $code, $previous);
        $this->_errors = $_errors;
    }

}
