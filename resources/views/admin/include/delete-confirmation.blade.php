<dialog class="admin-confirm-dialog" id="adminConfirmDialog" aria-labelledby="adminConfirmTitle" aria-describedby="adminConfirmMessage">
    <div class="admin-confirm-icon" aria-hidden="true"><i class="ri-delete-bin-6-line"></i></div>
    <h2 id="adminConfirmTitle">Confirm deletion</h2>
    <p id="adminConfirmMessage">This item will be permanently deleted. This action cannot be undone.</p>
    <div class="admin-confirm-actions">
        <button type="button" class="admin-confirm-cancel" data-confirm-cancel>Cancel</button>
        <button type="button" class="admin-confirm-delete" data-confirm-submit><i class="ri-delete-bin-line"></i> Delete</button>
    </div>
</dialog>
<style>
    .admin-confirm-dialog{width:min(420px,calc(100% - 32px));max-width:calc(100% - 32px);padding:28px;border:1px solid #e1e8ed;border-radius:20px;background:#fff;color:#173640;box-shadow:0 24px 80px rgba(15,36,48,.25);text-align:center}
    .admin-confirm-dialog[open]{position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%)}
    .admin-confirm-dialog::backdrop{background:rgba(7,24,33,.58);backdrop-filter:blur(3px)}
    .admin-confirm-icon{display:grid;width:54px;height:54px;place-items:center;margin:0 auto 14px;border-radius:17px;background:#fff0f0;color:#b43b46;font-size:24px}
    .admin-confirm-dialog h2{margin:0;font-size:19px}.admin-confirm-dialog p{margin:9px 0 22px;color:#738392;font-size:13px;line-height:1.6}
    .admin-confirm-actions{display:flex;justify-content:center;gap:10px}.admin-confirm-actions button{min-height:42px;padding:0 18px;border-radius:10px;font:inherit;font-size:12px;font-weight:700;cursor:pointer}
    .admin-confirm-cancel{border:1px solid #e1e8ed;background:#fff;color:#173640}.admin-confirm-delete{display:inline-flex;align-items:center;gap:7px;border:1px solid #b43b46;background:#b43b46;color:#fff}
    @media(max-width:420px){.admin-confirm-dialog{padding:22px}.admin-confirm-actions button{flex:1}}
</style>
<script>
    (function () {
        if (window.__adminDeleteConfirmBound) return;
        window.__adminDeleteConfirmBound = true;
        var activeForm = null;
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form');
            if (!form) return;
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }
            var methodField = form.querySelector('input[name="_method"]');
            var method = (methodField ? methodField.value : form.method).toUpperCase();
            var path = new URL(form.action, window.location.href).pathname;
            if (!(form.hasAttribute('data-confirm-modal') || method === 'DELETE' || /(?:^|\/)(?:destroy|delete)(?:\/|$)/i.test(path))) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            var dialog = document.getElementById('adminConfirmDialog');
            if (!dialog) {
                console.error('[admin-delete-confirmation] Confirmation dialog is missing from the current page.');
                window.alert('The delete confirmation could not be opened. Please reload this page and try again.');
                return;
            }
            activeForm = form;
            document.getElementById('adminConfirmTitle').textContent = form.dataset.confirmTitle || 'Delete this item?';
            document.getElementById('adminConfirmMessage').textContent = form.dataset.confirmMessage || 'This item will be permanently deleted. This action cannot be undone.';
            dialog.showModal();
        }, true);
        document.addEventListener('click', function (event) {
            var dialog = document.getElementById('adminConfirmDialog');
            if (!dialog) return;
            if (event.target.closest('[data-confirm-cancel]')) {
                activeForm = null;
                dialog.close();
                return;
            }
            if (event.target.closest('[data-confirm-submit]')) {
                if (!activeForm) return;
                var form = activeForm;
                activeForm = null;
                dialog.close();
                form.dataset.confirmed = 'true';
                form.requestSubmit();
                return;
            }
            if (event.target === dialog) {
                activeForm = null;
                dialog.close();
            }
        }, true);
    })();
</script>
