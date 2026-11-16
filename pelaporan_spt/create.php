<?php
// pelaporan_spt/create.php - Input Pelaporan SPT & Kalkulasi Otomatis
require_once __DIR__ . '/../config/database.php';

$page_title = 'Buat Pelaporan SPT Baru';

$wajib_pajak_list = [];
$jenis_pajak_list = [];
$petugas_list = [];
$nomor_spt_auto = '';

if ($pdo) {
    try {
        $wajib_pajak_list = $pdo->query("SELECT id, npwp, nama_wajib_pajak, jenis_wp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC")->fetchAll();
        $jenis_pajak_list = $pdo->query("SELECT id, kode_pajak, nama_pajak, tarif_persen FROM jenis_pajak ORDER BY kode_pajak ASC")->fetchAll();
        $petugas_list = $pdo->query("SELECT id, nip, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC")->fetchAll();
        $nomor_spt_auto = generate_nomor_spt($pdo);
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memuat referensi data: ' . $e->getMessage());
    }
}

$errors = [];
$id_wajib_pajak = $_POST['id_wajib_pajak'] ?? '';
$id_jenis_pajak = $_POST['id_jenis_pajak'] ?? '';
$id_petugas = $_POST['id_petugas'] ?? 1;
$masa_pajak = $_POST['masa_pajak'] ?? date('F');
$tahun_pajak = $_POST['tahun_pajak'] ?? date('Y');
$dasar_pengenaan_pajak = $_POST['dasar_pengenaan_pajak'] ?? '';
$tanggal_lapor = $_POST['tanggal_lapor'] ?? date('Y-m-d');
$status_lapor = $_POST['status_lapor'] ?? 'TEPAT WAKTU';

