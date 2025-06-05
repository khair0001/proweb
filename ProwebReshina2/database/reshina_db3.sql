-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jun 2025 pada 06.32
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
-- Database: `reshina_db3`
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
(18, 17, '684032a3172b0_1749037731.jpeg', 1, '2025-06-04 19:48:51'),
(19, 18, '68403e1bbef45_1749040667.jpg', 1, '2025-06-04 20:37:47'),
(20, 19, '684047c7360f8_1749043143.jpg', 1, '2025-06-04 21:19:03'),
(21, 20, '68404936e79cb_1749043510.jpg', 1, '2025-06-04 21:25:10'),
(22, 21, '68404a74853ef_1749043828.jpg', 1, '2025-06-04 21:30:28'),
(23, 22, '68404d7fbe5f5_1749044607.jpg', 1, '2025-06-04 21:43:27'),
(24, 23, '684050393b902_1749045305.jpg', 1, '2025-06-04 21:55:05'),
(25, 24, '6840a29ca1507_1749066396.jpg', 1, '2025-06-05 03:46:36'),
(26, 24, '6840a2c9204ef_1749066441.jpg', 0, '2025-06-05 03:47:21'),
(27, 25, '6840d8dd28f81_1749080285.jpg', 1, '2025-06-05 07:38:05'),
(28, 26, '6840dc51629b8_1749081169.avif', 1, '2025-06-05 07:52:49'),
(29, 27, '6840fec93f8f6_1749089993.jpg', 1, '2025-06-05 10:19:53');

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
(1, 1, 2, '2025-06-04 08:16:02', 1),
(2, 2, 1, '2025-06-04 08:25:38', 1),
(3, 12, 1, '2025-06-04 18:25:22', 1),
(4, 14, 1, '2025-06-04 18:31:54', 1),
(6, 16, 3, '2025-06-04 18:40:39', 0),
(7, 10, 5, '2025-06-04 22:29:45', 1),
(8, 18, 5, '2025-06-04 23:15:43', 0),
(10, 22, 6, '2025-06-05 01:50:43', 0),
(11, 25, 3, '2025-06-05 07:39:52', 1),
(12, 27, 2, '2025-06-05 10:29:05', 1),
(15, 18, 2, '2025-06-05 10:55:29', 1);

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
(5, 'Ouao9ucG3Ptf', '2025-06-04 16:03:55', 1, 2, 23456.00, 'ngirim', NULL),
(6, 'gdrK9f03d4', '2025-06-05 07:40:18', 3, 1, 999999999999.00, 'gudang', NULL),
(7, 'av1wgC4S7M', '2025-06-05 10:30:34', 2, 7, 200000.00, 'ngirim', NULL),
(8, 'yroMBLkcAB', '2025-06-05 10:48:02', 2, 7, 200000.00, 'gudang', NULL),
(9, 'qNOmdwewam', '2025-06-05 10:55:40', 2, 4, 0.00, 'gudang', NULL);

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
(19, 1, NULL, 16, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk Boris Pena dengan tawaran Rp 12.456', 1, '2025-06-04 10:38:06'),
(20, 2, 1, 16, 'Lelang Selesai: Pemenang', 'Lelang produk \'Boris Pena\' telah selesai. Pemenangnya adalah ahmad dengan tawaran Rp 12.456.', 0, '2025-06-04 10:38:06'),
(21, 2, 4, 15, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 500 dari Garaka (email: rakaken58@gmail.com, HP: +6283115709409)', 0, '2025-06-04 12:35:13'),
(22, 2, 1, 12, 'Lelang Selesai: Pemenang', 'Lelang produk \'Nash Parks\' telah selesai. Pemenangnya adalah ahmad dengan tawaran Rp 7.777.', 0, '2025-06-04 13:11:41'),
(23, 4, 5, 20, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 300.000 dari alfia (email: alettaran53@gmail.com, HP: +6283115709409)', 0, '2025-06-04 23:01:53'),
(24, 1, 3, 25, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 999.999.999.999 dari umar (email: umam@gmail.com, HP: +628124567)', 1, '2025-06-04 23:38:23'),
(25, 3, NULL, 25, 'Anda Menang Lelang', 'Selamat, Anda memenangkan lelang produk handphone asus rog 8 dengan tawaran Rp 2.147.483.647', 0, '2025-06-04 23:39:02'),
(26, 1, 3, 25, 'Lelang Selesai: Pemenang', 'Lelang produk \'handphone asus rog 8\' telah selesai. Pemenangnya adalah umar dengan tawaran Rp 2.147.483.647.', 0, '2025-06-04 23:39:02'),
(27, 3, NULL, 25, 'Pembelian Diproses: handphone asus rog 8', 'Proses pembelian untuk produk \'handphone asus rog 8\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: gdrK9f03d4', 0, '2025-06-04 23:40:18'),
(28, 1, 3, 25, 'Produk Anda Dibeli: handphone asus rog 8', 'Produk Anda \'handphone asus rog 8\' telah dibeli oleh umar. ID Pembayaran: gdrK9f03d4. Harap segera hubungi pembeli.', 0, '2025-06-04 23:40:18'),
(29, 2, NULL, 27, 'Pembelian Diproses: hp rog 8', 'Proses pembelian untuk produk \'hp rog 8\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: av1wgC4S7M', 0, '2025-06-05 02:30:34'),
(30, 7, 2, 27, 'Produk Anda Dibeli: hp rog 8', 'Produk Anda \'hp rog 8\' telah dibeli oleh khair. ID Pembayaran: av1wgC4S7M. Harap segera hubungi pembeli.', 0, '2025-06-05 02:30:34'),
(31, 2, 1, NULL, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 999.999 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 0, '2025-06-05 02:36:27'),
(32, 2, 1, NULL, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 12.345.678 dari ahmad (email: ahmad@gmail.com, HP: +628312316556)', 0, '2025-06-05 02:37:14'),
(33, 2, 7, NULL, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 12.345.678.912.345.678 dari tegar potret (email: zamzamitgr@gmail.com, HP: +6283115709409)', 0, '2025-06-05 02:38:20'),
(34, 2, 7, 15, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 12.345.678.234.567 dari tegar potret (email: zamzamitgr@gmail.com, HP: +6283115709409)', 0, '2025-06-05 02:42:52'),
(35, 4, 7, 20, 'Tawaran Baru di Lelang', 'Ada tawaran baru Rp 400.000 dari tegar potret (email: zamzamitgr@gmail.com, HP: +6283115709409)', 0, '2025-06-05 02:46:45'),
(36, 2, NULL, 27, 'Pembelian Diproses: hp rog 8', 'Proses pembelian untuk produk \'hp rog 8\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: yroMBLkcAB', 0, '2025-06-05 02:48:02'),
(37, 7, 2, 27, 'Produk Anda Dibeli: hp rog 8', 'Produk Anda \'hp rog 8\' telah dibeli oleh khair. ID Pembayaran: yroMBLkcAB. Harap segera hubungi pembeli.', 0, '2025-06-05 02:48:02'),
(38, 2, NULL, 18, 'Pembelian Diproses: Boneka Lucu', 'Proses pembelian untuk produk \'Boneka Lucu\' telah dimulai. Silakan lanjutkan konfirmasi dengan penjual melalui WhatsApp. ID Pembayaran Anda: qNOmdwewam', 0, '2025-06-05 02:55:40'),
(39, 4, 2, 18, 'Produk Anda Dibeli: Boneka Lucu', 'Produk Anda \'Boneka Lucu\' telah dibeli oleh khair. ID Pembayaran: qNOmdwewam. Harap segera hubungi pembeli.', 0, '2025-06-05 02:55:40');

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
('av1wgC4S7M', 27, 2, '2025-06-05 10:30:34'),
('ev3q1wujFf', 1, 2, '2025-06-04 13:55:57'),
('gdrK9f03d4', 25, 3, '2025-06-05 07:40:18'),
('gK6jJk0pPku', 2, 1, '2025-06-04 15:28:41'),
('jqpz3fDXV9w', 1, 2, '2025-06-04 14:04:29'),
('lHpAyYhh8rM', 1, 2, '2025-06-04 16:01:33'),
('Ouao9ucG3Ptf', 2, 1, '2025-06-04 16:03:55'),
('qNOmdwewam', 18, 2, '2025-06-05 10:55:40'),
('yroMBLkcAB', 27, 2, '2025-06-05 10:48:02');

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
(1, 1, 'sepatu', 'Sepatu bekas merek Nike, model sporty kasual, cocok untuk aktivitas sehari-hari maupun olahraga ringan. Warna dominan hitam dengan aksen putih, tampilan masih cukup baik meskipun terdapat sedikit goresan pada bagian sol dan tanda pemakaian di bagian dalam. Ukuran 42 (EU), nyaman dipakai dan masih layak fungsi. Sol masih empuk dan tidak licin. Cocok bagi yang ingin tampil sporty dengan budget terbatas.', 1200000.00, 'jl. pejanggik', 1, 2, 2, 'active', '2025-06-04 01:05:22', '2025-06-05 07:32:39', 'selong, lombok timur', NULL),
(2, 2, 'Lysandra Morris', 'Voluptas voluptatem', 23456.00, 'Quidem ex reprehende', 1, 4, 1, 'inactive', '2025-06-04 08:24:51', '2025-06-05 07:55:54', '0', NULL),
(3, 2, 'September Knight', 'Voluptates ratione q', 928.00, 'Dolores ex aut cupid', 1, 5, 1, 'active', '2025-06-04 16:54:23', '2025-06-04 16:54:23', 'Assumenda in omnis a', NULL),
(4, 2, 'Mas adit Mas adit', 'Manusia yang bisa bantu anda membuat stiker meme lucu', 476.00, 'Jl. Raya Praya No. 88, RT 003/RW 001, Kel. Jontlak, Kec. Praya, Kab. Lombok Tengah, Nusa Tenggara Barat, 83511', 1, 6, 3, 'active', '2025-06-04 17:09:50', '2025-06-04 21:14:21', 'Praya', NULL),
(5, 2, 'Taoqi', 'istri gue', 9999999999999.99, '1-chome, Shibuya, Shibuya City, Tokyo 150-0002', 1, 6, 1, 'active', '2025-06-04 17:10:16', '2025-06-05 07:55:33', 'shibuya', NULL),
(6, 2, 'Regan Palmer', 'Mollit amet ut pari', 0.00, 'Aspernatur et impedi', 3, 6, 1, 'inactive', '2025-06-04 17:10:52', '2025-06-04 21:12:01', 'Blanditiis dolor mai', NULL),
(7, 2, 'Hamish Sykes', 'Hic minim odit sed d', 0.00, 'Quis atque aliquid d', 3, 1, 2, 'inactive', '2025-06-04 17:18:12', '2025-06-04 21:11:00', 'Nobis iure nisi mole', NULL),
(8, 2, 'Women\\\'s Air Jordan 1 High OG', 'Yang terbaik ditempa dalam api. AJ1 \\\'Ruby\\\' ini merupakan penghormatan kepada sepatu yang memulai semuanya 40 tahun yang lalu.', 2000000.00, 'qwerty', 1, 2, 2, 'active', '2025-06-04 17:46:46', '2025-06-04 20:47:46', '0', NULL),
(9, 2, 'istri gue', 'qwertyuioasglzxcvbnm', 9999999999999.99, 'asdfghjk', 3, 6, 1, 'active', '2025-06-04 17:58:52', '2025-06-05 07:23:31', 'lombok timur', '2025-06-11 17:09:00'),
(10, 1, 'headphone', 'barang ', 100000.00, 'jl. imam bonjol', 1, 6, 3, 'active', '2025-06-04 18:06:17', '2025-06-05 07:21:25', 'selong', NULL),
(11, 2, 'Wang Frederick', 'Non eiusmod sed dolo', 1523.00, 'Ut nostrum enim in q', 3, 1, 4, 'inactive', '2025-06-04 18:19:18', '2025-06-04 21:12:32', 'Non quasi nemo omnis', '2025-06-05 18:19:00'),
(12, 2, 'Nash Parks', 'Possimus adipisicin', 7777.00, 'Veritatis numquam is', 3, 1, 1, 'inactive', '2025-06-04 18:22:22', '2025-06-04 21:11:47', 'Ullamco sunt ea eum ', '2025-06-04 18:25:00'),
(13, 2, 'Leo Torres', 'Unde aperiam vero au', 2345678.00, 'Voluptatem eiusmod ', 3, 4, 2, 'inactive', '2025-06-04 18:27:53', '2025-06-04 21:11:21', 'Maiores sed dolores ', '2025-06-04 18:29:00'),
(14, 2, 'Dominique Curtis', 'Id sequi itaque aut ', 12345678.00, 'Possimus fugiat dol', 3, 3, 2, 'inactive', '2025-06-04 18:31:34', '2025-06-04 20:58:19', 'Dolorum aute lorem a', '2025-06-04 18:33:00'),
(15, 2, 'Bang Padang', 'sosok penunggu kampus yang bisa membantu anda untuk mengerjakan tugas dan mengantar membeli geprek', 9999999999999.99, 'Jl. Pejanggik No. 21, RT 001/RW 002, Kel. Cakranegara Timur, Kec. Cakranegara, Kota Mataram, Nusa Tenggara Barat, 83231', 3, 6, 3, 'active', '2025-06-04 18:35:27', '2025-06-05 10:42:52', 'Mataram', '2025-06-07 18:35:00'),
(16, 2, 'Boris Pena', 'Magnam facere alias ', 12456.00, 'Elit itaque volupta', 3, 5, 1, 'inactive', '2025-06-04 18:36:07', '2025-06-04 20:57:59', 'Dolore quo iste qui ', '2025-06-04 18:38:00'),
(17, 2, 'Buku gratis', '4 buku dengan bermacam genre', 0.00, 'Jl. Raya Praya No. 88, RT 003/RW 001, Kel. Jontlak, Kec. Praya, Kab. Lombok Tengah, Nusa Tenggara Barat, 83511', 2, 5, 2, 'active', '2025-06-04 19:48:51', '2025-06-05 07:22:58', 'praya, lombok tengah', NULL),
(18, 4, 'Boneka Lucu', 'Boneka Lucu yang bisa menemani harimu dengan nyanyiannya ', 0.00, 'Jln. Angsoka II Kel. Mataram Barat Kec. Selaparang', 2, 6, 2, 'inactive', '2025-06-04 20:37:47', '2025-06-05 10:55:40', 'Mataram', NULL),
(19, 2, 'Batu akik biru laut', 'Dibuka kesempatan langka untuk memiliki batu akik biru laut asli dengan kualitas premium. Batu akik ini menampilkan warna biru laut yang jernih dan memukau, menyerupai keindahan ombak laut yang tenang dan segar. Batu ini merupakan batu alam natural, bukan sintetis, dengan pancaran warna yang kuat dan motif alami yang unik, menjadikannya koleksi yang sangat langka dan bernilai tinggi', 1000000.00, 'Jl. TGH. Abdul Kadir Jaelani No. 10, RT 002/RW 005, Kel. Pancor, Kec. Selong, Kab. Lombok Timur, Nusa Tenggara Barat, 83612', 3, 6, 3, 'active', '2025-06-04 21:19:03', '2025-06-04 21:19:03', 'Selong', '2025-06-07 21:18:00'),
(20, 4, 'Jam  Stainless ', 'Dilelang sebuah jam tangan eksklusif dengan desain unik dan futuristik. Jam tangan ini terbuat dari material stainless steel berkualitas tinggi, menampilkan motif gelang bergelombang yang artistik dan modern. Bagian dial berwarna putih bersih dengan detail penanda waktu minimalis, serta sub-dial kecil yang menambah kesan elegan dan fungsionalitas.\\\\\\\\r\\\\\\\\n\\\\\\\\r\\\\\\\\nDesain gelang bergelombang memberikan kesan dinamis dan berbeda dari jam tangan pada umumnya, sangat cocok bagi Anda yang ingin tampil menonjol dan berkelas. Jam tangan ini cocok digunakan untuk berbagai acara formal maupun kasual, serta menjadi koleksi istimewa bagi para pecinta fashion dan aksesori unik.\\\\\\\\r\\\\\\\\n\\\\\\\\r\\\\\\\\nKondisi barang sangat baik, siap pakai, dan menjadi pilihan tepat untuk Anda yang mencari jam tangan dengan nilai estetika tinggi dan tampilan yang tidak pasaran. Jangan lewatkan kesempatan untuk memiliki jam tangan stainless steel gelombang eksklusif ini melalui lelang terbatas!', 400000.00, 'Jl. Raya Tanjung No. 5A, RT 004/RW 002, Kel. Tanjung, Kec. Tanjung, Kab. Lombok Utara, Nusa Tenggara Barat, 83353', 3, 1, 2, 'active', '2025-06-04 21:25:10', '2025-06-05 10:46:45', 'Lombok Utara', '2025-06-11 21:24:00'),
(21, 4, 'Jaket winter', 'Bersiaplah menghadapi cuaca dingin dengan gaya dan kenyamanan maksimal! Jaket puffer Nike original ini adalah pilihan sempurna untuk menjaga Anda tetap hangat dan modis. Desainnya yang klasik dengan warna hitam solid mudah dipadukan dengan berbagai outfit.Fitur Unggulan:Kain Tahan Air/Angin: Melindungi Anda dari elemen cuaca. Isian Puffer Berkualitas: Memberikan insulasi superior untuk kehangatan optimal.Desain Ergonomis: Memberikan kebebasan bergerak dan kenyamanan sepanjang hari.Logo Nike Bordir: Menambah sentuhan autentik pada jaket.', 200000.00, 'Jl. Kecubung No. 13, Gomong Lama, Kec. Selaparang, Kota Mataram, NTB', 3, 2, 2, 'active', '2025-06-04 21:30:28', '2025-06-05 07:04:46', 'Mataram', '2025-06-11 21:29:00'),
(22, 4, 'Al-Quran', 'Al-quran baru terdapat bacaan terjemahan dan beserta tanda tanda tajwidnya', 0.00, 'Jl. Majapahit No. 62, Kota Mataram, NTB', 2, 5, 1, 'active', '2025-06-04 21:43:27', '2025-06-04 21:43:27', 'Mataram', NULL),
(23, 5, 'Blender yamaha', 'Blender yamaha yang bisa melaju sangat melaju kencang saat memblender bumbu dah jus', 0.00, 'Jl. Langko No. 68A, Kota Mataram, NTB 83114', 2, 1, 2, 'active', '2025-06-04 21:55:05', '2025-06-04 21:55:05', 'Mataram', NULL),
(24, 5, 'Sweater Bebek', 'ukuran M, warna Biru', 90000.00, 'Jl. Gajahmada No. 41, Pagesangan, Kec. Mataram, Kota Mataram, NTB', 1, 2, 1, 'active', '2025-06-05 03:46:36', '2025-06-05 03:47:52', 'Mataram', NULL),
(25, 1, 'handphone asus rog 8', 'ASUS ROG Phone 8 adalah smartphone gaming flagship yang dirancang untuk memberikan performa maksimal bagi para gamer dan pengguna yang menginginkan kinerja tinggi dalam satu perangkat.\\r\\n\\r\\n⚙️ Spesifikasi Utama\\r\\nProsesor: Qualcomm Snapdragon 8 Gen 3 (Octa-core, hingga 3.3GHz)\\r\\n\\r\\nRAM: 12GB LPDDR5X\\r\\n\\r\\nPenyimpanan: 256GB UFS 4.0\\r\\n\\r\\nLayar: 6,78 inci FHD+ (2400x1080) AMOLED fleksibel dari Samsung\\r\\n\\r\\nRefresh rate hingga 165Hz\\r\\n\\r\\nKecerahan puncak 2.500 nits\\r\\n\\r\\nMendukung HDR10 dan Always-On Display\\r\\n\\r\\nKamera Belakang:\\r\\n\\r\\n50MP (Sony IMX890) dengan 6-Axis Hybrid Gimbal Stabilizer 3.0\\r\\n\\r\\nLensa ultrawide dan telefoto\\r\\n\\r\\nKamera Depan: 32MP\\r\\n\\r\\nBaterai: 5.500 mAh dengan pengisian cepat 65W\\r\\n\\r\\nSistem Operasi: Android 14 dengan ROG UI\\r\\n\\r\\nFitur Tambahan:\\r\\n\\r\\nSertifikasi IP68 (tahan debu dan air)\\r\\n\\r\\nLogo RGB di bagian belakang\\r\\n\\r\\nGaming triggers sensitif tekanan', 999999999999.00, 'jl.terara', 3, 1, 2, 'active', '2025-06-05 07:38:05', '2025-06-05 07:41:29', 'terara, lombok timur', '2025-06-05 07:39:00'),
(26, 3, 'Mitsubishi Pajero', 'Mitsubishi Pajero adalah mobil SUV legendaris yang dikenal dengan performa tangguh di berbagai medan, baik on-road maupun off-road. Ditenagai mesin diesel turbo yang bertenaga, Pajero menghadirkan kombinasi antara kenyamanan berkendara dan ketangguhan kendaraan petualang. Desain eksteriornya gagah dengan garis tegas dan bodi besar, mencerminkan karakter maskulin dan elegan.\\r\\n\\r\\nInterior Pajero luas dan mewah, dilengkapi dengan fitur-fitur modern seperti sistem audio premium, AC double blower, kursi kulit, dan sistem keamanan canggih. Suspensi yang empuk serta sistem penggerak 4WD membuat mobil ini andal di berbagai kondisi jalan.\\r\\n\\r\\nPajero sangat cocok untuk keluarga maupun pengemudi yang membutuhkan kendaraan kuat, aman, dan nyaman untuk perjalanan jauh maupun medan berat.', 100000000.00, 'Jl. Raya Praya - KopangKabupaten Lombok Tengah', 1, 4, 1, 'active', '2025-06-05 07:52:49', '2025-06-05 07:52:49', 'Kopang, lombok tengah', NULL),
(27, 7, 'hp rog 8', 'asus rog 8', 200000.00, 'JL.Gajah Mada', 1, 1, 2, 'active', '2025-06-05 10:19:53', '2025-06-05 10:48:08', 'Mataram', NULL);

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
(6, 16, 1, 12456, '2025-06-04 18:36:27'),
(7, 15, 4, 500, '2025-06-04 20:35:13'),
(8, 20, 5, 300000, '2025-06-05 07:01:53'),
(9, 25, 3, 2147483647, '2025-06-05 07:38:23'),
(13, 15, 7, 2147483647, '2025-06-05 10:42:52'),
(14, 20, 7, 400000, '2025-06-05 10:46:45');

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
(1, 'ahmad', 'ahmad@gmail.com', '8de13959395270bf9d6819f818ab1a00', '../uploads/profile/1_1748970345_test.jpg', '2025-06-04 00:54:11', '2025-06-05 10:25:06', '+628312316556', NULL, 'mataram', NULL, NULL, 'admin'),
(2, 'khair', 'khair@gmail.com', '09166e1870d01680f8e2debde8fc5032', '../uploads/profile/2_1749062393_noah.jpg', '2025-06-04 08:14:17', '2025-06-05 07:30:28', '+6283123163556', 'Jl. Pejanggik No.16Pejanggik, Kec. Mataram, Kota Mataram, Nusa Tenggara Bar. 83122', 'mataram', NULL, NULL, 'admin'),
(3, 'umar', 'umam@gmail.com', '68e8792c50234aff1ca5b2d824a3bf89', '../uploads/profile/3_1749080058_download.jpg', '2025-06-04 18:40:16', '2025-06-05 07:34:18', '+628124567', NULL, 'mataram', NULL, NULL, 'user'),
(4, 'Garaka', 'rakaken58@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', '../uploads/profile/4_1749040562_pipa.jpg', '2025-06-04 20:29:28', '2025-06-04 20:36:02', '+6283115709409', NULL, 'Mataram', NULL, NULL, NULL),
(5, 'alfia', 'alettaran53@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', '../uploads/profile/5_1749045148_mbek.jpg', '2025-06-04 21:51:55', '2025-06-04 21:52:28', '+6283115709409', NULL, 'Mataram', NULL, NULL, NULL),
(6, 'kaneki ken', 'kanekiken@gmail.com', '202cb962ac59075b964b07152d234b70', '../uploads/profile/6_1749059409_ken.jpg', '2025-06-05 01:38:24', '2025-06-05 01:50:09', '+6283115709409', NULL, 'Mataram', NULL, NULL, NULL),
(7, 'tegar potret', 'zamzamitgr@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', '../uploads/profile/7_1749091976_adit.jpg', '2025-06-05 10:17:13', '2025-06-05 10:52:56', '+6283115709409', '', 'mataram', NULL, NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `kondisi`
--
ALTER TABLE `kondisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `laporan_transaksi`
--
ALTER TABLE `laporan_transaksi`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `tawaran`
--
ALTER TABLE `tawaran`
  MODIFY `id_tawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
