-- ========================================================
-- SISTEM INFORMASI PERPAJAKAN (SIPAJAK)
-- Skrip DDL Database, Relasi Foreign Key, dan Data Dummy
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_perpajakan` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_perpajakan`;

-- --------------------------------------------------------
-- 1. Tabel Master: wajib_pajak
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pembayaran_pajak`;
DROP TABLE IF EXISTS `pelaporan_spt`;
DROP TABLE IF EXISTS `petugas_pajak`;
DROP TABLE IF EXISTS `jenis_pajak`;
DROP TABLE IF EXISTS `wajib_pajak`;

CREATE TABLE `wajib_pajak` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `npwp` VARCHAR(25) NOT NULL UNIQUE,
  `nik` VARCHAR(20) NOT NULL UNIQUE,
  `nama_wajib_pajak` VARCHAR(150) NOT NULL,
  `jenis_wp` ENUM('OP', 'Badan') NOT NULL,
  `alamat` TEXT NOT NULL,
  `no_telepon` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `tanggal_daftar` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabel Master: jenis_pajak
-- --------------------------------------------------------
CREATE TABLE `jenis_pajak` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pajak` VARCHAR(20) NOT NULL UNIQUE,
  `nama_pajak` VARCHAR(100) NOT NULL,
  `tarif_persen` DECIMAL(5, 2) NOT NULL,
  `keterangan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabel Master: petugas_pajak
-- --------------------------------------------------------
CREATE TABLE `petugas_pajak` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nip` VARCHAR(30) NOT NULL UNIQUE,
  `nama_petugas` VARCHAR(150) NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabel Transaksi: pelaporan_spt
-- --------------------------------------------------------
CREATE TABLE `pelaporan_spt` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_spt` VARCHAR(50) NOT NULL UNIQUE,
  `id_wajib_pajak` INT NOT NULL,
  `id_jenis_pajak` INT NOT NULL,
  `id_petugas` INT NOT NULL,
  `masa_pajak` VARCHAR(20) NOT NULL,
  `tahun_pajak` INT NOT NULL,
  `dasar_pengenaan_pajak` DECIMAL(15, 2) NOT NULL,
  `pajak_terutang` DECIMAL(15, 2) NOT NULL,
  `tanggal_lapor` DATE NOT NULL,
  `status_lapor` ENUM('TEPAT WAKTU', 'TERLAMBAT') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_spt_wajib_pajak` FOREIGN KEY (`id_wajib_pajak`) REFERENCES `wajib_pajak` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_jenis_pajak` FOREIGN KEY (`id_jenis_pajak`) REFERENCES `jenis_pajak` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas_pajak` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabel Transaksi: pembayaran_pajak
