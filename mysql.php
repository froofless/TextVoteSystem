<?php

// Update these with correct values
$username = "admin";
$password = "2ee4e13a587ed32c4557e5b9ddda39e26f15f73ea504d38d";
$hostname = "localhost";
$database = "mysql";


$mysqli = new mysqli($hostname, $username, $password, $database);
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
