<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
// Handler untuk select_all dan unselect_all
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'select_all') {
        foreach ($_SESSION['cart'] as $id => $val) {
            $_SESSION['cart'][$id]['selected'] = 1;
        }
        header('Location: beli-barang.php');
        exit();
    } elseif ($action === 'unselect_all') {
        foreach ($_SESSION['cart'] as $id => $val) {
            $_SESSION['cart'][$id]['selected'] = 0;
        }
        header('Location: beli-barang.php');
        exit();
    }
}
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Add to cart
    if ($action === 'add' && $product_id > 0) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        $user_id = $_SESSION['user_id'];

        // Check if product exists
        $check_query = "SELECT id FROM produk WHERE id = ? AND status = 'active'";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $product_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            // Cek apakah produk sudah ada di keranjang user di database
            $cek_keranjang_query = "SELECT id FROM keranjang WHERE id_produk = ? AND id_user = ?";
            $cek_keranjang_stmt = mysqli_prepare($conn, $cek_keranjang_query);
            mysqli_stmt_bind_param($cek_keranjang_stmt, "ii", $product_id, $user_id);
            mysqli_stmt_execute($cek_keranjang_stmt);
            $cek_keranjang_result = mysqli_stmt_get_result($cek_keranjang_stmt);

            if (mysqli_num_rows($cek_keranjang_result) == 0) {
                // Insert ke tabel keranjang, selected=1 jika buy_now, else 0
                $selected_db = (isset($_GET['buy_now']) && $_GET['buy_now'] == 1) ? 1 : 0;
                $insert_query = "INSERT INTO keranjang (id_produk, id_user, waktu, selected) VALUES (?, ?, NOW(), ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "iii", $product_id, $user_id, $selected_db);
                mysqli_stmt_execute($insert_stmt);
            } else if (isset($_GET['buy_now']) && $_GET['buy_now'] == 1) {
                // Jika sudah ada dan buy_now=1, update selected=1
                $update_query = "UPDATE keranjang SET selected=1 WHERE id_produk=? AND id_user=?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "ii", $product_id, $user_id);
                mysqli_stmt_execute($update_stmt);
            }

            // Tambah ke session cart juga
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'selected' => 0
            ];

            // Redirect based on buy_now parameter
            if (isset($_GET['buy_now']) && $_GET['buy_now'] == 1) {
                // Mark as selected
                $_SESSION['cart'][$product_id]['selected'] = 1;
                header('Location: beli-barang.php');
                exit();
            } else {
                // Return to previous page
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
        }
    }

    // Remove from cart
    if ($action === 'remove' && $product_id > 0) {
        // Hapus dari session
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        // Hapus dari database jika user login
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $delete_query = "DELETE FROM keranjang WHERE id_produk = ? AND id_user = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "ii", $product_id, $user_id);
            mysqli_stmt_execute($delete_stmt);
        }
        header('Location: beli-barang.php');
        exit();
    }

    // Remove all from cart
    if ($action === 'remove_all') {
        $_SESSION['cart'] = [];
        header('Location: beli-barang.php');
        exit();
    }

    // Toggle selection
    if ($action === 'toggle_select' && $product_id > 0) {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            // Ambil status terpilih saat ini
            $cek_query = "SELECT selected FROM keranjang WHERE id_produk = ? AND id_user = ?";
            $cek_stmt = mysqli_prepare($conn, $cek_query);
            mysqli_stmt_bind_param($cek_stmt, "ii", $product_id, $user_id);
            mysqli_stmt_execute($cek_stmt);
            $cek_result = mysqli_stmt_get_result($cek_stmt);
            $selected = 0;
            if ($row = mysqli_fetch_assoc($cek_result)) {
                $selected = $row['selected'] ? 0 : 1; // toggle
            }
            // Update status di database
            $update_query = "UPDATE keranjang SET selected = ? WHERE id_produk = ? AND id_user = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "iii", $selected, $product_id, $user_id);
            mysqli_stmt_execute($update_stmt);
        } else if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['selected'] = $_SESSION['cart'][$product_id]['selected'] ? 0 : 1;
        }
        header('Location: beli-barang.php');
        exit();
    }
}

// Get cart items details
$cart_items = [];
$total_price = 0;

