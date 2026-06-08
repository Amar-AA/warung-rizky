-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 04:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warung_pojok`
--

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `kategori` enum('Digital','Sembako','Bumbu','Jajanan') DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `foto` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `kategori`, `harga`, `stok`, `foto`) VALUES
(4, 'Beras Shira 5kg', 'Sembako', 68000, 10, 'default.png'),
(5, 'Minyak Goreng 1L', 'Sembako', 18000, 20, 'default.png'),
(6, 'Gula Pasir 1kg', 'Sembako', 16000, 10, 'default.png'),
(11, 'Token Listrik', 'Digital', 0, 0, 'default.png'),
(12, 'Paket Data', 'Digital', 0, 0, 'default.png'),
(13, 'Top Up DANA', 'Digital', 100, 0, 'default.png'),
(14, 'Pulsa Reguler', 'Digital', 0, 0, 'default.png'),
(16, 'gas', 'Sembako', 22000, 8, 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `nominal` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `jarak_km` decimal(5,2) DEFAULT 0.00,
  `ongkir` int(11) DEFAULT 0,
  `nomor_tujuan` varchar(50) DEFAULT NULL,
  `alamat_kirim` text DEFAULT NULL,
  `koordinat_customer` varchar(100) DEFAULT NULL,
  `lokasi_admin` varchar(100) DEFAULT NULL,
  `metode_pembayaran` enum('Cash','Transfer','COD') NOT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `kode_token` varchar(50) DEFAULT '-',
  `status` enum('Menunggu Pembayaran','Menunggu Konfirmasi','Sedang Diproses','Dipacking','Diantar','Sampai','Selesai') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `username`, `nama_produk`, `nominal`, `harga`, `jarak_km`, `ongkir`, `nomor_tujuan`, `alamat_kirim`, `koordinat_customer`, `lokasi_admin`, `metode_pembayaran`, `bukti_transfer`, `kode_token`, `status`, `tanggal`, `waktu`) VALUES
(1, 'budi', 'Top Up DANA 50rb', NULL, 51000, 0.00, 0, '085730411799', NULL, NULL, NULL, '', NULL, 'selesai', 'Selesai', '2026-05-07 02:49:05', '2026-06-06 15:55:46'),
(2, 'budi', 'Top Up DANA', 50000, 50000, 0.00, 0, '085730411799', NULL, NULL, NULL, 'Cash', '', '-', 'Selesai', '2026-05-07 03:03:34', '2026-06-06 15:55:46'),
(3, 'budi', 'Token Listrik', 100000, 100000, 0.00, 0, '085730411799', NULL, NULL, NULL, 'Cash', '', '-', 'Selesai', '2026-05-07 04:07:53', '2026-06-06 15:55:46'),
(4, 'budi', 'gas', 22000, 22000, 12.54, 10000, '085730411799', 'jl.sawo kecik, no 10 ( KOS NDX )', '-7.753081623238717,110.40648218460284', '', 'COD', '', '-', 'Sampai', '2026-06-06 06:57:24', '2026-06-06 15:55:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `role` enum('admin','customer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `email`, `no_telepon`, `role`) VALUES
('admin', 'admin123', 'admin@warungrizky.com', '081234567890', 'admin'),
('Amar', 'Amar123', 'amar@students.amikom.ac.id', '085730411799', 'customer'),
('rehan', '1234', 'rehan11@gmail.com', '085314715835', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `no_telepon` (`no_telepon`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
