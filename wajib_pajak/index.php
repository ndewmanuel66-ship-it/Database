<?php
// wajib_pajak/index.php - Read / List Wajib Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Master Data Wajib Pajak';

// Filter & Pencarian
$search = trim($_GET['search'] ?? '');
$jenis_filter = trim($_GET['jenis_wp'] ?? '');

$query = "SELECT * FROM wajib_pajak WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (nama_wajib_pajak LIKE ? OR npwp LIKE ? OR nik LIKE ? OR email LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($jenis_filter !== '' && in_array($jenis_filter, ['OP', 'Badan'])) {
    $query .= " AND jenis_wp = ?";
    $params[] = $jenis_filter;
}

$query .= " ORDER BY id DESC";

$wajib_pajak_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $wajib_pajak_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memuat data: ' . $e->getMessage());
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
            <li class="breadcrumb-item active">Wajib Pajak</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Daftar Wajib Pajak</h4>
      </div>
      <div>
        <a href="<?= base_url('wajib_pajak/create.php'); ?>" class="btn btn-primary shadow-sm">
          <i class="bi bi-person-plus-fill me-1"></i> Tambah Wajib Pajak
        </a>
      </div>
    </div>

    <!-- Filter & Pencarian Card -->
    <div class="app-card mb-4">
      <div class="app-card-body p-3">
        <form method="GET" action="<?= base_url('wajib_pajak/index.php'); ?>" class="row g-2 align-items-center">
          <div class="col-12 col-md-6 col-lg-5">
            <div class="input-group">
              <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control" placeholder="Cari nama, NPWP, NIK, atau email..." value="<?= htmlspecialchars($search); ?>">
            </div>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <select name="jenis_wp" class="form-select">
              <option value="">Semua Jenis WP</option>
              <option value="OP" <?= $jenis_filter === 'OP' ? 'selected' : ''; ?>>Orang Pribadi (OP)</option>
              <option value="Badan" <?= $jenis_filter === 'Badan' ? 'selected' : ''; ?>>Badan Usaha (PT / CV)</option>
            </select>
          </div>
          <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if ($search !== '' || $jenis_filter !== ''): ?>
              <a href="<?= base_url('wajib_pajak/index.php'); ?>" class="btn btn-outline-secondary" title="Reset Filter">
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
          <i class="bi bi-people-fill text-primary"></i>
          Data Wajib Pajak Terdaftar (<?= count($wajib_pajak_list); ?> Data)
        </h2>
      </div>
      <div class="table-responsive">
        <table class="table table-custom align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>Identitas Wajib Pajak</th>
              <th>Jenis WP</th>
              <th>Kontak</th>
              <th>Alamat</th>
              <th>Tgl. Daftar</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($wajib_pajak_list)): ?>
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="bi bi-person-x fs-2 d-block mb-1"></i>
                  Data Wajib Pajak tidak ditemukan.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($wajib_pajak_list as $row): ?>
                <tr>
                  <td class="text-muted small"><?= $no++; ?></td>
                  <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_wajib_pajak']); ?></div>
                    <div class="small text-muted">
                      NPWP: <code class="text-primary"><?= htmlspecialchars($row['npwp']); ?></code>
                    </div>
                    <div class="small text-muted">
                      NIK: <span class="font-monospace"><?= htmlspecialchars($row['nik']); ?></span>
                    </div>
                  </td>
                  <td>
                    <?php if ($row['jenis_wp'] === 'OP'): ?>
                      <span class="badge badge-op"><i class="bi bi-person"></i> Orang Pribadi</span>
                    <?php else: ?>
                      <span class="badge badge-badan"><i class="bi bi-building"></i> Badan Usaha</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="small"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($row['no_telepon']); ?></div>
                    <div class="small"><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($row['email']); ?></div>
                  </td>
                  <td class="small text-muted" style="max-width: 250px;">
                    <?= htmlspecialchars($row['alamat']); ?>
                  </td>
                  <td class="small">
                    <?= format_tanggal($row['tanggal_daftar']); ?>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= base_url('wajib_pajak/edit.php?id=' . $row['id']); ?>" class="btn btn-outline-primary" title="Edit Data">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a href="<?= base_url('wajib_pajak/delete.php?id=' . $row['id']); ?>" 
                         class="btn btn-outline-danger btn-confirm-delete" 
                         data-confirm="Yakin ingin menghapus Wajib Pajak <?= htmlspecialchars($row['nama_wajib_pajak']); ?>?"
                         title="Hapus Data">
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
