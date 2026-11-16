<?php
// includes/footer.php
?>
    </div> <!-- end content-body -->

    <footer class="bg-white border-top py-3 px-4 text-center text-md-between d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted mt-auto">
      <div>
        <strong>SIPAJAK</strong> &copy; <?= date('Y'); ?> Sistem Informasi Perpajakan. Dibuat dengan PHP Native & MySQL (PDO).
      </div>
      <div class="mt-2 mt-md-0">
        <span class="badge bg-light text-secondary border">Bootstrap 5.3</span>
        <span class="badge bg-light text-secondary border">PHP <?= phpversion(); ?></span>
      </div>
    </footer>
  </div> <!-- end main-wrapper -->

  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom App JS -->
  <script src="<?= base_url('assets/js/app.js'); ?>"></script>
</body>
</html>
