{{-- Satu modal konfirmasi untuk semua form yang punya data-confirm. Sudah dipasang sekali di layouts.app.
     Pemakaian di halaman cukup atribut form:
       <form method="POST" action="..." data-confirm="Arsipkan kasus AK-2026-0001?"
             data-confirm-title="Arsipkan kasus ini?" data-confirm-label="Ya, arsipkan"> @csrf ... </form>
     data-confirm-title dan data-confirm-label opsional. Teks diisi lewat textContent, jadi aman dari HTML. --}}
<div class="modal fade" id="confirm-modal" tabindex="-1" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="confirm-modal-title">Konfirmasi tindakan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-muted" id="confirm-modal-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-confirm-ok>Ya, lanjutkan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('confirm-modal');
    var okButton = modal.querySelector('[data-confirm-ok]');
    var pending = null, submitter = null;

    // Fase capture: jalan sebelum listener lain di form, lalu hentikan submit sampai pengguna setuju
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.matches('form[data-confirm]')) return;
        if (form.dataset.confirmed) { delete form.dataset.confirmed; return; }

        event.preventDefault();
        event.stopPropagation();
        pending = form;
        submitter = event.submitter || null;
        document.getElementById('confirm-modal-title').textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
        document.getElementById('confirm-modal-message').textContent = form.dataset.confirm;
        okButton.textContent = form.dataset.confirmLabel || 'Ya, lanjutkan';
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }, true);

    okButton.addEventListener('click', function () {
        if (!pending) return;
        var form = pending;
        pending = null;
        form.dataset.confirmed = '1';
        bootstrap.Modal.getInstance(modal).hide();
        form.requestSubmit(submitter); // submitter ikut terkirim (name/value tombol)
    });

    modal.addEventListener('hidden.bs.modal', function () { pending = null; });
})();
</script>
