<?php
/* ================= DATABASE CONFIG ================= */

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "mrkit";

/* ================= CONNECT ================= */

$link = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

/* ================= CHECK CONNECTION ================= */

if (!$link) {
    die("Database Connection Failed : " . mysqli_connect_error());
}

/* ================= SET CHARSET ================= */

mysqli_set_charset($link, "utf8mb4");
?>