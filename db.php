<?php
// Load Composer dependencies (Medoo & other libraries)
require __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;


// Initialize Medoo database connection
$db = new Medoo([
    'database_type' => 'mysql',     
    'server' => 'localhost',   
    'database_name' => 'db',       
    'username' => 'root',         
    'password' => '',             
]);

// Secret key used for generating and verifying JWT tokens
$jwt_secret = "MY_ULTRA_SECRET_KEY_123";
?>
