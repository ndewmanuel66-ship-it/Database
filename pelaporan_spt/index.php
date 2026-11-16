<?php
// pelaporan_spt/index.php - Daftar Pelaporan SPT
require_once __DIR__ . '/../config/database.php';

$page_title = 'Transaksi Pelaporan SPT';

// Filter parameter
$search = trim($_GET['search'] ?? '');
$tahun = trim($_GET['tahun'] ?? '');
$status_lapor = trim($_GET['status_lapor'] ?? '');
$status_bayar = trim($_GET['status_bayar'] ?? '');

$query = "SELECT 
    s.*, 
    w.nama_wajib_pajak, 
    w.npwp, 
    w.jenis_wp,
    j.kode_pajak, 
    j.nama_pajak, 
    j.tarif_persen,
    p.nama_petugas,
    b.id AS id_pembayaran,
    b.kode_billing,
    b.status_bayar
    FROM pelaporan_spt s
    JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
    JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
    JOIN petugas_pajak p ON s.id_petugas = p.id
    LEFT JOIN pembayaran_pajak b ON b.id_pelaporan = s.id
    WHERE 1=1";

$params = [];

if ($search !== '') {
    $query .= " AND (s.nomor_spt LIKE ? OR w.nama_wajib_pajak LIKE ? OR w.npwp LIKE ? OR b.kode_billing LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($tahun !== '') {
    $query .= " AND s.tahun_pajak = ?";
    $params[] = (int)$tahun;
}

if ($status_lapor !== '' && in_array($status_lapor, ['TEPAT WAKTU', 'TERLAMBAT'])) {
    $query .= " AND s.status_lapor = ?";
    $params[] = $status_lapor;
}

if ($status_bayar !== '' && in_array($status_bayar, ['LUNAS', 'BELUM LUNAS'])) {
    $query .= " AND b.status_bayar = ?";
    $params[] = $status_bayar;
}

$query .= " ORDER BY s.id DESC";

$spt_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $spt_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memuat data SPT: ' . $e->getMessage());
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

    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Transaksi</li>
            <li class="breadcrumb-item active">Pelaporan SPT</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Transaksi Pelaporan SPT Pajak</h4>
      </div>
      <div>
        <a href="<?= base_url('pelaporan_spt/create.php'); ?>" class="btn btn-primary shadow-sm">
          <i class="bi bi-plus-lg me-1"></i> Buat Pelaporan SPT Baru
        </a>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="app-card mb-4">
      <div class="app-card-body p-3">
        <form method="GET" action="<?= base_url('pelaporan_spt/index.php'); ?>" class="row g-2 align-items-center">
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control" placeholder="Cari No. SPT, WP, NPWP, Billing..." value="<?= htmlspecialchars($search); ?>">
            </div>
          </div>
          <div class="col-6 col-md-2">
            <select name="tahun" class="form-select">
              <option value="">Semua Tahun</option>
              <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                <option value="<?= $y; ?>" <?= $tahun == $y ? 'selected' : ''; ?>><?= $y; ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select name="status_lapor" class="form-select">
              <option value="">Status Lapor</option>
              <option value="TEPAT WAKTU" <?= $status_lapor === 'TEPAT WAKTU' ? 'selected' : ''; ?>>Tepat Waktu</option>
              <option value="TERLAMBAT" <?= $status_lapor === 'TERLAMBAT' ? 'selected' : ''; ?>>Terlambat</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select name="status_bayar" class="form-select">
              <option value="">Status Bayar</option>
              <option value="LUNAS" <?= $status_bayar === 'LUNAS' ? 'selected' : ''; ?>>Lunas</option>
              <option value="BELUM LUNAS" <?= $status_bayar === 'BELUM LUNAS' ? 'selected' : ''; ?>>Belum Lunas</option>
            </select>
          </div>
          <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if ($search !== '' || $tahun !== '' || $status_lapor !== '' || $status_bayar !== ''): ?>
              <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-outline-secondary" title="Reset">
                <i class="bi bi-arrow-clockwise"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="app-card">
      <div class="app-card-header">
        <h2 class="app-card-title">
          <i class="bi bi-file-earmark-spreadsheet-fill text-primary"></i>
          Daftar Surat Pemberitahuan (SPT) - (<?= count($spt_list); ?> Data)
        </h2>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle">
          <thead>
            <tr>
              <th>No. SPT & Tgl Lapor</th>
              <th>Wajib Pajak</th>
              <th>Jenis Pajak</th>
              <th>Masa / Tahun</th>
              <th class="text-end">Dasar Pengenaan (DPP)</th>
              <th class="text-end">Pajak Terutang</th>
              <th>Status Lapor</th>
              <th>Status Bayar</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($spt_list)): ?>
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                  Data pelaporan SPT tidak ditemukan.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($spt_list as $row): ?>
                <tr>
                  <td>
                    <a href="<?= base_url('pelaporan_spt/detail.php?id=' . $row['id']); ?>" class="fw-bold text-primary text-decoration-none font-monospace">
                      <?= htmlspecialchars($row['nomor_spt']); ?>
                    </a>
                    <div class="small text-muted"><?= format_tanggal($row['tanggal_lapor']); ?></div>
                  </td>
                  <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_wajib_pajak']); ?></div>
                    <div class="small text-muted font-monospace"><?= htmlspecialchars($row['npwp']); ?></div>
                  </td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                      <?= htmlspecialchars($row['kode_pajak']); ?> (<?= (float)$row['tarif_persen']; ?>%)
                    </span>
                  </td>
                  <td class="small fw-semibold">
                    <?= htmlspecialchars($row['masa_pajak'] . ' ' . $row['tahun_pajak']); ?>
                  </td>
                  <td class="text-end text-muted small">
                    <?= format_rupiah($row['dasar_pengenaan_pajak']); ?>
                  </td>
                  <td class="text-end fw-bold text-dark">
                    <?= format_rupiah($row['pajak_terutang']); ?>
                  </td>
                  <td>
                    <?php if ($row['status_lapor'] === 'TEPAT WAKTU'): ?>
                      <span class="badge badge-tepat-waktu"><i class="bi bi-check2"></i> Tepat Waktu</span>
                    <?php else: ?>
                      <span class="badge badge-terlambat"><i class="bi bi-exclamation-triangle"></i> Terlambat</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($row['status_bayar'] === 'LUNAS'): ?>
                      <span class="badge badge-lunas"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                    <?php else: ?>
                      <span class="badge badge-belum-lunas"><i class="bi bi-clock-history"></i> Belum Lunas</span>
                      <?php if (!empty($row['id_pembayaran'])): ?>
                        <div class="mt-1">
                          <a href="<?= base_url('pembayaran/bayar.php?id=' . $row['id_pembayaran']); ?>" class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.72rem;">
                            <i class="bi bi-credit-card"></i> Bayar
                          </a>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= base_url('pelaporan_spt/detail.php?id=' . $row['id']); ?>" class="btn btn-outline-secondary" title="Detail / BPE">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= base_url('pelaporan_spt/edit.php?id=' . $row['id']); ?>" class="btn btn-outline-primary" title="Edit SPT">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a href="<?= base_url('pelaporan_spt/delete.php?id=' . $row['id']); ?>" 
                         class="btn btn-outline-danger btn-confirm-delete" 
                         data-confirm="Yakin ingin menghapus berkas SPT <?= htmlspecialchars($row['nomor_spt']); ?>? Kode billing terkait juga akan dihapus."
                         title="Hapus SPT">
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
