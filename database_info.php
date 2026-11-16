<?php
// database_info.php - Informasi & Auto-Installer Database
require_once __DIR__ . '/config/database.php';

$page_title = 'Struktur & Skrip Database';

$action = $_GET['action'] ?? '';
$import_message = null;

// Fitur Auto Import SQL jika user menekan tombol inisialisasi
if ($action === 'import') {
    try {
        $rawPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $sqlPath = __DIR__ . '/database.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            $rawPdo->exec($sqlContent);
            set_flash('success', 'Database db_perpajakan dan seluruh data dummy berhasil diimpor secara otomatis!');
        } else {
            set_flash('danger', 'File database.sql tidak ditemukan.');
        }
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal melakukan impor otomatis: ' . $e->getMessage() . '. Silakan gunakan phpMyAdmin di Laragon.');
    }
    header('Location: ' . base_url('database_info.php'));
    exit;
}

$tables_status = [];
if ($pdo) {
    try {
        $tables = ['wajib_pajak', 'jenis_pajak', 'petugas_pajak', 'pelaporan_spt', 'pembayaran_pajak'];
        foreach ($tables as $t) {
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `{$t}`");
            $tables_status[$t] = $stmt->fetch()['total'] ?? 0;
        }
    } catch (Exception $e) {
        // Abaikan jika tabel belum ada
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Sistem</li>
            <li class="breadcrumb-item active">Struktur Database</li>
          </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Informasi Arsitektur Database Relasional</h4>
      </div>
      <div>
        <a href="<?= base_url('database_info.php?action=import'); ?>" 
           class="btn btn-primary btn-confirm-delete"
           data-confirm="Tindakan ini akan membuat/mereset tabel `db_perpajakan` dan mengisi data dummy awal. Lanjutkan?">
          <i class="bi bi-database-fill-gear me-1"></i> Jalankan Inisialisasi Database
        </a>
      </div>
    </div>

    <!-- Status Tabel -->
    <div class="row g-3 mb-4">
      <?php
      $table_labels = [
          'wajib_pajak'      => ['label' => 'Wajib Pajak', 'icon' => 'bi-people', 'badge' => 'bg-primary'],
          'jenis_pajak'      => ['label' => 'Jenis Pajak', 'icon' => 'bi-receipt', 'badge' => 'bg-info text-dark'],
          'petugas_pajak'    => ['label' => 'Petugas Pajak', 'icon' => 'bi-person-badge', 'badge' => 'bg-secondary'],
          'pelaporan_spt'    => ['label' => 'Pelaporan SPT', 'icon' => 'bi-file-earmark-check', 'badge' => 'bg-success'],
          'pembayaran_pajak' => ['label' => 'Pembayaran Kas', 'icon' => 'bi-wallet2', 'badge' => 'bg-warning text-dark'],
      ];
      foreach ($table_labels as $tbl_name => $info):
          $count = $tables_status[$tbl_name] ?? 0;
      ?>
        <div class="col-6 col-md-4 col-xl">
          <div class="app-card text-center p-3 mb-0">
            <i class="bi <?= $info['icon']; ?> fs-3 text-primary mb-1 d-block"></i>
            <div class="fw-bold text-dark small"><?= $info['label']; ?></div>
            <code class="small text-muted d-block mb-1"><?= $tbl_name; ?></code>
            <span class="badge <?= $info['badge']; ?>"><?= $count; ?> Baris Data</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ERD Relasi Diagram Explanation -->
    <div class="app-card mb-4">
      <div class="app-card-header">
        <h2 class="app-card-title">
          <i class="bi bi-diagram-3-fill text-primary"></i>
          Relasi Skema Database (Foreign Key)
        </h2>
      </div>
      <div class="app-card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="p-3 border rounded-3 bg-light h-100">
              <h6 class="fw-bold text-primary mb-2">1. wajib_pajak</h6>
              <small class="text-muted d-block mb-2">Menyimpan data identitas pembayar pajak (OP &amp; Badan).</small>
              <div class="font-monospace small">
                &bull; <strong>id</strong> (PK)<br>
                &bull; npwp (UNIQUE)<br>
                &bull; nik (UNIQUE)<br>
                &bull; nama_wajib_pajak<br>
                &bull; jenis_wp [OP, Badan]<br>
                &bull; alamat, no_telepon, email<br>
                &bull; tanggal_daftar
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="p-3 border rounded-3 bg-light h-100">
              <h6 class="fw-bold text-primary mb-2">2. jenis_pajak</h6>
              <small class="text-muted d-block mb-2">Menyimpan regulasi tarif pajak yang berlaku.</small>
              <div class="font-monospace small">
                &bull; <strong>id</strong> (PK)<br>
                &bull; kode_pajak (UNIQUE)<br>
                &bull; nama_pajak<br>
                &bull; tarif_persen (DECIMAL)<br>
                &bull; keterangan
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="p-3 border rounded-3 bg-light h-100">
              <h6 class="fw-bold text-primary mb-2">3. petugas_pajak</h6>
              <small class="text-muted d-block mb-2">Menyimpan akun fungsional pemeriksa dan AR DJP.</small>
              <div class="font-monospace small">
                &bull; <strong>id</strong> (PK)<br>
                &bull; nip (UNIQUE)<br>
                &bull; nama_petugas<br>
                &bull; jabatan, username, password
              </div>
            </div>
          </div>

          <div class="col-md-6 mt-3">
            <div class="p-3 border rounded-3 bg-light h-100">
              <h6 class="fw-bold text-success mb-2">4. pelaporan_spt (Transaksi Utama)</h6>
              <small class="text-muted d-block mb-2">Menghubungkan wajib pajak, jenis pajak, dan petugas pajak.</small>
              <div class="font-monospace small">
                &bull; <strong>id</strong> (PK)<br>
                &bull; nomor_spt (UNIQUE)<br>
                &bull; <strong>id_wajib_pajak</strong> (FK &rarr; wajib_pajak.id)<br>
                &bull; <strong>id_jenis_pajak</strong> (FK &rarr; jenis_pajak.id)<br>
                &bull; <strong>id_petugas</strong> (FK &rarr; petugas_pajak.id)<br>
                &bull; masa_pajak, tahun_pajak<br>
                &bull; dasar_pengenaan_pajak<br>
                &bull; <strong>pajak_terutang</strong> (Hasil kalkulasi DPP &times; Tarif)<br>
                &bull; tanggal_lapor, status_lapor
              </div>
            </div>
          </div>

          <div class="col-md-6 mt-3">
            <div class="p-3 border rounded-3 bg-light h-100">
              <h6 class="fw-bold text-warning mb-2">5. pembayaran_pajak (Billing Kas Negara)</h6>
              <small class="text-muted d-block mb-2">Menyimpan rekaman pembayaran dan kode billing MPN G3.</small>
              <div class="font-monospace small">
                &bull; <strong>id</strong> (PK)<br>
                &bull; kode_billing (15 Digit UNIQUE)<br>
                &bull; <strong>id_pelaporan</strong> (FK &rarr; pelaporan_spt.id, CASCADE)<br>
                &bull; jumlah_bayar (DECIMAL)<br>
                &bull; metode_bayar<br>
                &bull; tanggal_bayar<br>
                &bull; status_bayar [LUNAS / BELUM LUNAS]
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SQL Source Preview -->
    <div class="app-card">
      <div class="app-card-header">
        <h2 class="app-card-title">
          <i class="bi bi-filetype-sql text-primary"></i>
          File database.sql
        </h2>
        <span class="small text-muted">Lokasi: <code>c:\laragon\www\databases_pajak\database.sql</code></span>
      </div>
      <div class="app-card-body">
        <p class="small text-muted mb-2">Anda dapat mengeksekusi file SQL ini secara langsung melalui terminal MySQL, HeidiSQL, atau phpMyAdmin di Laragon.</p>
        <pre class="p-3 bg-dark text-light rounded-3 small mb-0" style="max-height: 400px; overflow-y: auto;"><code><?= htmlspecialchars(file_get_contents(__DIR__ . '/database.sql')); ?></code></pre>
      </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
