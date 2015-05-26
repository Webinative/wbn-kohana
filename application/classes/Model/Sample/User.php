<?php

/**
 * Description of User
 *
 * @author mageshravi
 */
class Model_Sample_User extends WbnModel {
    
    public static $table_name = 'SAM_USERS';
    public static $model_name = __CLASS__;
    
    public $id;
    public $full_name;
    public $username;
    public $passwd;
    
    /**
     * 
     * @param array $_errors
     * @return \Model_Sample_User
     * @throws Exception_ValidationFailure
     */
    public function validate(array &$_errors) {
        
        $this->validate_not_null_fields(array(
            'full_name' => 'Full name is required',
            'username' => 'Username is required',
            'passwd' => 'Password is required'
        ), $_errors);
        
        if(!empty($_errors)) {
            throw new Exception_ValidationFailure($_errors);
        }
        
        return $this;
    }
    
    /**
     * Hash the current object's password field using SHA1 algorithm
     * @return \Model_Sample_User
     */
    public function hash_passwd() {
        $this->passwd = sha1($this->passwd);
        return $this;
    }
}
