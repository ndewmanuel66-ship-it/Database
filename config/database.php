<?php
/**
 * SISTEM INFORMASI PERPAJAKAN (SIPAJAK)
 * Konfigurasi Database & Helper Global
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database MySQL
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'db_perpajakan');
define('DB_USER', 'root');
define('DB_PASS', '');

// Inisialisasi Koneksi PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Tangani jika database belum dibuat / koneksi error
    $db_error_message = $e->getMessage();
    $pdo = null;
}

/**
 * Base URL Helper
 * Menentukan URL dasar aplikasi secara dinamis
 */
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Deteksi sub-direktori jika dijalankan di htdocs / www Laragon
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Cari root direktori proyek 'databases_pajak'
    $pos = strpos($scriptDir, '/databases_pajak');
    if ($pos !== false) {
        $root = substr($scriptDir, 0, $pos + strlen('/databases_pajak'));
    } else {
        // Jika menggunakan VirtualHost khusus (misal: databases_pajak.test)
        $root = '';
    }
    
    $cleanPath = ltrim($path, '/');
    return rtrim($protocol . $host . $root, '/') . ($cleanPath ? '/' . $cleanPath : '');
}

/**
 * Format Angka ke Rupiah Indonesia
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Format Tanggal Indonesia
 * Contoh: 2026-11-16 -> 16 November 2026
 */
function format_tanggal($tanggal, $dengan_waktu = false) {
    if (!$tanggal || $tanggal == '0000-00-00' || $tanggal == '0000-00-00 00:00:00') {
        return '-';
    }
    
    $bulan_indo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($tanggal);
    $tgl = date('d', $time);
    $bln = (int)date('m', $time);
    $thn = date('Y', $time);
    
    $hasil = $tgl . ' ' . ($bulan_indo[$bln] ?? '') . ' ' . $thn;
    
    if ($dengan_waktu) {
        $hasil .= ' ' . date('H:i:s', $time) . ' WIB';
    }
    
    return $hasil;
}

/**
 * Flash Message Notification Helper
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitasi Input String
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generator Nomor SPT Unik Otomatis
 * Format: SPT-YYYY-XXXX (contoh: SPT-2026-0009)
 */
function generate_nomor_spt($pdo) {
    $tahun = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pelaporan_spt WHERE nomor_spt LIKE ?");
    $stmt->execute(["SPT-{$tahun}-%"]);
    $row = $stmt->fetch();
    $next = ($row['total'] ?? 0) + 1;
    return sprintf("SPT-%s-%04d", $tahun, $next);
}

/**
 * Generator Kode Billing Unik Otomatis (15 Digit Standar DJP)
 * Format: 8 + YYYYMMDD + 5 digit urut
 */
function generate_kode_billing($pdo) {
    $prefix = '8' . date('Ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pembayaran_pajak WHERE kode_billing LIKE ?");
    $stmt->execute(["{$prefix}%"]);
    $row = $stmt->fetch();
    $next = ($row['total'] ?? 0) + 1;
    return sprintf("%s%05d", $prefix, $next);
}
