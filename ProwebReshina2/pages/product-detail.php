<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';

// Proses tawar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bidAmount'], $_GET['id'])) {
    $bidAmount = intval($_POST['bidAmount']);
    $product_id = intval($_GET['id']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $query = mysqli_query($conn, "SELECT harga, id_user, lelang_end_time FROM produk WHERE id = $product_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
        $current_price = intval($row['harga']);
        $seller_id = intval($row['id_user']);
        $lelang_end_time = $row['lelang_end_time'];
        $now = date('Y-m-d H:i:s');
        if ($user_id > 0 && $user_id != $seller_id && !empty($lelang_end_time) && $now < $lelang_end_time) {
            if ($bidAmount > $current_price) {
                // Simpan tawaran ke tabel tawaran
                mysqli_query($conn, "INSERT INTO tawaran (id_produk, id_user, jumlah_tawaran) VALUES ($product_id, $user_id, $bidAmount)");
                // Update harga produk
                mysqli_query($conn, "UPDATE produk SET harga = $bidAmount WHERE id = $product_id");
                // Ambil data penjual dan penawar
                $seller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, email, username, nomor_hp FROM user WHERE id = $seller_id"));
                $bidder = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, email, username, nomor_hp FROM user WHERE id = $user_id"));
                // Kirim notifikasi ke penjual (tabel notifications)
                $title = 'Tawaran Baru di Lelang';
                $message = "Ada tawaran baru Rp " . number_format($bidAmount,0,',','.') . " dari " . $bidder['username'] . " (email: " . $bidder['email'] . ", HP: " . $bidder['nomor_hp'] . ")";
                $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, sender_id, title, message, product_id) VALUES (?, ?, ?, ?, ?)");
                $stmtNotif->bind_param("iissi", $seller['id'], $bidder['id'], $title, $message, $product_id);
                $stmtNotif->execute();
                $stmtNotif->close();
                echo "<script>alert('Tawaran berhasil! Notifikasi telah dikirim ke penjual.');window.location.href='product-detail.php?id=$product_id';</script>";
                exit();
            } else {
                echo "<script>alert('Tawaran harus lebih besar dari harga saat ini!');window.location.href='product-detail.php?id=$product_id';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Tidak dapat menawar produk ini.');window.location.href='product-detail.php?id=$product_id';</script>";
            exit();
        }
    }
}

// Penentuan pemenang otomatis setelah lelang berakhir
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $product_id"));
    if (!empty($product['lelang_end_time']) && strtotime($product['lelang_end_time']) < time()) {
        // Cari tawaran tertinggi
        $last_bid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tawaran WHERE id_produk = $product_id ORDER BY jumlah_tawaran DESC, waktu DESC LIMIT 1"));
        if ($last_bid) {
            $winner = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE id = " . $last_bid['id_user']));
            $msg = "Selamat, Anda memenangkan lelang produk {$product['judul']} dengan tawaran Rp " . number_format($last_bid['jumlah_tawaran'],0,',','.');
            // Insert notifikasi ke pemenang (tabel notifications) hanya jika belum ada
            $title = 'Anda Menang Lelang';
            $message = $msg;
            $notif_pemenang_check = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = {$winner['id']} AND product_id = $product_id AND title = '$title'");
            if (mysqli_num_rows($notif_pemenang_check) == 0) {
                $stmtNotifWin = $conn->prepare("INSERT INTO notifications (user_id, sender_id, title, message, product_id) VALUES (?, NULL, ?, ?, ?)");
                $stmtNotifWin->bind_param("issi", $winner['id'], $title, $message, $product_id);
                $stmtNotifWin->execute();
                $stmtNotifWin->close();
            }
            // Kirim notifikasi ke penjual juga (hanya jika belum ada notifikasi pemenang untuk produk ini)
            $seller_id = $product['id_user'];
            $notif_check = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = $seller_id AND product_id = $product_id AND title = 'Lelang Selesai: Pemenang'");
            if (mysqli_num_rows($notif_check) == 0) {
                $seller_title = 'Lelang Selesai: Pemenang';
                $seller_message = "Lelang produk '{$product['judul']}' telah selesai. Pemenangnya adalah " . $winner['username'] . " dengan tawaran Rp " . number_format($last_bid['jumlah_tawaran'],0,',','.') . ".";
                $stmtNotifSeller = $conn->prepare("INSERT INTO notifications (user_id, sender_id, title, message, product_id) VALUES (?, ?, ?, ?, ?)");
                $stmtNotifSeller->bind_param("iissi", $seller_id, $winner['id'], $seller_title, $seller_message, $product_id);
                $stmtNotifSeller->execute();
                $stmtNotifSeller->close();
            }
            // Untuk demo, tampilkan alert jika user yang login adalah pemenang
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $winner['id']) {
                echo "<script>alert('$msg');</script>";
            }
        }
    }
}


