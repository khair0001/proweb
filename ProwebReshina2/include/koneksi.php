<?php

$host = "localhost";
$username = "root";
$password = "";
$dbname = "reshina_db3";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Makassar'); // atau 'Asia/Ujung_Pandang' untuk WITA