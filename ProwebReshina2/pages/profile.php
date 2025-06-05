<?php
// Mulai session untuk mengambil data user yang sedang login
session_start();
include '../include/koneksi.php';

// Fungsi waktu_lalu: tampilkan waktu yang lalu dalam format "x menit/jam/hari lalu"
function waktu_lalu($datetime)
{
    $time_diff = time() - strtotime($datetime);
    if ($time_diff < 60) {
        return $time_diff . ' detik yang lalu';
    } else if ($time_diff < 3600) {
        return floor($time_diff / 60) . ' menit yang lalu';
    } else if ($time_diff < 86400) {
        return floor($time_diff / 3600) . ' jam yang lalu';
    } else if ($time_diff < 2592000) {
        return floor($time_diff / 86400) . ' hari yang lalu';
    } else {
        return date('d M Y', strtotime($datetime));
    }
}
// Jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user yang sedang login dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE id = $user_id";
$result = mysqli_query($conn, $query);

// Jika data user ditemukan di database
if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    $email = $user_data['email'];
    $alamat = $user_data['alamat'] ?? '';
    $no_hp = $user_data['nomor_hp'] ?? '';
    $created_at = $user_data['created_at'];
    $profile_image = $user_data['profile_image'] ? $user_data['profile_image'] : '../assets/image/user.png';
    $city = $user_data['city'] ?? '';
}

// Ambil jumlah produk user
$product_query = "SELECT COUNT(*) as total_products FROM produk WHERE id_user = $user_id";
$product_result = mysqli_query($conn, $product_query);
$total_products = 0;

// Jika data produk ditemukan di database
if ($product_result && mysqli_num_rows($product_result) > 0) {
    $product_data = mysqli_fetch_assoc($product_result);
    $total_products = $product_data['total_products'];
}

// Proses hapus produk permanen jika ada form yang dikirim
if (isset($_POST['delete_permanent']) && isset($_POST['delete_permanent_id'])) {
    $delete_id = intval($_POST['delete_permanent_id']);
    // Pastikan produk milik user
    $cek = mysqli_query($conn, "SELECT id FROM produk WHERE id = $delete_id AND id_user = $user_id");
    if (mysqli_num_rows($cek) > 0) {
        // Hapus gambar produk
        mysqli_query($conn, "DELETE FROM image_produk WHERE id_produk = $delete_id");
        // Hapus produk
        $del = mysqli_query($conn, "DELETE FROM produk WHERE id = $delete_id");
        if ($del) {
            $_SESSION['delete_perm_msg'] = '<div class="alert alert-success" style="margin:16px 0 0 0;">Produk berhasil dihapus secara permanen.</div>';
        } else {
            $_SESSION['delete_perm_msg'] = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Gagal menghapus produk. Silakan coba lagi.</div>';
        }
    } else {
        $_SESSION['delete_perm_msg'] = '<div class="alert alert-danger" style="margin:16px 0 0 0;">Akses tidak diizinkan.</div>';
    }
    header('Location: profile.php');
    exit();
}

