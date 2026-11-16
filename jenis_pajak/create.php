<?php
// jenis_pajak/create.php - Tambah Jenis Pajak Baru
require_once __DIR__ . '/../config/database.php';

$page_title = 'Tambah Jenis Pajak';
$errors = [];

$kode_pajak = '';
$nama_pajak = '';
$tarif_persen = '';
$keterangan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_pajak = strtoupper(sanitize($_POST['kode_pajak'] ?? ''));
    $nama_pajak = sanitize($_POST['nama_pajak'] ?? '');
    $tarif_persen = sanitize($_POST['tarif_persen'] ?? '');
    $keterangan = sanitize($_POST['keterangan'] ?? '');

    if (empty($kode_pajak)) {
        $errors[] = 'Kode Pajak wajib diisi.';
    }
    if (empty($nama_pajak)) {
        $errors[] = 'Nama Pajak wajib diisi.';
    }
    if ($tarif_persen === '' || !is_numeric($tarif_persen) || (float)$tarif_persen < 0) {
        $errors[] = 'Tarif Persen harus berupa angka valid (>= 0).';
    }

    if (empty($errors) && $pdo) {
        try {
            // Periksa duplikasi Kode Pajak
            $check = $pdo->prepare("SELECT id FROM jenis_pajak WHERE kode_pajak = ?");
            $check->execute([$kode_pajak]);
            if ($check->fetch()) {
                $errors[] = "Kode Pajak '{$kode_pajak}' sudah terdaftar dalam sistem.";
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("INSERT INTO jenis_pajak (kode_pajak, nama_pajak, tarif_persen, keterangan) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $kode_pajak,
                    $nama_pajak,
                    (float)$tarif_persen,
                    $keterangan
                ]);

                set_flash('success', "Jenis Pajak '{$nama_pajak}' ({$kode_pajak}) berhasil ditambahkan!");
                header('Location: ' . base_url('jenis_pajak/index.php'));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Kesalahan database: ' . $e->getMessage();
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
            <li class="breadcrumb-item"><a href="<?= base_url('jenis_pajak/index.php'); ?>">Jenis Pajak</a></li>
            <li class="breadcrumb-item active">Tambah Baru</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Tambah Jenis Pajak & Tarif</h4>
      </div>
      <div>
        <a href="<?= base_url('jenis_pajak/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger shadow-sm alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Validasi Gagal:</h6>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-12 col-lg-7">
        <div class="app-card">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-plus-circle text-primary"></i>
              Formulir Master Jenis Pajak
            </h2>
          </div>
          <div class="app-card-body">
            <form method="POST" action="" novalidate>
              <div class="mb-3">
                <label class="form-label fw-semibold">Kode Pajak <span class="text-danger">*</span></label>
                <input type="text" name="kode_pajak" class="form-control text-uppercase" placeholder="Contoh: PPH21, PPN, PBB-P2" value="<?= htmlspecialchars($kode_pajak); ?>" required>
                <div class="form-text">Kode unik sebagai identifier pada pelaporan SPT.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Pajak <span class="text-danger">*</span></label>
                <input type="text" name="nama_pajak" class="form-control" placeholder="Contoh: PPh Pasal 21, Pajak Pertambahan Nilai" value="<?= htmlspecialchars($nama_pajak); ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Tarif Pajak (%) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100" name="tarif_persen" class="form-control" placeholder="Contoh: 5.00 atau 11.00" value="<?= htmlspecialchars($tarif_persen); ?>" required>
                  <span class="input-group-text fw-bold">%</span>
                </div>
                <div class="form-text">Persentase tarif yang akan otomatis dikalikan dengan DPP pada SPT.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Keterangan / Dasar Regulasi</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Berdasarkan UU HPP / PP 23 Tahun 2018"><?= htmlspecialchars($keterangan); ?></textarea>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('jenis_pajak/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-1"></i> Simpan Jenis Pajak
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="app-card">
          <div class="app-card-header bg-light">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-lightbulb me-1"></i> Contoh Tarif Umum Pajak RI</h6>
          </div>
          <div class="app-card-body small">
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
              <li class="d-flex justify-content-between border-bottom pb-2">
                <span><strong>PPh 21 (Lapis 1):</strong> PKP s.d 60 Juta</span>
                <span class="badge bg-primary">5%</span>
              </li>
              <li class="d-flex justify-content-between border-bottom pb-2">
                <span><strong>PPh 22 (Impor/Pemerintah):</strong> Barang tertentu</span>
                <span class="badge bg-primary">1.5%</span>
              </li>
              <li class="d-flex justify-content-between border-bottom pb-2">
                <span><strong>PPh 23:</strong> Jasa, Sewa & Dividen</span>
                <span class="badge bg-primary">2%</span>
              </li>
              <li class="d-flex justify-content-between border-bottom pb-2">
                <span><strong>PPh Final PP 23:</strong> Omset UMKM</span>
                <span class="badge bg-primary">0.5%</span>
              </li>
              <li class="d-flex justify-content-between">
                <span><strong>PPN (UU Harmonisasi):</strong> Barang/Jasa</span>
                <span class="badge bg-primary">11%</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
