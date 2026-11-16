<?php
// index.php - Dashboard
require_once __DIR__ . '/config/database.php';

$page_title = 'Dashboard Perpajakan';

// Variabel default untuk dashboard jika database belum connect
$total_wp = 0;
$total_wp_op = 0;
$total_wp_badan = 0;
$total_penerimaan = 0;
$total_spt = 0;
$total_spt_tepat = 0;
$total_spt_terlambat = 0;
$total_tunggakan = 0;
$total_tunggakan_nominal = 0;
$spt_terbaru = [];
$jenis_pajak_list = [];

if ($pdo) {
    try {
        // 1. Total Wajib Pajak & Breakdown
        $stmt = $pdo->query("SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN jenis_wp = 'OP' THEN 1 ELSE 0 END) AS total_op,
            SUM(CASE WHEN jenis_wp = 'Badan' THEN 1 ELSE 0 END) AS total_badan
            FROM wajib_pajak");
        $wp_data = $stmt->fetch();
        $total_wp = $wp_data['total'] ?? 0;
        $total_wp_op = $wp_data['total_op'] ?? 0;
        $total_wp_badan = $wp_data['total_badan'] ?? 0;

        // 2. Total Penerimaan Pajak (Status LUNAS)
        $stmt = $pdo->query("SELECT SUM(jumlah_bayar) AS total_bayar FROM pembayaran_pajak WHERE status_bayar = 'LUNAS'");
        $total_penerimaan = $stmt->fetch()['total_bayar'] ?? 0;

        // 3. Total Pelaporan SPT & Breakdown Status
        $stmt = $pdo->query("SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status_lapor = 'TEPAT WAKTU' THEN 1 ELSE 0 END) AS tepat_waktu,
            SUM(CASE WHEN status_lapor = 'TERLAMBAT' THEN 1 ELSE 0 END) AS terlambat
            FROM pelaporan_spt");
        $spt_stat = $stmt->fetch();
        $total_spt = $spt_stat['total'] ?? 0;
        $total_spt_tepat = $spt_stat['tepat_waktu'] ?? 0;
        $total_spt_terlambat = $spt_stat['terlambat'] ?? 0;

        // 4. Total Tunggakan Pajak (Status BELUM LUNAS)
        $stmt = $pdo->query("SELECT 
            COUNT(*) AS count_tunggakan,
            SUM(jumlah_bayar) AS nominal_tunggakan 
            FROM pembayaran_pajak WHERE status_bayar = 'BELUM LUNAS'");
        $tunggakan_data = $stmt->fetch();
        $total_tunggakan = $tunggakan_data['count_tunggakan'] ?? 0;
        $total_tunggakan_nominal = $tunggakan_data['nominal_tunggakan'] ?? 0;

        // 5. Daftar 8 SPT Terbaru
        $stmt = $pdo->query("SELECT 
            s.*, 
            w.nama_wajib_pajak, 
            w.npwp, 
            w.jenis_wp,
            j.nama_pajak, 
            j.kode_pajak,
            p.status_bayar,
            p.kode_billing,
            p.id AS id_pembayaran
            FROM pelaporan_spt s
            JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
            JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
            LEFT JOIN pembayaran_pajak p ON p.id_pelaporan = s.id
            ORDER BY s.id DESC
            LIMIT 8");
        $spt_terbaru = $stmt->fetchAll();

        // 6. Ringkasan Jenis Pajak
        $stmt = $pdo->query("SELECT j.*, COUNT(s.id) AS total_pelaporan, COALESCE(SUM(s.pajak_terutang), 0) AS total_potensi 
            FROM jenis_pajak j 
            LEFT JOIN pelaporan_spt s ON j.id = s.id_jenis_pajak 
            GROUP BY j.id 
            ORDER BY j.id ASC");
        $jenis_pajak_list = $stmt->fetchAll();

    } catch (PDOException $e) {
        $db_error_message = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <div class="content-body">
    <!-- Flash Notification -->
    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i><?= $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (!$pdo): ?>
      <!-- Alert jika database MySQL belum diimport -->
      <div class="alert alert-warning border-warning shadow-sm mb-4">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
          <div>
            <h5 class="alert-heading fw-bold mb-1">Database Belum Terhubung atau Belum Dibuat</h5>
            <p class="mb-2">Koneksi ke database <code><?= DB_NAME; ?></code> belum berhasil. Pastikan MySQL di Laragon/XAMPP sudah aktif dan skrip database sudah diimpor.</p>
            <div class="d-flex gap-2">
              <a href="database_info.php" class="btn btn-sm btn-dark"><i class="bi bi-filetype-sql me-1"></i>Lihat Skrip SQL & Panduan Import</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Header Page & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold text-dark mb-1">Ringkasan Sistem Perpajakan</h4>
        <p class="text-muted mb-0 small">Monitoring real-time wajib pajak, pelaporan SPT masa/tahunan, dan realisasi penerimaan negara.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('wajib_pajak/create.php'); ?>" class="btn btn-outline-primary btn-sm px-3 shadow-sm">
          <i class="bi bi-person-plus-fill me-1"></i> Registrasi WP
        </a>
        <a href="<?= base_url('pelaporan_spt/create.php'); ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
          <i class="bi bi-plus-lg me-1"></i> Buat SPT Baru
        </a>
      </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="row g-3 mb-4">
      <!-- Total Wajib Pajak -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Wajib Pajak Terdaftar</div>
              <div class="stat-value"><?= number_format($total_wp, 0, ',', '.'); ?></div>
              <div class="small mt-2 text-muted">
                <span class="badge badge-op me-1">OP: <?= $total_wp_op; ?></span>
                <span class="badge badge-badan">Badan: <?= $total_wp_badan; ?></span>
              </div>
            </div>
            <div class="stat-icon-box stat-icon-blue">
              <i class="bi bi-people-fill"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Penerimaan Pajak -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Realisasi Penerimaan</div>
              <div class="stat-value text-success"><?= format_rupiah($total_penerimaan); ?></div>
              <div class="small mt-2 text-muted">
                <i class="bi bi-check-circle-fill text-success me-1"></i>Total Kas Masuk (Lunas)
              </div>
            </div>
            <div class="stat-icon-box stat-icon-green">
              <i class="bi bi-cash-stack"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Pelaporan SPT -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Total SPT Dilaporkan</div>
              <div class="stat-value"><?= number_format($total_spt, 0, ',', '.'); ?></div>
              <div class="small mt-2 text-muted">
                <span class="badge badge-tepat-waktu me-1">Tepat: <?= $total_spt_tepat; ?></span>
                <span class="badge badge-terlambat">Telat: <?= $total_spt_terlambat; ?></span>
              </div>
            </div>
            <div class="stat-icon-box stat-icon-purple">
              <i class="bi bi-file-earmark-check-fill"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Tunggakan Pajak -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="stat-label">Pajak Belum Lunas</div>
              <div class="stat-value text-danger"><?= format_rupiah($total_tunggakan_nominal); ?></div>
              <div class="small mt-2 text-muted">
                <span class="badge badge-belum-lunas"><?= $total_tunggakan; ?> Tagihan Menunggu</span>
              </div>
            </div>
            <div class="stat-icon-box stat-icon-amber">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Row: Recent SPT & Jenis Pajak List -->
    <div class="row g-4">
      <!-- Tabel Daftar SPT Terbaru (8 Item) -->
      <div class="col-12 col-xl-8">
        <div class="app-card">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-clock-history text-primary"></i>
              Daftar Pelaporan SPT Terbaru
            </h2>
            <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-sm btn-link text-decoration-none">
              Lihat Semua SPT <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="table-responsive">
            <table class="table table-custom align-middle">
              <thead>
                <tr>
                  <th>No. SPT</th>
                  <th>Wajib Pajak</th>
                  <th>Jenis Pajak</th>
                  <th>Pajak Terutang</th>
                  <th>Status Lapor</th>
                  <th>Status Bayar</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($spt_terbaru)): ?>
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                      Belum ada data pelaporan SPT.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($spt_terbaru as $row): ?>
                    <tr>
                      <td class="fw-semibold text-primary">
                        <a href="<?= base_url('pelaporan_spt/detail.php?id=' . $row['id']); ?>" class="text-decoration-none">
                          <?= htmlspecialchars($row['nomor_spt']); ?>
                        </a>
                        <div class="small text-muted"><?= format_tanggal($row['tanggal_lapor']); ?></div>
                      </td>
                      <td>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_wajib_pajak']); ?></div>
                        <div class="small text-muted font-monospace"><?= htmlspecialchars($row['npwp']); ?></div>
                      </td>
                      <td>
                        <span class="badge bg-secondary-subtle text-secondary border">
                          <?= htmlspecialchars($row['kode_pajak']); ?>
                        </span>
                        <div class="small text-muted"><?= htmlspecialchars($row['masa_pajak'] . ' ' . $row['tahun_pajak']); ?></div>
                      </td>
                      <td class="fw-bold text-dark">
                        <?= format_rupiah($row['pajak_terutang']); ?>
                      </td>
                      <td>
                        <?php if ($row['status_lapor'] === 'TEPAT WAKTU'): ?>
                          <span class="badge badge-tepat-waktu"><i class="bi bi-check2"></i> Tepat Waktu</span>
                        <?php else: ?>
                          <span class="badge badge-terlambat"><i class="bi bi-exclamation-circle"></i> Terlambat</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($row['status_bayar'] === 'LUNAS'): ?>
                          <span class="badge badge-lunas"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                        <?php else: ?>
                          <span class="badge badge-belum-lunas"><i class="bi bi-hourglass-split"></i> Belum Lunas</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="btn-group btn-group-sm">
                          <a href="<?= base_url('pelaporan_spt/detail.php?id=' . $row['id']); ?>" class="btn btn-outline-secondary" title="Detail SPT">
                            <i class="bi bi-eye"></i>
                          </a>
                          <?php if ($row['status_bayar'] !== 'LUNAS' && !empty($row['id_pembayaran'])): ?>
                            <a href="<?= base_url('pembayaran/bayar.php?id=' . $row['id_pembayaran']); ?>" class="btn btn-outline-success" title="Bayar Sekarang">
                              <i class="bi bi-credit-card"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Side Column: Master Jenis Pajak & Quick Info -->
      <div class="col-12 col-xl-4">
        <!-- Jenis Pajak Reference Card -->
        <div class="app-card mb-4">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-percent text-success"></i>
              Tarif Pajak Berlaku
            </h2>
            <a href="<?= base_url('jenis_pajak/index.php'); ?>" class="btn btn-sm btn-link text-decoration-none">Kelola</a>
          </div>
          <div class="app-card-body p-0">
            <ul class="list-group list-group-flush small">
              <?php if (empty($jenis_pajak_list)): ?>
                <li class="list-group-item text-center text-muted py-3">Tidak ada data jenis pajak.</li>
              <?php else: ?>
                <?php foreach ($jenis_pajak_list as $jp): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <div>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($jp['nama_pajak']); ?></div>
                      <div class="text-muted" style="font-size: 0.75rem;">Kode: <code><?= htmlspecialchars($jp['kode_pajak']); ?></code></div>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-primary fs-6"><?= (float)$jp['tarif_persen']; ?>%</span>
                      <div class="text-muted" style="font-size: 0.7rem;"><?= $jp['total_pelaporan']; ?> SPT</div>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>

        <!-- Quick Help Card -->
        <div class="card border-0 bg-primary text-white p-3 rounded-4 shadow-sm">
          <div class="d-flex align-items-start gap-3">
            <div class="fs-1"><i class="bi bi-shield-check"></i></div>
            <div>
              <h6 class="fw-bold mb-1">Perhitungan Pajak Akurat</h6>
              <p class="small text-white-50 mb-2">Aplikasi secara otomatis menghitung Pajak Terutang berdasarkan Dasar Pengenaan Pajak (DPP) dikalikan tarif resmi.</p>
              <a href="<?= base_url('pelaporan_spt/create.php'); ?>" class="btn btn-light btn-sm text-primary fw-semibold">
                Simulasi Hitung SPT &rarr;
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