// Proses update profil jika ada form yang disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Ambil data dari form
        $new_username = mysqli_real_escape_string($conn, $_POST['fullName']);
        $new_email = mysqli_real_escape_string($conn, $_POST['email']);
        $new_phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $new_address = mysqli_real_escape_string($conn, $_POST['address']);
        $new_city = mysqli_real_escape_string($conn, $_POST['city']);

        // Update data user di database
        $update_query = "UPDATE user SET 
                        username = '$new_username', 
                        email = '$new_email', 
                        nomor_hp = '$new_phone',
                        alamat = '$new_address',
                        city = '$new_city'
                        WHERE id = $user_id";

        // Jika update berhasil
        if (mysqli_query($conn, $update_query)) {
            // Update session data
            $_SESSION['username'] = $new_username;
            $_SESSION['email'] = $new_email;

            // Refresh halaman untuk menampilkan data terbaru
            header("Location: profile.php?update=success");
            exit();
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }

    // Proses upload foto profil
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $upload_dir = '../uploads/profile/';

        // Buat direktori jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $user_id . '_' . time() . '_' . basename($_FILES['profile_photo']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $valid_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($image_file_type, $valid_extensions)) {
            // Cek ukuran file (max 2MB)
            if ($_FILES['profile_photo']['size'] <= 2000000) {
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    // Update path gambar di database
                    $update_image_query = "UPDATE user SET profile_image = '$target_file' WHERE id = $user_id";

                    // Jika update berhasil
                    if (mysqli_query($conn, $update_image_query)) {
                        $profile_image = $target_file;
                        // Refresh halaman
                        header("Location: profile.php?photo=success");
                        exit();
                    } else {
                        $photo_error = "Error updating database: " . mysqli_error($conn);
                    }
                } else {
                    $photo_error = "Gagal mengupload file.";
                }
            } else {
                $photo_error = "Ukuran file terlalu besar (max 2MB).";
            }
        } else {
            $photo_error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }
    }

    // Proses ubah password
    if (isset($_POST['change_password'])) {
        $current_password = mysqli_real_escape_string($conn, $_POST['currentPassword']);
        $new_password = mysqli_real_escape_string($conn, $_POST['newPassword']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

        // Cek password saat ini
        $password_query = "SELECT password FROM user WHERE id = $user_id";
        $password_result = mysqli_query($conn, $password_query);

        if ($password_result && mysqli_num_rows($password_result) > 0) {
            $user_password = mysqli_fetch_assoc($password_result)['password'];

            if (password_verify($current_password, $user_password)) {
                // Cek apakah password baru dan konfirmasi sama
                if ($new_password === $confirm_password) {
                    // Hash password baru
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password di database
                    $update_password_query = "UPDATE user SET password = '$hashed_password' WHERE id = $user_id";

                    // Jika update berhasil
                    if (mysqli_query($conn, $update_password_query)) {
                        $password_success = "Password berhasil diubah!";
                    } else {
                        $password_error = "Gagal mengubah password: " . mysqli_error($conn);
                    }
                } else {
                    $password_error = "Password baru dan konfirmasi password tidak sama!";
                }
            } else {
                $password_error = "Password saat ini tidak valid!";
            }
        }
    }
}

