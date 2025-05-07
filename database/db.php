<?php
function getDatabaseConnection(){
    $servername ="localhost";
    $username = "root";
    $password = "";
    $database = "lelelemon_db";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error){
    die("Error failed to connect to MySQLI: " . $connection->connect_error);
    }
    return $connection;
}