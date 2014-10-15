<?php

/* 
 * 
 * Copyrights 2014
 * @author udhayakumar
 */

class HTTP_Exception_500 extends Kohana_HTTP_Exception_404 {
 
    public function get_response()
    {
        $response = Response::factory();
 
        $view = View::factory('errors/500');
 
        $response->body($view->render());
 
        return $response;
    }
 
}   