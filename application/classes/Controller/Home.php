<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @
 */
class Controller_Home extends Controller_Template {

    public $template = 'templates/default';
    
    public function action_index()
    {
		Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
		
        $view = View::factory('home/index');
        
        $this->template->content = $view;
    }

} // End Welcome
