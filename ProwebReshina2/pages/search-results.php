<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';

// Get search parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = isset($_GET['q']) ? $_GET['q'] : ''; // legacy, if still used somewhere
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 12;

// Get filter parameters
$selected_categories = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : [$_GET['category']]) : [];
$selected_condition = isset($_GET['condition']) ? $_GET['condition'] : '';
$selected_types = isset($_GET['transaction_type']) ? (is_array($_GET['transaction_type']) ? $_GET['transaction_type'] : [$_GET['transaction_type']]) : [];

// Get unique cities for location filter
$city_query = "SELECT DISTINCT city FROM produk WHERE status = 'active' ORDER BY city";
$city_result = mysqli_query($conn, $city_query);
$cities = [];
while ($city_row = mysqli_fetch_assoc($city_result)) {
    if (!empty($city_row['city'])) {
        $cities[] = $city_row['city'];
    }
}

// Function to calculate time difference
function waktu_lalu($timestamp)
{
    $selisih = time() - $timestamp;
    $detik = $selisih;
    $menit = round($selisih / 60);
    $jam = round($selisih / 3600);
    $hari = round($selisih / 86400);
    $minggu = round($selisih / 604800);
    $bulan = round($selisih / 2419200);
    $tahun = round($selisih / 29030400);

    if ($detik <= 60) {
        return 'Baru saja';
    } else if ($menit <= 60) {
        return $menit . ' menit yang lalu';
    } else if ($jam <= 24) {
        return $jam . ' jam yang lalu';
    } else if ($hari <= 7) {
        return $hari . ' hari yang lalu';
    } else if ($minggu <= 4) {
        return $minggu . ' minggu yang lalu';
    } else if ($bulan <= 12) {
        return $bulan . ' bulan yang lalu';
    } else {
        return $tahun . ' tahun yang lalu';
    }
}

// Build the SQL query
$sql = "SELECT p.*, p.city as produk_city, u.username as seller_name, 
        jp.nama as jenis_barang, kd.nama as condition_name,
        (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
        (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama
        FROM produk p 
        LEFT JOIN user u ON p.id_user = u.id
        LEFT JOIN jenis_produk jp ON p.id_jenis_produk = jp.id
        LEFT JOIN kondisi kd ON p.id_kondisi = kd.id
        WHERE p.status = 'active'";

// Add filter kota terdekat jika filter aktif dan user login
if (isset($_GET['nearest']) && $_GET['nearest'] == 1) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $city_q = mysqli_query($conn, "SELECT city FROM user WHERE id = '" . mysqli_real_escape_string($conn, $user_id) . "' LIMIT 1");
        $city_row = mysqli_fetch_assoc($city_q);
        $user_city = $city_row ? $city_row['city'] : null;
        if ($user_city) {
            $sql .= " AND p.city LIKE '%" . mysqli_real_escape_string($conn, $user_city) . "%'";
        } else {
            // Jika user belum set kota, tampilkan pesan di hasil pencarian nanti
            $user_city = null;
        }
    } else {
        // Jika belum login, tampilkan pesan di hasil pencarian nanti
        $user_city = false;
    }
}

// Add search conditions
if (!empty($search)) {
    $sql .= " AND (
        p.judul LIKE '%$search%' OR
        p.city LIKE '%$search%' OR
        p.alamat LIKE '%$search%'
    )";
} elseif (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);
    $sql .= " AND (p.judul LIKE '%$search_query%' OR p.deskripsi LIKE '%$search_query%')";
}

// Add location filter
if (!empty($location)) {
    $location = mysqli_real_escape_string($conn, $location);
    $sql .= " AND (p.city LIKE '%$location%' OR p.alamat LIKE '%$location%')";
}

// Add price range filter
if ($min_price > 0) {
    $sql .= " AND p.harga >= $min_price";
}
if ($max_price > 0) {
    $sql .= " AND p.harga <= $max_price";
}

// Add category filter
if (!empty($selected_categories)) {
    $category_ids = array_map('intval', $selected_categories);
    $category_list = implode(',', $category_ids);
    $sql .= " AND p.id_kategori IN ($category_list)";
}

