-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2026 at 05:54 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pajak`
--

-- --------------------------------------------------------

--
-- Table structure for table `jenis_pajak`
--

CREATE TABLE `jenis_pajak` (
  `id` int NOT NULL,
  `kode_pajak` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pajak` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tarif_persen` decimal(5,2) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_pajak`
--

INSERT INTO `jenis_pajak` (`id`, `kode_pajak`, `nama_pajak`, `tarif_persen`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'PPH21', 'PPh Pasal 21 (Pajak Penghasilan Karyawan/Pegawai)', '5.00', 'Tarif lapis pertama penghasilan kena pajak orang pribadi', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(2, 'PPH22', 'PPh Pasal 22 (Pajak atas Impor & Pembelian Barang Tertentu)', '1.50', 'Pajak atas pembelian barang dari bendaharawan pemerintah atau badan tertentu', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(3, 'PPH23', 'PPh Pasal 23 (Pajak atas Jasa, Modal & Royalti)', '2.00', 'Pajak penghasilan atas imbalan jasa sewa, manajemen, teknik', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(4, 'PPH-FINAL', 'PPh Final PP 23/2018 (UMKM)', '0.50', 'Pajak penghasilan final atas peredaran bruto wajib pajak UMKM', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(5, 'PPN', 'Pajak Pertambahan Nilai (PPN)', '11.00', 'Pajak pertambahan nilai barang dan jasa kena pajak (UU HPP 11%)', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(6, 'PBB-P2', 'Pajak Bumi dan Bangunan (PBB-P2)', '0.50', 'Pajak daerah atas kepemilikan tanah dan bangunan komersial/hunian', '2026-11-16 05:47:30', '2026-11-16 05:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `pelaporan_spt`
--

CREATE TABLE `pelaporan_spt` (
  `id` int NOT NULL,
  `nomor_spt` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_wajib_pajak` int NOT NULL,
  `id_jenis_pajak` int NOT NULL,
  `id_petugas` int NOT NULL,
  `masa_pajak` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_pajak` int NOT NULL,
  `dasar_pengenaan_pajak` decimal(15,2) NOT NULL,
  `pajak_terutang` decimal(15,2) NOT NULL,
  `tanggal_lapor` date NOT NULL,
  `status_lapor` enum('TEPAT WAKTU','TERLAMBAT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelaporan_spt`
--

INSERT INTO `pelaporan_spt` (`id`, `nomor_spt`, `id_wajib_pajak`, `id_jenis_pajak`, `id_petugas`, `masa_pajak`, `tahun_pajak`, `dasar_pengenaan_pajak`, `pajak_terutang`, `tanggal_lapor`, `status_lapor`, `created_at`, `updated_at`) VALUES
(1, 'SPT-2026-0001', 1, 1, 1, 'Maret', 2026, '60000000.00', '3000000.00', '2026-03-25', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(2, 'SPT-2026-0002', 4, 5, 2, 'Januari', 2026, '450000000.00', '49500000.00', '2026-02-18', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(3, 'SPT-2026-0003', 5, 4, 1, 'Februari', 2026, '120000000.00', '600000.00', '2026-03-12', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(4, 'SPT-2026-0004', 2, 1, 2, 'April', 2026, '95000000.00', '4750000.00', '2026-05-02', 'TERLAMBAT', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(5, 'SPT-2026-0005', 6, 5, 3, 'Maret', 2026, '320000000.00', '35200000.00', '2026-04-20', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(6, 'SPT-2026-0006', 4, 3, 1, 'Maret', 2026, '85000000.00', '1700000.00', '2026-04-10', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(7, 'SPT-2026-0007', 3, 4, 2, 'Maret', 2026, '45000000.00', '225000.00', '2026-04-14', 'TEPAT WAKTU', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(8, 'SPT-2026-0008', 7, 6, 3, 'Tahunan', 2026, '500000000.00', '2500000.00', '2026-06-10', 'TERLAMBAT', '2026-11-16 05:47:30', '2026-11-16 05:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_pajak`
--

CREATE TABLE `pembayaran_pajak` (
  `id` int NOT NULL,
  `kode_billing` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_pelaporan` int NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_bayar` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `status_bayar` enum('LUNAS','BELUM LUNAS') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM LUNAS',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembayaran_pajak`
--

INSERT INTO `pembayaran_pajak` (`id`, `kode_billing`, `id_pelaporan`, `jumlah_bayar`, `metode_bayar`, `tanggal_bayar`, `status_bayar`, `created_at`, `updated_at`) VALUES
(1, '820260325010011', 1, '3000000.00', 'Bank Transfer BCA', '2026-03-26 10:15:30', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(2, '820260218010022', 2, '49500000.00', 'Virtual Account Mandiri', '2026-02-19 14:22:10', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(3, '820260312010033', 3, '600000.00', 'Bank Transfer BNI', '2026-03-12 11:05:00', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(4, '820260502010044', 4, '4750000.00', NULL, NULL, 'BELUM LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(5, '820260420010055', 5, '35200000.00', 'Virtual Account BRI', '2026-04-21 09:40:00', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(6, '820260410010066', 6, '1700000.00', 'Bank Transfer BCA', '2026-04-11 16:30:15', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(7, '820260414010077', 7, '225000.00', 'QRIS Bank Indonesia', '2026-04-14 13:00:22', 'LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(8, '820260610010088', 8, '2500000.00', NULL, NULL, 'BELUM LUNAS', '2026-11-16 05:47:30', '2026-11-16 05:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `petugas_pajak`
--

CREATE TABLE `petugas_pajak` (
  `id` int NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_petugas` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `petugas_pajak`
--

INSERT INTO `petugas_pajak` (`id`, `nip`, `nama_petugas`, `jabatan`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, '198507122008011002', 'Budi Santoso, S.E., M.Ak.', 'Account Representative', 'budi_santoso', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(2, '199003252012022003', 'Siti Rahmawati, S.A.P.', 'Fungsional Pemeriksa Pajak', 'siti_rahma', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(3, '198211052005011004', 'Hendrawan Kusuma, M.M.', 'Kepala Seksi Pengawasan', 'hendrawan', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy', '2026-11-16 05:47:30', '2026-11-16 05:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `wajib_pajak`
--

CREATE TABLE `wajib_pajak` (
  `id` int NOT NULL,
  `npwp` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_wajib_pajak` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_wp` enum('OP','Badan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wajib_pajak`
--

INSERT INTO `wajib_pajak` (`id`, `npwp`, `nik`, `nama_wajib_pajak`, `jenis_wp`, `alamat`, `no_telepon`, `email`, `tanggal_daftar`, `created_at`, `updated_at`) VALUES
(1, '09.254.341.2-404.000', '3273011205880001', 'Ahmad Fadillah Pratama', 'OP', 'Jl. Merdeka No. 45, Coblong, Kota Bandung', '081234567890', 'ahmad.fadillah@gmail.com', '2022-01-15', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(2, '08.765.432.1-402.000', '3273022408920003', 'dr. Ratna Wulandari, Sp.A', 'OP', 'Jl. Diponegoro No. 12, Cibeunying, Bandung', '081398765432', 'dr.ratna.w@gmail.com', '2022-03-20', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(3, '01.345.678.9-012.000', '3171015509890005', 'Reza Pramudya, S.Kom.', 'OP', 'Jl. Rasuna Said Kav. 8, Setiabudi, Jakarta Selatan', '085712349988', 'reza.pramudya@digitaltax.id', '2023-02-10', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(4, '02.456.789.0-071.000', '3174090101900007', 'PT Nusantara Solusi Teknologi', 'Badan', 'Sudirman Central Business District (SCBD) Tower 3 Lt. 14, Jakarta', '02157908888', 'tax@nusantarasolusi.co.id', '2021-06-01', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(5, '03.567.890.1-411.000', '3275080202870009', 'CV Karya Mandiri Makmur', 'Badan', 'Kawasan Industri Sentul Blok B4 No. 8, Bogor', '02518654321', 'finance@karyamandiri.com', '2021-08-17', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(6, '04.678.901.2-501.000', '3374011406950002', 'PT Cahaya Abadi Logistik', 'Badan', 'Jl. Tanjung Mas No. 102, Semarang Utara, Semarang', '02435678901', 'perpajakan@cahayalogistik.co.id', '2022-11-05', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(7, '05.789.012.3-602.000', '3578012903840004', 'Bambang Soeprapto', 'OP', 'Jl. Darmo Permai Selatan No. 88, Surabaya Barat', '081233445566', 'bambang.soeprapto@yahoo.co.id', '2023-05-12', '2026-11-16 05:47:30', '2026-11-16 05:47:30'),
(8, '06.890.123.4-901.000', '5171010807910006', 'CV Bali Indah Cemerlang', 'Badan', 'Jl. Sunset Road No. 77, Kuta, Badung, Bali', '03617654321', 'admin@bali-indah.com', '2023-09-01', '2026-11-16 05:47:30', '2026-11-16 05:47:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jenis_pajak`
--
ALTER TABLE `jenis_pajak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pajak` (`kode_pajak`);

--
-- Indexes for table `pelaporan_spt`
--
ALTER TABLE `pelaporan_spt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_spt` (`nomor_spt`),
  ADD KEY `fk_spt_wajib_pajak` (`id_wajib_pajak`),
  ADD KEY `fk_spt_jenis_pajak` (`id_jenis_pajak`),
  ADD KEY `fk_spt_petugas` (`id_petugas`);

--
-- Indexes for table `pembayaran_pajak`
--
ALTER TABLE `pembayaran_pajak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_billing` (`kode_billing`),
  ADD KEY `fk_bayar_pelaporan` (`id_pelaporan`);

--
-- Indexes for table `petugas_pajak`
--
ALTER TABLE `petugas_pajak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `wajib_pajak`
--
ALTER TABLE `wajib_pajak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `npwp` (`npwp`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_pajak`
--
ALTER TABLE `jenis_pajak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pelaporan_spt`
--
ALTER TABLE `pelaporan_spt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pembayaran_pajak`
--
ALTER TABLE `pembayaran_pajak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `petugas_pajak`
--
ALTER TABLE `petugas_pajak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wajib_pajak`
--
ALTER TABLE `wajib_pajak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pelaporan_spt`
--
ALTER TABLE `pelaporan_spt`
  ADD CONSTRAINT `fk_spt_jenis_pajak` FOREIGN KEY (`id_jenis_pajak`) REFERENCES `jenis_pajak` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_spt_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas_pajak` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_spt_wajib_pajak` FOREIGN KEY (`id_wajib_pajak`) REFERENCES `wajib_pajak` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran_pajak`
--
ALTER TABLE `pembayaran_pajak`
  ADD CONSTRAINT `fk_bayar_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `pelaporan_spt` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
