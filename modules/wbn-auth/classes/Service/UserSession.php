<?php

/**
 * Description of UserSession
 *
 * @author Gandhi
 */
class Service_UserSession extends DBService {
    
    public function create(Model_UserSession $user_session) {
        Log::instance()->add(Log::DEBUG, 'Inside' . __METHOD__ . '()');
        
        $config = Kohana::$config->load('wbnauth');
        $user_session_table = $config->get('user_sessions_table');

        $stmt = $this->instance()->prepare('
            INSERT INTO '.$user_session_table.' (
                user_id,
                remote_ip,
                auth_key,
                last_sign_in
            ) VALUES (
                :user_id,
                :remote_ip,
                :auth_key,
                :last_sign_in
            )
        ');
        $stmt->bindValue(':user_id', $user_session->user_id);
        $stmt->bindValue(':remote_ip', $user_session->remote_ip);
        $stmt->bindValue(':auth_key', $user_session->auth_key);
        $stmt->bindValue(':last_sign_in', $user_session->last_sign_in);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
