<?php
// pembayaran/bayar.php - Formulir Eksekusi Pembayaran Pajak
require_once __DIR__ . '/../config/database.php';

$page_title = 'Proses Pembayaran Pajak';
$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID Pembayaran tidak valid.');
    header('Location: ' . base_url('pembayaran/index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT 
    b.*, 
    s.nomor_spt, 
    s.masa_pajak, 
    s.tahun_pajak, 
    s.pajak_terutang,
    w.nama_wajib_pajak, 
    w.npwp, 
    w.jenis_wp,
    w.alamat,
    j.kode_pajak, 
    j.nama_pajak,
    j.tarif_persen
    FROM pembayaran_pajak b
    JOIN pelaporan_spt s ON b.id_pelaporan = s.id
    JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
    JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
    WHERE b.id = ?");
$stmt->execute([$id]);
$bayar = $stmt->fetch();

if (!$bayar) {
    set_flash('danger', 'Data tagihan billing tidak ditemukan.');
    header('Location: ' . base_url('pembayaran/index.php'));
    exit;
}

if ($bayar['status_bayar'] === 'LUNAS') {
    set_flash('info', 'Tagihan pajak ini sudah lunas.');
    header('Location: ' . base_url('pembayaran/cetak_bukti.php?id=' . $id));
    exit;
}

$errors = [];
$metode_bayar = $_POST['metode_bayar'] ?? 'Virtual Account Mandiri';
$tanggal_bayar = $_POST['tanggal_bayar'] ?? date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_bayar = sanitize($_POST['metode_bayar'] ?? '');
    $tanggal_bayar = sanitize($_POST['tanggal_bayar'] ?? date('Y-m-d H:i:s'));

    if (empty($metode_bayar)) {
        $errors[] = 'Silakan pilih metode pembayaran.';
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE pembayaran_pajak SET 
                metode_bayar = ?, 
                tanggal_bayar = ?, 
                status_bayar = 'LUNAS' 
                WHERE id = ?");
            $update->execute([
                $metode_bayar,
                date('Y-m-d H:i:s', strtotime($tanggal_bayar)),
                $id
            ]);

            set_flash('success', "Pembayaran pajak berhasil disetor! Status billing '{$bayar['kode_billing']}' telah LUNAS.");
            header('Location: ' . base_url('pembayaran/cetak_bukti.php?id=' . $id));
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal memproses pembayaran: ' . $e->getMessage();
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
            <li class="breadcrumb-item"><a href="<?= base_url('pembayaran/index.php'); ?>">Pembayaran</a></li>
            <li class="breadcrumb-item active">Bayar Tagihan</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Loket Setoran Pajak ke Kas Negara</h4>
      </div>
      <div>
        <a href="<?= base_url('pembayaran/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Batal
        </a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger shadow-sm alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Transaksi Gagal:</h6>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="app-card">
          <div class="app-card-header">
            <h2 class="app-card-title">
              <i class="bi bi-credit-card-2-front text-primary"></i>
              Formulir Pembayaran Billing DJP
            </h2>
          </div>
          <div class="app-card-body">
            <form method="POST" action="" novalidate>
              <div class="mb-3">
                <label class="form-label fw-semibold">Pilih Kanal / Metode Pembayaran <span class="text-danger">*</span></label>
                <div class="row g-2">
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="Virtual Account Mandiri" <?= $metode_bayar === 'Virtual Account Mandiri' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">Bank Mandiri (VA)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">ATM / Mandiri Livin'</div>
                      </div>
                    </label>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="Bank Transfer BCA" <?= $metode_bayar === 'Bank Transfer BCA' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">Bank BCA (Transfer/KlikBCA)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Menu Pembayaran Pajak</div>
                      </div>
                    </label>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="Virtual Account BRI" <?= $metode_bayar === 'Virtual Account BRI' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">Bank BRI (BRIMO / ATM)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Kode Perusahaan Kas Negara</div>
                      </div>
                    </label>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="Bank Transfer BNI" <?= $metode_bayar === 'Bank Transfer BNI' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">Bank BNI (BNI Mobile)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Penerimaan Negara MPN G3</div>
                      </div>
                    </label>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="QRIS Bank Indonesia" <?= $metode_bayar === 'QRIS Bank Indonesia' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">QRIS DJP Online</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Semua Dompet Digital & Bank</div>
                      </div>
                    </label>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="p-3 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer bg-light">
                      <input type="radio" name="metode_bayar" value="Teller Bank / Kantor Pos" <?= $metode_bayar === 'Teller Bank / Kantor Pos' ? 'checked' : ''; ?>>
                      <div>
                        <div class="fw-bold small text-dark">Teller Pos / Bank Persepsi</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Loket Setoran Tunai Langsung</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold">Waktu Setoran Pajak <span class="text-danger">*</span></label>
                <input type="datetime-local" name="tanggal_bayar" class="form-control" value="<?= htmlspecialchars($tanggal_bayar); ?>" required>
                <div class="form-text">Waktu transaksi tercatat pada sistem Bank Persepsi / MPN G3.</div>
              </div>

              <hr class="my-4">

              <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('pembayaran/index.php'); ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-success px-4 fw-bold">
                  <i class="bi bi-check2-circle me-1"></i> Konfirmasi Pelunasan & Terbitkan BPN
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Informasi Tagihan Box -->
      <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm text-white mb-4" style="background: linear-gradient(135deg, #059669, #064e3b); border-radius: 16px;">
          <div class="card-body p-4">
            <span class="badge bg-white text-success fw-bold text-uppercase px-2 py-1 mb-2">Tagihan Kas Negara</span>
            <div class="text-white-50 small">Kode Billing DJP (15 Digit):</div>
            <div class="fs-4 font-monospace fw-bold text-warning mb-3">
              <?= htmlspecialchars($bayar['kode_billing']); ?>
            </div>

            <div class="text-white-50 small">Nominal Yang Harus Dibayar:</div>
            <div class="display-6 fw-bold text-white mb-3">
              <?= format_rupiah($bayar['jumlah_bayar']); ?>
            </div>

            <hr class="border-white-50">

            <div class="small text-white-50">
              <div>Wajib Pajak: <strong class="text-white"><?= htmlspecialchars($bayar['nama_wajib_pajak']); ?></strong></div>
              <div>NPWP: <strong class="text-white font-monospace"><?= htmlspecialchars($bayar['npwp']); ?></strong></div>
              <div>Objek Pajak: <strong class="text-white"><?= htmlspecialchars($bayar['kode_pajak']); ?> - <?= htmlspecialchars($bayar['nama_pajak']); ?></strong></div>
              <div>Masa / Tahun: <strong class="text-white"><?= htmlspecialchars($bayar['masa_pajak'] . ' ' . $bayar['tahun_pajak']); ?></strong></div>
              <div>No. SPT: <strong class="text-white font-monospace"><?= htmlspecialchars($bayar['nomor_spt']); ?></strong></div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