// Ambil produk user
$products_query = "SELECT p.*, k.nama as kategori_nama, jp.nama as jenis_produk_nama, 
                  (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as image 
                  FROM produk p 
                  LEFT JOIN kategori k ON p.id_kategori = k.id
                  LEFT JOIN jenis_produk jp ON p.id_jenis_produk = jp.id
                  WHERE p.id_user = $user_id 
                  ORDER BY p.created_at DESC"; // tanpa LIMIT, ambil semua produk
$products_result = mysqli_query($conn, $products_query);

include '../include/header.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna | Reshina</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php
    if (isset($_SESSION['delete_perm_msg'])) {
        echo $_SESSION['delete_perm_msg'];
        unset($_SESSION['delete_perm_msg']);
    }
    ?>
    <main class="main-content">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header-banner">
                <div class="profile-cover">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            <img src="<?php echo $profile_image; ?>" alt="<?php echo $username; ?>" id="userAvatar">
                            <div class="avatar-overlay"></div>
                            <button type="button" class="avatar-edit" id="editFotoBtn" title="Ubah Foto Profil">
                                <i class="fas fa-camera"></i>
                            </button>
                            <form id="avatarUploadForm" action="" method="POST" enctype="multipart/form-data" style="display:none;">
                                <input type="file" id="avatarInput" name="profile_photo" accept="image/*">
                                <input type="hidden" name="update_photo" value="1">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="profile-info-summary">
                    <h1 class="profile-name" id="userName"><?php echo $_SESSION['username']; ?></h1>
                    <div class="profile-meta">
                        <div class="profile-meta-item">
                            <i class="fas fa-envelope"></i>
                            <span id="email"><?php echo $email; ?></span>
                        </div>
                        <div class="profile-meta-item">
                            <i class="fas fa-phone"></i>
                            <span id="phone"><?php echo $no_hp ? $no_hp : 'Belum diatur'; ?></span>
                        </div>
                        <div class="profile-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Bergabung sejak <?php echo date('d M Y', strtotime($created_at)); ?></span>
                        </div>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $total_products; ?></span>
                            <span class="stat-label">Produk</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content-wrapper">
                <!-- Profile Navigation -->
                <div class="profile-tabs">
                    <button class="tab-btn active" data-tab="profile-info"><i class="fas fa-user"></i> Informasi Profil</button>
                    <button class="tab-btn" data-tab="my-products"><i class="fas fa-box"></i> Produk Saya</button>
                    <button class="tab-btn" data-tab="transaction-history"><i class="fas fa-history"></i> Riwayat Transaksi</button>
                    <button class="tab-btn" data-tab="settings"><i class="fas fa-cog"></i> Pengaturan</button>
                    <button class="tab-btn" onclick="window.location.href='../include/logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>

                <!-- Tab Contents -->
                <div class="tab-content-wrapper">
                    <!-- Profile Info Tab -->
                    <div class="tab-content active" id="profile-info">
                        <div class="card">
                            <div class="card-header">
                                <h2>Informasi Profil</h2>
                                <button class="btn btn-primary btn-sm" id="editProfileBtn"><i class="fas fa-edit"></i> Edit Profil</button>
                            </div>
                            <div class="card-body">
                                <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
                                    <div class="alert alert-success">
                                        Profil berhasil diperbarui!
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['photo']) && $_GET['photo'] === 'success'): ?>
                                    <div class="alert alert-success">
                                        Foto profil berhasil diperbarui!
                                    </div>
                                <?php endif; ?>

                                <div class="info-section" id="profileInfoView">
                                    <div class="info-group">
                                        <h3>Informasi Pribadi</h3>
                                        <div class="info-row">
                                            <div class="info-item">
                                                <span class="info-label">Nama Lengkap</span>
                                                <span class="info-value" id="fullName"><?php echo $_SESSION['username']; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Email</span>
                                                <span class="info-value"><?php echo $email; ?></span>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-item">
                                                <span class="info-label">Nomor HP</span>
                                                <span class="info-value" id="mainPhone"><?php echo $no_hp ? $no_hp : '<span class="text-muted">Belum diatur</span>'; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Alamat</span>
                                                <span class="info-value" id="mainAddress"><?php echo $alamat ? $alamat : '<span class="text-muted">Belum diatur</span>'; ?></span>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-item">
                                                <span class="info-label">Kota</span>
                                                <span class="info-value" id="city"><?php echo $city ? $city : '<span class="text-muted">Belum diatur</span>'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Profile Form (Hidden by default) -->
                                <div class="edit-profile-form hidden" id="editProfileForm">
                                    <form method="POST" action="" id="profileForm">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="editFullName">Nama Lengkap</label>
                                                <input type="text" id="editFullName" name="fullName" value="<?php echo $_SESSION['username']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="editEmail">Email</label>
                                                <input type="email" id="editEmail" name="email" value="<?php echo $email; ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="editPhone">Nomor HP</label>
                                                <input type="tel" id="editPhone" name="phone" value="<?php echo $no_hp ? $no_hp : ''; ?>" placeholder="Masukkan nomor HP Anda">
                                            </div>
                                            <div class="form-group">
                                                <label for="editCity">Kota</label>
                                                <input type="text" id="editCity" name="city" value="<?php echo $city ? $city : ''; ?>" placeholder="Masukkan nama kota Anda">
                                            </div>
                                            <div class="form-group">
                                                <label for="editMainAddress">Alamat</label>
                                                <textarea id="editMainAddress" name="address" rows="3" placeholder="Masukkan alamat lengkap Anda"><?php echo $alamat; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-secondary" id="cancelEditBtn">Batal</button>
                                            <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Products Tab -->
                    <div class="tab-content" id="my-products">
                        <div class="card">
                            <div class="card-header">
                                <h2>Produk Saya</h2>
                                <a href="product-form.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Produk</a>
                            </div>
                            <div class="card-body">
                                <div class="filter-tabs">
                                    <button class="filter-btn active" data-filter="all">Semua</button>
                                    <button class="filter-btn" data-filter="active">Aktif</button>
                                    <button class="filter-btn" data-filter="inactive">Nonaktif</button>
                                </div>

                                <div class="products-grid">
                                    <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                            <?php
                                            $product_image = '../uploads/products/' . ($product['image'] ?? 'no-image.png');
                                            if (!file_exists($product_image)) {
                                                $product_image = '../assets/image/no-image.png';
                                            }

                                            if ($product['status'] == 'inactive') {
                                                $status_class = 'inactive';
                                                $status_text = 'Nonaktif';
                                            } else {
                                                $status_class = 'active';
                                                $status_text = 'Aktif';
                                            }
                                            ?>
                                            <div class="product-card" data-status="<?php echo $product['status']; ?>" data-id="<?php echo $product['id']; ?>">
                                                <div class="product-image">
                                                    <img src="<?php echo $product_image; ?>" alt="<?php echo $product['judul']; ?>">
                                                    <span class="product-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                </div>
                                                <div class="product-details">
                                                    <h3><?php echo $product['judul']; ?></h3>
                                                    <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                                    <div class="product-meta">
                                                        <span><i class="far fa-calendar-alt"></i> <?php echo waktu_lalu($product['created_at']); ?></span>
                                                    </div>
                                                    <div class="product-actions">

                                                        <a href="edit-barang.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></a>
                                                        <form method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini secara permanen? Tindakan ini tidak bisa dibatalkan!')">
                                                            <input type="hidden" name="delete_permanent_id" value="<?php echo $product['id']; ?>">
                                                            <button type="submit" name="delete_permanent" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                document.querySelectorAll('.product-card').forEach(function(card) {
                                                    card.addEventListener('click', function(e) {
                                                        // Jangan redirect jika klik pada tombol aksi
                                                        if (e.target.closest('.product-actions button, .product-actions a, .product-actions form')) return;
                                                        var id = card.getAttribute('data-id');
                                                        if (id) {
                                                            window.location.href = 'product-detail.php?id=' + id;
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fas fa-box-open"></i>
                                            <p>Anda belum memiliki produk</p>
                                            <a href="product-form.php" class="btn btn-primary">Tambah Produk Sekarang</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History Tab -->
                    <div class="tab-content" id="transaction-history">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom py-3">
                                <h2 class="h5 mb-0 d-flex align-items-center">
                                    <i class="fas fa-receipt text-primary me-2"></i>
                                    <span>Riwayat Transaksi</span>
                                </h2>
                            </div>
                            <div class="card-body p-0">
                                <?php
                                $user_id = $_SESSION['user_id'];
                                $query = "SELECT 
                                    lt.id_laporan, 
                                    lt.tanggal_transaksi, 
                                    produk.judul AS nama_produk, 
                                    lt.harga, 
                                    penjual.username AS nama_penjual,
                                    pembeli.username AS nama_pembeli,
                                    CASE 
                                        WHEN lt.id_pelanggan = ? THEN 'pembelian' 
                                        WHEN lt.id_penjual = ? THEN 'penjualan'
                                    END AS tipe_transaksi
                                FROM laporan_transaksi lt
                                JOIN user penjual ON lt.id_penjual = penjual.id
                                JOIN user pembeli ON lt.id_pelanggan = pembeli.id
                                JOIN payment ON lt.id_pembayaran = payment.id_pembayaran
                                JOIN produk ON payment.id_produk = produk.id
                                WHERE lt.id_pelanggan = ? OR lt.id_penjual = ?
                                ORDER BY lt.tanggal_transaksi DESC
                                LIMIT 10";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                ?>
                                <?php if ($result->num_rows > 0): ?>
                                    <div class="transaction-list">
                                        <?php while ($row = $result->fetch_assoc()): 
                                            $isPenjualan = $row['tipe_transaksi'] === 'penjualan';
                                            $badgeClass = $isPenjualan ? 'bg-success' : 'bg-primary';
                                            $badgeText = $isPenjualan ? 'Penjualan' : 'Pembelian';
                                            $namaLain = $isPenjualan ? $row['nama_pembeli'] : $row['nama_penjual'];
                                            $icon = $isPenjualan ? 'fa-store' : 'fa-user-tag';
                                            $label = $isPenjualan ? 'Pembeli' : 'Penjual';
                                        ?>
                                            <a class="transaction-item" href="laporan.php?id_laporan=<?php echo $row['id_laporan']; ?>">
                                                <div class="transaction-icon <?php echo $isPenjualan ? 'bg-success bg-opacity-10 text-success' : ''; ?>">
                                                    <i class="fas <?php echo $isPenjualan ? 'fa-cash-register' : 'fa-shopping-bag'; ?>"></i>
                                                </div>
                                                <div class="transaction-details">
                                                    <div class="transaction-title"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                                    <div class="transaction-meta">
                                                        <span class="badge <?php echo $badgeClass; ?>-subtle text-<?php echo $badgeClass; ?>">
                                                            <i class="fas fa-<?php echo $isPenjualan ? 'money-bill-wave' : 'tag'; ?> me-1"></i> <?php echo $badgeText; ?>
                                                        </span>
                                                        <span class="text-muted">
                                                            <i class="far fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($row['tanggal_transaksi'])); ?>
                                                        </span>
                                                        <span class="text-muted">
                                                            <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $label; ?>: <?php echo htmlspecialchars($namaLain); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="transaction-amount">
                                                    <span class="fw-bold text-<?php echo $isPenjualan ? 'success' : 'primary'; ?>">
                                                        <?php echo $isPenjualan ? '+' : '-'; ?>Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?>
                                                    </span>
                                                    <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                                </div>
                                            </a>
                                        <?php endwhile; ?>
                                    </div>
                                    <style>
                                        .transaction-list {
                                            list-style: none;
                                            padding: 0;
                                            margin: 0;
                                        }

                                        .transaction-item {
                                            display: flex;
                                            align-items: center;
                                            padding: 1.25rem 1.5rem;
                                            border-bottom: 1px solid #f0f0f0;
                                            text-decoration: none;
                                            color: #333;
                                            transition: all 0.2s ease;
                                        }

                                        .transaction-item:last-child {
                                            border-bottom: none;
                                        }

                                        .transaction-item:hover {
                                            background-color: #f8f9ff;
                                            transform: translateX(4px);
                                        }

                                        .transaction-icon {
                                            width: 44px;
                                            height: 44px;
                                            border-radius: 10px;
                                            background: rgba(33, 150, 243, 0.1);
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            margin-right: 1rem;
                                            color: #2196f3;
                                            font-size: 1.25rem;
                                            flex-shrink: 0;
                                        }

                                        .transaction-details {
                                            flex-grow: 1;
                                            min-width: 0;
                                        }

                                        .transaction-title {
                                            font-weight: 600;
                                            margin-bottom: 0.25rem;
                                            white-space: nowrap;
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }

                                        .transaction-meta {
                                            display: flex;
                                            flex-wrap: wrap;
                                            gap: 0.75rem;
                                            font-size: 0.85rem;
                                        }

                                        .transaction-meta .badge {
                                            font-weight: 500;
                                            padding: 0.25rem 0.5rem;
                                        }

                                        .transaction-amount {
                                            margin-left: 1rem;
                                            font-size: 1.1rem;
                                            font-weight: 600;
                                            white-space: nowrap;
                                            display: flex;
                                            align-items: center;
                                        }

                                        .empty-transaction {
                                            text-align: center;
                                            padding: 3rem 1.5rem;
                                            color: #6c757d;
                                        }

                                        .empty-transaction i {
                                            font-size: 3rem;
                                            margin-bottom: 1rem;
                                            color: #dee2e6;
                                        }

                                        .empty-transaction p {
                                            margin-bottom: 1.5rem;
                                            font-size: 1.1rem;
                                        }

                                        @media (max-width: 768px) {
                                            .transaction-meta {
                                                flex-direction: column;
                                                gap: 0.25rem;
                                            }

                                            .transaction-item {
                                                padding: 1rem;
                                            }
                                        }
                                    </style>
                                <?php else: ?>
                                    <div class="empty-transaction">
                                        <p>Belum ada riwayat transaksi</p>
                                        <a href="search.php" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Jelajahi Produk
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php $stmt->close(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-content" id="settings">
                        <div class="card">
                            <div class="card-header">
                                <h2>Pengaturan Akun</h2>
                            </div>
                            <div class="card-body">
                                <!-- Password Change Section -->
                                <div class="settings-section">
                                    <h3>Ubah Password</h3>
                                    <?php if (isset($password_success)): ?>
                                        <div class="alert alert-success">
                                            <?php echo $password_success; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($password_error)): ?>
                                        <div class="alert alert-danger">
                                            <?php echo $password_error; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="" id="passwordChangeForm">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="currentPassword">Password Saat Ini</label>
                                                <input type="password" id="currentPassword" name="currentPassword" required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="newPassword">Password Baru</label>
                                                <input type="password" id="newPassword" name="newPassword" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirmPassword">Konfirmasi Password</label>
                                                <input type="password" id="confirmPassword" name="confirmPassword" required>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" name="change_password" class="btn btn-primary">Ubah Password</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Notification Settings -->
                                <div class="settings-section">
                                    <h3>Notifikasi</h3>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h4>Email Notifikasi</h4>
                                            <p>Terima notifikasi melalui email</p>
                                        </div>
                                        <div class="setting-control">
                                            <label class="toggle-switch">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h4>Notifikasi Penawaran</h4>
                                            <p>Terima notifikasi ketika ada penawaran baru</p>
                                        </div>
                                        <div class="setting-control">
                                            <label class="toggle-switch">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Privacy Settings -->
                                <div class="settings-section">
                                    <h3>Privasi</h3>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h4>Profil Publik</h4>
                                            <p>Izinkan pengguna lain melihat profil Anda</p>
                                        </div>
                                        <div class="setting-control">
                                            <label class="toggle-switch">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Danger Zone -->
                                <div class="settings-section danger-zone">
                                    <h3>Zona Berbahaya</h3>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h4>Hapus Akun</h4>
                                            <p>Tindakan ini akan menghapus akun Anda secara permanen dan tidak dapat dikembalikan</p>
                                        </div>
                                        <div class="setting-control">
                                            <button class="btn btn-danger" id="deleteAccountBtn">Hapus Akun</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Modal -->
            <div class="modal" id="photoUploadModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Ubah Foto Profil</h3>
                        <button type="button" class="close-modal" id="closePhotoModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="" enctype="multipart/form-data" id="photoUploadForm">
                            <div class="upload-area" id="dropArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Seret gambar ke sini atau klik untuk memilih</p>
                                <input type="file" id="photoInput" name="profile_photo" accept="image/*" style="display: none;">
                            </div>
                            <div class="image-preview" id="imagePreview" style="display:none;">
                                <img id="previewImage" src="" alt="Preview Foto">
                            </div>
                            <script>
                                // Preview dan hide upload-area jika file dipilih
                                const photoInput = document.getElementById('photoInput');
                                const dropArea = document.getElementById('dropArea');
                                const imagePreview = document.getElementById('imagePreview');
                                const previewImage = document.getElementById('previewImage');
                                const cancelUpload = document.getElementById('cancelUpload');

                                photoInput.addEventListener('change', function() {
                                    if (photoInput.files && photoInput.files[0]) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            previewImage.src = e.target.result;
                                            imagePreview.style.display = 'block';
                                            dropArea.style.display = 'none';
                                        };
                                        reader.readAsDataURL(photoInput.files[0]);
                                    }
                                });
                                // Jika klik batal, kembali ke upload-area
                                cancelUpload.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    imagePreview.style.display = 'none';
                                    dropArea.style.display = 'block';
                                    photoInput.value = '';
                                    previewImage.src = '';
                                });
                            </script>
                            <?php if (isset($photo_error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $photo_error; ?>
                                </div>
                            <?php endif; ?>
                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" id="cancelUpload">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Foto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <div class="modal" id="deleteAccountModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Konfirmasi Hapus Akun</h3>
                        <button type="button" class="close-modal" id="closeDeleteModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan dan semua data Anda akan dihapus secara permanen.</p>
                        <form method="POST" action="" id="deleteAccountForm">
                            <div class="form-group">
                                <label for="deleteConfirmPassword">Masukkan password Anda untuk konfirmasi</label>
                                <input type="password" id="deleteConfirmPassword" name="confirm_password" required>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" id="cancelDelete">Batal</button>
                                <button type="submit" name="delete_account" class="btn btn-danger">Hapus Akun</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="../assets/js/profile.js"></script>
</body>

</html>