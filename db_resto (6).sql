-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 03, 2026 at 08:05 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_resto`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_bahan`
--

CREATE TABLE `tb_bahan` (
  `id_bahan` int NOT NULL,
  `nama_bahan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stok` decimal(10,2) DEFAULT '0.00',
  `stok_minimum` decimal(10,2) DEFAULT '0.00',
  `satuan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_modal` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_bahan`
--

INSERT INTO `tb_bahan` (`id_bahan`, `nama_bahan`, `kategori`, `stok`, `stok_minimum`, `satuan`, `harga_modal`) VALUES
(1, 'Tepung Terigu', 'bahan_baku', 3.00, 1.00, 'kg', 15000.00),
(2, 'Gula Pasir', 'bahan_baku', 2.00, 0.50, 'kg', 14000.00),
(3, 'Telur', 'bahan_baku', 25.00, 6.00, 'pcs', 2500.00),
(4, 'Mentega', 'bahan_baku', 1.50, 0.25, 'kg', 32000.00),
(5, 'Baking Powder', 'bahan_baku', 100.00, 25.00, 'gr', 80.00),
(6, 'Garam', 'bahan_baku', 100.00, 25.00, 'gr', 20.00),
(7, 'Susu UHT', 'bahan_baku', 2.00, 0.50, 'liter', 18000.00),
(8, 'Cream Cheese', 'bahan_baku', 0.50, 0.15, 'kg', 85000.00),
(9, 'Dark Chocolate', 'bahan_baku', 0.50, 0.15, 'kg', 65000.00),
(10, 'Vanili Bubuk', 'bahan_baku', 25.00, 5.00, 'gr', 300.00),
(11, 'Ragi Instan', 'bahan_baku', 50.00, 10.00, 'gr', 150.00),
(12, 'Biji Kopi', 'minuman', 0.50, 0.10, 'kg', 120000.00),
(13, 'Matcha Powder', 'minuman', 75.00, 15.00, 'gr', 1200.00),
(14, 'Susu Cair', 'minuman', 2.00, 0.50, 'liter', 16000.00),
(15, 'Simple Syrup', 'minuman', 250.00, 50.00, 'ml', 50.00),
(16, 'Vanilla Extract', 'minuman', 50.00, 10.00, 'ml', 500.00),
(17, 'Coklat Bubuk', 'minuman', 100.00, 25.00, 'gr', 180.00),
(18, 'Es Batu', 'minuman', 1.00, 0.25, 'kg', 5000.00),
(19, 'Teh Lychee', 'minuman', 50.00, 10.00, 'gr', 400.00),
(20, 'Choco Chips', 'topping', 150.00, 30.00, 'gr', 120.00),
(21, 'Keju Parut', 'topping', 100.00, 25.00, 'gr', 250.00),
(22, 'Almond Slice', 'topping', 100.00, 25.00, 'gr', 350.00),
(23, 'Meses', 'topping', 100.00, 25.00, 'gr', 80.00),
(24, 'Whipping Cream', 'topping', 250.00, 50.00, 'ml', 100.00),
(25, 'Kayu Manis Bubuk', 'topping', 25.00, 5.00, 'gr', 400.00),
(26, 'Gula Halus', 'topping', 150.00, 30.00, 'gr', 20.00),
(27, 'Cup 16oz', 'kemasan', 25.00, 5.00, 'pcs', 1500.00),
(28, 'Sedotan', 'kemasan', 25.00, 5.00, 'pcs', 200.00),
(29, 'Tisu', 'kemasan', 50.00, 10.00, 'pcs', 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_pesanan`
--

CREATE TABLE `tb_detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int NOT NULL,
  `id_menu` int NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int NOT NULL,
  `catatan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_meja`
--

CREATE TABLE `tb_meja` (
  `id_meja` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `status` enum('kosong','terisi') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_meja`
--

INSERT INTO `tb_meja` (`id_meja`, `nomor_meja`, `status`) VALUES
(1, 1, 'kosong'),
(2, 2, 'kosong'),
(3, 3, 'kosong'),
(4, 4, 'kosong'),
(5, 5, 'kosong'),
(6, 6, 'kosong'),
(7, 7, 'kosong'),
(8, 8, 'kosong'),
(9, 9, 'kosong'),
(10, 10, 'kosong'),
(11, 11, 'kosong'),
(12, 12, 'kosong'),
(13, 13, 'kosong'),
(14, 14, 'kosong'),
(15, 15, 'kosong'),
(16, 16, 'kosong'),
(17, 17, 'kosong'),
(18, 18, 'kosong'),
(19, 19, 'kosong'),
(20, 20, 'kosong'),
(21, 21, 'kosong'),
(22, 22, 'kosong'),
(23, 23, 'kosong'),
(24, 24, 'kosong'),
(25, 25, 'kosong'),
(26, 26, 'kosong'),
(27, 27, 'kosong'),
(28, 28, 'kosong'),
(29, 29, 'kosong'),
(30, 30, 'kosong'),
(31, 31, 'kosong'),
(32, 32, 'kosong'),
(33, 33, 'kosong'),
(34, 34, 'kosong'),
(35, 35, 'kosong'),
(36, 36, 'kosong'),
(37, 37, 'kosong'),
(38, 38, 'kosong'),
(39, 39, 'kosong'),
(40, 40, 'kosong'),
(41, 41, 'kosong'),
(42, 42, 'kosong'),
(43, 43, 'kosong'),
(44, 44, 'kosong'),
(45, 45, 'kosong'),
(46, 46, 'kosong'),
(47, 47, 'kosong'),
(48, 48, 'kosong'),
(49, 49, 'kosong'),
(50, 50, 'kosong'),
(51, 51, 'kosong'),
(52, 52, 'kosong'),
(53, 53, 'kosong'),
(54, 54, 'kosong'),
(55, 55, 'kosong'),
(56, 56, 'kosong'),
(57, 57, 'kosong'),
(58, 58, 'kosong'),
(59, 59, 'kosong'),
(60, 60, 'kosong'),
(61, 61, 'kosong'),
(62, 62, 'kosong'),
(63, 63, 'kosong'),
(64, 64, 'kosong'),
(65, 65, 'kosong'),
(66, 66, 'kosong'),
(67, 67, 'kosong'),
(68, 68, 'kosong'),
(69, 69, 'kosong'),
(70, 70, 'kosong'),
(71, 71, 'kosong'),
(72, 72, 'kosong'),
(73, 73, 'kosong'),
(74, 74, 'kosong'),
(75, 75, 'kosong'),
(76, 76, 'kosong'),
(77, 77, 'kosong'),
(78, 78, 'kosong'),
(79, 79, 'kosong'),
(80, 80, 'kosong'),
(81, 81, 'kosong'),
(82, 82, 'kosong'),
(83, 83, 'kosong'),
(84, 84, 'kosong'),
(85, 85, 'kosong'),
(86, 86, 'kosong'),
(87, 87, 'kosong'),
(88, 88, 'kosong'),
(89, 89, 'kosong'),
(90, 90, 'kosong'),
(91, 91, 'kosong'),
(92, 92, 'kosong'),
(93, 93, 'kosong'),
(94, 94, 'kosong'),
(95, 95, 'kosong'),
(96, 96, 'kosong'),
(97, 97, 'kosong'),
(98, 98, 'kosong'),
(99, 99, 'kosong'),
(100, 100, 'kosong');

-- --------------------------------------------------------

--
-- Table structure for table `tb_menu`
--

CREATE TABLE `tb_menu` (
  `id_menu` int NOT NULL,
  `nama_menu` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` enum('makanan','minuman') COLLATE utf8mb4_general_ci NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_menu`
--

INSERT INTO `tb_menu` (`id_menu`, `nama_menu`, `kategori`, `harga`, `stok`) VALUES
(1, 'Croissant Butter', 'makanan', 18000, 10),
(2, 'Cinnamon Roll', 'makanan', 22000, 10),
(3, 'Matcha Muffin', 'makanan', 20000, 10),
(4, 'Almond Croissant', 'makanan', 25000, 10),
(5, 'Cheese Danish', 'makanan', 22000, 10),
(6, 'Red Velvet Muffin', 'makanan', 22000, 10),
(7, 'Banana Bread', 'makanan', 18000, 10),
(8, 'Americano', 'minuman', 20000, 10),
(9, 'Matcha Latte', 'minuman', 25000, 10),
(10, 'Lychee Tea', 'minuman', 18000, 10),
(11, 'Cappuccino', 'minuman', 22000, 10),
(12, 'Vanilla Latte', 'minuman', 25000, 10);

-- --------------------------------------------------------

--
-- Table structure for table `tb_pesanan`
--

CREATE TABLE `tb_pesanan` (
  `id_pesanan` int NOT NULL,
  `nama_pelanggan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `no_meja` int NOT NULL,
  `total_harga` int NOT NULL,
  `status_bayar` enum('belum_bayar','lunas') COLLATE utf8mb4_general_ci NOT NULL,
  `status_pesanan` enum('proses','selesai') COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_pesanan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `uang_bayar` int NOT NULL,
  `nama_kasir` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `metode_bayar` enum('tunai','qris','transfer','kartu') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'tunai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_resep`
--

CREATE TABLE `tb_resep` (
  `id_resep` int NOT NULL,
  `id_menu` int NOT NULL,
  `id_bahan` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_resep`
--

INSERT INTO `tb_resep` (`id_resep`, `id_menu`, `id_bahan`, `jumlah`) VALUES
(1, 1, 1, 100.00),
(2, 1, 4, 50.00),
(3, 1, 3, 1.00),
(4, 1, 7, 50.00),
(5, 1, 6, 2.00),
(6, 1, 11, 5.00),
(7, 2, 1, 120.00),
(8, 2, 4, 40.00),
(9, 2, 3, 1.00),
(10, 2, 7, 60.00),
(11, 2, 2, 30.00),
(12, 2, 25, 5.00),
(13, 2, 11, 5.00),
(14, 2, 26, 10.00),
(15, 3, 1, 100.00),
(16, 3, 3, 1.00),
(17, 3, 4, 40.00),
(18, 3, 2, 30.00),
(19, 3, 13, 10.00),
(20, 3, 5, 5.00),
(21, 3, 7, 30.00),
(22, 4, 1, 100.00),
(23, 4, 4, 50.00),
(24, 4, 3, 1.00),
(25, 4, 2, 20.00),
(26, 4, 22, 20.00),
(27, 4, 11, 5.00),
(28, 5, 1, 100.00),
(29, 5, 4, 40.00),
(30, 5, 3, 1.00),
(31, 5, 8, 50.00),
(32, 5, 2, 20.00),
(33, 5, 11, 5.00),
(34, 6, 1, 100.00),
(35, 6, 3, 1.00),
(36, 6, 4, 40.00),
(37, 6, 2, 30.00),
(38, 6, 9, 20.00),
(39, 6, 5, 5.00),
(40, 6, 7, 30.00),
(41, 7, 1, 120.00),
(42, 7, 3, 1.00),
(43, 7, 4, 40.00),
(44, 7, 2, 30.00),
(45, 7, 5, 5.00),
(46, 7, 10, 2.00),
(47, 8, 12, 15.00),
(48, 8, 18, 100.00),
(49, 8, 27, 1.00),
(50, 8, 28, 1.00),
(51, 9, 13, 10.00),
(52, 9, 14, 200.00),
(53, 9, 15, 20.00),
(54, 9, 18, 100.00),
(55, 9, 27, 1.00),
(56, 9, 28, 1.00),
(57, 10, 19, 10.00),
(58, 10, 15, 30.00),
(59, 10, 18, 150.00),
(60, 10, 14, 100.00),
(61, 10, 27, 1.00),
(62, 10, 28, 1.00),
(63, 11, 12, 15.00),
(64, 11, 14, 150.00),
(65, 11, 15, 15.00),
(66, 11, 27, 1.00),
(67, 11, 28, 1.00),
(68, 12, 12, 15.00),
(69, 12, 14, 200.00),
(70, 12, 16, 5.00),
(71, 12, 15, 20.00),
(72, 12, 27, 1.00),
(73, 12, 28, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_stok_log`
--

CREATE TABLE `tb_stok_log` (
  `id_log` int NOT NULL,
  `id_bahan` int NOT NULL,
  `jenis` enum('masuk','keluar','terpakai') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `keterangan` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_pesanan` int DEFAULT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_stok_log`
--

INSERT INTO `tb_stok_log` (`id_log`, `id_bahan`, `jenis`, `jumlah`, `keterangan`, `id_pesanan`, `tanggal`) VALUES
(56, 1, 'masuk', 3.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(57, 2, 'masuk', 2.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(58, 3, 'masuk', 25.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(59, 4, 'masuk', 1.50, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(60, 5, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(61, 6, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(62, 7, 'masuk', 2.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(63, 8, 'masuk', 0.50, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(64, 9, 'masuk', 0.50, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(65, 10, 'masuk', 25.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(66, 11, 'masuk', 50.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(67, 12, 'masuk', 0.50, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(68, 13, 'masuk', 75.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(69, 14, 'masuk', 2.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(70, 15, 'masuk', 250.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(71, 16, 'masuk', 50.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(72, 17, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(73, 18, 'masuk', 1.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(74, 19, 'masuk', 50.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(75, 20, 'masuk', 150.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(76, 21, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(77, 22, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(78, 23, 'masuk', 100.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(79, 24, 'masuk', 250.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(80, 25, 'masuk', 25.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(81, 26, 'masuk', 150.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(82, 27, 'masuk', 25.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(83, 28, 'masuk', 25.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59'),
(84, 29, 'masuk', 50.00, 'Stok awal saat tambah bahan', NULL, '2026-06-01 15:53:59');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int NOT NULL,
  `username` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('Owner','Admin','Kasir','Dapur') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'Owner', '$2y$10$SjJlpUQZiDiMuLUZtPA/Euj7NaG3VO28XoSb0UAOw58n2duJ4aiBu', 'Owner'),
(2, 'Admin', '$2y$10$CGnSl1rtHXNW9b/P.6a1DevfYZBDdCA9p0lVKr3fVSdJScLmbeRPa', 'Admin'),
(3, 'Kasir', '$2y$10$NO06qewOIHg71qoS/VrLveOPxZ5gPWMGe/SRyzt1xk7uTTDVcA5g6', 'Kasir'),
(4, 'Dapur', '$2y$10$pUN.eI8E45IjgjJfVi8rBO1yVK/73yyPZOCM8U08ykIaevIgoO.ke', 'Dapur');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_bahan`
--
ALTER TABLE `tb_bahan`
  ADD PRIMARY KEY (`id_bahan`);

--
-- Indexes for table `tb_detail_pesanan`
--
ALTER TABLE `tb_detail_pesanan`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indexes for table `tb_meja`
--
ALTER TABLE `tb_meja`
  ADD PRIMARY KEY (`id_meja`);

--
-- Indexes for table `tb_menu`
--
ALTER TABLE `tb_menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `tb_pesanan`
--
ALTER TABLE `tb_pesanan`
  ADD PRIMARY KEY (`id_pesanan`);

--
-- Indexes for table `tb_resep`
--
ALTER TABLE `tb_resep`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_bahan` (`id_bahan`);

--
-- Indexes for table `tb_stok_log`
--
ALTER TABLE `tb_stok_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_bahan` (`id_bahan`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_bahan`
--
ALTER TABLE `tb_bahan`
  MODIFY `id_bahan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tb_detail_pesanan`
--
ALTER TABLE `tb_detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_meja`
--
ALTER TABLE `tb_meja`
  MODIFY `id_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `tb_menu`
--
ALTER TABLE `tb_menu`
  MODIFY `id_menu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tb_pesanan`
--
ALTER TABLE `tb_pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `tb_resep`
--
ALTER TABLE `tb_resep`
  MODIFY `id_resep` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `tb_stok_log`
--
ALTER TABLE `tb_stok_log`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_resep`
--
ALTER TABLE `tb_resep`
  ADD CONSTRAINT `tb_resep_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tb_menu` (`id_menu`),
  ADD CONSTRAINT `tb_resep_ibfk_2` FOREIGN KEY (`id_bahan`) REFERENCES `tb_bahan` (`id_bahan`);

--
-- Constraints for table `tb_stok_log`
--
ALTER TABLE `tb_stok_log`
  ADD CONSTRAINT `tb_stok_log_ibfk_1` FOREIGN KEY (`id_bahan`) REFERENCES `tb_bahan` (`id_bahan`),
  ADD CONSTRAINT `tb_stok_log_ibfk_2` FOREIGN KEY (`id_pesanan`) REFERENCES `tb_pesanan` (`id_pesanan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
