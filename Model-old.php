<?php

define('DB_HOST', 'localhost');
//define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8');

class Model2
{
    protected static $instance = null;
    public static $db;
    public static $output;
    public static $constrains = array('constrains_names' => array(), 'full_constrains' => array());

    protected function __construct() {}
    protected function __clone() {}

    public static function instance($db = null)
    {

        if(null !== $db)
            $db = ';dbname=' . trim(htmlspecialchars($db));
        else
            $db = '';

        if (self::$instance === null)
        {
            $options  = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => FALSE,
            );
            $dsn = 'mysql:host=' . DB_HOST . $db . ';charset='.DB_CHAR;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    public static function get_databases()
    {
        $pdo = self::instance();
        $sql = 'SHOW DATABASES';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $output = [];
//    $rs = $db->query("SHOW DATABASES");
        while($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (($row[0] != "information_schema") && ($row[0] != "mysql") && ($row[0] != "performance_schema") && ($row[0] != "phpmyadmin")) {
                $output[] = $row[0] . "\r\n";
            }

        }

        return $output;
    }

    public static function get_databases_tables($db)
    {
//        self::$db = trim(htmlspecialchars($db) ) ;
//        $_SESSION['db'] = trim(htmlspecialchars($db) );
        self::$instance = null;
//        self::$us = 'halo';

        $pdo = self::instance($db);

        $sql = "SHOW TABLES";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $output = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $output;
    }

    public static function get_tables_fields($db, $table)
    {
        self::$constrains = array('constrains_names' => array(), 'full_constrains' => array());
        self::get_constrains($db, $table);

        $field_names = array();

        $sql = "DESCRIBE " . $table;
        $pdo = self::instance($db);
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $fields = $stmt->fetchAll();

        foreach ($fields as $field) {
//            if($field['Field'] != 'id') {

            $field_name = $field['Field'];
            $field_type = $field['Type'];
            $key = $field['Key'];
            $null = $field['Null'];
            $default = $field['Default'];
            $extra = $field['Extra'];

            $field_names[] = $field_name;

            $new_field = array(
                'key' => $key,
                'null' => $null,
                'default' => $default,
                'extra' => $extra,
                "field_type" => $field_type,
                'name' => $field_name,
                'status' => '',
                'foreign_key' => false,
            );

            if(isset(self::$constrains['constrains_names']) && isset(self::$constrains['full_constrains'])) {
                if (in_array($field['Field'], self::$constrains['constrains_names'])) {
                    for ($e = 0; $e < count(self::$constrains['full_constrains']); $e++) {
                        if ($field['Field'] == self::$constrains['full_constrains'][$e]['COLUMN_NAME']) {
                            $new_field['foreign_key'] = true;
                            $new_field['referenced-table-name'] = self::$constrains['full_constrains'][$e]['REFERENCED_TABLE_NAME'];
                            $new_field['referenced-column-name'] = self::$constrains['full_constrains'][$e]['REFERENCED_COLUMN_NAME'];
                            $new_field['status'] = 'disabled';
                        }
                    }
                }
            }

            self::$output[] = $new_field;

//            }
        }

        return self::$output;
    }

    public static function get_constrains($db, $table)
    {
        // Sql, getting all constrains from table users, where constrains = 'foreign_key', getting table name, constraint type,constraint name, refferenced table, refferenced column

        $sql = "SELECT i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME 
	            FROM information_schema.TABLE_CONSTRAINTS i 
	            LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
	            ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
	            WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' 
	            AND i.TABLE_SCHEMA = database()
                AND i.TABLE_NAME = :table";

        $pdo = self::instance($db);
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':table', $table);
//        $stmt->bindValue(':database', $db);
        $stmt->execute();

        while($row = $stmt->fetch()) {
            self::$constrains['constrains_names'][] = $row['COLUMN_NAME'];
            self::$constrains['full_constrains'][] = $row;
        }

    }

    public static function generate_example($name, $parameters, $database = null) {
        if($name === 'Foreign') {
            return self::get_foreign_keys($parameters, $database);
        }

        $faker = Faker\Factory::create();
        return call_user_func_array(array($faker, $name), $parameters);
    }

    public static function get_foreign_keys($parameters, $database)
    {
        $field = $parameters[1];
        $table = $parameters[0];

        self::$instance = null;
        $pdo = self::instance(trim(htmlspecialchars($database)));
        $sql = "SELECT $field FROM $table ORDER BY RAND() LIMIT 5";
        $stmt = $pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public static function insert($fields, $count, $database, $table)
    {
        self::$instance = null;
        $pdo = self::instance(trim(htmlspecialchars($database)));
        $faker = Faker\Factory::create();

        $columns = implode(array_column($fields, 'column_name'), ', ');
        $valuesArray = array_column($fields, 'column_value');

        for ($i = 0; $i < $count; $i++){
            $values = [];

            foreach ($valuesArray as $value) {
                if($value->field_name === 'Foreign') {
                    $tuble = $value->params[0];
                    $field = $value->params[1];
                    $sql = "SELECT $field FROM $tuble ORDER BY RAND() LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $values[] = '\'' . $stmt->fetch()["id"] . '\'';
//                    var_dump('\'' . $stmt->fetch() . '\'');
                } else {
                    $values[] = '\'' . call_user_func_array(array($faker, $value->field_name), $value->params) . '\'';
                }
            }

            $valuesChanged = implode($values, ', ');
            $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($valuesChanged)");
            $stmt->execute();
        }

        return 200;
    }
    
    public static function run($sql, $args = [])
    {
        if (!$args)
        {
            return self::instance()->query($sql);
        }
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}