if (isset($_SESSION['user_id'])) {
    // Jika login, ambil dari database keranjang
    $user_id = $_SESSION['user_id'];
    $keranjang_query = "SELECT k.id_produk, k.id_user, k.selected, p.*, 
        (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
        ktr.nama as kategori_nama,
        jp.nama as jenis_barang
        FROM keranjang k
        JOIN produk p ON k.id_produk = p.id
        LEFT JOIN kategori ktr ON p.id_kategori = ktr.id
        LEFT JOIN jenis_produk jp ON p.id_jenis_produk = jp.id
        WHERE k.id_user = ? AND p.status = 'active'";
    $keranjang_stmt = mysqli_prepare($conn, $keranjang_query);
    mysqli_stmt_bind_param($keranjang_stmt, "i", $user_id);
    mysqli_stmt_execute($keranjang_stmt);
    $result = mysqli_stmt_get_result($keranjang_stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($item = mysqli_fetch_assoc($result)) {
            // Ambil selected dari database
            $item['selected'] = $item['selected'];
            $cart_items[] = $item;
            if ($item['selected']) {
                $total_price += $item['harga'];
            }
        }
    }
} else if (!empty($_SESSION['cart'])) {
    // Jika tidak login, ambil dari session
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $product_ids));
    $query = "SELECT p.*, 
              (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
              k.nama as kategori_nama,
              jp.nama as jenis_barang
              FROM produk p
              LEFT JOIN kategori k ON p.id_kategori = k.id
              LEFT JOIN jenis_produk jp ON p.id_jenis_produk = jp.id
              WHERE p.id IN ($ids_string) AND p.status = 'active'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($item = mysqli_fetch_assoc($result)) {
            $item['selected'] = $_SESSION['cart'][$item['id']]['selected'];
            $cart_items[] = $item;
            if ($item['selected']) {
                $total_price += $item['harga'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja | ProwebReshina</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/components/tampilan_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .cart-title {
            font-size: 24px;
            font-weight: 600;
        }

        .cart-empty {
            text-align: center;
            padding: 50px 0;
            color: #888;
        }

        .cart-item {
            display: flex;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .cart-item-checkbox {
            margin-right: 15px;
            display: flex;
            align-items: center;
        }

        .cart-item-checkbox input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            margin-right: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-item-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            text-decoration: none;
        }

        .cart-item-title:hover {
            color: #1976d2;
        }

        .cart-item-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        .cart-item-price {
            font-size: 18px;
            font-weight: 600;
            color: #1976d2;
        }

        .cart-item-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .cart-item-remove {
            text-decoration: none;
            background: none;
            border: none;
            color: #e53935;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .cart-item-remove:hover {
            text-decoration: none;
            background-color: #ffebee;
        }

        .cart-summary {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .cart-summary-total {
            font-size: 20px;
            font-weight: 600;
            color: #1976d2;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-checkout {
            background-color: #1976d2;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-checkout:hover {
            background-color: #1565c0;
        }

        .btn-remove-all {
            text-decoration: none;
            background-color: #fff;
            color: #e53935;
            border: 1px solid #e53935;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-remove-all:hover {
            text-decoration: none;
            background-color: #ffebee;
        }

        .empty-cart-message {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-cart-message i {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-cart-message h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 15px;
        }

        .empty-cart-message p {
            color: #888;
            margin-bottom: 25px;
        }

        .btn-continue-shopping {
            background-color: #1976d2;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-continue-shopping:hover {
            background-color: #1565c0;
        }
    </style>
</head>

<body>
    <main class="main-content">
        <div class="cart-container">
            <div class="cart-header">
                <h1 class="cart-title">Keranjang Belanja</h1>
            </div>
            <!-- Checkbox master pilih semua -->
            <?php if (!empty($cart_items)): ?>
                <div style="display: flex; align-items: center; margin-bottom: 15px; gap: 8px;">
                    <input type="checkbox" id="master-checkbox"
                        <?php
                        $all_selected = count($cart_items) > 0 && count(array_filter($cart_items, function ($item) {
                            return $item['selected'];
                        })) === count($cart_items);
                        echo $all_selected ? 'checked' : '';
                        ?> />
                    <label for="master-checkbox" style="margin:0;cursor:pointer;">Pilih Semua</label>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var master = document.getElementById('master-checkbox');
                        master.addEventListener('change', function() {
                            if (this.checked) {
                                window.location = 'beli-barang.php?action=select_all';
                            } else {
                                window.location = 'beli-barang.php?action=unselect_all';
                            }
                        });
                    });
                </script>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang Belanja Kosong</h3>
                    <p>Anda belum menambahkan produk apapun ke keranjang.</p>
                    <a href="dashboard.php" class="btn-continue-shopping">Lanjut Belanja</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-checkbox">
                            <a href="beli-barang.php?action=toggle_select&id=<?php echo $item['id']; ?>">
                                <input type="checkbox" class="product-checkbox" <?php echo $item['selected'] ? 'checked' : ''; ?>
                                    onchange="window.location='beli-barang.php?action=toggle_select&id=<?php echo $item['id']; ?>'" />
                            </a>
                        </div>
                        <div class="cart-item-image">
                            <?php if (!empty($item['main_image'])): ?>
                                <img src="../uploads/products/<?php echo htmlspecialchars($item['main_image']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>">
                            <?php else: ?>
                                <img src="../assets/image/product-placeholder.jpg" alt="Product Image">
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-details">
                            <div>
                                <a href="product-detail.php?id=<?php echo $item['id']; ?>" class="cart-item-title">
                                    <?php echo htmlspecialchars($item['judul']); ?>
                                </a>
                                <div class="cart-item-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['kategori_nama']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['city']); ?></span>
                                </div>
                                <div class="cart-item-price">
                                    <?php echo 'Rp ' . number_format($item['harga'], 0, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <a href="beli-barang.php?action=remove&id=<?php echo $item['id']; ?>" class="cart-item-remove">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                                <a href="payment.php?price=<?php echo $item['harga']; ?>&id=<?php echo $item['id']; ?>" class="btn-checkout" style="margin-left:10px; text-decoration: none;">
                                    Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="cart-summary-row">
                        <span>Total Harga (<?php echo count(array_filter($cart_items, function ($item) {
                                                return $item['selected'];
                                            })); ?> barang)</span>
                        <span class="cart-summary-total">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                    </div>

                    <div class="cart-actions">
                        <a href="beli-barang.php?action=remove_all" class="btn-remove-all">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>
</body>

</html>