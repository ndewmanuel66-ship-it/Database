<?php
// pembayaran/cetak_bukti.php - Bukti Penerimaan Negara (BPN) Resmi
require_once __DIR__ . '/../config/database.php';

$page_title = 'Bukti Penerimaan Negara (BPN)';
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
    s.dasar_pengenaan_pajak,
    s.pajak_terutang,
    w.nama_wajib_pajak, 
    w.npwp, 
    w.nik,
    w.jenis_wp,
    w.alamat,
    j.kode_pajak, 
    j.nama_pajak,
    j.tarif_persen,
    p.nama_petugas,
    p.nip
    FROM pembayaran_pajak b
    JOIN pelaporan_spt s ON b.id_pelaporan = s.id
    JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
    JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
    JOIN petugas_pajak p ON s.id_petugas = p.id
    WHERE b.id = ?");
$stmt->execute([$id]);
$bpn = $stmt->fetch();

if (!$bpn) {
    set_flash('danger', 'Data setoran pajak tidak ditemukan.');
    header('Location: ' . base_url('pembayaran/index.php'));
    exit;
}

// Generate NTPN unik berbasis ID dan Billing jika belum ada
$ntpn = strtoupper(substr(md5('NTPN' . $bpn['kode_billing'] . $bpn['id']), 0, 16));
$ntb = 'BCA' . substr($bpn['kode_billing'], -8);

