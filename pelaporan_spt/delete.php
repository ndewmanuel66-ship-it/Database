<?php
// pelaporan_spt/delete.php - Hapus Pelaporan SPT
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID SPT tidak valid.');
    header('Location: ' . base_url('pelaporan_spt/index.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT s.nomor_spt, b.status_bayar 
        FROM pelaporan_spt s 
        LEFT JOIN pembayaran_pajak b ON b.id_pelaporan = s.id 
        WHERE s.id = ?");
    $stmt->execute([$id]);
    $spt = $stmt->fetch();

    if (!$spt) {
        set_flash('warning', 'Data SPT tidak ditemukan.');
        header('Location: ' . base_url('pelaporan_spt/index.php'));
        exit;
    }

    // Integritas Keuangan: Jangan hapus SPT yang sudah disetor ke Kas Negara (LUNAS)
    if ($spt['status_bayar'] === 'LUNAS') {
        set_flash('danger', "SPT '{$spt['nomor_spt']}' tidak dapat dihapus karena pajaknya telah LUNAS disetorkan ke Kas Negara (Audit Trail Keuangan).");
        header('Location: ' . base_url('pelaporan_spt/index.php'));
        exit;
    }

    $delete = $pdo->prepare("DELETE FROM pelaporan_spt WHERE id = ?");
    $delete->execute([$id]);

    set_flash('success', "Pelaporan SPT '{$spt['nomor_spt']}' dan kode billing terkait berhasil dihapus.");
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus SPT: ' . $e->getMessage());
}

header('Location: ' . base_url('pelaporan_spt/index.php'));
exit;
