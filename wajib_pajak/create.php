<?php
// wajib_pajak/create.php - Tambah Wajib Pajak Baru
require_once __DIR__ . '/../config/database.php';

$page_title = 'Tambah Wajib Pajak';
$errors = [];

$npwp = '';
$nik = '';
$nama_wajib_pajak = '';
$jenis_wp = 'OP';
$alamat = '';
$no_telepon = '';
$email = '';
$tanggal_daftar = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npwp = sanitize($_POST['npwp'] ?? '');
    $nik = sanitize($_POST['nik'] ?? '');
    $nama_wajib_pajak = sanitize($_POST['nama_wajib_pajak'] ?? '');
    $jenis_wp = sanitize($_POST['jenis_wp'] ?? 'OP');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $no_telepon = sanitize($_POST['no_telepon'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $tanggal_daftar = sanitize($_POST['tanggal_daftar'] ?? date('Y-m-d'));

    // Validasi Sederhana & Kuat
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

    if (empty($errors) && $pdo) {
        try {
            // Periksa duplikasi NPWP
            $check_npwp = $pdo->prepare("SELECT id FROM wajib_pajak WHERE npwp = ?");
            $check_npwp->execute([$npwp]);
            if ($check_npwp->fetch()) {
                $errors[] = "NPWP '{$npwp}' sudah terdaftar dalam sistem.";
            }

            // Periksa duplikasi NIK
            $check_nik = $pdo->prepare("SELECT id FROM wajib_pajak WHERE nik = ?");
            $check_nik->execute([$nik]);
            if ($check_nik->fetch()) {
                $errors[] = "NIK '{$nik}' sudah terdaftar dalam sistem.";
            }

            // Jika tidak ada duplikasi, lakukan INSERT
            if (empty($errors)) {
                $stmt = $pdo->prepare("INSERT INTO wajib_pajak 
                    (npwp, nik, nama_wajib_pajak, jenis_wp, alamat, no_telepon, email, tanggal_daftar) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $npwp,
                    $nik,
                    $nama_wajib_pajak,
                    $jenis_wp,
                    $alamat,
                    $no_telepon,
                    $email,
                    $tanggal_daftar
                ]);

                set_flash('success', "Wajib Pajak '{$nama_wajib_pajak}' berhasil didaftarkan!");
                header('Location: ' . base_url('wajib_pajak/index.php'));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan database: ' . $e->getMessage();
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
            <li class="breadcrumb-item active">Tambah Baru</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Form Registrasi Wajib Pajak Baru</h4>
      </div>
      <div>
        <a href="<?= base_url('wajib_pajak/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger shadow-sm alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Pendaftaran Gagal:</h6>
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
              <i class="bi bi-person-lines-fill text-primary"></i>
              Informasi Pokok Wajib Pajak
            </h2>
          </div>
          <div class="app-card-body">
            <form method="POST" action="" novalidate>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor NPWP <span class="text-danger">*</span></label>
                  <input type="text" name="npwp" class="form-control" placeholder="Contoh: 09.254.341.2-404.000" value="<?= htmlspecialchars($npwp); ?>" required>
                  <div class="form-text">Format 15 atau 16 digit NPWP terdaftar di DJP.</div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor NIK (KTP / Paspor) <span class="text-danger">*</span></label>
                  <input type="text" name="nik" class="form-control" maxlength="20" placeholder="16 Digit NIK Kependudukan" value="<?= htmlspecialchars($nik); ?>" required>
                  <div class="form-text">Sebagai integrasi NIK menjadi NPWP.</div>
                </div>

                <div class="col-12 col-md-8">
                  <label class="form-label fw-semibold">Nama Lengkap Wajib Pajak / Entitas <span class="text-danger">*</span></label>
                  <input type="text" name="nama_wajib_pajak" class="form-control" placeholder="Nama sesuai KTP atau Akta Perusahaan" value="<?= htmlspecialchars($nama_wajib_pajak); ?>" required>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label fw-semibold">Jenis Wajib Pajak <span class="text-danger">*</span></label>
                  <select name="jenis_wp" class="form-select" required>
                    <option value="OP" <?= $jenis_wp === 'OP' ? 'selected' : ''; ?>>Orang Pribadi (OP)</option>
                    <option value="Badan" <?= $jenis_wp === 'Badan' ? 'selected' : ''; ?>>Badan Usaha (PT/CV)</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Alamat Lengkap Domisili / Kantor <span class="text-danger">*</span></label>
                  <textarea name="alamat" class="form-control" rows="3" placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos" required><?= htmlspecialchars($alamat); ?></textarea>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                  <input type="tel" name="no_telepon" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($no_telepon); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Alamat Email Aktif <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($email); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold">Tanggal Terdaftar <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_daftar" class="form-control" value="<?= htmlspecialchars($tanggal_daftar); ?>" required>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('wajib_pajak/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-save me-1"></i> Simpan Data WP
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Informasi Petunjuk -->
      <div class="col-12 col-lg-4">
        <div class="app-card">
          <div class="app-card-header bg-light">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-1"></i> Ketentuan Wajib Pajak</h6>
          </div>
          <div class="app-card-body small text-muted">
            <p><strong>Orang Pribadi (OP):</strong> Individu yang memiliki penghasilan dari pekerjaan bebas, usaha, atau pegawai di atas PTKP.</p>
            <p><strong>Badan:</strong> Sekumpulan orang dan/atau modal yang merupakan kesatuan (PT, CV, BUMN, Koperasi, Yayasan).</p>
            <hr>
            <div class="alert alert-info py-2 px-3 small mb-0">
              <i class="bi bi-shield-lock me-1"></i> NPWP dan NIK bersifat unik dan tidak boleh ganda di dalam sistem.
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
