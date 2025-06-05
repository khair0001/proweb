<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';

// Ambil data produk berdasarkan id dari GET
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_name = '';
$price_per_item = 0;
if ($product_id > 0) {
    $query_produk = "SELECT judul, harga FROM produk WHERE id = $product_id LIMIT 1";
    $result_produk = mysqli_query($conn, $query_produk);
    if ($result_produk && mysqli_num_rows($result_produk) > 0) {
        $row_produk = mysqli_fetch_assoc($result_produk);
        $product_name = $row_produk['judul'];
        $price_per_item = $row_produk['harga'];
    }
}
$total_price = $price_per_item;


$success = false;
$error = '';

// Ambil data user dari database
$user = [
    'username' => '',
    'email' => '',
    'nomor_hp' => '',
    'alamat' => '',
    'city' => '',
    'password' => ''
];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query_user = "SELECT username, email, nomor_hp, alamat, city, password FROM user WHERE id = $user_id LIMIT 1";
    $result_user = mysqli_query($conn, $query_user);
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user = mysqli_fetch_assoc($result_user);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buyer_name = trim($_POST['buyer_name']);
    $buyer_email = trim($_POST['buyer_email']);
    $buyer_phone = trim($_POST['buyer_phone']);
    $buyer_address = trim($_POST['buyer_address']);
    $buyer_city = trim($_POST['buyer_city']);
    $shipping_method = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : '';
    $notes = trim($_POST['notes']);
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validasi field wajib
    if ($buyer_name && $buyer_email && $buyer_phone && $buyer_address && $buyer_city && $shipping_method && $confirm_password) {
        // Validasi password
        $input_pass_md5 = md5($confirm_password);
        if ($user && $input_pass_md5 === $user['password']) {
            $success = true;
            // Ambil nomor HP penjual dari produk
            $seller_phone = '';
            if ($product_id > 0) {
                $query_seller = "SELECT u.nomor_hp FROM produk p JOIN user u ON p.id_user = u.id WHERE p.id = $product_id LIMIT 1";
                $result_seller = mysqli_query($conn, $query_seller);
                if ($result_seller && mysqli_num_rows($result_seller) > 0) {
                    $row_seller = mysqli_fetch_assoc($result_seller);
                    $seller_phone = preg_replace('/[^0-9]/', '', $row_seller['nomor_hp']); // sanitize phone
                }
            }
            if ($seller_phone) {
                // --- Generate random id_pembayaran (10-12 karakter alfanumerik) ---
                function generatePaymentId($length = 12)
                {
                    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    $id = '';
                    for ($i = 0; $i < $length; $i++) {
                        $id .= $chars[random_int(0, strlen($chars) - 1)];
                    }
                    return $id;
                }
                $id_pembayaran = generatePaymentId(random_int(10, 12));
                $id_user_pembeli = $user_id;
                // --- Simpan ke tabel payment ---
                $sql_payment = "INSERT INTO payment (id_pembayaran, id_produk, id_user, waktu_pembayaran) VALUES (?, ?, ?, ?)";
                $stmt_payment = mysqli_prepare($conn, $sql_payment);
                $now = date('Y-m-d H:i:s');
                mysqli_stmt_bind_param($stmt_payment, 'siis', $id_pembayaran, $product_id, $id_user_pembeli, $now);
                mysqli_stmt_execute($stmt_payment);

                // --- Insert ke laporan_transaksi ---
                // Ambil id_penjual dari produk
                $queryProduk = "SELECT id_user FROM produk WHERE id = ? LIMIT 1";
                $stmtProduk = $conn->prepare($queryProduk);
                $stmtProduk->bind_param("i", $product_id);
                $stmtProduk->execute();
                $stmtProduk->bind_result($id_penjual);
                $stmtProduk->fetch();
                $stmtProduk->close();

                // Insert ke laporan_transaksi
                $queryLaporan = "INSERT INTO laporan_transaksi (id_pembayaran, tanggal_transaksi, id_pelanggan, id_penjual, harga, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtLaporan = $conn->prepare($queryLaporan);
                $stmtLaporan->bind_param("ssiids", $id_pembayaran, $now, $id_user_pembeli, $id_penjual, $price_per_item, $shipping_method);
                $stmtLaporan->execute();
                $stmtLaporan->close();
                // --- Set produk jadi nonaktif ---
                $sql_update_produk = "UPDATE produk SET status='inactive' WHERE id=?";
                $stmt_update = mysqli_prepare($conn, $sql_update_produk);
                mysqli_stmt_bind_param($stmt_update, 'i', $product_id);
                mysqli_stmt_execute($stmt_update);

                // --- Buat Notifikasi untuk Pembeli ---
                $buyer_notif_title = "Pembelian Diproses: " . htmlspecialchars($product_name);
                $buyer_notif_message = "Proses pembelian untuk produk '" . htmlspecialchars($product_name) . "' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: " . $id_pembayaran;
                $sql_buyer_notif = "INSERT INTO notifications (user_id, product_id, title, message, sender_id) VALUES (?, ?, ?, ?, NULL)";
                $stmt_buyer_notif = $conn->prepare($sql_buyer_notif);
                $stmt_buyer_notif->bind_param("iiss", $id_user_pembeli, $product_id, $buyer_notif_title, $buyer_notif_message);
                $stmt_buyer_notif->execute();
                $stmt_buyer_notif->close();

                // --- Buat Notifikasi untuk Penjual ---
                $seller_notif_title = "Produk Anda Dibeli: " . htmlspecialchars($product_name);
                $seller_notif_message = "Produk Anda '" . htmlspecialchars($product_name) . "' telah dibeli oleh " . htmlspecialchars($buyer_name) . ". ID Pembayaran: " . $id_pembayaran . ". Harap segera hubungi pembeli.";
                $sql_seller_notif = "INSERT INTO notifications (user_id, product_id, title, message, sender_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_seller_notif = $conn->prepare($sql_seller_notif);
                $stmt_seller_notif->bind_param("iissi", $id_penjual, $product_id, $seller_notif_title, $seller_notif_message, $id_user_pembeli);
                $stmt_seller_notif->execute();
                $stmt_seller_notif->close();

                // Format tanggal dan waktu pembelian
                date_default_timezone_set('Asia/Makassar');
                $purchase_datetime = date('Y-m-d H:i:s');
                // Komposisi pesan WhatsApp (format rapi)
                $message = "*Halo, saya ingin mengkonfirmasi pembelian produk berikut:*\n\n";

                $message .= "🛒 *Detail Produk*\n";
                $message .= "• Nama Produk : $product_name\n";
                $message .= "• Harga Satuan : Rp " . number_format($price_per_item, 0, ',', '.') . "\n";
                $message .= "• Total Pembayaran : Rp " . number_format($total_price, 0, ',', '.') . "\n";
                $message .= "• Metode Pengiriman : " . ucfirst($shipping_method) . "\n\n";

                $message .= "🔑 *ID Pembayaran*\n";
                $message .= "$id_pembayaran\n\n";

                $message .= "👤 *Info Pembeli*\n";
                $message .= "• Nama : $buyer_name\n";
                $message .= "• Email : $buyer_email\n";
                $message .= "• No HP : $buyer_phone\n";
                $message .= "• Alamat : $buyer_address, $buyer_city\n\n";

                if ($notes) {
                    $message .= "📝 *Catatan*\n";
                    $message .= "$notes\n\n";
                }

                $message .= "⏰ *Tanggal/Waktu Pembelian*\n";
                $message .= "$purchase_datetime";
                $wa_url = "https://wa.me/" . $seller_phone . "?text=" . urlencode($message);
                // Tampilkan halaman sukses dengan link WhatsApp (tab baru) dan auto-redirect dashboard
                echo "<html><head><meta charset='UTF-8'><title>Pembayaran Berhasil</title><link rel='stylesheet' href='../assets/css/header.css'><link rel='stylesheet' href='../assets/css/dashboard.css'><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'></head><body><div style='display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;'><div style='background:#e6f7e6;color:#228b22;padding:24px 32px;border-radius:10px;font-size:1.3rem;font-weight:500;box-shadow:0 2px 16px #0001;text-align:center;'><i class='fas fa-check-circle' style='font-size:2.2rem;'></i><br><br>Pesanan Anda sedang diproses.<br><br><a href='$wa_url' target='_blank' rel='noopener noreferrer' style='margin-top:22px;display:inline-block;padding:12px 28px;border-radius:8px;background:#25D366;color:#fff;font-size:1.1rem;font-weight:600;text-decoration:none;'><i class='fab fa-whatsapp'></i> Kirim WhatsApp ke Penjual</a></div></div></body></html>";
                exit();
            } else {
                $error = 'Nomor WhatsApp penjual tidak ditemukan.';
            }
        } else {
            $error = 'Password tidak sesuai dengan akun Anda.';
        }
    } else {
        $error = 'Semua field wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 32px 28px;
            border-radius: 14px;
            box-shadow: 0 6px 32px rgba(0, 0, 0, 0.08);
        }

        .payment-container h2 {
            text-align: center;
            margin-bottom: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }

        .order-summary {
            background: #f6f6f6;
            padding: 18px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .order-summary h4 {
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: #1c87c9;
            color: #fff;
            border: none;
            padding: 12px 0;
            border-radius: 8px;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #166ba3;
        }

        .alert-success {
            background: #e6f7e6;
            color: #228b22;
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-danger {
            background: #ffeaea;
            color: #d93025;
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <h2>Pembayaran</h2>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <!-- RINGKASAN PESANAN -->
            <div class="order-summary">
                <h4>Ringkasan Pesanan</h4>
                <div>Produk: <strong><?php echo htmlspecialchars($product_name); ?></strong></div>
                <div>Harga: <strong>Rp <?php echo number_format($price_per_item, 0, ',', '.'); ?></strong></div>
                <div>Total Pembayaran: <strong style="color:#1c87c9">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></div>
            </div>
            <!-- INFORMASI PEMBELI -->
            <div class="form-group">
                <label for="buyer_name">Nama Lengkap</label>
                <input type="text" name="buyer_name" id="buyer_name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="buyer_email">Email</label>
                <input type="email" name="buyer_email" id="buyer_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="buyer_phone">Nomor Telepon</label>
                <input type="text" name="buyer_phone" id="buyer_phone" value="<?php echo htmlspecialchars($user['nomor_hp']); ?>" required>
            </div>
            <div class="form-group">
                <label for="buyer_address">Alamat</label>
                <input type="text" name="buyer_address" id="buyer_address" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
            </div>
            <div class="form-group">
                <label for="buyer_city">Kota</label>
                <input type="text" name="buyer_city" id="buyer_city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
            </div>
            <!-- METODE PENGIRIMAN -->
            <div class="form-group">
                <label>Metode Pengiriman</label>
                <div style="display:flex;gap:18px;">
                    <label><input type="radio" name="shipping_method" value="gudang" required> Ambil Gudang</label>
                    <label><input type="radio" name="shipping_method" value="ngirim"> Ngirim</label>
                    <label><input type="radio" name="shipping_method" value="ketemu"> Ketemu Langsung</label>
                </div>
            </div>
            <!-- CATATAN -->
            <div class="form-group">
                <label for="notes">Catatan</label>
                <textarea name="notes" id="notes" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
            </div>
            <!-- KONFIRMASI PASSWORD -->
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary">Konfirmasi & Kirim ke WhatsApp Penjual</button>
        </form>
    </div>
    <?php include '../include/footer.php'; ?>
</body>

</html>