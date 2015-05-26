<?php

/**
 * Description of User
 *
 * @author mageshravi
 */
class Controller_Sample_User extends Controller_Template {
    
    public $template = 'templates/default';
    
    public function action_index() {    // list all users here
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        $view = View::factory('sample/user/index');
        
        $this->template->title = 'Users';
        
        $arr_users = Model_Sample_User::all();
        
        $view->set('arr_users', $arr_users);
        
        $this->template->content = $view->render();
    }
    
    public function action_add() {      // display form
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        $view = View::factory('sample/user/add');
        
        $this->template->title = 'Add user';
        
        $success = $this->request->query('success');
        if($success) {
            $view->set('success', true);
        }
        
        $this->template->content = $view->render();
    }
    
    public function action_create() {   // handle form submission
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if($this->request->method() != 'POST') {
            $this->redirect('/sample/user/add');
        }
        
        $_errors = array();
        $view = View::factory('sample/user/add');
        $view->bind('_errors', $_errors);
        
        $arr_allowed = array(
            'full_name',
            'username',
            'passwd'
        );
        
        $arr_submitted = array_keys($this->request->post());
        
        try {
            if($arr_allowed != $arr_submitted) {
                throw new Exception_App('Invalid form submission', 400);
            }
            
            $user = new Model_Sample_User();
            $user->full_name = $this->request->post('full_name');
            $user->username = $this->request->post('username');
            $user->passwd = $this->request->post('passwd');
            
            $user->validate($_errors)
                ->hash_passwd()
                ->create();
            
            if($user->id) {
                $this->redirect('/sample/user/add?success=1');
            }
        } catch (Exception_App $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            
            if(is_a($ex, 'Exception_ValidationFailure')) {  /* @var $ex Exception_ValidationFailure */
                $_errors = $ex->_errors;
            } else {
                $_errors['flash'] = $ex->getMessage();
            }
            
            $this->template->content = $view->render();
        }
    }
    
    public function action_edit() {     // display form
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        $view = View::factory('sample/user/edit');
        
        $id = $this->request->param('id');
        
        if(!$id) {
            $this->redirect('/sample/user');
        }
        
        $success = $this->request->query('success');
        if($success) {
            $view->set('success', true);
        }
        
        $user = Model_Sample_User::find($id);
        if($user) {
            $view->set('user', $user);
        }
        
        $this->template->content = $view->render();
    }
    
    public function action_update() {   // handle form submisison
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if($this->request->method() != 'POST') {
            $this->redirect('/sample/user');
        }
        
        $_errors = array();
        $view = View::factory('sample/user/edit');
        $view->bind('_errors', $_errors);
        
        $arr_allowed = array(
            'full_name',
            'username',
            'id'
        );
        
        $arr_submitted = array_keys($this->request->post());
        
        try {
            if($arr_allowed != $arr_submitted) {
                throw new Exception_App('Invalid form submission', 400);
            }
            
            $id = $this->request->post('id');
            
            if(is_numeric($id) == FALSE) {
                throw new Exception_App('Invalid form submission', 400);
            }
            
            $user = Model_Sample_User::find($id);
            /* @var $user Model_Sample_User */
            $user->full_name = $this->request->post('full_name');
            $user->username = $this->request->post('username');
            $rowCount = $user->validate($_errors)
                    ->update();
            
            if($rowCount) {
                $this->redirect("/sample/user/edit/$id?success=1");
            }
        } catch (Exception_App $ex) {
            Log::instance()->add(LOG::ERROR, $ex->getMessage());
            
            if(is_a($ex, 'Exception_ValidationFailure')) { /* @var $ex Exception_ValidationFailure */
                $_errors = $ex->_errors;
            } else {
                $_errors['flash'] = $ex->getMessage();
            }
            
            $this->template->content = $view->render();
        }
    }
    
    public function action_delete() {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        $id = $this->request->param('id');
        
        if(!$id) {
            $this->redirect('/sample/user');
        }
        
        $rowCount = Model_Sample_User::delete($id);
            
        if($rowCount) {
            $this->redirect('/sample/user?delete=success');
        }
    }
    
}
