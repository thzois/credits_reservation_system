<?php
	$servername = "";
	$username = "";
	$password = "";
	$dbname = "";

	$conn = new mysqli($servername, $username, $password, $dbname);
	
    if (mysqli_connect_errno()) {
        die("Connection failed: " . $conn->connect_error);
    }
	
	return $conn;
?>
