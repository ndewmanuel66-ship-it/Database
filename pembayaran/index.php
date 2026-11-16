<?php
// pembayaran/index.php - Daftar Kode Billing & Pembayaran Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Pembayaran Pajak & Billing';

// Filter & Pencarian
$search = trim($_GET['search'] ?? '');
$status_bayar = trim($_GET['status_bayar'] ?? '');

$query = "SELECT 
    b.*, 
    s.nomor_spt, 
    s.masa_pajak, 
    s.tahun_pajak, 
    s.pajak_terutang,
    w.nama_wajib_pajak, 
    w.npwp, 
    w.jenis_wp,
    j.kode_pajak, 
    j.nama_pajak
    FROM pembayaran_pajak b
    JOIN pelaporan_spt s ON b.id_pelaporan = s.id
    JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
    JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
    WHERE 1=1";

$params = [];

if ($search !== '') {
    $query .= " AND (b.kode_billing LIKE ? OR s.nomor_spt LIKE ? OR w.nama_wajib_pajak LIKE ? OR w.npwp LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($status_bayar !== '' && in_array($status_bayar, ['LUNAS', 'BELUM LUNAS'])) {
    $query .= " AND b.status_bayar = ?";
    $params[] = $status_bayar;
}

$query .= " ORDER BY b.id DESC";

$pembayaran_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $pembayaran_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memuat data pembayaran: ' . $e->getMessage());
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
            <li class="breadcrumb-item active">Pembayaran & Billing</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Daftar Kode Billing & Setoran Kas Negara</h4>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="app-card mb-4">
      <div class="app-card-body p-3">
        <form method="GET" action="<?= base_url('pembayaran/index.php'); ?>" class="row g-2 align-items-center">
          <div class="col-12 col-md-6 col-lg-5">
            <div class="input-group">
              <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control" placeholder="Cari Kode Billing, No. SPT, Wajib Pajak..." value="<?= htmlspecialchars($search); ?>">
            </div>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <select name="status_bayar" class="form-select">
              <option value="">Semua Status Bayar</option>
              <option value="LUNAS" <?= $status_bayar === 'LUNAS' ? 'selected' : ''; ?>>LUNAS</option>
              <option value="BELUM LUNAS" <?= $status_bayar === 'BELUM LUNAS' ? 'selected' : ''; ?>>BELUM LUNAS</option>
            </select>
          </div>
          <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if ($search !== '' || $status_bayar !== ''): ?>
              <a href="<?= base_url('pembayaran/index.php'); ?>" class="btn btn-outline-secondary" title="Reset">
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
          <i class="bi bi-wallet2 text-primary"></i>
          Billing & Setoran Pajak (<?= count($pembayaran_list); ?> Data)
        </h2>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle">
          <thead>
            <tr>
              <th>Kode Billing</th>
              <th>No. SPT Terkait</th>
              <th>Wajib Pajak</th>
              <th>Jenis Pajak</th>
              <th class="text-end">Jumlah Bayar</th>
              <th>Metode & Tgl. Bayar</th>
              <th>Status</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pembayaran_list)): ?>
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="bi bi-receipt-cutoff fs-2 d-block mb-1"></i>
                  Data billing atau pembayaran tidak ditemukan.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pembayaran_list as $row): ?>
                <tr>
                  <td>
                    <span class="font-monospace fw-bold text-dark fs-6">
                      <?= htmlspecialchars($row['kode_billing']); ?>
                    </span>
                    <div class="small text-muted">15 Digit DJP</div>
                  </td>
                  <td>
                    <a href="<?= base_url('pelaporan_spt/detail.php?id=' . $row['id_pelaporan']); ?>" class="fw-semibold text-primary text-decoration-none font-monospace">
                      <?= htmlspecialchars($row['nomor_spt']); ?>
                    </a>
                    <div class="small text-muted"><?= htmlspecialchars($row['masa_pajak'] . ' ' . $row['tahun_pajak']); ?></div>
                  </td>
                  <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_wajib_pajak']); ?></div>
                    <div class="small text-muted font-monospace"><?= htmlspecialchars($row['npwp']); ?></div>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary border">
                      <?= htmlspecialchars($row['kode_pajak']); ?>
                    </span>
                  </td>
                  <td class="text-end fw-bold fs-6 <?= $row['status_bayar'] === 'LUNAS' ? 'text-success' : 'text-danger'; ?>">
                    <?= format_rupiah($row['jumlah_bayar']); ?>
                  </td>
                  <td>
                    <?php if ($row['status_bayar'] === 'LUNAS'): ?>
                      <div class="fw-semibold text-dark small"><i class="bi bi-bank me-1 text-primary"></i><?= htmlspecialchars($row['metode_bayar']); ?></div>
                      <div class="small text-muted"><?= format_tanggal($row['tanggal_bayar'], true); ?></div>
                    <?php else: ?>
                      <span class="text-muted small fst-italic">Menunggu Pembayaran</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($row['status_bayar'] === 'LUNAS'): ?>
                      <span class="badge badge-lunas"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                    <?php else: ?>
                      <span class="badge badge-belum-lunas"><i class="bi bi-clock-history"></i> Belum Lunas</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($row['status_bayar'] === 'LUNAS'): ?>
                      <a href="<?= base_url('pembayaran/cetak_bukti.php?id=' . $row['id']); ?>" class="btn btn-sm btn-outline-dark" title="Cetak Bukti Penerimaan Negara (BPN)">
                        <i class="bi bi-printer me-1"></i> Cetak BPN
                      </a>
                    <?php else: ?>
                      <a href="<?= base_url('pembayaran/bayar.php?id=' . $row['id']); ?>" class="btn btn-sm btn-success" title="Proses Bayar Pajak">
                        <i class="bi bi-credit-card me-1"></i> Bayar
                      </a>
                    <?php endif; ?>
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
