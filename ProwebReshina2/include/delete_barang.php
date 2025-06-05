<?php
// delete_barang.php: Soft delete produk, hanya update status menjadi 'inactive'

include '../include/koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: ../pages/dashboard.php');
    exit();
}

$product_id = intval($_GET['id']);

// Cek login user
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Pastikan produk milik user yang login
$cek = mysqli_query($conn, "SELECT id FROM produk WHERE id = $product_id AND id_user = $user_id");
if (mysqli_num_rows($cek) === 0) {
    header('Location: ../pages/dashboard.php?err=notfound');
    exit();
}

// Soft delete: update status jadi 'inactive'
$update = mysqli_query($conn, "UPDATE produk SET status = 'inactive' WHERE id = $product_id");

if ($update) {
    header('Location: ../pages/dashboard.php?msg=deleted');
    exit();
} else {
    header('Location: ../pages/dashboard.php?err=fail');
    exit();
}
