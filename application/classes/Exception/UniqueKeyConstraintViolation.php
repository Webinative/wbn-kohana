<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UniqueKeyConstraintViolation
 *
 * @author mageshravi
 */
class Exception_UniqueKeyConstraintViolation extends Exception_App {
    
    function __construct($message='Unique key constraint violation', $code=NULL, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
