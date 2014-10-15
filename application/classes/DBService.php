<?php

/**
 * Description of MyModel
 *
 * @author mageshravi
 */
class DBService {
    
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
}
