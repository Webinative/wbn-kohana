<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Description of UserSession
 *
 * @author Gandhi
 */
class Model_UserSession extends Model {
    //put your code here

    public $session_id;
    public $user_id;
    public $remote_ip;
    public $auth_key;
    public $last_sign_in;

    public function set_session_id($session_id) {
        $this->session_id = $session_id;
        return $this;
    }

    public function set_user_id($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function set_remote_ip($remote_ip) {
        $this->remote_ip = $remote_ip;
        return $this;
    }

    public function set_auth_key($auth_key) {
        $this->auth_key = $auth_key;
        return $this;
    }

    public function set_last_sign_in($last_sign_in) {
        $this->last_sign_in = $last_sign_in;
        return $this;
    }
    
    public function create(){
        $serv_user_session = new Service_UserSession;
        return $serv_user_session->create($this);
    }

}
