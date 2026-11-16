/**
 * SISTEM INFORMASI PERPAJAKAN (SIPAJAK)
 * Frontend Interactivity & Auto-Calculation
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Sidebar Toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      if (backdrop) backdrop.classList.toggle('show');
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    });
  }

  // 2. Real-time Auto-Calculation for Pelaporan SPT
  const selectJenisPajak = document.getElementById('id_jenis_pajak');
  const inputTarif = document.getElementById('tarif_persen_display');
  const hiddenTarif = document.getElementById('tarif_persen');
  const inputDPP = document.getElementById('dasar_pengenaan_pajak');
  const inputPajakTerutang = document.getElementById('pajak_terutang');
  const previewPajakTerutang = document.getElementById('preview_pajak_terutang');

  function calculatePajakTerutang() {
    if (!inputDPP || !inputPajakTerutang) return;

    // Ambil tarif persen dari dropdown yang dipilih atau input tarif
    let tarif = 0;
    if (selectJenisPajak && selectJenisPajak.selectedIndex >= 0) {
      const selectedOption = selectJenisPajak.options[selectJenisPajak.selectedIndex];
      const dataTarif = selectedOption.getAttribute('data-tarif');
      if (dataTarif !== null && dataTarif !== '') {
        tarif = parseFloat(dataTarif);
        if (inputTarif) inputTarif.value = tarif + ' %';
        if (hiddenTarif) hiddenTarif.value = tarif;
      }
    } else if (hiddenTarif) {
      tarif = parseFloat(hiddenTarif.value) || 0;
    }

    // Ambil DPP (Dasar Pengenaan Pajak)
    const dppValue = parseFloat(inputDPP.value) || 0;

    // Hitung Pajak Terutang = DPP * (Tarif / 100)
    const pajakTerutang = (dppValue * (tarif / 100));
    
    // Bulatkan hingga 2 desimal atau integer sesuai standar
    inputPajakTerutang.value = pajakTerutang.toFixed(2);

    if (previewPajakTerutang) {
      previewPajakTerutang.textContent = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
      }).format(pajakTerutang);
    }
  }

  if (selectJenisPajak) {
    selectJenisPajak.addEventListener('change', calculatePajakTerutang);
  }

  if (inputDPP) {
    inputDPP.addEventListener('input', calculatePajakTerutang);
    // Jalankan sekali saat load jika sedang dalam mode edit
    calculatePajakTerutang();
  }

  // 3. Confirm Delete Dialog Helper
  const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
  deleteButtons.forEach(button => {
    button.addEventListener('click', function (e) {
      const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // 4. Auto dismiss alerts after 5 seconds
  const autoAlerts = document.querySelectorAll('.alert-dismissible');
  autoAlerts.forEach(alert => {
    setTimeout(() => {
      try {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      } catch (e) {
        // Fallback jika bootstrap instance tidak langsung tersedia
        alert.style.display = 'none';
      }
    }, 6000);
  });
});