// Fungsi pembaca angka terbilang sederhana
function terbilang($angka) {
    $angka = abs((float)$angka);
    $baca = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    $terbilang = '';

    if ($angka < 12) {
        $terbilang = ' ' . $baca[$angka];
    } elseif ($angka < 20) {
        $terbilang = terbilang($angka - 10) . ' belas';
    } elseif ($angka < 100) {
        $terbilang = terbilang($angka / 10) . ' puluh' . terbilang($angka % 10);
    } elseif ($angka < 200) {
        $terbilang = ' seratus' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . ' ratus' . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        $terbilang = ' seribu' . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . ' ribu' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . ' juta' . terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
        $terbilang = terbilang($angka / 1000000000) . ' milyar' . terbilang(fmod($angka, 1000000000));
    }

    return trim($terbilang);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="content-body">
    <!-- Action Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('pembayaran/index.php'); ?>">Pembayaran</a></li>
            <li class="breadcrumb-item active">Cetak BPN</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Bukti Penerimaan Negara (BPN)</h4>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('pembayaran/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button onclick="window.print()" class="btn btn-primary">
          <i class="bi bi-printer me-1"></i> Cetak Dokumen BPN
        </button>
      </div>
    </div>

    <!-- Dokumen BPN -->
    <div class="card border shadow-sm mx-auto" style="max-width: 850px; border-radius: 12px;">
      <div class="card-header bg-white border-bottom p-4">
        <div class="row align-items-center">
          <div class="col-2 text-center">
            <div class="bg-success text-white rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
              <i class="bi bi-shield-check"></i>
            </div>
          </div>
          <div class="col-10">
            <h5 class="fw-bold text-uppercase mb-0 text-dark">Kementerian Keuangan Republik Indonesia</h5>
            <h6 class="fw-semibold text-secondary mb-1">Direktorat Jenderal Perbendaharaan &amp; Pajak</h6>
            <div class="small text-muted">Modul Penerimaan Negara Generasi Ketiga (MPN G3)</div>
          </div>
        </div>
        <hr class="my-3">
        <div class="text-center">
          <h5 class="fw-bold text-dark text-uppercase letter-spacing-1 mb-1">BUKTI PENERIMAAN NEGARA (BPN)</h5>
          <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1">
            STATUS: PEMBAYARAN SAH / TELAH MASUK KE KAS NEGARA
          </span>
        </div>
      </div>

      <div class="card-body p-4">
        <!-- Rincian Validasi Perbankan & MPN -->
        <div class="p-3 bg-light rounded-3 border mb-4">
          <div class="row g-2 small">
            <div class="col-md-6">
              <span class="text-muted d-block">Nomor Transaksi Penerimaan Negara (NTPN):</span>
              <strong class="font-monospace fs-6 text-dark"><?= $ntpn; ?></strong>
            </div>
            <div class="col-md-6">
              <span class="text-muted d-block">Nomor Transaksi Bank (NTB) / Referensi:</span>
              <strong class="font-monospace fs-6 text-dark"><?= $ntb; ?></strong>
            </div>
            <div class="col-md-6 mt-2">
              <span class="text-muted d-block">Kode Billing:</span>
              <strong class="font-monospace fs-6 text-primary"><?= htmlspecialchars($bpn['kode_billing']); ?></strong>
            </div>
            <div class="col-md-6 mt-2">
              <span class="text-muted d-block">Kanal Pembayaran / Bank Persepsi:</span>
              <strong class="text-dark"><?= htmlspecialchars($bpn['metode_bayar'] ?? 'Bank Transfer'); ?></strong>
            </div>
          </div>
        </div>

        <!-- Data Wajib Pajak -->
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-person text-success me-1"></i> IDENTITAS WAJIB PAJAK PENYETOR
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-sm table-borderless small">
            <tbody>
              <tr>
                <td style="width: 200px;" class="text-muted">Nomor Pokok Wajib Pajak</td>
                <td class="font-monospace fw-bold">: <?= htmlspecialchars($bpn['npwp']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Nama Wajib Pajak</td>
                <td class="fw-bold text-dark">: <?= htmlspecialchars($bpn['nama_wajib_pajak']); ?> (<?= $bpn['jenis_wp']; ?>)</td>
              </tr>
              <tr>
                <td class="text-muted">Alamat Wajib Pajak</td>
                <td>: <?= htmlspecialchars($bpn['alamat']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Rincian Setoran Pajak -->
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-receipt text-success me-1"></i> RINCIAN SETORAN PAJAK
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-bordered small">
            <thead class="table-light">
              <tr>
                <th>Objek Pajak</th>
                <th>Masa / Tahun</th>
                <th>No. Referensi SPT</th>
                <th class="text-end">Jumlah Setoran</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($bpn['nama_pajak']); ?></strong>
                  <div class="text-muted font-monospace">Kode: <?= htmlspecialchars($bpn['kode_pajak']); ?></div>
                </td>
                <td class="align-middle fw-semibold"><?= htmlspecialchars($bpn['masa_pajak'] . ' ' . $bpn['tahun_pajak']); ?></td>
                <td class="align-middle font-monospace"><?= htmlspecialchars($bpn['nomor_spt']); ?></td>
                <td class="text-end align-middle fw-bold text-success fs-6"><?= format_rupiah($bpn['jumlah_bayar']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Terbilang Nominal -->
        <div class="p-3 bg-light rounded-3 border mb-4">
          <div class="small text-muted mb-1">Terbilang:</div>
          <div class="fw-bold text-dark fst-italic text-capitalize">
            # <?= terbilang($bpn['jumlah_bayar']); ?> rupiah #
          </div>
        </div>

        <!-- Tanda Tangan & Waktu Setoran -->
        <div class="row mt-4 pt-3 border-top small">
          <div class="col-6">
            <div class="text-muted">Waktu Pembayaran Tercatat:</div>
            <strong class="text-dark"><?= format_tanggal($bpn['tanggal_bayar'], true); ?></strong>
            <div class="mt-2 text-muted" style="font-size: 0.72rem;">
              Kuitansi ini dicetak melalui sistem SIPAJAK secara otomatis.
            </div>
          </div>
          <div class="col-6 text-end">
            <div class="text-muted">Petugas Verifikasi DJP:</div>
            <strong class="text-dark d-block mt-4"><?= htmlspecialchars($bpn['nama_petugas']); ?></strong>
            <span class="text-muted">NIP. <?= htmlspecialchars($bpn['nip']); ?></span>
          </div>
        </div>
      </div>

      <div class="card-footer bg-light text-center small text-muted py-3">
        Bukti Penerimaan Negara (BPN) ini adalah dokumen sah pengganti Surat Setoran Pajak (SSP) sesuai Peraturan Menteri Keuangan.
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
