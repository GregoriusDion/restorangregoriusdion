<?php
// File: includes/header.php
$halaman_aktif = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Restoran 🍽️</title>
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>css/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">🍽️ RestoApp</a>
    <nav>
        <a href="index.php"       class="<?= $halaman_aktif == 'index.php'       ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="menu.php"        class="<?= $halaman_aktif == 'menu.php'        ? 'active' : '' ?>">🍜 Menu</a>
        <a href="pesanan.php"     class="<?= $halaman_aktif == 'pesanan.php'     ? 'active' : '' ?>">📋 Pesanan</a>
        <a href="transaksi.php"   class="<?= $halaman_aktif == 'transaksi.php'   ? 'active' : '' ?>">💰 Transaksi</a>
    </nav>
</div>
