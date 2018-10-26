<?php
session_start();

require_once 'vendor/autoload.php';
require_once 'Model.php';

if(isset($_POST['action']))
    $action = $_POST['action'];

if(isset($_POST['database']))
    $database = $_POST['database'];

if(isset($_POST['table']))
    $table = $_POST['table'];

//$model = Model::instance();

switch ($action){
//    case 'get_databases' : echo json_encode(767);break;
    case 'get_databases' : echo json_encode(Model::get_databases() );break;
    case 'get_databases_tables' : echo json_encode(Model::get_databases_tables($_POST['database']) );break;
    case 'get_tables_fields' : echo json_encode(Model::get_tables_fields($database, $table));break;
    case 'generate_example' : echo json_encode(Model::generate_example($_POST['faker_name'], json_decode($_POST['parameters']), $database));break;
    case 'insert' : echo json_encode(Model::insert(json_decode($_POST['fields']),$_POST['count'], $database, $table));break;
}


