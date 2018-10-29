<?php
session_start();

require_once 'vendor/autoload.php';
require_once 'Model.php';

$database = isset($_POST['database']) ? trim(htmlspecialchars($_POST['database']) ) : null;
$table = isset($_POST['table']) ? trim(htmlspecialchars($_POST['table']) ) :  null;
$action = isset($_POST['action']) ? trim(htmlspecialchars($_POST['action']) ) :  null;

$model = new Model($database);

switch ($action){
    case 'get_databases' : echo json_encode($model->get_databases() );break;
    case 'get_databases_tables' : echo json_encode($model->get_databases_tables() );break;
    case 'get_tables_fields' : echo json_encode($model->get_tables_fields($table) );break;
    case 'generate_example' : echo json_encode($model->generate_example($_POST['faker_name'], json_decode($_POST['parameters']) ) );break;
    case 'insert' : echo json_encode($model->insert(json_decode($_POST['fields']),$_POST['count'], $table) );break;
}


