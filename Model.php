<?php

class Model
{
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $charset = 'utf8';

    private $pdo;
    private $error;
    private $output;
    private $constrains = array('constrains_names' => array(), 'full_constrains' => array());

    // Connect to pdo
    public function __construct($db = null)
    {
        // Get Db name if provided
        if(null !== $db)
            $db = ';dbname=' . $db;
        else
            $db = '';

        // Set DSN
        $dsn ='mysql:host=' . $this->host . $db . ';charset=' . $this->charset;

        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => TRUE,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => FALSE,
//            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

        // Create new PDO
        try{
            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e){
            $this->error = $e->getMessage();
        }
    }

    public function get_databases()
    {
        $result = $this->pdo->query('SHOW DATABASES');

        $output = [];

        while($row = $result->fetch(PDO::FETCH_NUM)) {
            if (($row[0] != "information_schema") && ($row[0] != "mysql") && ($row[0] != "performance_schema") && ($row[0] != "phpmyadmin")) {
                $output[] = $row[0] . "\r\n";
            }
        }

        return $output;
    }

    public function get_databases_tables()
    {
        return $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_tables_fields($table)
    {
        $this->constrains = array('constrains_names' => array(), 'full_constrains' => array());
        $this->get_constrains($table);

        $field_names = array();

        $sql = "DESCRIBE " . $table;
        $stmt = $this->pdo->query($sql);

        $fields = $stmt->fetchAll();

        foreach ($fields as $field) {
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

            if(isset($this->constrains['constrains_names']) && isset($this->constrains['full_constrains'])) {
                if (in_array($field['Field'], $this->constrains['constrains_names'])) {
                    for ($e = 0; $e < count($this->constrains['full_constrains']); $e++) {
                        if ($field['Field'] == $this->constrains['full_constrains'][$e]['COLUMN_NAME']) {
                            $new_field['foreign_key'] = true;
                            $new_field['referenced-table-name'] = $this->constrains['full_constrains'][$e]['REFERENCED_TABLE_NAME'];
                            $new_field['referenced-column-name'] = $this->constrains['full_constrains'][$e]['REFERENCED_COLUMN_NAME'];
                            $new_field['status'] = 'disabled';
                        }
                    }
                }
            }

            $this->output[] = $new_field;
        }

        return $this->output;
    }

    public function get_constrains($table)
    {
        // Sql, getting all constrains from table users, where constrains = 'foreign_key', getting table name, constraint type,constraint name, refferenced table, refferenced column

        $sql = "SELECT i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME 
	            FROM information_schema.TABLE_CONSTRAINTS i 
	            LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
	            ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
	            WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' 
	            AND i.TABLE_SCHEMA = database()
                AND i.TABLE_NAME = :table";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':table', $table);
        $stmt->execute();

        while($row = $stmt->fetch()) {
            $this->constrains['constrains_names'][] = $row['COLUMN_NAME'];
            $this->constrains['full_constrains'][] = $row;
        }

    }

    public function generate_example($name, $parameters) {
        if($name === 'Foreign') {
            return $this->get_foreign_keys($parameters);
        }

        $faker = Faker\Factory::create();
        return call_user_func_array(array($faker, $name), $parameters);
    }

    public function get_foreign_keys($parameters)
    {
        $field = trim(htmlspecialchars($parameters[1]) );
        $table = trim(htmlspecialchars($parameters[0]) );

        $sql = "SELECT $field FROM $table ORDER BY RAND() LIMIT 5";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function insert($fields, $count, $table)
    {
        $faker = Faker\Factory::create();

        $columns = implode(array_column($fields, 'column_name'), ', ');
        $valuesArray = array_column($fields, 'column_value');

        for ($i = 0; $i < $count; $i++){
            $values = [];

            foreach ($valuesArray as $value) {
                if($value->field_name === 'Foreign') {
                    $foreign_table = trim(htmlspecialchars($value->params[0]) );
                    $foreign_field = trim(htmlspecialchars($value->params[1]) );

                    $sql = "SELECT $foreign_field
                            FROM $foreign_table
                            ORDER BY RAND()
                            LIMIT 1";

                    $result = $this->pdo->query($sql);
                    $values[] = $result->fetch()["id"];
                } else {
                    $values[] = call_user_func_array(array($faker, $value->field_name), $value->params);
                }
            }

            $values_to_input = ":".implode(",:", array_keys($values));
            $sql = "INSERT INTO $table($columns) VALUES($values_to_input)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($values as $key => $value) {
                switch (true){
                    case is_int($value) : $type = PDO::PARAM_INT; break;
                    case is_bool($value) : $type = PDO::PARAM_BOOL; break;
                    case is_null($value) : $type = PDO::PARAM_NULL; break;
                    default : $type = PDO::PARAM_STR;
                }
                $stmt->bindValue(":$key",$value, $type);
            }

            $stmt->execute();
        }

        return true;
    }

}