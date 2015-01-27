<?php

/**
 * Description of MyModel
 *
 * @author mageshravi
 */
class DBService {
    
    /** @var PDO */ 
    private static $pdo;
    
    /** @var string */
    public $table_name;
    public $model_name;
    
    public function __construct($table_name=NULL, $model_name=NULL) {
        $this->table_name = $table_name;
        $this->model_name = $model_name;
    }

    public static function instance($name='default') {
        
        if(self::$pdo)
            return self::$pdo;
        
        $config = Kohana::$config->load('database');

        if( ! isset($config[$name]))
            $name = 'default';
        
        $connection = $config[$name]['connection'];
        
        // type is not defined
        if ( ! isset($config[$name]['type'])) {
            throw new Kohana_Exception('Database type not defined in :name configuration',
                    array(':name' => $name));
        }
        
        // type is not PDO
        if ($config[$name]['type'] != 'PDO') {
            throw new Kohana_Exception('Database type is NOT PDO in :name configuration', 
                    array(':name' => $name));
        }
        
        try {
            self::$pdo = new PDO($connection['dsn'], $connection['username'], $connection['password']);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            Log::instance()->add(Log::DEBUG, "Could not create DB connection: {$e->getMessage()}");
            throw new Kohana_Exception('Could not create PDO connection!');
        }
        
        return self::$pdo;
    }
    
    public function find($id, $assc=FALSE) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null($this->table_name) || is_null($this->model_name))
            return;
        
        $stmt = $this->instance()->prepare('
            SELECT
                *
            FROM
                '.$this->table_name.'
            WHERE
                id = :id
            LIMIT 1
            ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            
            if($stmt->rowCount() != 1)
                return FALSE;

            if($assc)
                return $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt->setFetchMode(PDO::FETCH_CLASS, $this->model_name);
            return $stmt->fetch();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFoundException($this->table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
    
    public function get_all($assc=FALSE) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null($this->table_name) || is_null($this->model_name))
            return;
        
        $stmt = $this->instance()->prepare('
            SELECT
                *
            FROM
                '.$this->table_name.'
            ');
        
        try {
            $stmt->execute();
            
            if($stmt->rowCount() <= 0)
                return FALSE;

            if($assc)
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->setFetchMode(PDO::FETCH_CLASS, $this->model_name);
            return $stmt->fetchAll();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFoundException($this->table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
    
    public function delete($id) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null($this->table_name) || is_null($this->model_name))
            return;
        
        $stmt = $this->instance()->prepare('
            DELETE FROM
                '.$this->table_name.'
            WHERE
                id = :id
            LIMIT 1
            ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            return $stmt->rowCount();
            
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFoundException($this->table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
}
