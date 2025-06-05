<?php
// Hapus gambar produk via AJAX
session_start();
include '../include/koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit();
}

$user_id = $_SESSION['user_id'];
$img_id = isset($_POST['img_id']) ? intval($_POST['img_id']) : 0;
if ($img_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'Invalid image id']);
  exit();
}

// Ambil data gambar dan pastikan user adalah pemilik produk
$q = mysqli_query($conn, "SELECT image_produk.*, produk.id_user FROM image_produk JOIN produk ON image_produk.id_produk = produk.id WHERE image_produk.id = $img_id");
if (!$q || mysqli_num_rows($q) == 0) {
  echo json_encode(['success' => false, 'error' => 'Image not found']);
  exit();
}
$row = mysqli_fetch_assoc($q);
if ($row['id_user'] != $user_id) {
  echo json_encode(['success' => false, 'error' => 'Forbidden']);
  exit();
}
$file_path = '../uploads/products/' . $row['file_name'];

// Hapus file
if (file_exists($file_path)) {
  unlink($file_path);
}
// Hapus record di DB
mysqli_query($conn, "DELETE FROM image_produk WHERE id = $img_id");

echo json_encode(['success' => true]);