$id_produk = $_GET['id'] ?? 0;
$product_query = "SELECT u.profile_image AS seller_photo 
                  FROM produk p 
                  JOIN user u ON p.id_user = u.id 
                  WHERE p.id = ?";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah data ditemukan
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $profile_image_path = $data['seller_photo'];
    $image_src = str_replace('../uploads/profile/', '', $profile_image_path);
}

// Fungsi waktu lalu (waktu relatif)
function waktu_lalu($timestamp)
{
    $selisih = time() - $timestamp;
    if ($selisih < 60) {
        return $selisih . ' detik yang lalu';
    } elseif ($selisih < 3600) {
        return floor($selisih / 60) . ' menit yang lalu';
    } elseif ($selisih < 86400) {
        return floor($selisih / 3600) . ' jam yang lalu';
    } elseif ($selisih < 2592000) {
        return floor($selisih / 86400) . ' hari yang lalu';
    } elseif ($selisih < 31536000) {
        return floor($selisih / 2592000) . ' bulan yang lalu';
    } else {
        return floor($selisih / 31536000) . ' tahun yang lalu';
    }
}

// Soft delete produk jika form hapus dikirim
$delete_msg = '';
if (isset($_POST['delete_product']) && isset($_POST['delete_product_id'])) {
    $delete_id = intval($_POST['delete_product_id']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    // Pastikan produk milik user yang login
    $cek = mysqli_query($conn, "SELECT id FROM produk WHERE id = $delete_id AND id_user = $user_id");
    if (mysqli_num_rows($cek) > 0) {
        $update = mysqli_query($conn, "UPDATE produk SET status = 'inactive' WHERE id = $delete_id");
        if ($update) {
            $delete_msg = '<div class="alert alert-success" style="margin:16px 0 0 0;">Barang berhasil dihapus (status tidak aktif).</div>';
        } else {
            $delete_msg = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Gagal menghapus barang. Silakan coba lagi.</div>';
        }
    } else {
        $delete_msg = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Akses tidak diizinkan.</div>';
    }
}
// Restore produk jika form pulihkan dikirim
if (isset($_POST['restore_product']) && isset($_POST['restore_product_id'])) {
    $restore_id = intval($_POST['restore_product_id']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $cek = mysqli_query($conn, "SELECT id FROM produk WHERE id = $restore_id AND id_user = $user_id");
    if (mysqli_num_rows($cek) > 0) {
        $update = mysqli_query($conn, "UPDATE produk SET status = 'active' WHERE id = $restore_id");
        if ($update) {
            $delete_msg = '<div class="alert alert-success" style="margin:16px 0 0 0;">Barang berhasil dipulihkan (aktif kembali).</div>';
        } else {
            $delete_msg = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Gagal memulihkan barang. Silakan coba lagi.</div>';
        }
    } else {
        $delete_msg = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Akses tidak diizinkan.</div>';
    }
}

// Initialize variables
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$seller = null;
$seller_phone = ''; // Default empty phone number

// Check if product ID is provided
if ($product_id > 0) {
    // Get product details
    $product_query = "SELECT p.*, u.username as seller_name, u.nomor_hp as seller_phone, u.profile_image as seller_photo, 
                      u.created_at as seller_join_date, k.nama as category_name, 
                      jp.nama as jenis_barang, kd.nama as condition_name 
                      FROM produk p 
                      JOIN user u ON p.id_user = u.id 
                      JOIN kategori k ON p.id_kategori = k.id 
                      JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                      JOIN kondisi kd ON p.id_kondisi = kd.id 
                      WHERE p.id = ?";

    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        $seller_phone = !empty($product['seller_phone']) ? $product['seller_phone'] : '+6281234567890'; // Use default if empty
    } else {
        // Product not found or inactive
        header('Location: dashboard.php');
        exit();
    }

    // Get product images
    $images_query = "SELECT * FROM image_produk WHERE id_produk = ? ORDER BY is_primary DESC";
    $images_stmt = mysqli_prepare($conn, $images_query);
    mysqli_stmt_bind_param($images_stmt, "i", $product_id);
    mysqli_stmt_execute($images_stmt);
    $images_result = mysqli_stmt_get_result($images_stmt);
    $images = [];

    while ($image = mysqli_fetch_assoc($images_result)) {
        $images[] = $image;
    }

    // Get similar products (same category, same seller, or same transaction type)
    $similar_products = [];
    $similar_query = "SELECT p.*, u.username as seller_name, 
                      jp.nama as jenis_barang, kd.nama as condition_name,
                      (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                      k.nama as kategori_nama
                      FROM produk p 
                      JOIN user u ON p.id_user = u.id 
                      JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                      JOIN kondisi kd ON p.id_kondisi = kd.id 
                      JOIN kategori k on p.id_kategori = k.id
                      WHERE p.id != ? AND p.status = 'active' AND
                      (k.id = ? OR p.id_user = ? OR p.id_jenis_produk = ?)
                      ORDER BY RAND() LIMIT 4";

    $similar_stmt = mysqli_prepare($conn, $similar_query);
    mysqli_stmt_bind_param($similar_stmt, "isii", $product_id, $product['id_kategori'], $product['id_user'], $product['id_jenis_produk']);
    mysqli_stmt_execute($similar_stmt);
    $similar_result = mysqli_stmt_get_result($similar_stmt);

    while ($similar_product = mysqli_fetch_assoc($similar_result)) {
        $similar_products[] = $similar_product;
    }
} else {
    // No product ID provided
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['judul']); ?> | ProwebReshina</title>
    <style>
        /* Image Gallery Modal/Lightbox */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 70vh;
            object-fit: contain;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 25px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            box-sizing: border-box;
            transform: translateY(-50%);
        }

        .modal-prev,
        .modal-next {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .modal-prev:hover,
        .modal-next:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .modal-thumbnails {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal-thumb {
            width: 60px;
            height: 60px;
            border: 2px solid transparent;
            cursor: pointer;
            opacity: 0.7;
            transition: all 0.3s;
        }

        .modal-thumb.active {
            border-color: #fff;
            opacity: 1;
        }

        .modal-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Product Gallery Enhancements */
        .main-image {
            position: relative;
            cursor: pointer;
        }

        .zoom-hint {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .main-image:hover .zoom-hint {
            opacity: 1;
        }

        .thumbnail {
            cursor: pointer;
            transition: all 0.3s;
        }

        .thumbnail:hover {
            opacity: 0.8;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/product-detail.css">
    <link rel="stylesheet" href="../assets/css/components/tampilan_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <main class="main-content">
        <div class="container">
            <div class="breadcrumb">
                <a href="/ProwebReshina2/index.php">Home</a> &gt;
                <a href="search-results.php">Pencarian</a> &gt;
                <span id="productTitle"><?php echo htmlspecialchars($product['judul']); ?></span>
            </div>

            <div class="product-detail">
                <div class="product-gallery">
                    <div class="main-image" onclick="openImageModal()" style="position:relative;">
                        <span class="product-type" style="position:absolute;top:12px;right:12px;background:#1976d2;color:#fff;padding:5px 16px;border-radius:8px;font-weight:700;font-size:0.98rem;z-index:2;box-shadow:0 2px 8px rgba(25,118,210,0.08);">
                            <?php echo htmlspecialchars($product['jenis_barang']); ?>
                        </span>
                        <?php if (!empty($images)): ?>
                            <img id="mainImage" src="../uploads/products/<?php echo htmlspecialchars($images[0]['file_name']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                            <div class="zoom-hint"><i class="fas fa-search-plus"></i> Klik untuk memperbesar</div>
                        <?php else: ?>
                            <img id="mainImage" src="../assets/image/product-placeholder.jpg" alt="Product Image">
                        <?php endif; ?>
                    </div>
                    <div class="thumbnail-gallery" id="thumbnailGallery">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="thumbnail <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="changeMainImage(this, '../uploads/products/<?php echo htmlspecialchars($image['file_name']); ?>')">
                                <img src="../uploads/products/<?php echo htmlspecialchars($image['file_name']); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Image Modal/Lightbox -->
                <div id="imageModal" class="image-modal" style="background:rgba(0, 0, 0, 0.68);">
                    <span class="close-modal" onclick="closeImageModal()">&times;</span>
                    <img id="modalImage" class="modal-content" style="object-fit:contain;width:100vw;max-width:100vw;max-height:80vh;display:block;margin:auto;box-shadow:0 0 40px 8px rgba(0, 0, 0, 0.68);border-radius:10px;">
                    <div class="modal-nav">
                        <button class="modal-prev" onclick="changeModalImage(-1)"><i class="fas fa-chevron-left"></i></button>
                        <button class="modal-next" onclick="changeModalImage(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="modal-thumbnails">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="modal-thumb <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="showModalImage(<?php echo $index; ?>)">
                                <img src="../uploads/products/<?php echo htmlspecialchars($image['file_name']); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="product-info">
                    <h1 id="productName"><?php echo htmlspecialchars($product['judul']); ?></h1>
                    <div class="product-meta" style="margin-bottom:12px;display:flex;align-items:center;gap:18px;font-size:0.97rem;">
                        <span><i class="far fa-calendar-alt"></i> <?php echo waktu_lalu(strtotime($product['created_at'])); ?></span>
                        <span class="product-city"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['city']); ?></span>
                        <span class="product-category-badge"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>

                    <div class="product-price" id="productPrice" style="font-size:25px;">
                        <?php echo ($product['harga'] > 0) ? 'Rp ' . number_format($product['harga'], 0, ',', '.') : 'Donasi'; ?>
                        <?php if (!empty($product['lelang_end_time'])): ?>
                            <div id="auctionCountdown" style="margin-top:8px;font-size:1rem;color:#d32f2f;font-weight:600;"></div>
                            <script>
                                // Countdown Timer
                                function updateCountdown() {
                                    var endTime = new Date('<?php echo $product['lelang_end_time']; ?>').getTime();
                                    var now = new Date().getTime();
                                    var distance = endTime - now;
                                    if (distance < 0) {
                                        document.getElementById('auctionCountdown').innerHTML = 'Lelang telah berakhir';
                                        if(document.getElementById('bidButton')) document.getElementById('bidButton').disabled = true;
                                        return;
                                    }
                                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                    document.getElementById('auctionCountdown').innerHTML = 'Sisa waktu: ' + days + ' hari ' + hours + ' jam ' + minutes + ' menit ' + seconds + ' detik';
                                }
                                setInterval(updateCountdown, 1000);
                                updateCountdown();
                            </script>
                        <?php endif; ?>
                    </div>

                    <div class="product-condition product-meta">
                        <span><strong>Kondisi:</strong></span>
                        <span id="productCondition" style="color:#888;gap:4px;display:flex;align-items:center;">
                            <?php echo htmlspecialchars($product['condition_name']); ?>
                        </span>
                    </div>

                    <div class="product-description">
                        <h3>Deskripsi</h3>
                        <?php
                        $fullDesc = nl2br(htmlspecialchars($product['deskripsi']));
                        $descLimit = 500;
                        if (mb_strlen(strip_tags($product['deskripsi'])) > $descLimit) {
                            $shortDesc = nl2br(htmlspecialchars(mb_substr($product['deskripsi'], 0, $descLimit))) . '...';
                            echo '<p id="productDescription">' . $shortDesc . '</p>';
                            echo '<p id="productDescriptionFull" style="display:none;">' . $fullDesc . '</p>';
                            echo '<button class="btn btn-link" id="toggleDescBtn" type="button" style="padding:0;color:#1976d2;font-weight:600;">Lihat Selengkapnya</button>';
                        } else {
                            echo '<p id="productDescription">' . $fullDesc . '</p>';
                        }
                        ?>
                    </div>
                    <script>
                        const toggleBtn = document.getElementById('toggleDescBtn');
                        if (toggleBtn) {
                            toggleBtn.addEventListener('click', function() {
                                const shortDesc = document.getElementById('productDescription');
                                const fullDesc = document.getElementById('productDescriptionFull');
                                if (fullDesc.style.display === 'none') {
                                    shortDesc.style.display = 'none';
                                    fullDesc.style.display = 'block';
                                    toggleBtn.textContent = 'Sembunyikan';
                                } else {
                                    shortDesc.style.display = 'block';
                                    fullDesc.style.display = 'none';
                                    toggleBtn.textContent = 'Lihat Selengkapnya';
                                }
                            });
                        }
                    </script>

                    <div class="seller-info">
                        <h3>Informasi Penjual</h3>
                        <div class="seller-profile">
                            <div class="seller-avatar">
                            <img src='../uploads/profile/<?php echo $image_src; ?>' alt="Seller Avatar">
                            </div>
                            <div class="seller-details">
                                <h4 id="sellerName"><?php echo htmlspecialchars($product['seller_name']); ?></h4>
                                <p>Bergabung sejak <?php echo date('F Y', strtotime($product['seller_join_date'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="product-actions">
                        <?php if (isset($user_id) && isset($product['id_user']) && $user_id == $product['id_user']): ?>
                            <a href="edit-barang.php?id=<?php echo $product['id']; ?>" class="btn btn-primary"> <i class="fas fa-edit" style="margin-right:7px;"></i> Edit Barang </a>
                            <?php if ($product['status'] === 'inactive'): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Pulihkan barang ini?')">
                                    <input type="hidden" name="restore_product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="restore_product" class="btn btn-success" style="background:#fff;display:flex;align-items:center;gap:7px;font-weight:600;color:#388e3c" onmouseover="this.style.background='#4caf50';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#388e3c'">
                                        <i class="fas fa-undo"></i> Pulihkan Barang
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                                    <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-danger" style="background:#fff;display:flex;align-items:center;gap:7px;font-weight:600;color:#5c5c5c" onmouseover="this.style.background='#ff5252';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#5c5c5c'">
                                        <i class="fas fa-trash"></i> Hapus Barang
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo "<!-- Debug: id_jenis_produk = " . htmlspecialchars(isset($product['id_jenis_produk']) ? $product['id_jenis_produk'] : 'NOT SET') . " -->"; ?>
                            <?php
                            // Cek apakah produk lelang
                            $now = date('Y-m-d H:i:s');
                            $is_auction = !empty($product['lelang_end_time']);
                            $auction_ended = $is_auction && strtotime($product['lelang_end_time']) < time();
                            $winner_id = null;
                            if ($is_auction && $auction_ended) {
                                // Cari pemenang lelang
                                $last_bid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tawaran WHERE id_produk = {$product['id']} ORDER BY jumlah_tawaran DESC, waktu DESC LIMIT 1"));
                                if ($last_bid) {
                                    $winner_id = $last_bid['id_user'];
                                }
                            }
                            ?>
                            <?php if ($product['id_jenis_produk'] != '2'): ?>
                                <?php if (!$is_auction): ?>
                                    <a href="beli-barang.php?action=add&id=<?php echo $product['id']; ?>&buy_now=1" class="btn btn-primary" id="actionButton">Beli Sekarang</a>
                                <?php elseif ($is_auction && $auction_ended && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $winner_id): ?>
                                    <a href="beli-barang.php?action=add&id=<?php echo $product['id']; ?>&buy_now=1" class="btn btn-primary" id="actionButton">Beli Sekarang</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="donasi.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" id="actionButton">Ambil Donasi</a>
                            <?php endif; ?>
                            <?php if (!empty($product['lelang_end_time'])): ?>
                                <button class="btn btn-secondary" id="bidButton" style="background:#fff;display:flex;align-items:center;gap:7px;font-weight:600;color:#5c5c5c" onmouseover="this.style.background='#379aff';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#5c5c5c'">
                                    <i class="fas fa-gavel"></i> Tawar
                                </button>
                                <div id="bidModal" class="modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;overflow:auto;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
                                    <div style="background:#fff;margin:auto;padding:32px 28px 22px 28px;border-radius:12px;max-width:370px;box-shadow:0 4px 20px #0001;position:relative;">
                                        <span class="close-modal" id="closeBidModal" style="position:absolute;right:18px;top:12px;font-size:1.5rem;cursor:pointer;color:#888">&times;</span>
                                        <h3 style="margin-bottom:18px;font-size:1.2rem;font-weight:600;color:#e53935;">Tawar</h3>
                                        <form method="post" action="#" id="bidForm">
                                            <label for="bidAmount" style="font-weight:500;">Jumlah tawaran:</label>
                                            <input type="number" name="bidAmount" id="bidAmount" style="width:100%;margin:10px 0 18px 0;padding:8px 10px;border-radius:6px;border:1px solid #ccc;">
                                            <button type="submit" class="btn btn-primary" style="width:100%;background:#e53935;color:#fff;font-weight:600;padding:12px 0;font-size:1rem;border-radius:8px;margin-top:8px;">Tawar</button>
                                        </form>
                                    </div>
                                </div>
                                <script>
                                    const bidBtn = document.getElementById('bidButton');
                                    const bidModal = document.getElementById('bidModal');
                                    const closeBid = document.getElementById('closeBidModal');
                                    bidBtn.onclick = function() {
                                        bidModal.style.display = 'flex';
                                    };
                                    closeBid.onclick = function() {
                                        bidModal.style.display = 'none';
                                    };
                                    window.onclick = function(event) {
                                        if (event.target == bidModal) {
                                            bidModal.style.display = 'none';
                                        }
                                    };

                                </script>
                            <?php endif; ?>
                            <?php
                            // Tombol keranjang: non-lelang = tampil, lelang = hanya pemenang setelah selesai
                            // Tombol keranjang: non-lelang = tampil, lelang = hanya tampil untuk user selain pemenang setelah lelang selesai
                            $show_cart_btn = true;
                            if ($is_auction && $auction_ended) {
                                // Jika lelang dan sudah berakhir, SEMUA user tidak bisa tambah keranjang
                                $show_cart_btn = false;
                            }
                            if ($show_cart_btn): ?>
                            <a href="beli-barang.php?action=add&id=<?php echo $product['id']; ?>" class="add-to-cart">
                                <i class="fas fa-shopping-cart"></i> <span>Tambah Keranjang</span>
                            </a>
                            <?php endif; ?>
                            <button class="btn btn-secondary" id="reportButton" style="background:#fff;display:flex;align-items:center;gap:7px;font-weight:600;color:#5c5c5c" onmouseover="this.style.background='#ff5252';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#5c5c5c'">
                                <i class="fas fa-flag"></i> Laporkan
                            </button>
                            <a href="https://wa.me/<?php echo $seller_phone; ?>" class="btn btn-whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i> Hubungi Penjual
                            </a>
                        <?php endif; ?>
                    </div>
                    <!-- Modal Laporkan Produk -->
                    <div id="reportModal" class="modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;overflow:auto;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
                        <div style="background:#fff;margin:auto;padding:32px 28px 22px 28px;border-radius:12px;max-width:370px;box-shadow:0 4px 20px #0001;position:relative;">
                            <span class="close-modal" id="closeReportModal" style="position:absolute;right:18px;top:12px;font-size:1.5rem;cursor:pointer;color:#888">&times;</span>
                            <h3 style="margin-bottom:18px;font-size:1.2rem;font-weight:600;color:#e53935;">Laporkan Produk</h3>
                            <form method="post" action="#" id="reportForm">
                                <label for="reportReason" style="font-weight:500;">Alasan laporan:</label>
                                <select name="reportReason" id="reportReason" style="width:100%;margin:10px 0 18px 0;padding:8px 10px;border-radius:6px;border:1px solid #ccc;">
                                    <option value="">-- Pilih alasan --</option>
                                    <option value="spam">Spam / Iklan tidak pantas</option>
                                    <option value="penipuan">Penipuan / Barang tidak sesuai</option>
                                    <option value="sara">Konten SARA / Kekerasan</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <label for="reportMessage" style="font-weight:500;">Pesan tambahan (opsional):</label>
                                <textarea name="reportMessage" id="reportMessage" rows="3" style="width:100%;margin:10px 0 18px 0;padding:8px 10px;border-radius:6px;border:1px solid #ccc;resize:vertical;"></textarea>
                                <button type="submit" class="btn btn-primary" style="width:100%;background:#e53935;color:#fff;font-weight:600;padding:12px 0;font-size:1rem;border-radius:8px;margin-top:8px;">Kirim Laporan</button>
                            </form>
                        </div>
                    </div>
                    <script>
                        const reportBtn = document.getElementById('reportButton');
                        const reportModal = document.getElementById('reportModal');
                        const closeReport = document.getElementById('closeReportModal');
                        reportBtn.onclick = function() {
                            reportModal.style.display = 'flex';
                        };
                        closeReport.onclick = function() {
                            reportModal.style.display = 'none';
                        };
                        window.onclick = function(event) {
                            if (event.target == reportModal) {
                                reportModal.style.display = 'none';
                            }
                        };
                        // Prevent submit (demo only)
                        document.getElementById('reportForm').onsubmit = function(e) {
                            e.preventDefault();
                            alert('Terima kasih, laporan Anda sudah diterima.');
                            reportModal.style.display = 'none';
                        }
                    </script>
                </div>
            </div>

            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Produk Serupa</h2>
                </div>
                <div class="products-grid" id="similarProductsContainer">
                    <?php
                    // Query produk serupa berdasarkan jenis_produk yang sama
                    $jenis_produk = mysqli_real_escape_string($conn, $product['id_jenis_produk']);
                    $produk_id = mysqli_real_escape_string($conn, $product['id']);
                    $similar_query = "SELECT p.*, u.username as seller_name,
                    jp.nama as jenis_barang, kd.nama as condition_name,
                    (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                    (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama,
                    p.city as produk_city
                    FROM produk p
                    JOIN user u ON p.id_user = u.id
                    JOIN jenis_produk jp ON p.id_jenis_produk = jp.id
                    JOIN kondisi kd ON p.id_kondisi = kd.id
                    WHERE p.status = 'active' AND p.id_jenis_produk = '$jenis_produk' AND p.id != '$produk_id'
                    ORDER BY p.created_at DESC
                    LIMIT 4";
                    $similar_result = mysqli_query($conn, $similar_query);
                    if (mysqli_num_rows($similar_result) > 0) {
                        while ($similar = mysqli_fetch_assoc($similar_result)) {
                            $time_diff = time() - strtotime($similar['created_at']);
                            if ($time_diff < 60) {
                                $time_diff_string = "$time_diff detik yang lalu";
                            } else if ($time_diff < 3600) {
                                $time_diff_string = "" . floor($time_diff / 60) . " menit yang lalu";
                            } else if ($time_diff < 86400) {
                                $time_diff_string = "" . floor($time_diff / 3600) . " jam yang lalu";
                            } else if ($time_diff < 2592000) {
                                $time_diff_string = "" . floor($time_diff / 86400) . " hari yang lalu";
                            }
                    ?>
                            <div class="product-card">
                                <a href="product-detail.php?id=<?php echo $similar['id']; ?>">
                                    <div class="product-category-badge">
                                        <?php echo htmlspecialchars($similar['kategori_nama']); ?>
                                    </div>
                                    <div class="product-image">
                                        <?php if (!empty($similar['main_image'])): ?>
                                            <img src="../uploads/products/<?php echo htmlspecialchars($similar['main_image']); ?>" alt="<?php echo htmlspecialchars($similar['judul']); ?>">
                                        <?php else: ?>
                                            <img src="../assets/image/product-placeholder.jpg" alt="Product Image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo htmlspecialchars($similar['judul']); ?></h3>
                                        <p class="product-price"><?php echo ($similar['harga'] > 0) ? 'Rp ' . number_format($similar['harga'], 0, ',', '.') : 'Donasi'; ?></p>
                                        <div class="product-meta">
                                            <span><i class="far fa-calendar-alt"></i> <?php echo $time_diff_string; ?></span>
                                            <div class="product-type" style="text-align:right;">
                                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($similar['jenis_barang']); ?></span>
                                            </div>
                                        </div>
                                        <div class="product-city">
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($similar['produk_city']); ?></span>
                                        </div>  
                                    </div>
                                </a>
                            </div>
                    <?php }
                    } else {
                        echo '<p class="no-products">Tidak ada produk serupa saat ini.</p>';
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="../assets/js/product-detail.js"></script>
    <script>
        // Variabel untuk menyimpan gambar-gambar produk
        const productImages = [
            <?php foreach ($images as $index => $image): ?> '../uploads/products/<?php echo htmlspecialchars($image['file_name']); ?>',
            <?php endforeach; ?>
        ];

        let currentImageIndex = 0;

        // Fungsi untuk mengganti gambar utama saat thumbnail diklik
        function changeMainImage(thumbnailElement, imageSrc) {
            // Update gambar utama
            document.getElementById('mainImage').src = imageSrc;

            // Update class active pada thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnailElement.classList.add('active');

            // Update index gambar saat ini
            currentImageIndex = Array.from(thumbnails).indexOf(thumbnailElement);
        }

        // Fungsi untuk membuka modal/lightbox
        function openImageModal() {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            // Set gambar modal sesuai dengan gambar utama saat ini
            modalImage.src = productImages[currentImageIndex];

            // Update thumbnail aktif di modal
            updateModalThumbnails();

            // Tampilkan modal
            modal.style.display = 'block';
        }

        // Fungsi untuk menutup modal/lightbox
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Fungsi untuk menampilkan gambar tertentu di modal
        function showModalImage(index) {
            if (index >= 0 && index < productImages.length) {
                currentImageIndex = index;
                document.getElementById('modalImage').src = productImages[index];
                updateModalThumbnails();
            }
        }

        // Fungsi untuk mengganti gambar di modal (prev/next)
        function changeModalImage(direction) {
            let newIndex = currentImageIndex + direction;

            // Pastikan index tidak keluar batas
            if (newIndex < 0) newIndex = productImages.length - 1;
            if (newIndex >= productImages.length) newIndex = 0;

            showModalImage(newIndex);
        }

        // Fungsi untuk update thumbnail aktif di modal
        function updateModalThumbnails() {
            const modalThumbs = document.querySelectorAll('.modal-thumb');
            modalThumbs.forEach((thumb, index) => {
                if (index === currentImageIndex) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        // Tambahkan event listener untuk tombol keyboard saat modal terbuka
        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('imageModal');
            if (modal.style.display === 'block') {
                if (event.key === 'ArrowLeft') {
                    changeModalImage(-1);
                } else if (event.key === 'ArrowRight') {
                    changeModalImage(1);
                } else if (event.key === 'Escape') {
                    closeImageModal();
                }
            }
        });
    </script>
</body>

</html>