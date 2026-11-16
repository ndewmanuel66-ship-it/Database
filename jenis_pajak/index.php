<?php
// jenis_pajak/index.php - Read / List Jenis Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Master Data Jenis Pajak';

$jenis_pajak_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT j.*, COUNT(s.id) AS total_spt 
            FROM jenis_pajak j 
            LEFT JOIN pelaporan_spt s ON j.id = s.id_jenis_pajak 
            GROUP BY j.id 
            ORDER BY j.id ASC");
        $jenis_pajak_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memuat data jenis pajak: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="content-body">
    <!-- Flash Notification -->
    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i><?= $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Breadcrumb & Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Master Data</li>
            <li class="breadcrumb-item active">Jenis Pajak</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Master Jenis Pajak & Tarif</h4>
      </div>
      <div>
        <a href="<?= base_url('jenis_pajak/create.php'); ?>" class="btn btn-primary shadow-sm">
          <i class="bi bi-plus-circle me-1"></i> Tambah Jenis Pajak
        </a>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="app-card">
      <div class="app-card-header">
        <h2 class="app-card-title">
          <i class="bi bi-percent text-primary"></i>
          Daftar Jenis Pajak & Regulasi Tarif
        </h2>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th style="width: 140px;">Kode Pajak</th>
              <th>Nama Pajak</th>
              <th class="text-center" style="width: 130px;">Tarif (%)</th>
              <th>Keterangan / Dasar Regulasi</th>
              <th class="text-center" style="width: 120px;">Penggunaan</th>
              <th class="text-center" style="width: 120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($jenis_pajak_list)): ?>
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                  Data jenis pajak masih kosong.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($jenis_pajak_list as $row): ?>
                <tr>
                  <td class="text-muted small"><?= $no++; ?></td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1 fs-6">
                      <?= htmlspecialchars($row['kode_pajak']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_pajak']); ?></div>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-success fs-6 fw-bold">
                      <?= (float)$row['tarif_persen']; ?> %
                    </span>
                  </td>
                  <td class="small text-muted" style="max-width: 320px;">
                    <?= htmlspecialchars($row['keterangan'] ?? '-'); ?>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-light text-secondary border">
                      <?= $row['total_spt']; ?> Pelaporan
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= base_url('jenis_pajak/edit.php?id=' . $row['id']); ?>" class="btn btn-outline-primary" title="Edit Jenis Pajak">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a href="<?= base_url('jenis_pajak/delete.php?id=' . $row['id']); ?>" 
                         class="btn btn-outline-danger btn-confirm-delete" 
                         data-confirm="Yakin ingin menghapus jenis pajak <?= htmlspecialchars($row['kode_pajak']); ?> - <?= htmlspecialchars($row['nama_pajak']); ?>?"
                         title="Hapus Jenis Pajak">
                        <i class="bi bi-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
