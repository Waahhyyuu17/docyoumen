(() => {
  const form = document.getElementById('kontakForm');
  if (!form) return;

  const btn = document.getElementById('kontakSubmitBtn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const originalLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    try {
      const res = await fetch('api/send_contact.php', {
        method: 'POST',
        body: new FormData(form),
      });
      const data = await res.json();

      if (data.success) {
        showToast('Pesan terkirim! Terima kasih sudah menghubungi.', 'success');
        form.reset();
      } else {
        showToast(data.message || 'Gagal mengirim pesan.', 'error');
      }
    } catch (err) {
      showToast('Terjadi kesalahan jaringan. Coba lagi.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = originalLabel;
    }
  });
})();
