<?php
// wajib_pajak/delete.php - Hapus Data Wajib Pajak
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID Wajib Pajak tidak valid.');
    header('Location: ' . base_url('wajib_pajak/index.php'));
    exit;
}

try {
    // 1. Ambil nama wajib pajak untuk pesan konfirmasi
    $stmt = $pdo->prepare("SELECT nama_wajib_pajak FROM wajib_pajak WHERE id = ?");
    $stmt->execute([$id]);
    $wp = $stmt->fetch();

    if (!$wp) {
        set_flash('warning', 'Data Wajib Pajak tidak ditemukan.');
        header('Location: ' . base_url('wajib_pajak/index.php'));
        exit;
    }

    // 2. Proteksi Foreign Key: Cek apakah WP memiliki riwayat pelaporan SPT
    $check_spt = $pdo->prepare("SELECT COUNT(*) AS total FROM pelaporan_spt WHERE id_wajib_pajak = ?");
    $check_spt->execute([$id]);
    $total_spt = $check_spt->fetch()['total'] ?? 0;

    if ($total_spt > 0) {
        set_flash('danger', "Wajib Pajak '{$wp['nama_wajib_pajak']}' tidak dapat dihapus karena memiliki {$total_spt} riwayat pelaporan SPT.");
        header('Location: ' . base_url('wajib_pajak/index.php'));
        exit;
    }

    // 3. Eksekusi Hapus
    $delete = $pdo->prepare("DELETE FROM wajib_pajak WHERE id = ?");
    $delete->execute([$id]);

    set_flash('success', "Wajib Pajak '{$wp['nama_wajib_pajak']}' berhasil dihapus dari sistem.");
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus data: ' . $e->getMessage());
}

header('Location: ' . base_url('wajib_pajak/index.php'));
exit;
