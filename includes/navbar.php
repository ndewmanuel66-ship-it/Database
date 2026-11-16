<?php
// includes/navbar.php
?>
<header class="top-navbar">
  <div class="d-flex align-items-center gap-3">
    <button class="btn btn-outline-secondary d-lg-none" type="button" id="sidebarToggle">
      <i class="bi bi-list fs-5"></i>
    </button>
    <div class="d-none d-sm-block">
      <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($page_title ?? 'SIPAJAK'); ?></h6>
      <small class="text-muted">Kementerian Keuangan RI - Direktorat Jenderal Pajak</small>
    </div>
  </div>

  <div class="d-flex align-items-center gap-3">
    <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border">
      <i class="bi bi-calendar3 text-primary"></i>
      <span class="small fw-medium text-secondary"><?= date('l, d F Y'); ?></span>
    </div>

    <!-- Petugas Profile -->
    <div class="dropdown">
      <button class="btn btn-light border d-flex align-items-center gap-2 py-1 px-2 rounded-pill" type="button" data-bs-toggle="dropdown">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: 700;">
          AR
        </div>
        <div class="text-start d-none d-md-block pe-1">
          <div class="small fw-bold text-dark lh-1">Budi Santoso, S.E.</div>
          <small class="text-muted" style="font-size: 0.7rem;">Account Representative</small>
        </div>
        <i class="bi bi-chevron-down text-muted small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
        <li><h6 class="dropdown-header">Petugas Pajak Aktif</h6></li>
        <li><div class="dropdown-item-text small text-muted">NIP: 198507122008011002</div></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-primary" href="<?= base_url('pelaporan_spt/create.php'); ?>"><i class="bi bi-plus-circle me-2"></i>Input SPT Baru</a></li>
      </ul>
    </div>
  </div>
</header>
