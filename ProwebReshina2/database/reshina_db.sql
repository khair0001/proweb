-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Jun 2025 pada 13.49
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reshina_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `image_produk`
--

CREATE TABLE `image_produk` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `image_produk`
--

INSERT INTO `image_produk` (`id`, `id_produk`, `file_name`, `is_primary`, `created_at`) VALUES
(1, 1, '683f2b52d33aa_1748970322.jpeg', 1, '2025-06-04 01:05:22'),
(3, 2, '683f9705b5325_1748997893.png', 1, '2025-06-04 08:44:53'),
(4, 3, '684009bf33a31_1749027263.jpg', 1, '2025-06-04 16:54:23'),
(5, 4, '68400d5e6995c_1749028190.jpg', 1, '2025-06-04 17:09:50'),
(6, 5, '68400d783c58a_1749028216.jpg', 1, '2025-06-04 17:10:16'),
(7, 6, '68400d9c4c482_1749028252.jpg', 1, '2025-06-04 17:10:52'),
(8, 7, '68400f54ad726_1749028692.jpeg', 1, '2025-06-04 17:18:12'),
(9, 8, '684016066b63b_1749030406.jpeg', 1, '2025-06-04 17:46:46'),
(10, 9, '684018dcd4ef1_1749031132.jpg', 1, '2025-06-04 17:58:52'),
(11, 10, '68401a9967d60_1749031577.jpg', 1, '2025-06-04 18:06:17'),
(12, 11, '68401da6c1906_1749032358.jpg', 1, '2025-06-04 18:19:18'),
(13, 12, '68401e5eac009_1749032542.jpeg', 1, '2025-06-04 18:22:22'),
(14, 13, '68401fa91c1d6_1749032873.jpg', 1, '2025-06-04 18:27:53'),
(15, 14, '68402086b144d_1749033094.jpeg', 1, '2025-06-04 18:31:34'),
(16, 15, '6840216f466c7_1749033327.png', 1, '2025-06-04 18:35:27'),
(17, 16, '68402197b3b87_1749033367.jpeg', 1, '2025-06-04 18:36:07'),
(18, 17, '684032a3172b0_1749037731.jpeg', 1, '2025-06-04 19:48:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_produk`
--

CREATE TABLE `jenis_produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_produk`
--

INSERT INTO `jenis_produk` (`id`, `nama`, `icon`) VALUES
(1, 'Elektronik', 'fa-solid fa-tv'),
(2, 'Pakaian', 'fa-solid fa-shirt'),
(3, 'Furniture', 'fa-solid fa-couch'),
(4, 'Otomotif', 'fa-solid fa-car'),
(5, 'Buku', 'fa-solid fa-book'),
(6, 'Lainnya', 'fa-solid fa-boxes-stacked');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `icon`) VALUES
(1, 'Jual', 'fa-solid fa-tag'),
(2, 'Donasi', 'fa-solid fa-hand-holding-heart'),
(3, 'Lelang', 'fa-solid fa-gavel');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `selected` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id`, `id_produk`, `id_user`, `waktu`, `selected`) VALUES
(1, 1, 2, '2025-06-04 08:16:02', 0),
(2, 2, 1, '2025-06-04 08:25:38', 1),
(3, 12, 1, '2025-06-04 18:25:22', 1),
(4, 14, 1, '2025-06-04 18:31:54', 1),
(6, 16, 3, '2025-06-04 18:40:39', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kondisi`
--

CREATE TABLE `kondisi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kondisi`
--

