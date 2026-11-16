<?php
// includes/sidebar.php
$current_page = $_SERVER['PHP_SELF'];
?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-icon">
      <i class="bi bi-bank2"></i>
    </div>
    <div>
      <h1 class="brand-title">SIPAJAK</h1>
      <span class="brand-subtitle">Sistem Info Perpajakan</span>
    </div>
  </div>

  <div class="sidebar-menu">
    <div class="sidebar-header">Utama</div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= (basename($current_page) == 'index.php' && strpos($current_page, 'wajib_pajak') === false && strpos($current_page, 'jenis_pajak') === false && strpos($current_page, 'pelaporan_spt') === false && strpos($current_page, 'pembayaran') === false) ? 'active' : ''; ?>" href="<?= base_url('index.php'); ?>">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-header mt-3">Master Data</div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'wajib_pajak') !== false ? 'active' : ''; ?>" href="<?= base_url('wajib_pajak/index.php'); ?>">
          <i class="bi bi-people-fill"></i>
          <span>Wajib Pajak (WP)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'jenis_pajak') !== false ? 'active' : ''; ?>" href="<?= base_url('jenis_pajak/index.php'); ?>">
          <i class="bi bi-receipt-cutoff"></i>
          <span>Jenis Pajak & Tarif</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-header mt-3">Transaksi Perpajakan</div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'pelaporan_spt') !== false ? 'active' : ''; ?>" href="<?= base_url('pelaporan_spt/index.php'); ?>">
          <i class="bi bi-file-earmark-spreadsheet-fill"></i>
          <span>Pelaporan SPT</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'pembayaran') !== false ? 'active' : ''; ?>" href="<?= base_url('pembayaran/index.php'); ?>">
          <i class="bi bi-wallet2"></i>
          <span>Pembayaran & Billing</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-header mt-3">Sistem & Panduan</div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'database_info.php') !== false ? 'active' : ''; ?>" href="<?= base_url('database_info.php'); ?>">
          <i class="bi bi-database-check"></i>
          <span>Struktur Database</span>
        </a>
      </li>
    </ul>

    <div class="px-3 mt-4 text-center">
      <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
        <small class="d-block text-white fw-semibold mb-1">DJP Virtual Terminal</small>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
          <i class="bi bi-dot"></i> Online & Terkoneksi
        </span>
      </div>
    </div>
  </div>
</aside>
