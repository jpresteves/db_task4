<?php

function connect($servername, $username, $password, $database) {
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $database);
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

function check_connection ($conn) {
    if ($conn){
        return ('Connected');
    } else { return ('Not Connected');}

    return ('None');
}



?>
