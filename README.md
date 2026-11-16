# 🏛️ SIPAJAK - Sistem Informasi Perpajakan

Aplikasi Web CRUD & Transaksi Manajemen Perpajakan Lengkap yang dibangun menggunakan **PHP Native (PDO)**, **MySQL**, dan antarmuka modern **Bootstrap 5**.

Aplikasi ini siap digunakan langsung di lingkungan **Laragon** (atau XAMPP) pada folder `c:\laragon\www\databases_pajak`.

---

## 📋 Daftar Isi
1. [Fitur Utama](#-fitur-utama)
2. [Struktur Database & ERD](#-struktur-database--erd)
3. [Panduan Instalasi & Menjalankan Aplikasi](#-panduan-instalasi--menjalankan-aplikasi)
4. [Arsitektur File & Direktori](#-arsitektur-file--direktori)
5. [Logika Kalkulasi Pajak Terutang](#-logika-kalkulasi-pajak-terutang)
6. [Data Akun Petugas](#-data-akun-petugas)

---

## ✨ Fitur Utama

- **1. Dashboard Ringkasan Eksekutif:**
  - Kartu KPI: Total Wajib Pajak (Breakdown Orang Pribadi vs Badan).
  - Realisasi Penerimaan Kas Negara (Total setoran dengan status `LUNAS`).
  - Total Pelaporan SPT (Statistik Kepatuhan: `TEPAT WAKTU` vs `TERLAMBAT`).
  - Total Tunggakan Pajak (Status `BELUM LUNAS`).
  - Tabel Interaktif 8 SPT Terbaru dengan status dan navigasi langsung ke pembayaran/detail.
  - Kartu referensi regulasi tarif pajak yang aktif.

- **2. Master Data Wajib Pajak (CRUD Lengkap):**
  - **Create:** Registrasi Wajib Pajak baru (NPWP, NIK, Nama, Jenis WP [OP / Badan], Alamat, Telepon, Email, Tanggal Daftar).
  - **Read:** Pencarian instan berdasarkan Nama, NPWP, NIK, dan filter jenis WP.
  - **Update:** Edit data WP dengan validasi keunikan NPWP & NIK.
  - **Delete:** Penghapusan aman dengan *Foreign Key Protection* (mencegah penghapusan jika WP memiliki riwayat SPT).

- **3. Master Data Jenis Pajak & Tarif (CRUD Lengkap):**
  - **Create:** Tambah jenis pajak dengan kode unik (misal: `PPH21`, `PPN`, `PBB-P2`), nama pajak, persentase tarif, dan keterangan dasar hukum.
  - **Read:** Menampilkan daftar jenis pajak beserta persentase tarif dan jumlah SPT yang menggunakannya.
  - **Update:** Edit kode pajak, nama, dan persentase tarif.
  - **Delete:** Proteksi penghapusan jika telah digunakan pada pelaporan SPT.

- **4. Transaksi Pelaporan SPT dengan Kalkulasi Otomatis:**
  - Penomoran SPT otomatis berurutan (misal: `SPT-2026-0001`).
  - **Kalkulasi Otomatis Real-time:**
    - Saat memilih *Jenis Pajak*, sistem otomatis memuat tarif resminya.
    - Saat menginput *Dasar Pengenaan Pajak (DPP)*, sistem langsung menghitung nominal **Pajak Terutang** di browser (`pajak_terutang = DPP * tarif%`).
    - Divalidasi dan dihitung ulang di sisi server (PHP) untuk mencegah manipulasi.
  - Penerbitan otomatis **Kode Billing 15 Digit DJP** ke tabel `pembayaran_pajak` dengan status awal `BELUM LUNAS`.
  - Formulir status kepatuhan: `TEPAT WAKTU` atau `TERLAMBAT`.
  - **Bukti Penerimaan Elektronik (BPE):** Halaman tanda terima resmi yang dapat dicetak (`window.print()`).

- **5. Transaksi Pembayaran & Billing:**
  - Monitoring seluruh tagihan billing dan status pembayarannya (`LUNAS` / `BELUM LUNAS`).
  - Form eksekusi pembayaran dengan pemilihan kanal/metode bayar (Bank Transfer BCA, Mandiri VA, BRI, BNI, QRIS, Kantor Pos).
  - **Bukti Penerimaan Negara (BPN):** Dokumen kuitansi resmi berstandar MPN G3 dengan NTPN unik, NTB, dan terbilang rupiah.

---

## 🗄️ Struktur Database & ERD

Database yang digunakan bernama `db_perpajakan`.

### Relasi Antar Tabel (Foreign Key):
```
wajib_pajak (1) ───< (N) pelaporan_spt (1) ─── (1) pembayaran_pajak
                             ^
jenis_pajak (1) ─────────────┤
                             ^
petugas_pajak (1) ───────────┘
```

1. **`wajib_pajak`**
   - `id` (INT PK AI)
   - `npwp` (VARCHAR(25) UNIQUE)
   - `nik` (VARCHAR(20) UNIQUE)
   - `nama_wajib_pajak` (VARCHAR(150))
   - `jenis_wp` (ENUM('OP', 'Badan'))
   - `alamat` (TEXT)
   - `no_telepon` (VARCHAR(20))
   - `email` (VARCHAR(100))
   - `tanggal_daftar` (DATE)
   - `created_at`, `updated_at`

2. **`jenis_pajak`**
   - `id` (INT PK AI)
   - `kode_pajak` (VARCHAR(20) UNIQUE)
   - `nama_pajak` (VARCHAR(100))
   - `tarif_persen` (DECIMAL(5, 2))
   - `keterangan` (TEXT)
   - `created_at`, `updated_at`

3. **`petugas_pajak`**
   - `id` (INT PK AI)
   - `nip` (VARCHAR(30) UNIQUE)
   - `nama_petugas` (VARCHAR(150))
   - `jabatan` (VARCHAR(100))
   - `username` (VARCHAR(50) UNIQUE)
   - `password` (VARCHAR(255))
   - `created_at`, `updated_at`

4. **`pelaporan_spt`**
   - `id` (INT PK AI)
   - `nomor_spt` (VARCHAR(50) UNIQUE)
   - `id_wajib_pajak` (INT FK -> `wajib_pajak.id`)
   - `id_jenis_pajak` (INT FK -> `jenis_pajak.id`)
   - `id_petugas` (INT FK -> `petugas_pajak.id`)
   - `masa_pajak` (VARCHAR(20))
   - `tahun_pajak` (INT)
   - `dasar_pengenaan_pajak` (DECIMAL(15, 2))
   - `pajak_terutang` (DECIMAL(15, 2))
   - `tanggal_lapor` (DATE)
   - `status_lapor` (ENUM('TEPAT WAKTU', 'TERLAMBAT'))
   - `created_at`, `updated_at`

5. **`pembayaran_pajak`**
   - `id` (INT PK AI)
   - `kode_billing` (VARCHAR(50) UNIQUE)
   - `id_pelaporan` (INT FK -> `pelaporan_spt.id` ON DELETE CASCADE)
   - `jumlah_bayar` (DECIMAL(15, 2))
   - `metode_bayar` (VARCHAR(50))
   - `tanggal_bayar` (DATETIME)
   - `status_bayar` (ENUM('LUNAS', 'BELUM LUNAS'))
   - `created_at`, `updated_at`

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### Langkah 1: Aktifkan Laragon / MySQL
1. Buka aplikasi **Laragon**.
2. Klik tombol **"Start All"** (pastikan Apache dan MySQL aktif).

### Langkah 2: Impor Database `db_perpajakan`
Pilih salah satu metode berikut:

- **Cara A (Melalui Tombol di Aplikasi):**
  1. Buka browser ke alamat: `http://localhost/databases_pajak/database_info.php`
  2. Klik tombol biru **"Jalankan Inisialisasi Database"**.
  3. Skrip `database.sql` akan otomatis dieksekusi oleh PHP PDO.

- **Cara B (Melalui HeidiSQL / phpMyAdmin):**
  1. Buka phpMyAdmin (`http://localhost/phpmyadmin`) atau klik menu Database di Laragon.
  2. Buka tab **Import**.
  3. Pilih file `c:\laragon\www\databases_pajak\database.sql`.
  4. Klik **Go / Execute**.

### Langkah 3: Akses Aplikasi di Browser
Buka salah satu URL berikut di browser Anda:
- `http://localhost/databases_pajak`
- Atau via Virtual Host Laragon: `http://databases_pajak.test`

---

## 📁 Arsitektur File & Direktori

```
databases_pajak/
├── config/
│   └── database.php         # Koneksi PDO MySQL & Fungsi Helper Global
├── database.sql             # Skrip DDL Database, Relasi FK, dan Data Dummy
├── includes/
│   ├── header.php           # Template Head HTML, Bootstrap 5 CDN & CSS
│   ├── sidebar.php          # Navigasi Menu Samping Responsif
│   ├── navbar.php           # Navigasi Atas & Info Petugas Aktif
│   └── footer.php           # Footer, Bootstrap JS Bundle, dan Skrip
├── assets/
│   ├── css/
│   │   └── style.css        # Desain Kustom Modern, Kartu KPI, & Print Styles
│   └── js/
│       └── app.js           # Real-time Auto-Calculation & Interaktivitas
├── index.php                # Halaman Dashboard Eksekutif
├── database_info.php        # Informasi Skema ERD & Auto-Import Tool
├── wajib_pajak/
│   ├── index.php            # Tampil / Read Data Wajib Pajak & Filter
│   ├── create.php           # Tambah Wajib Pajak Baru
│   ├── edit.php             # Ubah Data Wajib Pajak
│   └── delete.php           # Hapus Wajib Pajak (dengan Proteksi FK)
├── jenis_pajak/
│   ├── index.php            # Tampil / Read Jenis Pajak & Tarif
│   ├── create.php           # Tambah Jenis Pajak Baru
│   ├── edit.php             # Ubah Jenis Pajak & Tarif
│   └── delete.php           # Hapus Jenis Pajak
├── pelaporan_spt/
│   ├── index.php            # Daftar Transaksi Pelaporan SPT
│   ├── create.php           # Form Pelaporan dengan Kalkulasi Otomatis
│   ├── detail.php           # Detail SPT & Bukti Penerimaan Elektronik (BPE)
│   ├── edit.php             # Ubah Data SPT (Rekalkulasi Otomatis)
│   └── delete.php           # Hapus SPT (Proteksi jika sudah Lunas)
└── pembayaran/
    ├── index.php            # Daftar Billing & Monitoring Kas Negara
    ├── bayar.php            # Form Setor Pajak & Pelunasan
    └── cetak_bukti.php      # Cetak Bukti Penerimaan Negara (BPN Resmi)
```

---

## 🧮 Logika Kalkulasi Pajak Terutang

Rumus formal yang diterapkan:
$$\text{Pajak Terutang} = \text{Dasar Pengenaan Pajak (DPP)} \times \left( \frac{\text{Tarif Persen}}{100} \right)$$

Contoh pada aplikasi:
1. Pengguna memilih Wajib Pajak `PT Nusantara Solusi Teknologi`.
2. Pengguna memilih Jenis Pajak `PPN (11.00%)`.
3. Pengguna menginput DPP sebesar `Rp 100.000.000`.
4. Secara *instant* JavaScript menghitung: `100.000.000 * 0.11 = Rp 11.000.000`.
5. Saat formulir disubmit, PHP melakukan verifikasi ulang tarif dari database untuk memastikan integritas data keuangan negara.

---

## 👥 Data Akun Petugas Pajak

| ID | NIP | Nama Petugas | Jabatan | Username | Password |
|---|---|---|---|---|---|
| 1 | `198507122008011002` | Budi Santoso, S.E., M.Ak. | Account Representative | `budi_santoso` | `admin123` |
| 2 | `199003252012022003` | Siti Rahmawati, S.A.P. | Fungsional Pemeriksa Pajak | `siti_rahma` | `admin123` |
| 3 | `198211052005011004` | Hendrawan Kusuma, M.M. | Kepala Seksi Pengawasan | `hendrawan` | `admin123` |
