<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /ProwebReshina2/pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Query untuk mengambil kategori dari database
$categories_query = "SELECT * FROM kategori ORDER BY nama LIMIT 7";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Reshina</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/components/tampilan_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <main class="main-content">
        <div class="container">
            <!-- Carousel Banner Section -->
            <section class="banner-carousel">
                <div class="carousel-container">
                    <div class="carousel-track" id="carouselTrack">
                        <div class="carousel-slide active">
                            <img src="../assets/image/diskon.jpeg" alt="Promo Banner 1">
                            <div class="carousel-caption">
                                <h2>Diskon 20% untuk Semua Elektronik</h2>
                                <p>Berlaku hingga 30 Juni 2025</p>
                                <a href="search-results.php?id=2" class="btn">Belanja Sekarang</a>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="../assets/image/mewah1.jpg" alt="Promo Banner 2">
                            <div class="carousel-caption">
                                <h2>Lelang Barang Mewah</h2>
                                <p>Dapatkan barang mewah dengan harga terjangkau</p>
                                <a href="search-results.php?category=3" class="btn">Ikut Lelang</a>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="../assets/image/donasi.jpg" alt="Promo Banner 3">
                            <div class="carousel-caption">
                                <h2>Donasi untuk Sesama</h2>
                                <p>Berikan barang yang tidak terpakai kepada yang membutuhkan</p>
                                <a href="search-results.php?category=2" class="btn">Donasi Sekarang</a>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-indicators">
                        <span class="indicator active" data-slide="0"></span>
                        <span class="indicator" data-slide="1"></span>
                        <span class="indicator" data-slide="2"></span>
                    </div>
                    <button class="carousel-control prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                    <button class="carousel-control next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </section>

            <!-- Category Section -->
            <section class="category-section">
                <h2 class="section-title">Kategori</h2>
                <div class="category-container">
                    <a href="search-results.php?category=1" class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>Jual</h3>
                        <p>Barang bekas berkualitas dengan harga terjangkau</p>
                    </a>
                    <a href="search-results.php?category=2" class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h3>Donasi</h3>
                        <p>Berikan barang yang tidak terpakai kepada yang membutuhkan</p>
                    </a>
                    <a href="search-results.php?category=3" class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h3>Lelang</h3>
                        <p>Tawar barang dan dapatkan dengan harga terbaik</p>
                    </a>
                </div>
            </section>

            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Produk terdekat</h2>
                    <a href="search-results.php?nearest=1" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="products-grid" id="nearestProducts">
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        // Ambil city user dari database
                        $user_id = $_SESSION['user_id'];
                        $city_q = mysqli_query($conn, "SELECT city FROM user WHERE id = '" . mysqli_real_escape_string($conn, $user_id) . "' LIMIT 1");
                        $city_row = mysqli_fetch_assoc($city_q);
                        $user_city = $city_row ? $city_row['city'] : null;
                        if ($user_city) {
                            $nearest_query = "SELECT p.*, u.username as seller_name, 
                            jp.nama as jenis_barang, kd.nama as condition_name,
                            (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                            (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama,
                            p.city as produk_city
                            FROM produk p 
                            JOIN user u ON p.id_user = u.id 
                            JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                            JOIN kondisi kd ON p.id_kondisi = kd.id 
                            WHERE p.status = 'active' AND p.city LIKE '%" . mysqli_real_escape_string($conn, $user_city) . "%'
                            ORDER BY p.created_at DESC
                            LIMIT 10";
                            $nearest_result = mysqli_query($conn, $nearest_query);
                            if (mysqli_num_rows($nearest_result) > 0) {
                                while ($product = mysqli_fetch_assoc($nearest_result)) {
                                    $time_diff = time() - strtotime($product['created_at']);
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
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                            <div class="product-category-badge">
                                                <?php echo htmlspecialchars($product['kategori_nama']); ?>
                                            </div>
                                            <div class="product-image">
                                                <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                                            </div>
                                            <div class="product-info">
                                                <h3><?php echo htmlspecialchars($product['judul']); ?></h3>
                                                <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                                <div class="product-meta">
                                                    <span><i class="far fa-calendar-alt"></i> <?php echo $time_diff_string; ?></span>
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
                    <?php
                                }
                            } else {
                                echo '<div class="no-product-info">Tidak ada produk terdekat di kota Anda saat ini.</div>';
                            }
                        } else {
                            echo '<div class="no-product-info">Kota Anda belum diatur di profil.</div>';
                        }
                    } else {
                        echo '<div class="no-product-info">Silakan login untuk melihat produk terdekat sesuai kota Anda.</div>';
                    }
                    ?>
                </div>
            </section>



            <!-- Latest Products Section -->
            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Produk Terbaru</h2>
                    <a href="search-results.php" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="products-grid" id="latestProducts">
                    <?php
                    $query = "SELECT p.*, u.username as seller_name, 
                                  jp.nama as jenis_barang, kd.nama as condition_name,
                                  (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                                  (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama,
                                  p.city as produk_city
                                  FROM produk p 
                                  JOIN user u ON p.id_user = u.id 
                                  JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                                  JOIN kondisi kd ON p.id_kondisi = kd.id 
                                  WHERE p.status = 'active'
                                  ORDER BY p.created_at DESC
                                  LIMIT 10";
                                $result = mysqli_query($conn, $query);
                                while ($product = mysqli_fetch_assoc($result)) {
                                    $time_diff = time() - strtotime($product['created_at']);
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
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($product['kategori_nama']); ?>
                                </div>
                                <div class="product-image">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['judul']); ?></h3>
                                    <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                    <div class="product-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?php echo $time_diff_string; ?></span>
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

                    <?php } ?>
                </div>
            </section>

            <!-- Latest Auctions Section -->
            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Lelang Terbaru</h2>
                    <a href="search-results.php?category=3" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="products-grid" id="latestAuctions">
                    <?php
                    // Ganti id_kategori = 3 sesuai id kategori lelang di database kamu
                    $auction_query = "SELECT p.*, u.username as seller_name, 
                    jp.nama as jenis_barang, kd.nama as condition_name,
                    (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                    (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama,
                    p.city as produk_city
                    FROM produk p 
                    JOIN user u ON p.id_user = u.id 
                    JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                    JOIN kondisi kd ON p.id_kondisi = kd.id 
                    WHERE p.status = 'active' AND p.id_kategori = 3
                    ORDER BY p.created_at DESC
                    LIMIT 10";
                    $auction_result = mysqli_query($conn, $auction_query);
                    while ($product = mysqli_fetch_assoc($auction_result)) {
                        $time_diff = time() - strtotime($product['created_at']);
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
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($product['kategori_nama']); ?>
                                </div>
                                <div class="product-image">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['judul']); ?></h3>
                                    <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                    <div class="product-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?php echo $time_diff_string; ?></span>
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
                    <?php } ?>
                </div>
            </section>


            <!-- Latest Donations Section -->
            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Donasi Terbaru</h2>
                    <a href="search-results.php?category=2" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="products-grid" id="latestDonations">
                    <?php
                    // Ganti id_kategori = 2 sesuai id kategori donasi di database kamu
                    $donation_query = "SELECT p.*, u.username as seller_name, 
                    jp.nama as jenis_barang, kd.nama as condition_name,
                    (SELECT file_name FROM image_produk WHERE id_produk = p.id AND is_primary = 1 LIMIT 1) as main_image,
                    (SELECT nama FROM kategori WHERE id = p.id_kategori) as kategori_nama,
                    p.city as produk_city
                    FROM produk p 
                    JOIN user u ON p.id_user = u.id 
                    JOIN jenis_produk jp ON p.id_jenis_produk = jp.id 
                    JOIN kondisi kd ON p.id_kondisi = kd.id 
                    WHERE p.status = 'active' AND p.id_kategori = 2
                    ORDER BY p.created_at DESC
                    LIMIT 10";
                    $donation_result = mysqli_query($conn, $donation_query);
                    while ($product = mysqli_fetch_assoc($donation_result)) {
                        $time_diff = time() - strtotime($product['created_at']);
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
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($product['kategori_nama']); ?>
                                </div>
                                <div class="product-image">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['judul']); ?>">
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['judul']); ?></h3>
                                    <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                    <div class="product-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?php echo $time_diff_string; ?></span>
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
                    <?php } ?>
                </div>
            </section>

        </div>
    </main>
    <?php include '../include/footer.php'; ?>

    <script>
        // Fungsi untuk carousel (tetap dipertahankan)
        document.addEventListener('DOMContentLoaded', function() {
            // Kode carousel dari dashboard.js
            const carouselTrack = document.getElementById('carouselTrack');
            if (carouselTrack) {
                const slides = document.querySelectorAll('.carousel-slide');
                const indicators = document.querySelectorAll('.indicator');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                let currentSlide = 0;
                let slideInterval;

                function showSlide(index) {
                    slides.forEach(slide => slide.classList.remove('active'));
                    indicators.forEach(indicator => indicator.classList.remove('active'));

                    slides[index].classList.add('active');
                    indicators[index].classList.add('active');
                    currentSlide = index;
                }

                function nextSlide() {
                    let nextIndex = (currentSlide + 1) % slides.length;
                    showSlide(nextIndex);
                }

                function prevSlide() {
                    let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
                    showSlide(prevIndex);
                }

                // Event listeners
                if (prevBtn) prevBtn.addEventListener('click', prevSlide);
                if (nextBtn) nextBtn.addEventListener('click', nextSlide);

                indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => showSlide(index));
                });

                // Auto slide
                function startSlideInterval() {
                    slideInterval = setInterval(nextSlide, 5000);
                }

                // Start carousel
                showSlide(0);
                startSlideInterval();

                // Pause on hover
                carouselTrack.addEventListener('mouseenter', () => clearInterval(slideInterval));
                carouselTrack.addEventListener('mouseleave', startSlideInterval);
            }
        });
    </script>
</body>

</html>