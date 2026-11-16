<?php
// wajib_pajak/edit.php - Ubah Data Wajib Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Edit Wajib Pajak';
$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID Wajib Pajak tidak valid.');
    header('Location: ' . base_url('wajib_pajak/index.php'));
    exit;
}

// Ambil data wajib pajak eksisting
$stmt = $pdo->prepare("SELECT * FROM wajib_pajak WHERE id = ?");
$stmt->execute([$id]);
$wp = $stmt->fetch();

if (!$wp) {
    set_flash('danger', 'Data Wajib Pajak tidak ditemukan.');
    header('Location: ' . base_url('wajib_pajak/index.php'));
    exit;
}

$errors = [];
$npwp = $wp['npwp'];
$nik = $wp['nik'];
$nama_wajib_pajak = $wp['nama_wajib_pajak'];
$jenis_wp = $wp['jenis_wp'];
$alamat = $wp['alamat'];
$no_telepon = $wp['no_telepon'];
$email = $wp['email'];
$tanggal_daftar = $wp['tanggal_daftar'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npwp = sanitize($_POST['npwp'] ?? '');
    $nik = sanitize($_POST['nik'] ?? '');
    $nama_wajib_pajak = sanitize($_POST['nama_wajib_pajak'] ?? '');
    $jenis_wp = sanitize($_POST['jenis_wp'] ?? 'OP');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $no_telepon = sanitize($_POST['no_telepon'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $tanggal_daftar = sanitize($_POST['tanggal_daftar'] ?? date('Y-m-d'));

    if (empty($npwp)) {
        $errors[] = 'Nomor NPWP wajib diisi.';
    }
    if (empty($nik)) {
        $errors[] = 'Nomor NIK wajib diisi.';
    }
    if (empty($nama_wajib_pajak)) {
        $errors[] = 'Nama Wajib Pajak wajib diisi.';
    }
    if (!in_array($jenis_wp, ['OP', 'Badan'])) {
        $errors[] = 'Pilihan Jenis WP tidak valid.';
    }
    if (empty($no_telepon)) {
        $errors[] = 'Nomor telepon wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (empty($errors)) {
        try {
            // Periksa apakah NPWP sudah digunakan oleh record lain
            $check_npwp = $pdo->prepare("SELECT id FROM wajib_pajak WHERE npwp = ? AND id != ?");
            $check_npwp->execute([$npwp, $id]);
            if ($check_npwp->fetch()) {
                $errors[] = "NPWP '{$npwp}' sudah digunakan oleh Wajib Pajak lain.";
            }

            // Periksa apakah NIK sudah digunakan oleh record lain
            $check_nik = $pdo->prepare("SELECT id FROM wajib_pajak WHERE nik = ? AND id != ?");
            $check_nik->execute([$nik, $id]);
            if ($check_nik->fetch()) {
                $errors[] = "NIK '{$nik}' sudah digunakan oleh Wajib Pajak lain.";
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE wajib_pajak SET 
                    npwp = ?, 
                    nik = ?, 
                    nama_wajib_pajak = ?, 
                    jenis_wp = ?, 
                    alamat = ?, 
                    no_telepon = ?, 
                    email = ?, 
                    tanggal_daftar = ? 
                    WHERE id = ?");
                $stmt->execute([
                    $npwp,
                    $nik,
                    $nama_wajib_pajak,
                    $jenis_wp,
                    $alamat,
                    $no_telepon,
                    $email,
                    $tanggal_daftar,
                    $id
                ]);

                set_flash('success', "Data Wajib Pajak '{$nama_wajib_pajak}' berhasil diperbarui!");
                header('Location: ' . base_url('wajib_pajak/index.php'));
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
            <li class="breadcrumb-item"><a href="<?= base_url('wajib_pajak/index.php'); ?>">Wajib Pajak</a></li>
            <li class="breadcrumb-item active">Edit Data</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Edit Data Wajib Pajak: <?= htmlspecialchars($wp['nama_wajib_pajak']); ?></h4>
      </div>
      <div>
        <a href="<?= base_url('wajib_pajak/index.php'); ?>" class="btn btn-outline-secondary">
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
      <div class="col-12 col-lg-8">
        <div class="app-card">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-pencil-square text-primary"></i>
              Form Perubahan Data Wajib Pajak
            </h2>
          </div>
          <div class="app-card-body">
            <form method="POST" action="" novalidate>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor NPWP <span class="text-danger">*</span></label>
                  <input type="text" name="npwp" class="form-control" value="<?= htmlspecialchars($npwp); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor NIK <span class="text-danger">*</span></label>
                  <input type="text" name="nik" class="form-control" maxlength="20" value="<?= htmlspecialchars($nik); ?>" required>
                </div>

                <div class="col-12 col-md-8">
                  <label class="form-label fw-semibold">Nama Lengkap Wajib Pajak <span class="text-danger">*</span></label>
                  <input type="text" name="nama_wajib_pajak" class="form-control" value="<?= htmlspecialchars($nama_wajib_pajak); ?>" required>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label fw-semibold">Jenis Wajib Pajak <span class="text-danger">*</span></label>
                  <select name="jenis_wp" class="form-select" required>
                    <option value="OP" <?= $jenis_wp === 'OP' ? 'selected' : ''; ?>>Orang Pribadi (OP)</option>
                    <option value="Badan" <?= $jenis_wp === 'Badan' ? 'selected' : ''; ?>>Badan Usaha (PT/CV)</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                  <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($alamat); ?></textarea>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                  <input type="tel" name="no_telepon" class="form-control" value="<?= htmlspecialchars($no_telepon); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Tanggal Daftar <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_daftar" class="form-control" value="<?= htmlspecialchars($tanggal_daftar); ?>" required>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('wajib_pajak/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="app-card">
          <div class="app-card-header bg-light">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-1"></i> Informasi Sistem</h6>
          </div>
          <div class="app-card-body small text-muted">
            <p><strong>ID Record:</strong> #<?= $wp['id']; ?></p>
            <p><strong>Didaftarkan:</strong> <?= format_tanggal($wp['created_at'], true); ?></p>
            <p><strong>Terakhir Diperbarui:</strong> <?= format_tanggal($wp['updated_at'], true); ?></p>
            <hr>
            <p class="mb-0 text-secondary">Perubahan NPWP akan langsung terupdate pada seluruh riwayat pencarian SPT terkait.</p>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
