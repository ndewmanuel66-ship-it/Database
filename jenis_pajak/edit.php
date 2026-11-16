<?php
// jenis_pajak/edit.php - Ubah Jenis Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Edit Jenis Pajak';
$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID Jenis Pajak tidak valid.');
    header('Location: ' . base_url('jenis_pajak/index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM jenis_pajak WHERE id = ?");
$stmt->execute([$id]);
$jp = $stmt->fetch();

if (!$jp) {
    set_flash('danger', 'Data Jenis Pajak tidak ditemukan.');
    header('Location: ' . base_url('jenis_pajak/index.php'));
    exit;
}

$errors = [];
$kode_pajak = $jp['kode_pajak'];
$nama_pajak = $jp['nama_pajak'];
$tarif_persen = $jp['tarif_persen'];
$keterangan = $jp['keterangan'];

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

    if (empty($errors)) {
        try {
            $check = $pdo->prepare("SELECT id FROM jenis_pajak WHERE kode_pajak = ? AND id != ?");
            $check->execute([$kode_pajak, $id]);
            if ($check->fetch()) {
                $errors[] = "Kode Pajak '{$kode_pajak}' sudah digunakan oleh Jenis Pajak lain.";
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE jenis_pajak SET kode_pajak = ?, nama_pajak = ?, tarif_persen = ?, keterangan = ? WHERE id = ?");
                $stmt->execute([
                    $kode_pajak,
                    $nama_pajak,
                    (float)$tarif_persen,
                    $keterangan,
                    $id
                ]);

                set_flash('success', "Jenis Pajak '{$nama_pajak}' berhasil diperbarui!");
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
            <li class="breadcrumb-item active">Edit Data</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Edit Jenis Pajak: <?= htmlspecialchars($jp['kode_pajak']); ?></h4>
      </div>
      <div>
        <a href="<?= base_url('jenis_pajak/index.php'); ?>" class="btn btn-outline-secondary">
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

    <div class="row">
      <div class="col-12 col-lg-7">
        <div class="app-card">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-pencil-square text-primary"></i>
              Form Perubahan Jenis Pajak
            </h2>
          </div>
          <div class="app-card-body">
            <form method="POST" action="" novalidate>
              <div class="mb-3">
                <label class="form-label fw-semibold">Kode Pajak <span class="text-danger">*</span></label>
                <input type="text" name="kode_pajak" class="form-control text-uppercase" value="<?= htmlspecialchars($kode_pajak); ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Pajak <span class="text-danger">*</span></label>
                <input type="text" name="nama_pajak" class="form-control" value="<?= htmlspecialchars($nama_pajak); ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Tarif Pajak (%) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100" name="tarif_persen" class="form-control" value="<?= htmlspecialchars($tarif_persen); ?>" required>
                  <span class="input-group-text fw-bold">%</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Keterangan / Regulasi</label>
                <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($keterangan); ?></textarea>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('jenis_pajak/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="app-card">
          <div class="app-card-header bg-light">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-1"></i> Catatan Perubahan Tarif</h6>
          </div>
          <div class="app-card-body small text-muted">
            <p>Perubahan persentase tarif pada master jenis pajak akan diterapkan pada pembuatan pelaporan SPT baru selanjutnya.</p>
            <p class="mb-0">Data SPT yang telah dilaporkan sebelumnya akan tetap menyimpan nilai nominal pajak terutang saat laporan tersebut disahkan.</p>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
