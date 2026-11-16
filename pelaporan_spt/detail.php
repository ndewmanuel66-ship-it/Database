<?php
// pelaporan_spt/detail.php - Detail SPT & Bukti Penerimaan Elektronik (BPE)
require_once __DIR__ . '/../config/database.php';

$page_title = 'Detail Pelaporan SPT';
$id = (int)($_GET['id'] ?? 0);

if (!$id || !$pdo) {
    set_flash('danger', 'ID SPT tidak valid.');
    header('Location: ' . base_url('pelaporan_spt/index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT 
    s.*, 
    w.nama_wajib_pajak, 
    w.npwp, 
    w.nik,
    w.jenis_wp,
    w.alamat,
    w.no_telepon,
    w.email,
    j.kode_pajak, 
    j.nama_pajak, 
    j.tarif_persen,
    p.nip,
    p.nama_petugas,
    p.jabatan,
    b.id AS id_pembayaran,
    b.kode_billing,
    b.jumlah_bayar,
    b.metode_bayar,
    b.tanggal_bayar,
    b.status_bayar
    FROM pelaporan_spt s
    JOIN wajib_pajak w ON s.id_wajib_pajak = w.id
    JOIN jenis_pajak j ON s.id_jenis_pajak = j.id
    JOIN petugas_pajak p ON s.id_petugas = p.id
    LEFT JOIN pembayaran_pajak b ON b.id_pelaporan = s.id
    WHERE s.id = ?");
$stmt->execute([$id]);
$spt = $stmt->fetch();

if (!$spt) {
    set_flash('danger', 'Data SPT tidak ditemukan.');
    header('Location: ' . base_url('pelaporan_spt/index.php'));
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="content-body">
    <!-- Breadcrumb & Action Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('pelaporan_spt/index.php'); ?>">Pelaporan SPT</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($spt['nomor_spt']); ?></li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Bukti Penerimaan Elektronik (BPE) & Detail SPT</h4>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('pelaporan_spt/index.php'); ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark">
          <i class="bi bi-printer me-1"></i> Cetak Dokumen
        </button>
        <?php if ($spt['status_bayar'] !== 'LUNAS' && !empty($spt['id_pembayaran'])): ?>
          <a href="<?= base_url('pembayaran/bayar.php?id=' . $spt['id_pembayaran']); ?>" class="btn btn-success">
            <i class="bi bi-credit-card me-1"></i> Bayar Pajak Ini
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Official Document Box (BPE) -->
    <div class="card border shadow-sm mx-auto" style="max-width: 900px; border-radius: 12px;">
      <!-- Kop Surat Resmi -->
      <div class="card-header bg-white border-bottom p-4">
        <div class="row align-items-center">
          <div class="col-2 text-center">
            <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 65px; height: 65px; font-size: 2rem;">
              <i class="bi bi-bank2"></i>
            </div>
          </div>
          <div class="col-10">
            <h5 class="fw-bold text-uppercase mb-0 text-dark">Kementerian Keuangan Republik Indonesia</h5>
            <h6 class="fw-semibold text-secondary mb-1">Direktorat Jenderal Pajak</h6>
            <div class="small text-muted">Kantor Pelayanan Pajak Pratama - Sistem Administrasi SPT Elektronik</div>
          </div>
        </div>
        <hr class="my-3">
        <div class="text-center">
          <h5 class="fw-bold text-dark text-uppercase letter-spacing-1 mb-1">BUKTI PENERIMAAN ELEKTRONIK (BPE)</h5>
          <span class="badge bg-light text-primary border font-monospace fs-6 px-3 py-1">
            NOMOR SPT: <?= htmlspecialchars($spt['nomor_spt']); ?>
          </span>
        </div>
      </div>

      <div class="card-body p-4">
        <!-- Status Banner -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light border">
              <span class="small text-muted d-block mb-1">Status Kepatuhan Pelaporan:</span>
              <?php if ($spt['status_lapor'] === 'TEPAT WAKTU'): ?>
                <span class="badge badge-tepat-waktu fs-6"><i class="bi bi-check-circle-fill me-1"></i> TEPAT WAKTU</span>
              <?php else: ?>
                <span class="badge badge-terlambat fs-6"><i class="bi bi-exclamation-triangle-fill me-1"></i> TERLAMBAT</span>
              <?php endif; ?>
              <div class="small text-muted mt-2">Diterima pada: <?= format_tanggal($spt['tanggal_lapor']); ?></div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light border">
              <span class="small text-muted d-block mb-1">Status Pembayaran Kas Negara:</span>
              <?php if ($spt['status_bayar'] === 'LUNAS'): ?>
                <span class="badge badge-lunas fs-6"><i class="bi bi-patch-check-fill me-1"></i> LUNAS (NTPN Valid)</span>
                <div class="small text-muted mt-2">Dibayar tgl: <?= format_tanggal($spt['tanggal_bayar'], true); ?></div>
              <?php else: ?>
                <span class="badge badge-belum-lunas fs-6"><i class="bi bi-hourglass-split me-1"></i> BELUM LUNAS</span>
                <div class="small text-danger mt-2">Silakan selesaikan pembayaran sebelum jatuh tempo.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- 1. Identitas Wajib Pajak -->
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-person-badge text-primary me-1"></i> 1. IDENTITAS WAJIB PAJAK
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-sm table-borderless">
            <tbody>
              <tr>
                <td style="width: 220px;" class="text-muted">Nama Wajib Pajak</td>
                <td class="fw-bold text-dark">: <?= htmlspecialchars($spt['nama_wajib_pajak']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">NPWP</td>
                <td class="font-monospace fw-semibold">: <?= htmlspecialchars($spt['npwp']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">NIK Kependudukan</td>
                <td class="font-monospace">: <?= htmlspecialchars($spt['nik']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Klasifikasi WP</td>
                <td>: <?= $spt['jenis_wp'] === 'OP' ? 'Orang Pribadi (OP)' : 'Badan Usaha (PT/CV)'; ?></td>
              </tr>
              <tr>
                <td class="text-muted">Alamat Domisili</td>
                <td>: <?= htmlspecialchars($spt['alamat']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">No. Telepon / Email</td>
                <td>: <?= htmlspecialchars($spt['no_telepon']); ?> / <?= htmlspecialchars($spt['email']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 2. Rincian Pelaporan & Perhitungan Pajak -->
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-calculator text-primary me-1"></i> 2. RINCIAN PERHITUNGAN PAJAK TERUTANG
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>Uraian Pajak</th>
                <th>Masa / Tahun</th>
                <th class="text-center">Tarif</th>
                <th class="text-end">Dasar Pengenaan Pajak (DPP)</th>
                <th class="text-end">Pajak Terutang</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($spt['nama_pajak']); ?></div>
                  <small class="text-muted">Kode Objek Pajak: <code><?= htmlspecialchars($spt['kode_pajak']); ?></code></small>
                </td>
                <td class="align-middle fw-semibold"><?= htmlspecialchars($spt['masa_pajak'] . ' ' . $spt['tahun_pajak']); ?></td>
                <td class="text-center align-middle fw-bold"><?= (float)$spt['tarif_persen']; ?>%</td>
                <td class="text-end align-middle"><?= format_rupiah($spt['dasar_pengenaan_pajak']); ?></td>
                <td class="text-end align-middle fw-bold fs-6 text-primary"><?= format_rupiah($spt['pajak_terutang']); ?></td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <th colspan="4" class="text-end">TOTAL TAGIHAN PAJAK:</th>
                <th class="text-end text-success fs-5"><?= format_rupiah($spt['pajak_terutang']); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- 3. Informasi Billing & Pembayaran -->
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-receipt text-primary me-1"></i> 3. KODE BILLING & PEMBAYARAN KAS NEGARA
        </h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tbody>
                <tr>
                  <td style="width: 160px;" class="text-muted">Kode Billing DJP</td>
                  <td class="font-monospace fw-bold text-primary fs-6">: <?= htmlspecialchars($spt['kode_billing'] ?? '-'); ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Jumlah Bayar</td>
                  <td class="fw-bold">: <?= format_rupiah($spt['jumlah_bayar'] ?? $spt['pajak_terutang']); ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tbody>
                <tr>
                  <td style="width: 160px;" class="text-muted">Metode Bayar</td>
                  <td>: <?= htmlspecialchars($spt['metode_bayar'] ?? 'Menunggu Pembayaran'); ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Tanggal Setor</td>
                  <td>: <?= format_tanggal($spt['tanggal_bayar'], true); ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tanda Tangan Digital & Petugas -->
        <div class="row mt-5 pt-3 border-top">
          <div class="col-6 text-center">
            <div class="small text-muted mb-4">Wajib Pajak / Kuasa,</div>
            <div class="fw-bold text-dark mt-4"><?= htmlspecialchars($spt['nama_wajib_pajak']); ?></div>
            <div class="small text-muted font-monospace">NPWP: <?= htmlspecialchars($spt['npwp']); ?></div>
          </div>
          <div class="col-6 text-center">
            <div class="small text-muted mb-4">Petugas Penerima DJP,</div>
            <div class="fw-bold text-dark mt-4"><?= htmlspecialchars($spt['nama_petugas']); ?></div>
            <div class="small text-muted">NIP: <?= htmlspecialchars($spt['nip']); ?></div>
          </div>
        </div>
      </div>

      <div class="card-footer bg-light text-center small text-muted py-3">
        Bukti Penerimaan Elektronik (BPE) ini sah dan diterbitkan oleh Sistem Informasi Perpajakan Nasional (SIPAJAK).
      </div>
    </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
