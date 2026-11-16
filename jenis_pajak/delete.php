<?php
// jenis_pajak/delete.php - Hapus Data Jenis Pajak
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID Jenis Pajak tidak valid.');
    header('Location: ' . base_url('jenis_pajak/index.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT kode_pajak, nama_pajak FROM jenis_pajak WHERE id = ?");
    $stmt->execute([$id]);
    $jp = $stmt->fetch();

    if (!$jp) {
        set_flash('warning', 'Data Jenis Pajak tidak ditemukan.');
        header('Location: ' . base_url('jenis_pajak/index.php'));
        exit;
    }

    // Proteksi Foreign Key: Cek relasi di tabel pelaporan_spt
    $check_spt = $pdo->prepare("SELECT COUNT(*) AS total FROM pelaporan_spt WHERE id_jenis_pajak = ?");
    $check_spt->execute([$id]);
    $total_spt = $check_spt->fetch()['total'] ?? 0;

    if ($total_spt > 0) {
        set_flash('danger', "Jenis Pajak '{$jp['kode_pajak']} - {$jp['nama_pajak']}' tidak dapat dihapus karena telah tercatat pada {$total_spt} transaksi pelaporan SPT.");
        header('Location: ' . base_url('jenis_pajak/index.php'));
        exit;
    }

    $delete = $pdo->prepare("DELETE FROM jenis_pajak WHERE id = ?");
    $delete->execute([$id]);

    set_flash('success', "Jenis Pajak '{$jp['kode_pajak']} - {$jp['nama_pajak']}' berhasil dihapus.");
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus data: ' . $e->getMessage());
}

header('Location: ' . base_url('jenis_pajak/index.php'));
exit;
