<?php
// pelaporan_spt/edit.php - Ubah Pelaporan SPT
require_once __DIR__ . '/../config/database.php';

$page_title = 'Edit Pelaporan SPT';
$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID SPT tidak valid.');
    header('Location: ' . base_url('pelaporan_spt/index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT s.*, b.status_bayar, b.id AS id_pembayaran 
    FROM pelaporan_spt s 
    LEFT JOIN pembayaran_pajak b ON b.id_pelaporan = s.id 
    WHERE s.id = ?");
$stmt->execute([$id]);
$spt = $stmt->fetch();

if (!$spt) {
    set_flash('danger', 'Data SPT tidak ditemukan.');
    header('Location: ' . base_url('pelaporan_spt/index.php'));
    exit;
}

$wajib_pajak_list = $pdo->query("SELECT id, npwp, nama_wajib_pajak, jenis_wp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC")->fetchAll();
$jenis_pajak_list = $pdo->query("SELECT id, kode_pajak, nama_pajak, tarif_persen FROM jenis_pajak ORDER BY kode_pajak ASC")->fetchAll();
$petugas_list = $pdo->query("SELECT id, nip, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC")->fetchAll();
$daftar_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Tahunan'];

$errors = [];
$id_wajib_pajak = $spt['id_wajib_pajak'];
$id_jenis_pajak = $spt['id_jenis_pajak'];
$id_petugas = $spt['id_petugas'];
$masa_pajak = $spt['masa_pajak'];
$tahun_pajak = $spt['tahun_pajak'];
$dasar_pengenaan_pajak = $spt['dasar_pengenaan_pajak'];
$tanggal_lapor = $spt['tanggal_lapor'];
$status_lapor = $spt['status_lapor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_wajib_pajak = (int)($_POST['id_wajib_pajak'] ?? 0);
    $id_jenis_pajak = (int)($_POST['id_jenis_pajak'] ?? 0);
    $id_petugas = (int)($_POST['id_petugas'] ?? 0);
    $masa_pajak = sanitize($_POST['masa_pajak'] ?? '');
    $tahun_pajak = (int)($_POST['tahun_pajak'] ?? 0);
    $dasar_pengenaan_pajak = (float)str_replace(['.', ','], ['', '.'], (string)($_POST['dasar_pengenaan_pajak'] ?? 0));
    $tanggal_lapor = sanitize($_POST['tanggal_lapor'] ?? '');
    $status_lapor = sanitize($_POST['status_lapor'] ?? 'TEPAT WAKTU');

    if (!$id_wajib_pajak) $errors[] = 'Silakan pilih Wajib Pajak.';
    if (!$id_jenis_pajak) $errors[] = 'Silakan pilih Jenis Pajak.';
    if (!$id_petugas) $errors[] = 'Silakan pilih Petugas Pajak.';
    if ($dasar_pengenaan_pajak <= 0) $errors[] = 'Dasar Pengenaan Pajak (DPP) harus lebih dari 0.';

    if (empty($errors)) {
        try {
            $stmt_jp = $pdo->prepare("SELECT tarif_persen FROM jenis_pajak WHERE id = ?");
            $stmt_jp->execute([$id_jenis_pajak]);
            $jp = $stmt_jp->fetch();

            if (!$jp) {
                $errors[] = 'Jenis pajak tidak valid.';
            } else {
                $tarif_persen = (float)$jp['tarif_persen'];
                $pajak_terutang = round($dasar_pengenaan_pajak * ($tarif_persen / 100), 2);

                $pdo->beginTransaction();

                // Update SPT
                $update_spt = $pdo->prepare("UPDATE pelaporan_spt SET 
                    id_wajib_pajak = ?, 
                    id_jenis_pajak = ?, 
                    id_petugas = ?, 
                    masa_pajak = ?, 
                    tahun_pajak = ?, 
                    dasar_pengenaan_pajak = ?, 
                    pajak_terutang = ?, 
                    tanggal_lapor = ?, 
                    status_lapor = ? 
                    WHERE id = ?");
                $update_spt->execute([
                    $id_wajib_pajak,
                    $id_jenis_pajak,
                    $id_petugas,
                    $masa_pajak,
                    $tahun_pajak,
                    $dasar_pengenaan_pajak,
                    $pajak_terutang,
                    $tanggal_lapor,
                    $status_lapor,
                    $id
                ]);

                // Update juga jumlah bayar pada billing jika status bayar belum lunas
                if ($spt['status_bayar'] !== 'LUNAS') {
                    $update_billing = $pdo->prepare("UPDATE pembayaran_pajak SET jumlah_bayar = ? WHERE id_pelaporan = ?");
                    $update_billing->execute([$pajak_terutang, $id]);
                }

                $pdo->commit();

                set_flash('success', "Pelaporan SPT '{$spt['nomor_spt']}' berhasil diperbarui!");
                header('Location: ' . base_url('pelaporan_spt/detail.php?id=' . $id));
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Gagal menyimpan perubahan: ' . $e->getMessage();
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
            <li class="breadcrumb-item active">Edit SPT</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Edit Pelaporan SPT: <?= htmlspecialchars($spt['nomor_spt']); ?></h4>
      </div>
      <div>
        <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger shadow-sm alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Pembaruan Gagal:</h6>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="row g-4">
        <div class="col-12 col-lg-8">
          <div class="app-card">
            <div class="app-card-header">
              <h2 class="app-card-title">
                <i class="bi bi-pencil-square text-primary"></i>
                Form Perubahan Pelaporan SPT
              </h2>
            </div>
            <div class="app-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor SPT</label>
                  <input type="text" class="form-control font-monospace fw-bold bg-light" value="<?= htmlspecialchars($spt['nomor_spt']); ?>" readonly>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Petugas Penerima <span class="text-danger">*</span></label>
                  <select name="id_petugas" class="form-select" required>
                    <?php foreach ($petugas_list as $pet): ?>
                      <option value="<?= $pet['id']; ?>" <?= $id_petugas == $pet['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($pet['nama_petugas']); ?> (<?= htmlspecialchars($pet['jabatan']); ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Wajib Pajak <span class="text-danger">*</span></label>
                  <select name="id_wajib_pajak" class="form-select" required>
                    <?php foreach ($wajib_pajak_list as $wp): ?>
                      <option value="<?= $wp['id']; ?>" <?= $id_wajib_pajak == $wp['id'] ? 'selected' : ''; ?>>
                        [<?= $wp['jenis_wp']; ?>] <?= htmlspecialchars($wp['nama_wajib_pajak']); ?> - NPWP: <?= htmlspecialchars($wp['npwp']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12 col-md-8">
                  <label class="form-label fw-semibold">Jenis Pajak <span class="text-danger">*</span></label>
                  <select name="id_jenis_pajak" id="id_jenis_pajak" class="form-select" required>
                    <?php foreach ($jenis_pajak_list as $jp): ?>
                      <option value="<?= $jp['id']; ?>" data-tarif="<?= (float)$jp['tarif_persen']; ?>" <?= $id_jenis_pajak == $jp['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($jp['kode_pajak']); ?> - <?= htmlspecialchars($jp['nama_pajak']); ?> (<?= (float)$jp['tarif_persen']; ?>%)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label fw-semibold">Tarif Pajak</label>
                  <input type="text" id="tarif_persen_display" class="form-control text-center fw-bold bg-light" value="0.00 %" readonly>
                  <input type="hidden" id="tarif_persen" value="0">
                </div>

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
                  <input type="number" name="tahun_pajak" class="form-control" value="<?= htmlspecialchars($tahun_pajak); ?>" required>
                </div>

                <div class="col-12">
                  <div class="p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold text-dark">
                      <i class="bi bi-calculator text-primary me-1"></i>
                      Dasar Pengenaan Pajak (DPP) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg mb-2">
                      <span class="input-group-text fw-bold bg-white">Rp</span>
                      <input type="number" step="1000" name="dasar_pengenaan_pajak" id="dasar_pengenaan_pajak" class="form-control fw-bold fs-4" value="<?= htmlspecialchars($dasar_pengenaan_pajak); ?>" required>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Tanggal Pelaporan <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_lapor" class="form-control" value="<?= htmlspecialchars($tanggal_lapor); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Status Kepatuhan <span class="text-danger">*</span></label>
                  <select name="status_lapor" class="form-select" required>
                    <option value="TEPAT WAKTU" <?= $status_lapor === 'TEPAT WAKTU' ? 'selected' : ''; ?>>TEPAT WAKTU</option>
                    <option value="TERLAMBAT" <?= $status_lapor === 'TERLAMBAT' ? 'selected' : ''; ?>>TERLAMBAT</option>
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm text-white mb-4" style="background: linear-gradient(135deg, #1e3a8a, #0f172a); border-radius: 16px;">
            <div class="card-body p-4">
              <span class="badge bg-white text-primary fw-bold text-uppercase px-2 py-1 mb-2">Kalkulasi Ulang</span>
              <div class="text-white-50 small">Pajak Terutang Hasil Rekalkulasi:</div>
              <div class="display-6 fw-bold text-warning my-2" id="preview_pajak_terutang">
                <?= format_rupiah($spt['pajak_terutang']); ?>
              </div>
              <input type="hidden" name="pajak_terutang" id="pajak_terutang" value="<?= $spt['pajak_terutang']; ?>">

              <div class="p-3 rounded-3 mt-3" style="background: rgba(255, 255, 255, 0.08); font-size: 0.8rem;">
                Status Pembayaran Saat Ini: 
                <strong><?= $spt['status_bayar'] ?? 'BELUM LUNAS'; ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
