<?php
session_start();
include '../include/koneksi.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle delete user
if (isset($_POST['delete_user_id'])) {
    $uid = intval($_POST['delete_user_id']);
    mysqli_query($conn, "DELETE FROM user WHERE id = $uid");
}
// Handle edit user (username/email/role)
if (isset($_POST['edit_user_id'])) {
    $uid = intval($_POST['edit_user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['edit_username']);
    $email = mysqli_real_escape_string($conn, $_POST['edit_email']);
    $role = mysqli_real_escape_string($conn, $_POST['edit_role']);
    mysqli_query($conn, "UPDATE user SET username='$username', email='$email', role='$role' WHERE id=$uid");
}
// Handle delete produk
if (isset($_POST['delete_produk_id'])) {
    $pid = intval($_POST['delete_produk_id']);
    mysqli_query($conn, "DELETE FROM produk WHERE id = $pid");
}
// Handle add jenis barang
if (isset($_POST['add_jenis'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_nama']);
    mysqli_query($conn, "INSERT INTO jenis_produk (nama) VALUES ('$jenis')");
}
// Handle delete jenis barang
if (isset($_POST['delete_jenis_id'])) {
    $jid = intval($_POST['delete_jenis_id']);
    mysqli_query($conn, "DELETE FROM jenis_produk WHERE id = $jid");
}

// Handle reset password user ke default '123'
if (isset($_POST['reset_pass_user_id'])) {
    $uid = intval($_POST['reset_pass_user_id']);
    $default_pass = md5('123');
    mysqli_query($conn, "UPDATE user SET password='$default_pass' WHERE id=$uid");
}

// --- Handle Search & Sort ---
$user_search = isset($_GET['user_search']) ? mysqli_real_escape_string($conn, $_GET['user_search']) : '';
$user_sort = isset($_GET['user_sort']) ? $_GET['user_sort'] : 'terbaru';
$produk_search = isset($_GET['produk_search']) ? mysqli_real_escape_string($conn, $_GET['produk_search']) : '';
$produk_sort = isset($_GET['produk_sort']) ? $_GET['produk_sort'] : 'terbaru';
$jenis_search = isset($_GET['jenis_search']) ? mysqli_real_escape_string($conn, $_GET['jenis_search']) : '';
$jenis_sort = isset($_GET['jenis_sort']) ? $_GET['jenis_sort'] : 'terbaru';

// Query user
$user_order = $user_sort=='nama' ? 'username ASC' : ($user_sort=='terlama' ? 'id ASC' : 'id DESC');
$user_where = $user_search ? "WHERE username LIKE '%$user_search%' OR email LIKE '%$user_search%' OR role LIKE '%$user_search%'" : '';
$users = mysqli_query($conn, "SELECT * FROM user $user_where ORDER BY $user_order");
// Query produk
$produk_order = $produk_sort=='nama' ? 'judul ASC' : ($produk_sort=='terlama' ? 'id ASC' : 'id DESC');
$produk_where = $produk_search ? "WHERE judul LIKE '%$produk_search%' OR status LIKE '%$produk_search%'" : '';
$produks = mysqli_query($conn, "SELECT produk.*, kategori.nama AS kategori_nama FROM produk LEFT JOIN kategori ON produk.id_kategori = kategori.id $produk_where ORDER BY $produk_order");
// Query jenis barang
$jenis_order = $jenis_sort=='nama' ? 'nama ASC' : ($jenis_sort=='terlama' ? 'id ASC' : 'id DESC');
$jenis_where = $jenis_search ? "WHERE nama LIKE '%$jenis_search%'" : '';
$jenis_list = mysqli_query($conn, "SELECT * FROM jenis_produk $jenis_where ORDER BY $jenis_order");

// Query transaksi (payment)
$transaksi_list = mysqli_query($conn, "SELECT payment.id_pembayaran, payment.id_produk, produk.judul AS nama_produk, user.username AS nama_pembeli, user.email AS email_pembeli FROM payment LEFT JOIN produk ON payment.id_produk = produk.id LEFT JOIN user ON payment.id_user = user.id ORDER BY payment.waktu_pembayaran DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Reshina</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --success-color: #10b981;
            --success-dark: #059669;
            --danger-color: #ef4444;
            --danger-dark: #dc2626;
            --warning-color: #f59e0b;
            --warning-dark: #d97706;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--gray-500);
            font-size: 1.1rem;
        }

        .tabs-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .tabs-nav {
            display: flex;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            padding: 0.5rem;
            gap: 0.5rem;
        }

        .tab-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tab-btn:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
            box-shadow: var(--shadow);
        }

        .tab-content {
            padding: 2rem;
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
            min-width: 200px;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: var(--success-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background: var(--warning-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .actions-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .add-form {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }

        .add-form h3 {
            margin-bottom: 1rem;
            color: var(--gray-800);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .role-admin {
            background: var(--danger-color);
            color: white;
        }

        .role-user {
            background: var(--success-color);
            color: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .responsive-table {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }

            .search-form {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .tabs-nav {
                flex-direction: column;
            }

            .tab-btn {
                justify-content: flex-start;
            }

            .actions-group {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-400);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .card-body {
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h1><a href="dashboard.php" class="fas fa-cogs" style="text-decoration: none;"></a> Admin Panel</h1>
        </div>

        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="showTab('userTab')" id="defaultTab">
                    <i class="fas fa-users"></i> Kelola Akun User
                </button>
                <button class="tab-btn" onclick="showTab('produkTab')">
                    <i class="fas fa-box"></i> Kelola Produk
                </button>
                <button class="tab-btn" onclick="showTab('jenisTab')">
                    <i class="fas fa-tags"></i> Kelola Jenis Barang
                </button>
                <button class="tab-btn" onclick="showTab('transaksiTab')">
                    <i class="fas fa-money-check-alt"></i> Transaksi
                </button>
            </div>

            <!-- Tab User -->
            <div id="userTab" class="tab-content active">
                <!-- ... -->
                <div class="section-header">
                    <h2 class="section-title">Manajemen User</h2>
                </div>

                <form method="get" class="search-form">
                    <div class="form-group">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="user_search" value="<?php echo htmlspecialchars($user_search); ?>" 
                               placeholder="Cari username, email, atau role..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutkan</label>
                        <select name="user_sort" class="form-control">
                            <option value="terbaru" <?php if($user_sort=='terbaru')echo'selected';?>>Terbaru</option>
                            <option value="terlama" <?php if($user_sort=='terlama')echo'selected';?>>Terlama</option>
                            <option value="nama" <?php if($user_sort=='nama')echo'selected';?>>Nama</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari & Sortir
                        </button>
                    </div>
                </form>

                <div class="table-container">
                    <div class="responsive-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($u = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <form method="post" class="user-edit-form">
                                        <td><strong>#<?php echo $u['id']; ?></strong></td>
                                        <td>
                                            <input type="text" name="edit_username" value="<?php echo htmlspecialchars($u['username']); ?>" 
                                                   required class="form-control" style="margin:0;">
                                        </td>
                                        <td>
                                            <input type="email" name="edit_email" value="<?php echo htmlspecialchars($u['email']); ?>" 
                                                   required class="form-control" style="margin:0;">
                                        </td>
                                        <td>
                                            <select name="edit_role" class="form-control" style="margin:0;">
                                                <option value="user" <?php if($u['role']==='user')echo'selected';?>>User</option>
                                                <option value="admin" <?php if($u['role']==='admin')echo'selected';?>>Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="actions-group">
                                                <input type="hidden" name="edit_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-save"></i> Simpan
                                                </button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="reset_pass_user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm" 
                                                onclick="return confirm('Reset password user ke 123?')">
                                            <i class="fas fa-key"></i> Reset Pass
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Hapus user ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                            </div>
                                        </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Produk -->
            <div id="produkTab" class="tab-content">
                <div class="section-header">
                    <h2 class="section-title">Manajemen Produk</h2>
                </div>

                <form method="get" class="search-form">
                    <div class="form-group">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="produk_search" value="<?php echo htmlspecialchars($produk_search); ?>" 
                               placeholder="Cari judul atau status produk..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutkan</label>
                        <select name="produk_sort" class="form-control">
                            <option value="terbaru" <?php if($produk_sort=='terbaru')echo'selected';?>>Terbaru</option>
                            <option value="terlama" <?php if($produk_sort=='terlama')echo'selected';?>>Terlama</option>
                            <option value="nama" <?php if($produk_sort=='nama')echo'selected';?>>Nama</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari & Sortir
                        </button>
                    </div>
                </form>

                <div class="table-container">
                    <div class="responsive-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Judul Produk</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Kategori</th>
                                    <th>Waktu Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = mysqli_fetch_assoc($produks)): ?>
                                <tr>
                                    <td><strong>#<?php echo $p['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['judul']); ?></td>
                                    <td><strong>Rp <?php echo number_format($p['harga'],0,',','.'); ?></strong></td>
                                    <td>
                                            <span class="status-badge" style="background: var(--gray-200); color: var(--gray-700);">
                                                <?php echo htmlspecialchars($p['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo isset($p['kategori_nama']) ? htmlspecialchars($p['kategori_nama']) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo isset($p['created_at']) ? date('d-m-Y H:i', strtotime($p['created_at'])) : '-'; ?>
                                        </td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="delete_produk_id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Hapus produk ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Jenis Barang -->
            <div id="transaksiTab" class="tab-content">
            <div class="section-header">
                <h2 class="section-title">Manajemen Transaksi</h2>
            </div>
            <div class="table-container">
                <div class="responsive-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>ID Produk</th>
                                <th>Nama Produk</th>
                                <th>Nama Pembeli</th>
                                <th>Email Pembeli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($t = mysqli_fetch_assoc($transaksi_list)): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($t['id_pembayaran']); ?></strong></td>
                                <td><?php echo htmlspecialchars($t['id_produk']); ?></td>
                                <td><?php echo htmlspecialchars($t['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($t['nama_pembeli']); ?></td>
                                <td><?php echo htmlspecialchars($t['email_pembeli']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="jenisTab" class="tab-content">
                <div class="section-header">
                    <h2 class="section-title">Manajemen Jenis Barang</h2>
                </div>

                <div class="add-form">
                    <h3><i class="fas fa-plus-circle"></i> Tambah Jenis Barang Baru</h3>
                    <form method="post" class="search-form">
                        <div class="form-group">
                            <input type="text" name="jenis_nama" placeholder="Nama Jenis Barang" 
                                   required class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_jenis" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tambah Jenis
                            </button>
                        </div>
                    </form>
                </div>

                <form method="get" class="search-form">
                    <div class="form-group">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="jenis_search" value="<?php echo htmlspecialchars($jenis_search); ?>" 
                               placeholder="Cari nama jenis barang..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutkan</label>
                        <select name="jenis_sort" class="form-control">
                            <option value="terbaru" <?php if($jenis_sort=='terbaru')echo'selected';?>>Terbaru</option>
                            <option value="terlama" <?php if($jenis_sort=='terlama')echo'selected';?>>Terlama</option>
                            <option value="nama" <?php if($jenis_sort=='nama')echo'selected';?>>Nama</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari & Sortir
                        </button>
                    </div>
                </form>

                <div class="table-container">
                    <div class="responsive-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Jenis</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($j = mysqli_fetch_assoc($jenis_list)): ?>
                                <tr>
                                    <td><strong>#<?php echo $j['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($j['nama']); ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="delete_jenis_id" value="<?php echo $j['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Hapus jenis barang ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabId).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Smooth scroll and enhanced UX
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states to buttons
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.onclick) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                        submitBtn.disabled = true;
                    }
                });
            });

            // Enhanced table interactions
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>