// Daftar Bulan Pajak
$daftar_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Tahunan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_spt = sanitize($_POST['nomor_spt'] ?? $nomor_spt_auto);
    $id_wajib_pajak = (int)($id_wajib_pajak);
    $id_jenis_pajak = (int)($id_jenis_pajak);
    $id_petugas = (int)($id_petugas);
    $masa_pajak = sanitize($masa_pajak);
    $tahun_pajak = (int)($tahun_pajak);
    $dasar_pengenaan_pajak = (float)str_replace(['.', ','], ['', '.'], (string)$dasar_pengenaan_pajak);
    $tanggal_lapor = sanitize($tanggal_lapor);
    $status_lapor = sanitize($status_lapor);

    // Validasi Formulir
    if (empty($nomor_spt)) {
        $errors[] = 'Nomor SPT wajib diisi.';
    }
    if (!$id_wajib_pajak) {
        $errors[] = 'Silakan pilih Wajib Pajak.';
    }
    if (!$id_jenis_pajak) {
        $errors[] = 'Silakan pilih Jenis Pajak.';
    }
    if (!$id_petugas) {
        $errors[] = 'Silakan pilih Petugas Pajak.';
    }
    if ($dasar_pengenaan_pajak <= 0) {
        $errors[] = 'Dasar Pengenaan Pajak (DPP) harus lebih besar dari 0.';
    }
    if (!in_array($status_lapor, ['TEPAT WAKTU', 'TERLAMBAT'])) {
        $errors[] = 'Pilihan Status Lapor tidak valid.';
    }

    if (empty($errors) && $pdo) {
        try {
            // Ambil data tarif resmi jenis pajak dari database
            $stmt_jp = $pdo->prepare("SELECT tarif_persen, nama_pajak, kode_pajak FROM jenis_pajak WHERE id = ?");
            $stmt_jp->execute([$id_jenis_pajak]);
            $jp = $stmt_jp->fetch();

            if (!$jp) {
                $errors[] = 'Jenis pajak tidak ditemukan di database.';
            } else {
                // KALKULASI OTOMATIS DI SERVER
                // Pajak Terutang = Dasar Pengenaan Pajak * (Tarif Persen / 100)
                $tarif_persen = (float)$jp['tarif_persen'];
                $pajak_terutang = round($dasar_pengenaan_pajak * ($tarif_persen / 100), 2);

                // Mulai Database Transaction untuk integritas relasi pelaporan dan billing
                $pdo->beginTransaction();

                // 1. Simpan Data Pelaporan SPT
                $stmt_insert = $pdo->prepare("INSERT INTO pelaporan_spt 
                    (nomor_spt, id_wajib_pajak, id_jenis_pajak, id_petugas, masa_pajak, tahun_pajak, dasar_pengenaan_pajak, pajak_terutang, tanggal_lapor, status_lapor) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->execute([
                    $nomor_spt,
                    $id_wajib_pajak,
                    $id_jenis_pajak,
                    $id_petugas,
                    $masa_pajak,
                    $tahun_pajak,
                    $dasar_pengenaan_pajak,
                    $pajak_terutang,
                    $tanggal_lapor,
                    $status_lapor
                ]);

                $id_pelaporan = $pdo->lastInsertId();

                // 2. Buat Kode Billing Pembayaran Pajak Otomatis (Status: BELUM LUNAS)
                $kode_billing = generate_kode_billing($pdo);
                $stmt_billing = $pdo->prepare("INSERT INTO pembayaran_pajak 
                    (kode_billing, id_pelaporan, jumlah_bayar, metode_bayar, tanggal_bayar, status_bayar) 
                    VALUES (?, ?, ?, NULL, NULL, 'BELUM LUNAS')");
                $stmt_billing->execute([
                    $kode_billing,
                    $id_pelaporan,
                    $pajak_terutang
                ]);

                $pdo->commit();

                set_flash('success', "Pelaporan SPT '{$nomor_spt}' berhasil disimpan! Kode Billing diterbitkan: {$kode_billing}");
                header('Location: ' . base_url('pelaporan_spt/detail.php?id=' . $id_pelaporan));
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Gagal menyimpan transaksi SPT: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="content-body">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('pelaporan_spt/index.php'); ?>">Pelaporan SPT</a></li>
            <li class="breadcrumb-item active">Input Baru</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Formulir Pelaporan SPT & Perhitungan Pajak</h4>
      </div>
      <div>
        <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger shadow-sm alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Pelaporan Gagal:</h6>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="formPelaporanSPT" novalidate>
      <div class="row g-4">
        <!-- Kolom Kiri: Form Input -->
        <div class="col-12 col-lg-8">
          <div class="app-card">
            <div class="app-card-header">
              <h2 class="app-card-title">
                <i class="bi bi-file-earmark-plus-fill text-primary"></i>
                Data Surat Pemberitahuan (SPT)
              </h2>
              <span class="badge bg-light text-dark border">
                No. Ref: <strong class="text-primary font-monospace"><?= htmlspecialchars($nomor_spt_auto); ?></strong>
              </span>
            </div>
            <div class="app-card-body">
              <div class="row g-3">
                <!-- Nomor SPT -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor SPT <span class="text-danger">*</span></label>
                  <input type="text" name="nomor_spt" class="form-control font-monospace fw-bold" value="<?= htmlspecialchars($nomor_spt_auto); ?>" required>
                  <div class="form-text">Dihasilkan otomatis berurutan tahunan.</div>
                </div>

                <!-- Petugas Pajak -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Petugas Penerima (AR / Fungsional) <span class="text-danger">*</span></label>
                  <select name="id_petugas" class="form-select" required>
                    <?php foreach ($petugas_list as $pet): ?>
                      <option value="<?= $pet['id']; ?>" <?= $id_petugas == $pet['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($pet['nama_petugas']); ?> (<?= htmlspecialchars($pet['jabatan']); ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Wajib Pajak -->
                <div class="col-12">
                  <label class="form-label fw-semibold">Wajib Pajak <span class="text-danger">*</span></label>
                  <select name="id_wajib_pajak" class="form-select" required>
                    <option value="">-- Pilih Wajib Pajak Terdaftar --</option>
                    <?php foreach ($wajib_pajak_list as $wp): ?>
                      <option value="<?= $wp['id']; ?>" <?= $id_wajib_pajak == $wp['id'] ? 'selected' : ''; ?>>
                        [<?= $wp['jenis_wp']; ?>] <?= htmlspecialchars($wp['nama_wajib_pajak']); ?> - NPWP: <?= htmlspecialchars($wp['npwp']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Jenis Pajak (Dengan Atribut Data-Tarif untuk Auto Calculation) -->
                <div class="col-12 col-md-8">
                  <label class="form-label fw-semibold">Jenis Pajak Yang Dilaporkan <span class="text-danger">*</span></label>
                  <select name="id_jenis_pajak" id="id_jenis_pajak" class="form-select" required>
                    <option value="" data-tarif="0">-- Pilih Jenis Pajak --</option>
                    <?php foreach ($jenis_pajak_list as $jp): ?>
                      <option value="<?= $jp['id']; ?>" data-tarif="<?= (float)$jp['tarif_persen']; ?>" <?= $id_jenis_pajak == $jp['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($jp['kode_pajak']); ?> - <?= htmlspecialchars($jp['nama_pajak']); ?> (Tarif: <?= (float)$jp['tarif_persen']; ?>%)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Tampilan Tarif Persen Terpilih -->
                <div class="col-12 col-md-4">
                  <label class="form-label fw-semibold">Tarif Pajak</label>
                  <input type="text" id="tarif_persen_display" class="form-control text-center fw-bold bg-light" value="0.00 %" readonly>
                  <input type="hidden" id="tarif_persen" value="0">
                </div>

                <!-- Masa & Tahun Pajak -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Masa Pajak <span class="text-danger">*</span></label>
                  <select name="masa_pajak" class="form-select" required>
                    <?php foreach ($daftar_bulan as $bln): ?>
                      <option value="<?= $bln; ?>" <?= $masa_pajak == $bln ? 'selected' : ''; ?>><?= $bln; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Tahun Pajak <span class="text-danger">*</span></label>
                  <input type="number" name="tahun_pajak" class="form-control" value="<?= htmlspecialchars($tahun_pajak); ?>" min="2000" max="2099" required>
                </div>

                <!-- DASAR PENGENAAN PAJAK (DPP) - Memicu Auto Calculate -->
                <div class="col-12">
                  <div class="p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold text-dark">
                      <i class="bi bi-calculator text-primary me-1"></i>
                      Dasar Pengenaan Pajak (DPP) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg mb-2">
                      <span class="input-group-text fw-bold bg-white">Rp</span>
                      <input type="number" step="1000" name="dasar_pengenaan_pajak" id="dasar_pengenaan_pajak" class="form-control fw-bold fs-4" placeholder="0" value="<?= htmlspecialchars($dasar_pengenaan_pajak); ?>" required>
                    </div>
                    <div class="form-text">Masukkan nilai penghasilan bruto / transaksi kena pajak tanpa tanda titik atau koma.</div>
                  </div>
                </div>

                <!-- Tanggal Lapor & Status Lapor -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Tanggal Pelaporan <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_lapor" class="form-control" value="<?= htmlspecialchars($tanggal_lapor); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Status Kepatuhan Pelaporan <span class="text-danger">*</span></label>
                  <select name="status_lapor" class="form-select" required>
                    <option value="TEPAT WAKTU" <?= $status_lapor === 'TEPAT WAKTU' ? 'selected' : ''; ?>>TEPAT WAKTU (Sebelum Batas Akhir)</option>
                    <option value="TERLAMBAT" <?= $status_lapor === 'TERLAMBAT' ? 'selected' : ''; ?>>TERLAMBAT (Melewati Batas Akhir)</option>
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-check-circle-fill me-1"></i> Terbitkan SPT & Kode Billing
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Live Calculation Box -->
        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm text-white mb-4" style="background: linear-gradient(135deg, #1e3a8a, #0f172a); border-radius: 16px;">
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="badge bg-white text-primary fw-bold text-uppercase px-2 py-1">Kalkulasi Otomatis</span>
                <i class="bi bi-cpu fs-4 text-warning"></i>
              </div>
              
              <div class="text-white-50 small mb-1">Rumus Perhitungan:</div>
              <div class="fw-semibold text-white small font-monospace mb-3 pb-2 border-bottom border-white-50">
                Pajak Terutang = DPP &times; Tarif (%)
              </div>

              <div class="text-white-50 small">Estimasi Pajak Terutang:</div>
              <div class="display-6 fw-bold text-warning my-2" id="preview_pajak_terutang">
                Rp 0
              </div>
              
              <div class="mb-3">
                <input type="hidden" name="pajak_terutang" id="pajak_terutang" value="0">
              </div>

              <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); font-size: 0.8rem;">
                <i class="bi bi-shield-check text-success me-1"></i>
                Setelah disimpan, sistem akan secara otomatis menerbitkan <strong>Kode Billing 15 Digit</strong> untuk pembayaran pajak.
              </div>
            </div>
          </div>

          <!-- Batas Waktu Pelaporan -->
          <div class="app-card">
            <div class="app-card-header bg-light">
              <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event me-1"></i> Batas Waktu Pelaporan DJP</h6>
            </div>
            <div class="app-card-body small text-muted">
              <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                <li><strong>SPT Masa PPh 21:</strong> Tgl 20 bulan berikutnya.</li>
                <li><strong>SPT Masa PPN:</strong> Akhir bulan berikutnya.</li>
                <li><strong>SPT Tahunan OP:</strong> 31 Maret tahun berikutnya.</li>
                <li><strong>SPT Tahunan Badan:</strong> 30 April tahun berikutnya.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </form>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