-- --------------------------------------------------------
CREATE TABLE `pembayaran_pajak` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_billing` VARCHAR(50) NOT NULL UNIQUE,
  `id_pelaporan` INT NOT NULL,
  `jumlah_bayar` DECIMAL(15, 2) NOT NULL,
  `metode_bayar` VARCHAR(50) NULL,
  `tanggal_bayar` DATETIME NULL,
  `status_bayar` ENUM('LUNAS', 'BELUM LUNAS') NOT NULL DEFAULT 'BELUM LUNAS',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bayar_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `pelaporan_spt` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA DUMMY (SEEDS)
-- ========================================================

-- 1. Data Dummy Petugas Pajak (Password: admin123 -> $2y$10$w... bcrypt hash)
INSERT INTO `petugas_pajak` (`id`, `nip`, `nama_petugas`, `jabatan`, `username`, `password`) VALUES
(1, '198507122008011002', 'Budi Santoso, S.E., M.Ak.', 'Account Representative', 'budi_santoso', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy'),
(2, '199003252012022003', 'Siti Rahmawati, S.A.P.', 'Fungsional Pemeriksa Pajak', 'siti_rahma', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy'),
(3, '198211052005011004', 'Hendrawan Kusuma, M.M.', 'Kepala Seksi Pengawasan', 'hendrawan', '$2y$10$tZ2R8zM41XmHqXmJ45l2eeX9rK7E4w1.Xw0VbMfvKqjK7qf2s/Lhy');

-- 2. Data Dummy Jenis Pajak
INSERT INTO `jenis_pajak` (`id`, `kode_pajak`, `nama_pajak`, `tarif_persen`, `keterangan`) VALUES
(1, 'PPH21', 'PPh Pasal 21 (Pajak Penghasilan Karyawan/Pegawai)', 5.00, 'Tarif lapis pertama penghasilan kena pajak orang pribadi'),
(2, 'PPH22', 'PPh Pasal 22 (Pajak atas Impor & Pembelian Barang Tertentu)', 1.50, 'Pajak atas pembelian barang dari bendaharawan pemerintah atau badan tertentu'),
(3, 'PPH23', 'PPh Pasal 23 (Pajak atas Jasa, Modal & Royalti)', 2.00, 'Pajak penghasilan atas imbalan jasa sewa, manajemen, teknik'),
(4, 'PPH-FINAL', 'PPh Final PP 23/2018 (UMKM)', 0.50, 'Pajak penghasilan final atas peredaran bruto wajib pajak UMKM'),
(5, 'PPN', 'Pajak Pertambahan Nilai (PPN)', 11.00, 'Pajak pertambahan nilai barang dan jasa kena pajak (UU HPP 11%)'),
(6, 'PBB-P2', 'Pajak Bumi dan Bangunan (PBB-P2)', 0.50, 'Pajak daerah atas kepemilikan tanah dan bangunan komersial/hunian');

-- 3. Data Dummy Wajib Pajak
INSERT INTO `wajib_pajak` (`id`, `npwp`, `nik`, `nama_wajib_pajak`, `jenis_wp`, `alamat`, `no_telepon`, `email`, `tanggal_daftar`) VALUES
(1, '09.254.341.2-404.000', '3273011205880001', 'Ahmad Fadillah Pratama', 'OP', 'Jl. Merdeka No. 45, Coblong, Kota Bandung', '081234567890', 'ahmad.fadillah@gmail.com', '2022-01-15'),
(2, '08.765.432.1-402.000', '3273022408920003', 'dr. Ratna Wulandari, Sp.A', 'OP', 'Jl. Diponegoro No. 12, Cibeunying, Bandung', '081398765432', 'dr.ratna.w@gmail.com', '2022-03-20'),
(3, '01.345.678.9-012.000', '3171015509890005', 'Reza Pramudya, S.Kom.', 'OP', 'Jl. Rasuna Said Kav. 8, Setiabudi, Jakarta Selatan', '085712349988', 'reza.pramudya@digitaltax.id', '2023-02-10'),
(4, '02.456.789.0-071.000', '3174090101900007', 'PT Nusantara Solusi Teknologi', 'Badan', 'Sudirman Central Business District (SCBD) Tower 3 Lt. 14, Jakarta', '02157908888', 'tax@nusantarasolusi.co.id', '2021-06-01'),
(5, '03.567.890.1-411.000', '3275080202870009', 'CV Karya Mandiri Makmur', 'Badan', 'Kawasan Industri Sentul Blok B4 No. 8, Bogor', '02518654321', 'finance@karyamandiri.com', '2021-08-17'),
(6, '04.678.901.2-501.000', '3374011406950002', 'PT Cahaya Abadi Logistik', 'Badan', 'Jl. Tanjung Mas No. 102, Semarang Utara, Semarang', '02435678901', 'perpajakan@cahayalogistik.co.id', '2022-11-05'),
(7, '05.789.012.3-602.000', '3578012903840004', 'Bambang Soeprapto', 'OP', 'Jl. Darmo Permai Selatan No. 88, Surabaya Barat', '081233445566', 'bambang.soeprapto@yahoo.co.id', '2023-05-12'),
(8, '06.890.123.4-901.000', '5171010807910006', 'CV Bali Indah Cemerlang', 'Badan', 'Jl. Sunset Road No. 77, Kuta, Badung, Bali', '03617654321', 'admin@bali-indah.com', '2023-09-01');

-- 4. Data Dummy Pelaporan SPT
INSERT INTO `pelaporan_spt` (`id`, `nomor_spt`, `id_wajib_pajak`, `id_jenis_pajak`, `id_petugas`, `masa_pajak`, `tahun_pajak`, `dasar_pengenaan_pajak`, `pajak_terutang`, `tanggal_lapor`, `status_lapor`) VALUES
(1, 'SPT-2026-0001', 1, 1, 1, 'Maret', 2026, 60000000.00, 3000000.00, '2026-03-25', 'TEPAT WAKTU'),
(2, 'SPT-2026-0002', 4, 5, 2, 'Januari', 2026, 450000000.00, 49500000.00, '2026-02-18', 'TEPAT WAKTU'),
(3, 'SPT-2026-0003', 5, 4, 1, 'Februari', 2026, 120000000.00, 600000.00, '2026-03-12', 'TEPAT WAKTU'),
(4, 'SPT-2026-0004', 2, 1, 2, 'April', 2026, 95000000.00, 4750000.00, '2026-05-02', 'TERLAMBAT'),
(5, 'SPT-2026-0005', 6, 5, 3, 'Maret', 2026, 320000000.00, 35200000.00, '2026-04-20', 'TEPAT WAKTU'),
(6, 'SPT-2026-0006', 4, 3, 1, 'Maret', 2026, 85000000.00, 1700000.00, '2026-04-10', 'TEPAT WAKTU'),
(7, 'SPT-2026-0007', 3, 4, 2, 'Maret', 2026, 45000000.00, 225000.00, '2026-04-14', 'TEPAT WAKTU'),
(8, 'SPT-2026-0008', 7, 6, 3, 'Tahunan', 2026, 500000000.00, 2500000.00, '2026-06-10', 'TERLAMBAT');

-- 5. Data Dummy Pembayaran Pajak
INSERT INTO `pembayaran_pajak` (`id`, `kode_billing`, `id_pelaporan`, `jumlah_bayar`, `metode_bayar`, `tanggal_bayar`, `status_bayar`) VALUES
(1, '820260325010011', 1, 3000000.00, 'Bank Transfer BCA', '2026-03-26 10:15:30', 'LUNAS'),
(2, '820260218010022', 2, 49500000.00, 'Virtual Account Mandiri', '2026-02-19 14:22:10', 'LUNAS'),
(3, '820260312010033', 3, 600000.00, 'Bank Transfer BNI', '2026-03-12 11:05:00', 'LUNAS'),
(4, '820260502010044', 4, 4750000.00, NULL, NULL, 'BELUM LUNAS'),
(5, '820260420010055', 5, 35200000.00, 'Virtual Account BRI', '2026-04-21 09:40:00', 'LUNAS'),
(6, '820260410010066', 6, 1700000.00, 'Bank Transfer BCA', '2026-04-11 16:30:15', 'LUNAS'),
(7, '820260414010077', 7, 225000.00, 'QRIS Bank Indonesia', '2026-04-14 13:00:22', 'LUNAS'),
(8, '820260610010088', 8, 2500000.00, NULL, NULL, 'BELUM LUNAS');
