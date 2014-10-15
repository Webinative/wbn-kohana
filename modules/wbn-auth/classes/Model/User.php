<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Description of User
 *
 * @author Gandhi
 */
class Model_User extends Model {
    
    public $id;
    public $username;
    public $password;
    
    const ID_KEY = 'user_id';
    const USERNAME_KEY = 'username';
    const AUTH_KEY = 'auth_key';
    
    function set_id($id) {
        $this->id = $id;
        return $this;
    }

    function set_username($username) {
        $this->username = $username;
        return $this;
    }

    function set_password($password) {
        $this->password = $password;
    }


}
