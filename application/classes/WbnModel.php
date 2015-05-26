<?php

/**
 * Description of WbnModel
 *
 * @author mageshravi
 * @version 1.0
 */
class WbnModel {
    
    /** @var string */
    public $created_on;
    /** @var string */
    public $last_updated_on;
    
    /** @var string */
    protected static $table_name;
    /** @var string */
    protected static $model_name;
    /** @var boolean */
    protected static $timestamps = true;
    
    /** @var PDO */ 
    private static $pdo;
    
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
    
    // CRUD operations
    
    /**
     * 
     * @return int Last insert id
     * @throws Exception_TableNotFound
     * @throws Exception
     */
    public function create() {
        
        if(static::$timestamps) {
            $today = new DateTime();
            $this->created_on = $today->format('Y-m-d H:i:s');
            $this->last_updated_on = $today->format('Y-m-d H:i:s');
        }
        
        $model_attrs = $this->get_model_attrs();
        
        // construct query
        $cols = ""; $tokens = "";
        foreach($model_attrs as $attr => $value) {
            if($cols != ""){
                $cols .= ", ";
            }
            
            if($tokens != "") {
                $tokens .= ", ";
            }
            
            $cols .= $attr;
            $tokens .= ":".$attr;
        }

        $query = "INSERT INTO ".static::$table_name." (". $cols .") VALUES (". $tokens .")";

        $stmt = $this->instance()->prepare($query);

        foreach($model_attrs as $attr => $value) {
            $stmt->bindValue(':'.$attr, $this->{$attr});
        }

        try {
            $stmt->execute();
            $id = $this->instance()->lastInsertId();
            if(property_exists($this, 'id')) {
                $this->id = $id;
            }
            return $id;
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02') {
                throw new Exception_TableNotFound($this->table_name . ' table not found!');
            }
            
            // @todo handle unique key constraint violation
            
            // @todo handle foreign key constraint violation
            
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $id primary key
     * @param boolean $assc if set to TRUE, returns associative array if found, else boolean FALSE
     * @return WbnModel if found, else boolean FALSE
     * @throws Exception_TableNotFound
     * @throws Exception
     */
    public static function find($id, $assc=FALSE) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null(static::$table_name) || is_null(static::$model_name))
            return;

        $stmt = self::instance()->prepare('
            SELECT
                *
            FROM
                '.static::$table_name.'
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

            $stmt->setFetchMode(PDO::FETCH_CLASS, static::$model_name);
            return $stmt->fetch();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param boolean $assc if set to TRUE, returns array of rows (associative) if records found, else boolean FALSE
     * @return array array of WbnModel objects if records found, else boolean FALSE
     * @throws Exception_TableNotFound
     * @throws Exception
     */
    public static function all($assc=FALSE) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null(static::$table_name) || is_null(static::$model_name))
            return;
        
        $stmt = self::instance()->prepare('
            SELECT
                *
            FROM
                '.static::$table_name.'
            ');
        
        try {
            $stmt->execute();

            if($assc)
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->setFetchMode(PDO::FETCH_CLASS, static::$model_name);
            return $stmt->fetchAll();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
    
    public function update() {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(static::$timestamps) {
            $today = new DateTime();
            $this->last_updated_on = $today->format('Y-m-d H:i:s'); 
        }
        
        $model_attrs = $this->get_model_attrs();
        
        $set_clause = "";
        foreach($model_attrs as $attr => $value) {
            if($set_clause != ""){
                $set_clause .= ", ";
            }
            
            $set_clause .= "$attr = :$attr";
        }
        
        $query = "UPDATE ". static::$table_name 
                ." SET $set_clause WHERE id = :id LIMIT 1";
        
        Log::instance()->add(Log::DEBUG, $query);
        
        $stmt = $this->instance()->prepare($query);
        
        $_input_params = array();
        foreach($model_attrs as $attr => $value) {
            $_input_params[":$attr"] = $this->{$attr};
        }
        
        $_input_params[':id'] = $this->id;
        $stmt->execute($_input_params);
        
        return $stmt->rowCount();
    }
    
    /**
     * 
     * @param int $id
     * @return int number of rows deleted
     * @throws Exception_TableNotFound
     * @throws Exception
     */
    public static function delete($id) {
        Log::instance()->add(Log::DEBUG, 'Inside ' . __METHOD__ . '()');
        
        if(is_null(static::$table_name) || is_null(static::$model_name))
            return;
        
        $stmt = self::instance()->prepare('
            DELETE FROM
                '.static::$table_name.'
            WHERE
                id = :id
            LIMIT 1
            ');
        
        try {
            $stmt->execute(array(':id' => $id));
            return $stmt->rowCount();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            
            throw new Exception($ex->getMessage());
        }
    }
    
    // for internal operations
    
    /**
     * 
     * @return array
     */
    private function get_model_attrs() {
        $child_attrs = get_class_vars(get_class($this));
        $base_attrs = get_class_vars(__CLASS__);
        
        if(self::$timestamps) {
            // if timestamps are included, retain them
            unset($base_attrs['created_on']);
            unset($base_attrs['last_updated_on']);
        }
        
        // do not include base class attributes
        foreach($base_attrs as $attr => $value) {
            unset($child_attrs[$attr]);
        }
        
        // do not include id column from child
        unset($child_attrs['id']);
        
        // do not include related table columns from child
        foreach($child_attrs as $attr => $value) {
            if(preg_match('/^rel_/', $attr)) {
                unset($child_attrs[$attr]);
            }
        }
        
        return $child_attrs;
    }
    
    // validations
    
    /**
     * 
     * @param assoc $_fields Format ['field' => 'Error msg']
     * @param assoc $_errors Array to append the errors
     */
    public function validate_not_null_fields($_fields, &$_errors) {
        foreach($_fields as $field => $error_msg) {
            $field_attr = $this->{$field};
            
            if(empty($field_attr) || trim($field_attr) == "") {
                if(empty($_errors[$field])) {
                    $_errors[$field] = $error_msg;
                }
            }
        }
    }
    
    /**
     * 
     * @param assoc $_fields Format ['field' => array('allowed_1', 'allowed_2')]
     * @param assoc $_errors
     */
    public function validate_enum_fields($_fields, &$_errors) {
        foreach($_fields as $field => $arr_allowed) {
            $field_attr = $this->{$field};
            
            if(in_array($field_attr, $arr_allowed) === FALSE) {
                if(empty($_errors[$field])) {
                    $_errors[$field] = 'Invalid value for field';
                }
            }
        }
    }
}
