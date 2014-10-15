<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Description of Authenticate
 *
 * @author Gandhi
 */
class Controller_User extends Controller_Template {
    
    public $template = 'user/template';
    
    public function action_login()
    {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if (!is_null(Session::instance()->get(Model_User::ID_KEY)))
            $this->redirect('/');
        else
            $this->authenticate(TRUE);

        $view = View::factory('user/login');
        
        $form_validation = Validation::factory($this->request->post());
        $form_validation->rule('username', 'not_empty')
                ->rule('username', 'min_length', array(':value', 5))
                ->rule('username', 'max_length', array(':value', 64))
                ->rule('password', 'not_empty');
        
        try {
            if ($this->request->post()) {
                if($form_validation->check()) {
                    // check if username and password is valid
                    $serv_user = new Service_User();
                    $user = $serv_user->is_valid_user($this->request->post('username'), $this->request->post('password'));
                    if (is_a($user, 'Model_User')) {
                        $this->set_session_vars_and_cookies($user);
                    } else
                        $this->redirect('user/login?status=failed');

                    $auth_key = $this->generate_auth_key(md5($user->username));
                    $user_session = new Model_UserSession();
                    $row_count = $user_session->set_user_id($user->id)
                            ->set_auth_key($auth_key)
                            ->set_remote_ip($_SERVER['REMOTE_ADDR'])
                            ->set_last_sign_in(date('Y-m-d H:i:s'))
                            ->create();

                    if ($row_count == 0)
                        $this->redirect('user/login');

                    $this->redirect('/');
                } else {
                    $view->set('errors', $form_validation->errors('user'));
                }
            }
        } catch (HTTP_Exception_400 $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            $view->set('error', $ex->getMessage());
        }

        if ($this->request->method() == 'GET' && isset($_GET['status']))
            $view->bind('status', $_GET['status']);

        $this->template->content = $view;

    }
    
    private function set_session_vars_and_cookies(Model_User $user) {
        // initialize variables for authentication key
        $auth_key = $this->generate_auth_key(md5($user->username));

        // set authentication key and session id
        Session::instance()->set(Model_User::ID_KEY, $user->id);
        Session::instance()->set(Model_User::USERNAME_KEY, $user->username);

        // set cookie
        Cookie::set(md5(Model_User::AUTH_KEY), $auth_key, time() + 365 * 24 * 7);
        Cookie::set(md5(Model_User::USERNAME_KEY), md5($user->username), time() + 365 * 24 * 7);
    }

    public static function authenticate($login_page = FALSE) {
        Log::instance()->add(Log::DEBUG, 'Inside' . __METHOD__ . '()');

        if (is_null(Session::instance()->get(Model_User::ID_KEY))) {

            // get cookie
            $cookie_auth_key = Cookie::get(md5(Model_User::AUTH_KEY));
            $cookie_username = Cookie::get(md5(Model_User::USERNAME_KEY));

            $auth_key = self::generate_auth_key($cookie_username);
            if (is_null($cookie_auth_key) || $cookie_auth_key != $auth_key) {
                if ($login_page == FALSE)
                    self::redirect('user/login');
            } else {
                // extend authentication key and session id expiry time
                $serv_user = new Service_User();
                $user = $serv_user->get_by_md5_username($cookie_username);

                Session::instance()->set(Model_User::ID_KEY, $user->id);
                Session::instance()->set(Model_User::USERNAME_KEY, $user->username);

                // set cookie
                Cookie::set(md5(Model_User::AUTH_KEY), $auth_key, time() + 365 * 24 * 7);
                Cookie::set(md5(Model_User::USERNAME_KEY), md5($user->username), time() + 365 * 24 * 7);
                if ($login_page)
                    self::redirect('/');
            }
        }
    }
    
    public static function generate_auth_key($username) {
        $salt = Cookie::$salt;
        return md5($salt . $username . $_SERVER['REMOTE_ADDR'] . $_SERVER["HTTP_USER_AGENT"] . $salt);
    }
    
    public function action_logout() {
        Log::instance()->add(Log::DEBUG, 'Inside' . __METHOD__ . '()');
        
        $view = View::factory('user/logout');
        
        try {
            Session::instance()->delete(Model_User::USERNAME_KEY);
            Session::instance()->delete(Model_User::ID_KEY);
            Cookie::delete(md5(Model_User::AUTH_KEY));
            Cookie::delete(md5(Model_User::USERNAME_KEY));
        } catch (Exception $exc) {
            Log::instance()->add(Log::ERROR, $exc->getMessage());
        }
        
        $this->template->content = $view;
    }

}