// Add condition filter
if (!empty($selected_condition)) {
    $condition_id = intval($selected_condition);
    $sql .= " AND p.id_kondisi = $condition_id";
}

// Add transaction type filter
if (!empty($selected_types)) {
    $type_ids = array_map('intval', $selected_types);
    $type_list = implode(',', $type_ids);
    $sql .= " AND p.id_jenis_produk IN ($type_list)";
}

// Count total results for pagination
$count_sql = str_replace("p.*, p.city as produk_city, u.username as seller_name, 
        jp.nama as jenis_barang, kd.nama as condition_name,
        (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
        (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama", "COUNT(*) as total", $sql);
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $sql .= " ORDER BY p.harga ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.harga DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Add pagination
$offset = ($page - 1) * $items_per_page;
$sql .= " LIMIT $items_per_page OFFSET $offset";

// Execute query
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian | ProwebReshina</title>
    <link rel="stylesheet" href="../assets/css/search-results.css">
    <link rel="stylesheet" href="../assets/css/components/tampilan_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <main class="main-content">
        <div class="container">
            <div class="search-container">
                <aside class="filter-sidebar">
                    <div class="filter-section">
                        <h3>Filter</h3>
                        <form id="filterForm" method="GET" action="search-results.php">
                            <?php if (!empty($search_query)): ?>
                                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php endif; ?>
                            <div class="filter-group">
                                <h4>Kategori</h4>
                                <div class="filter-options">
                                    <?php
                                    $kategori_query = "SELECT * FROM kategori ORDER BY nama";
                                    $kategori_result = mysqli_query($conn, $kategori_query);
                                    $selected_categories = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : [$_GET['category']]) : [];

                                    while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                                        $checked = in_array($kategori['id'], $selected_categories) ? 'checked' : '';
                                        echo "<label><input type='checkbox' name='category[]' value='{$kategori['id']}' $checked> {$kategori['nama']}</label>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="filter-group">
                                <h4>Lokasi</h4>
                                <div class="filter-options">
                                    <input type="text" id="locationFilter" name="location" placeholder="kota atau alamat" value="<?php echo htmlspecialchars($location); ?>" class="form-control" style="width: 100%; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; border: 1px solid #ced4da; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;">
                                </div>
                            </div>

                            <div class="filter-group">
                                <h4>Kondisi</h4>
                                <div class="filter-options">
                                    <?php
                                    $kondisi_query = "SELECT * FROM kondisi ORDER BY nama";
                                    $kondisi_result = mysqli_query($conn, $kondisi_query);
                                    $selected_condition = isset($_GET['condition']) ? $_GET['condition'] : '';

                                    while ($kondisi = mysqli_fetch_assoc($kondisi_result)) {
                                        $checked = ($selected_condition == $kondisi['id']) ? 'checked' : '';
                                        echo "<label><input type='radio' name='condition' value='{$kondisi['id']}' $checked> {$kondisi['nama']}</label>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="filter-group">
                                <h4>Harga</h4>
                                <div class="price-range">
                                    <input type="number" name="min_price" id="minPrice" placeholder="Min" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                                    <span>-</span>
                                    <input type="number" name="max_price" id="maxPrice" placeholder="Max" value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
                                </div>
                            </div>

                            <div class="filter-group">
                                <h4>Jenis Transaksi</h4>
                                <div class="filter-options">
                                    <?php
                                    $jenis_query = "SELECT * FROM jenis_produk";
                                    $jenis_result = mysqli_query($conn, $jenis_query);
                                    $selected_types = isset($_GET['transaction_type']) ? (is_array($_GET['transaction_type']) ? $_GET['transaction_type'] : [$_GET['transaction_type']]) : [];

                                    while ($jenis = mysqli_fetch_assoc($jenis_result)) {
                                        $checked = in_array($jenis['id'], $selected_types) ? 'checked' : '';
                                        echo "<label><input type='checkbox' name='transaction_type[]' value='{$jenis['id']}' $checked> {$jenis['nama']}</label>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="filter-group">
                                    <label>
                                        <input type="checkbox" name="nearest" value="1" <?php if (isset($_GET['nearest']) && $_GET['nearest'] == '1') echo 'checked'; ?>>
                                        Tampilkan hanya produk di kota saya
                                    </label>
                                </div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                            <a href="search-results.php" class="btn btn-secondary" style="text-decoration: none;">Reset</a>
                        </form>
                    </div>
                </aside>

                <div class="search-results">
                    <div class="results-header">
                        <h2>Hasil Pencarian <?php if (!empty($search_query)) echo "untuk '$search_query'"; ?></h2>
                        <div class="sort-options">
                            <form id="sortForm" method="GET" action="search-results.php" style="display:inline;">
                                <?php
                                foreach ($_GET as $key => $value) {
                                    if ($key == 'sort') continue;
                                    if (is_array($value)) {
                                        foreach ($value as $v) {
                                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                                        }
                                    } else {
                                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                    }
                                }
                                ?>
                                <label for="sortBy">Urutkan: </label>
                                <select id="sortBy" name="sort" onchange="document.getElementById('sortForm').submit();">
                                    <option value="newest" <?php if ($sort_by == 'newest') echo 'selected'; ?>>Terbaru</option>
                                    <option value="price_low" <?php if ($sort_by == 'price_low') echo 'selected'; ?>>Harga: Rendah ke Tinggi</option>
                                    <option value="price_high" <?php if ($sort_by == 'price_high') echo 'selected'; ?>>Harga: Tinggi ke Rendah</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="results-grid" id="resultsContainer">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                                <div class="product-card">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                        <div class="product-category-badge">
                                            <?php echo htmlspecialchars($product['kategori_nama']); ?>
                                        </div>
                                        <div class="product-image">
                                            <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h3><?php echo htmlspecialchars($product['judul']); ?></h3>
                                            <p class="product-price">
                                                <?php if ($product['harga'] > 0): ?>
                                                    Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                                                <?php else: ?>
                                                    Donasi
                                                <?php endif; ?>
                                            </p>
                                            <div class="product-meta">
                                                <span><i class="far fa-calendar-alt"></i> <?php echo waktu_lalu(strtotime($product['created_at'])); ?></span>
                                            </div>
                                            <div class="product-type">
                                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['jenis_barang']); ?></span>
                                            </div>
                                            <div class="product-city">
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['produk_city']); ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-results" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 60px 20px; width: 100%; background-color: #f9f9f9; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin: 50% 100%">
                                <div style="background-color: #f2f2f2; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 25px;">
                                    <i class="fas fa-search" style="font-size: 40px; color: #888;"></i>
                                </div>
                                <h3 style="font-size: 24px; color: #333; margin-bottom: 15px;">Tidak ada hasil yang ditemukan</h3>
                                <p style="font-size: 16px; color: #666; max-width: 400px; line-height: 1.5;">
                                    Coba ubah filter pencarian Anda atau cari dengan kata kunci yang berbeda.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            // Create pagination links with current GET parameters
                            $params = $_GET;

                            // Previous button
                            if ($page > 1) {
                                $params['page'] = $page - 1;
                                $prev_url = 'search-results.php?' . http_build_query($params);
                                echo "<a href='$prev_url' class='pagination-btn' style='text-decoration: none;'><i class='fas fa-chevron-left'></i></a>";
                            } else {
                                echo "<button class='pagination-btn' disabled style='text-decoration: none;'><i class='fas fa-chevron-left'></i></button>";
                            }

                            // Page numbers
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $params['page'] = $i;
                                $page_url = 'search-results.php?' . http_build_query($params);
                                $active_class = ($i == $page) ? 'active' : '';
                                echo "<a href='$page_url' class='pagination-btn $active_class' style='text-decoration: none;'>$i</a>";
                            }

                            // Next button
                            if ($page < $total_pages) {
                                $params['page'] = $page + 1;
                                $next_url = 'search-results.php?' . http_build_query($params);
                                echo "<a href='$next_url' class='pagination-btn' style='text-decoration: none;'><i class='fas fa-chevron-right'></i></a>";
                            } else {
                                echo "<button class='pagination-btn' disabled style='text-decoration: none;'><i class='fas fa-chevron-right'></i></button>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle location filter change
            const locationFilter = document.getElementById('locationFilter');
            if (locationFilter) {
                locationFilter.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }
        });
    </script>
</body>

</html>