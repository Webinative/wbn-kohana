<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Description of User
 *
 * @author Gandhi
 */
class Service_User extends DBService {
    
    public function is_valid_user($username, $password) {
        Log::instance()->add(Log::DEBUG, 'Inside' . __METHOD__ . '()');
        
        $config = Kohana::$config->load('wbnauth');
        $user_table = $config->get('user_table');
        
        $stmt = $this->instance()->prepare('
            SELECT
                *
            FROM
                '.$user_table.'
            WHERE
                username = :username
                AND password = :password
            LIMIT 1
            ');
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', md5($password));
        $stmt->execute();

        if($stmt->rowCount() == 0)
            return FALSE;
        else {
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Model_User');
            return $stmt->fetch();
        }
    }
    
    public function get_by_md5_username($username) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        $config = Kohana::$config->load('wbnauth');
        $user_table = $config->get('user_table');
        
        $stmt = $this->instance()->prepare('
            SELECT
                *
            FROM
                '.$user_table.'
            WHERE
                md5(username) = :username
            LIMIT 1
            ');
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        
        if($stmt->rowCount() != 1)
            return FALSE;
        
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Model_User');
        return $stmt->fetch();
    }
}
