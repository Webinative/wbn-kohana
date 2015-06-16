<?php

/**
 * Description of WbnModel
 *
 * @author mageshravi
 * @version 1.0.1
 * 
 * CHANGELOG
 * ---------
 * One return type per function norm implemented for find() and all() functions
 * New function find_by(column) added.
 * Throws Exception_UniqueKeyConstraintViolation, Exception_RecordNotFound when applicable
 * Bug fixes
 * ---------
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
     * @throws Exception_App
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
            
            // unique key constraint violation
            if($ex->getCode() == '23000') {
                throw new Exception_UniqueKeyConstraintViolation;
            }
            
            // @todo handle foreign key constraint violation
            
            throw new Exception_App($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $id primary key
     * @return WbnModel
     * @throws Exception_TableNotFound
     * @throws Exception_App
     * @throws Exception_RecordNotFound
     */
    public static function find($id) {
        $stmt = self::exec_find($id);
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::$model_name);
        return $stmt->fetch();
    }
    
    /**
     * 
     * @param int $id
     * @return array row as associative array
     * @throws Exception_RecordNotFound
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    public static function assoc_find($id) {
        $stmt = self::exec_find($id);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * @param int $id
     * @return PDOStatement
     * @throws Exception_TableNotFound
     * @throws Exception_App
     * @throws Exception_RecordNotFound
     */
    private static function exec_find($id) {
        if(is_null(static::$table_name) || is_null(static::$model_name)) {
            throw new Exception_TableNotFound("Missing table name or model name!");
        }

        $stmt = self::instance()->prepare('
            SELECT
                *
            FROM
                '.static::$table_name.'
            WHERE
                id = :id
            LIMIT 1
            ');
        
        try {
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if($stmt->rowCount() == 0) {
                throw new Exception_RecordNotFound();
            }
            
            return $stmt;

        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02') {
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            }
            
            throw new Exception_App($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param int $data_type
     * @return array array of WbnModel
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    public static function find_by($column, $value, $data_type = PDO::PARAM_STR) {
        $stmt = self::exec_find_by($column, $value, $data_type);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::$model_name);
    }
    
    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param int $data_type
     * @return array array of rows
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    public static function assoc_find_by($column, $value, $data_type = PDO::PARAM_STR) {
        $stmt = self::exec_find_by($column, $value, $data_type);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param int $data_type Explicit data type for the parameter using the PDO::PARAM_* constants.
     * @return PDOStatement
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    private static function exec_find_by($column, $value, $data_type) {
        
        if(is_null(static::$table_name) || is_null(static::$model_name)) {
            throw new Exception_TableNotFound("Missing table name or model name!");
        }
        
        if (property_exists(static::$model_name, $column) !== TRUE) {
            throw new Exception_App("Column \"$column\" not found!");
        }
        
        $query = "
                SELECT
                    *
                FROM
                    " . self::$table_name . "
                WHERE
                    $column = :value
                ";

        $stmt = self::instance()->prepare($query);
        
        try {
            $stmt->bindValue(':value', $value, $data_type);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02') {
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            }
            
            throw new Exception_App($ex->getMessage());
        }
    }
    
    /**
     * 
     * @return array array of WbnModel objects
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    public static function all() {
        $stmt = self::exec_all();
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::$model_name);
    }
    
    /**
     * 
     * @return array array of rows
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    public static function assoc_all() {
        $stmt = self::exec_all();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * @return PDOStatement
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
    private static function exec_all() {
        if(is_null(static::$table_name) || is_null(static::$model_name)) {
            throw new Exception_TableNotFound('Missing table name or model name!');
        }
        
        $stmt = self::instance()->prepare('
            SELECT
                *
            FROM
                '.static::$table_name.'
            ');
        
        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $ex) {
            Log::instance()->add(Log::ERROR, $ex->getMessage());
            
            if($ex->getCode() == '42S02') {
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            }
            
            throw new Exception_App($ex->getMessage());
        }
    }
    
    /**
     * 
     * @return int rowCount
     * @throws Exception_TableNotFound
     * @throws Exception_App
     */
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
        
        try {
            $stmt->execute($_input_params);
            return $stmt->rowCount();
        } catch (PDOException $ex) {
            Log::instance()->add(Log::DEBUG, $ex->getMessage());
            
            if($ex->getCode() == '42S02')
                throw new Exception_TableNotFound(static::$table_name . ' table not found!');
            
            throw new Exception_App($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $id
     * @return int number of rows deleted
     * @throws Exception_TableNotFound
     * @throws Exception_App
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
            
            throw new Exception_App($ex->getMessage());
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
        
        if(static::$timestamps) {
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
            
            if($field_attr === "" || trim($field_attr) === "" || is_null($field_attr)) {
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