INSERT INTO `kondisi` (`id`, `nama`, `icon`) VALUES
(1, 'Baru', 'fa-solid fa-star'),
(2, 'Bekas', 'fa-solid fa-recycle'),
(3, 'Like New', 'fa-solid fa-thumbs-up'),
(4, 'Rusak', 'fa-solid fa-wrench');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_transaksi`
--

CREATE TABLE `laporan_transaksi` (
  `id_laporan` int(11) NOT NULL,
  `id_pembayaran` varchar(12) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_penjual` int(11) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan_transaksi`
--

INSERT INTO `laporan_transaksi` (`id_laporan`, `id_pembayaran`, `tanggal_transaksi`, `id_pelanggan`, `id_penjual`, `harga`, `metode_pembayaran`, `status`) VALUES
(1, 'ev3q1wujFf', '2025-06-04 13:55:57', 2, 1, 123456789.00, 'gudang', NULL),
(2, 'jqpz3fDXV9w', '2025-06-04 14:04:29', 2, 1, 123456789.00, 'ngirim', NULL),
(3, 'gK6jJk0pPku', '2025-06-04 15:28:41', 1, 2, 23456.00, 'gudang', NULL),
(4, 'lHpAyYhh8rM', '2025-06-04 16:01:33', 2, 1, 123456789.00, 'ngirim', NULL),
(5, 'Ouao9ucG3Ptf', '2025-06-04 16:03:55', 1, 2, 23456.00, 'ngirim', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID penerima notifikasi',
  `sender_id` int(11) DEFAULT NULL COMMENT 'ID pengirim notifikasi (bisa NULL jika sistem)',
  `product_id` int(11) DEFAULT NULL COMMENT 'ID produk terkait (jika ada)',
  `title` varchar(255) NOT NULL COMMENT 'Judul notifikasi',
  `message` text NOT NULL COMMENT 'Isi pesan',
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'Status baca (0=belum, 1=sudah)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `sender_id`, `product_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, NULL, 1, 'Pembelian Diproses: sepatu', 'Proses pembelian untuk produk \'sepatu\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: lHpAyYhh8rM', 1, '2025-06-04 08:01:33'),
(2, 1, 2, 1, 'Produk Anda Dibeli: sepatu', 'Produk Anda \'sepatu\' telah dibeli oleh khair. ID Pembayaran: lHpAyYhh8rM. Harap segera hubungi pembeli.', 1, '2025-06-04 08:01:33'),
(3, 1, NULL, 2, 'Pembelian Diproses: Lysandra Morris', 'Proses pembelian untuk produk \'Lysandra Morris\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: Ouao9ucG3Ptf', 1, '2025-06-04 08:03:55'),
(4, 2, 1, 2, 'Produk Anda Dibeli: Lysandra Morris', 'Produk Anda \'Lysandra Morris\' telah dibeli oleh ahmad. ID Pembayaran: Ouao9ucG3Ptf. Harap segera hubungi pembeli.', 1, '2025-06-04 08:03:55'),
(5, 2, 1, 11, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 1.523 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 1, '2025-06-04 10:20:03'),
(6, 2, 1, 12, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 7.777 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 1, '2025-06-04 10:22:38'),
(7, 1, NULL, 12, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Nash Parks dengan tawaran Rp 7.777', 1, '2025-06-04 10:25:03'),
(8, 1, NULL, 12, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Nash Parks dengan tawaran Rp 7.777', 1, '2025-06-04 10:25:11'),
(9, 1, NULL, 12, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Nash Parks dengan tawaran Rp 7.777', 1, '2025-06-04 10:25:39'),
(10, 2, 1, 13, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 2.345.678 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 1, '2025-06-04 10:28:27'),
(11, 1, NULL, 13, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Leo Torres dengan tawaran Rp 2.345.678', 1, '2025-06-04 10:29:05'),
(12, 2, 1, 13, 'Lelang Selesai: Pemenang', 'Lelang produk \'Leo Torres\' telah selesai. Pemenangnya adalah ahmad dengan tawaran Rp 2.345.678.', 1, '2025-06-04 10:29:05'),
(13, 1, NULL, 13, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Leo Torres dengan tawaran Rp 2.345.678', 1, '2025-06-04 10:29:09'),
(14, 1, NULL, 13, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Leo Torres dengan tawaran Rp 2.345.678', 1, '2025-06-04 10:29:20'),
(15, 2, 1, 14, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 12.345.678 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 1, '2025-06-04 10:31:51'),
(16, 1, NULL, 14, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Dominique Curtis dengan tawaran Rp 12.345.678', 1, '2025-06-04 10:35:10'),
(17, 2, 1, 14, 'Lelang Selesai: Pemenang', 'Lelang produk \'Dominique Curtis\' telah selesai. Pemenangnya adalah ahmad dengan tawaran Rp 12.345.678.', 1, '2025-06-04 10:35:10'),
(18, 2, 1, 16, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 12.456 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 1, '2025-06-04 10:36:27'),
(19, 1, NULL, 16, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Boris Pena dengan tawaran Rp 12.456', 0, '2025-06-04 10:38:06'),
(20, 2, 1, 16, 'Lelang Selesai: Pemenang', 'Lelang produk \'Boris Pena\' telah selesai. Pemenangnya adalah ahmad dengan tawaran Rp 12.456.', 0, '2025-06-04 10:38:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment`
--

CREATE TABLE `payment` (
  `id_pembayaran` varchar(12) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `waktu_pembayaran` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payment`
--

INSERT INTO `payment` (`id_pembayaran`, `id_produk`, `id_user`, `waktu_pembayaran`) VALUES
('ev3q1wujFf', 1, 2, '2025-06-04 13:55:57'),
('gK6jJk0pPku', 2, 1, '2025-06-04 15:28:41'),
('jqpz3fDXV9w', 1, 2, '2025-06-04 14:04:29'),
('lHpAyYhh8rM', 1, 2, '2025-06-04 16:01:33'),
('Ouao9ucG3Ptf', 2, 1, '2025-06-04 16:03:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `judul` varchar(150) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `id_jenis_produk` int(11) DEFAULT NULL,
  `id_kondisi` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `city` varchar(50) DEFAULT NULL,
  `lelang_end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `id_user`, `judul`, `deskripsi`, `harga`, `alamat`, `id_kategori`, `id_jenis_produk`, `id_kondisi`, `status`, `created_at`, `updated_at`, `city`, `lelang_end_time`) VALUES
(1, 1, 'sepatu', 'asdfghjk', 123456789.00, 'jl. pejanggik', 1, 2, 2, 'inactive', '2025-06-04 01:05:22', '2025-06-04 16:01:33', 'selong, lombok timur', NULL),
(2, 2, 'Lysandra Morris', 'Voluptas voluptatem', 23456.00, 'Quidem ex reprehende', 1, 4, 1, 'active', '2025-06-04 08:24:51', '2025-06-04 16:06:01', '0', NULL),
(3, 2, 'September Knight', 'Voluptates ratione q', 928.00, 'Dolores ex aut cupid', 1, 5, 1, 'active', '2025-06-04 16:54:23', '2025-06-04 16:54:23', 'Assumenda in omnis a', NULL),
(4, 2, 'Jeanette Hall', 'Quia magni quia porr', 476.00, 'Eius labore labore l', 1, 3, 3, 'active', '2025-06-04 17:09:50', '2025-06-04 17:09:50', 'Dignissimos omnis et', NULL),
(5, 2, 'Shaine Ortiz', 'Ut expedita perspici', 0.00, 'Magna harum expedita', 3, 2, 1, 'active', '2025-06-04 17:10:16', '2025-06-04 17:10:16', 'Tenetur cillum rerum', NULL),
(6, 2, 'Regan Palmer', 'Mollit amet ut pari', 0.00, 'Aspernatur et impedi', 3, 6, 1, 'active', '2025-06-04 17:10:52', '2025-06-04 17:10:52', 'Blanditiis dolor mai', NULL),
(7, 2, 'Hamish Sykes', 'Hic minim odit sed d', 0.00, 'Quis atque aliquid d', 3, 1, 2, 'active', '2025-06-04 17:18:12', '2025-06-04 17:18:12', 'Nobis iure nisi mole', NULL),
(8, 2, 'asdfg', 'asdfgh', 1234567.00, 'qwerty', 3, 2, 4, 'active', '2025-06-04 17:46:46', '2025-06-04 17:46:46', 'mataram', NULL),
(9, 2, 'istri gue', 'qwertyuioasglzxcvbnm', 9999999999999.99, 'asdfghjk', 3, 6, 1, 'active', '2025-06-04 17:58:52', '2025-06-04 18:09:21', 'mataram', '2025-06-11 17:09:00'),
(10, 1, 'sdf', 'gh', 245.00, 'ad', 1, 3, 3, 'active', '2025-06-04 18:06:17', '2025-06-04 18:06:17', 'a', NULL),
(11, 2, 'Wang Frederick', 'Non eiusmod sed dolo', 1523.00, 'Ut nostrum enim in q', 3, 1, 4, 'active', '2025-06-04 18:19:18', '2025-06-04 18:20:03', 'Non quasi nemo omnis', '2025-06-05 18:19:00'),
(12, 2, 'Nash Parks', 'Possimus adipisicin', 7777.00, 'Veritatis numquam is', 3, 1, 1, 'active', '2025-06-04 18:22:22', '2025-06-04 18:22:38', 'Ullamco sunt ea eum ', '2025-06-04 18:25:00'),
(13, 2, 'Leo Torres', 'Unde aperiam vero au', 2345678.00, 'Voluptatem eiusmod ', 3, 4, 2, 'active', '2025-06-04 18:27:53', '2025-06-04 18:28:27', 'Maiores sed dolores ', '2025-06-04 18:29:00'),
(14, 2, 'Dominique Curtis', 'Id sequi itaque aut ', 12345678.00, 'Possimus fugiat dol', 3, 3, 2, 'active', '2025-06-04 18:31:34', '2025-06-04 18:31:51', 'Dolorum aute lorem a', '2025-06-04 18:33:00'),
(15, 2, 'Morgan Sellers', 'Elit consectetur im', 398.00, 'Ratione reprehenderi', 3, 5, 3, 'active', '2025-06-04 18:35:27', '2025-06-04 18:35:27', 'Similique proident ', '2025-06-07 18:35:00'),
(16, 2, 'Boris Pena', 'Magnam facere alias ', 12456.00, 'Elit itaque volupta', 3, 5, 1, 'active', '2025-06-04 18:36:07', '2025-06-04 18:36:27', 'Dolore quo iste qui ', '2025-06-04 18:38:00'),
(17, 2, 'asdfgh', 'sfgh', 0.00, 'afgh', 2, 2, 2, 'active', '2025-06-04 19:48:51', '2025-06-04 19:48:51', 'selong', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tawaran`
--

CREATE TABLE `tawaran` (
  `id_tawaran` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jumlah_tawaran` int(11) NOT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tawaran`
--

INSERT INTO `tawaran` (`id_tawaran`, `id_produk`, `id_user`, `jumlah_tawaran`, `waktu`) VALUES
(1, 9, 1, 2147483647, '2025-06-04 18:17:28'),
(2, 11, 1, 1523, '2025-06-04 18:20:03'),
(3, 12, 1, 7777, '2025-06-04 18:22:38'),
(4, 13, 1, 2345678, '2025-06-04 18:28:27'),
(5, 14, 1, 12345678, '2025-06-04 18:31:51'),
(6, 16, 1, 12456, '2025-06-04 18:36:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nomor_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `id_lokasi` int(11) DEFAULT NULL,
  `role` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `profile_image`, `created_at`, `updated_at`, `nomor_hp`, `alamat`, `city`, `bio`, `id_lokasi`, `role`) VALUES
(1, 'ahmad', 'ahmad@gmail.com', '8de13959395270bf9d6819f818ab1a00', '../uploads/profile/1_1748970345_test.jpg', '2025-06-04 00:54:11', '2025-06-04 01:05:45', '+628312316556', NULL, 'mataram', NULL, NULL, ''),
(2, 'khair', 'khair@gmail.com', '09166e1870d01680f8e2debde8fc5032', NULL, '2025-06-04 08:14:17', '2025-06-04 18:54:20', '+628234567', '', 'mataram', NULL, NULL, 'admin'),
(3, 'umar', 'umam@gmail.com', '68e8792c50234aff1ca5b2d824a3bf89', NULL, '2025-06-04 18:40:16', '2025-06-04 18:58:12', '+628124567', NULL, 'mataram', NULL, NULL, 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `image_produk`
--
ALTER TABLE `image_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `jenis_produk`
--
ALTER TABLE `jenis_produk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `kondisi`
--
ALTER TABLE `kondisi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan_transaksi`
--
ALTER TABLE `laporan_transaksi`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_pembayaran` (`id_pembayaran`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_penjual` (`id_penjual`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`,`created_at`);

--
-- Indeks untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_jenis_produk` (`id_jenis_produk`),
  ADD KEY `id_kondisi` (`id_kondisi`);

--
-- Indeks untuk tabel `tawaran`
--
ALTER TABLE `tawaran`
  ADD PRIMARY KEY (`id_tawaran`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `image_produk`
--
ALTER TABLE `image_produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `jenis_produk`
--
ALTER TABLE `jenis_produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kondisi`
--
ALTER TABLE `kondisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `laporan_transaksi`
--
ALTER TABLE `laporan_transaksi`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `tawaran`
--
ALTER TABLE `tawaran`
  MODIFY `id_tawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `image_produk`
--
ALTER TABLE `image_produk`
  ADD CONSTRAINT `image_produk_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Ketidakleluasaan untuk tabel `laporan_transaksi`
--
ALTER TABLE `laporan_transaksi`
  ADD CONSTRAINT `laporan_transaksi_ibfk_1` FOREIGN KEY (`id_pembayaran`) REFERENCES `payment` (`id_pembayaran`),
  ADD CONSTRAINT `laporan_transaksi_ibfk_2` FOREIGN KEY (`id_pelanggan`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `laporan_transaksi_ibfk_3` FOREIGN KEY (`id_penjual`) REFERENCES `user` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `produk_ibfk_3` FOREIGN KEY (`id_jenis_produk`) REFERENCES `jenis_produk` (`id`),
  ADD CONSTRAINT `produk_ibfk_4` FOREIGN KEY (`id_kondisi`) REFERENCES `kondisi` (`id`);

--
-- Ketidakleluasaan untuk tabel `tawaran`
--
ALTER TABLE `tawaran`
  ADD CONSTRAINT `fk_tawaran_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tawaran_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
