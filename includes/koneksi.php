<?php
// File: includes/koneksi.php
// Pengaturan koneksi ke database XAMPP

$host     = "localhost";
$user     = "root";
$password = "";          // Default XAMPP: kosong
$database = "db_restoran";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("<div style='font-family:sans-serif;color:red;padding:20px;'>
        ❌ Koneksi gagal! Pastikan XAMPP sudah nyala dan database sudah dibuat.<br>
        Error: " . mysqli_connect_error() . "
    </div>");
}

mysqli_set_charset($koneksi, "utf8");
?>
