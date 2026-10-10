@php
    $quickIcons = config('product_icons.choices');
    $quickIconDefault = config('product_icons.choices.0.value');
@endphp

<style>
    .qt-select-row { display:flex; align-items:center; gap:8px }
    .qt-select-row select { flex:1; min-width:0; height:42px; min-height:42px; padding-top:0; padding-bottom:0; background-position:right 14px 50% }
    .qt-add-btn { flex:0 0 42px; min-height:42px; border:1px solid var(--border,#dfe7ee); border-radius:12px; background:var(--card,#fff); color:var(--forest,#0e5a75); font:inherit; font-size:18px; cursor:pointer; transition:.2s }
    .qt-add-btn:hover { border-color:var(--forest,#0e5a75); background:var(--lime-soft,#e4f6fb) }
    .qt-modal { position:fixed; inset:0; z-index:1200; display:grid; place-items:center; padding:20px; background:rgba(15,23,42,.62); opacity:0; visibility:hidden; transition:.3s }
    .qt-modal.show { opacity:1; visibility:visible }
    .qt-box { width:min(760px,calc(100vw - 40px)); max-height:90vh; border:1px solid var(--border,#e8edf3); border-radius:22px; background:var(--card,#fff); box-shadow:0 30px 70px rgba(0,0,0,.35); transform:translateY(14px) scale(.95); transition:.35s cubic-bezier(.22,1,.36,1); position:relative; overflow:hidden; display:flex; flex-direction:column }
    .qt-modal.show .qt-box { transform:none }
    .qt-head { flex-shrink:0; padding:22px 72px 20px 26px; background:linear-gradient(115deg,#0a3346 0%,#0e5a75 52%,#1a83ab 100%); position:relative; overflow:hidden }
    .qt-head::before,.qt-head::after { content:''; position:absolute; border-radius:50%; pointer-events:none }
    .qt-head::before { right:-40px; top:-64px; width:160px; height:160px; background:rgba(255,255,255,.08) }
    .qt-head::after { right:70px; bottom:-52px; width:90px; height:90px; background:rgba(255,255,255,.05) }
    .qt-head h3 { margin:0; color:#fff; font-size:21px; font-weight:800; letter-spacing:-.3px; position:relative; z-index:1 }
    .qt-head p { margin:6px 0 0; color:rgba(255,255,255,.78); font-size:12px; position:relative; z-index:1; display:flex; align-items:center; gap:7px }
    .qt-head p i { color:var(--lime); font-size:13px }
    .qt-close { position:absolute; top:16px; right:16px; z-index:2; width:40px; height:40px; border:0; border-radius:12px; background:#0c2e3c; color:#fff; font-size:17px; display:grid; place-items:center; cursor:pointer; box-shadow:0 6px 16px rgba(0,0,0,.25); transition:.25s }
    .qt-close:hover { background:#dc2626; transform:rotate(90deg) }
    .qt-body { padding:26px 26px 24px; overflow-y:auto; overflow-x:hidden }
    .qt-field { margin:0 0 22px }
    .qt-field>label { display:flex; align-items:center; gap:8px; margin-bottom:10px; color:#2f98a1; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase }
    body.dark .qt-field>label { color:var(--lime) }
    .qt-label-icon { width:22px; height:22px; min-width:22px; border-radius:7px; background:rgba(56,170,180,.13); color:#2f98a1; display:grid; place-items:center; font-size:11px }
    body.dark .qt-label-icon { background:rgba(62,197,224,.12); color:var(--lime) }
    .qt-field input,.qt-field textarea { width:100%; min-height:54px; border:1.5px solid transparent; border-radius:14px; background:#f4f7f8; color:var(--text); padding:15px 18px; outline:none; font:600 13.5px 'Plus Jakarta Sans',sans-serif; resize:none }
    body.dark .qt-field input,body.dark .qt-field textarea { background:rgba(255,255,255,.05) }
    .qt-field input::placeholder,.qt-field textarea::placeholder { color:#9fb0c0; font-weight:500 }
    .qt-field input:focus,.qt-field textarea:focus { border-color:var(--lime); background:var(--card); box-shadow:0 0 0 4px rgba(62,197,224,.14) }
    .qt-icon-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-top:4px }
    .qt-icon-option { position:relative; min-width:0; min-height:95px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:7px; padding:12px 6px; border:1.5px solid transparent; border-radius:16px; background:#f4f7f8; cursor:pointer; transition:.25s; box-shadow:0 3px 10px rgba(16,24,40,.05) }
    body.dark .qt-icon-option { background:rgba(255,255,255,.05) }
    .qt-icon-option .qt-icon { width:46px; height:46px; position:relative; display:grid; place-items:center; border-radius:13px; background:var(--lime-soft,#e4f6fb) }
    .qt-icon-option .qt-icon i { color:var(--forest); font-size:24px }
    body.dark .qt-icon-option .qt-icon i { color:var(--lime) }
    .qt-icon-option small { color:var(--muted); font-size:8.5px; font-weight:800; letter-spacing:.5px; text-align:center; text-transform:uppercase }
    .qt-icon-option:hover,.qt-icon-option.selected { border-color:var(--lime); background:linear-gradient(160deg,var(--lime-soft),var(--card)); box-shadow:0 0 0 4px rgba(62,197,224,.16),0 12px 26px rgba(62,197,224,.2) }
    .qt-icon-option.selected::after { content:'✓'; position:absolute; top:6px; right:6px; width:18px; height:18px; border-radius:50%; background:linear-gradient(135deg,var(--lime),var(--forest-2)); color:#fff; font-size:11px; font-weight:800; display:grid; place-items:center }
    .qt-quick-row { display:grid; grid-template-columns:repeat(4,1fr); gap:9px; margin-top:12px }
    .qt-quick-row button { min-height:44px; border:1.5px solid transparent; border-radius:12px; background:#f4f7f8; color:var(--muted); font:800 12.5px 'Plus Jakarta Sans',sans-serif; cursor:pointer; transition:.2s }
    .qt-quick-row button:hover { border-color:var(--lime); background:var(--card); color:var(--forest); transform:translateY(-2px) }
    .qt-active { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:22px; padding:16px 18px; border-radius:14px; background:#f4f7f8 }
    body.dark .qt-active,.qt-modal body.dark .qt-active { background:rgba(255,255,255,.05) }
    .qt-active b { font-size:13.5px }
    .qt-active span { display:block; margin-top:2px; color:var(--muted); font-size:11.5px; font-weight:600 }
    .qt-active input { width:19px; height:19px; accent-color:var(--forest) }
    .qt-actions { display:flex; gap:12px }
    .qt-actions button { min-height:50px; padding:14px 22px; border:0; border-radius:13px; font:800 13.5px 'Plus Jakarta Sans',sans-serif; cursor:pointer; transition:.2s }
    .qt-cancel { flex:1; border:1px solid var(--border)!important; background:#f4f7f8; color:var(--text) }
    .qt-save { flex:2; background:linear-gradient(135deg,var(--forest),var(--forest-2)); color:#fff; box-shadow:0 8px 20px rgba(14,90,117,.35) }
    .qt-actions button:disabled { opacity:.6; cursor:wait }
    @media(max-width:600px) { .qt-modal{padding:10px}.qt-box{width:calc(100vw - 20px);border-radius:18px}.qt-body{padding:20px 18px}.qt-icon-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.qt-actions{flex-direction:column-reverse} }
</style>

<div class="qt-modal" id="quickTaxonomyModal" role="dialog" aria-modal="true" aria-labelledby="quickTaxonomyTitle"
    onclick="if(event.target === this) quickTaxonomyClose()">
    <div class="qt-box">
        <div class="qt-head">
            <button type="button" class="qt-close" aria-label="Close" onclick="quickTaxonomyClose()"><i class="ri-close-line"></i></button>
            <h3 id="quickTaxonomyTitle">Add Category</h3>
            <p><i class="ri-information-line"></i><span id="quickTaxonomyHelp">Create a new product category</span></p>
        </div>
        <div class="qt-body">
            <form id="quickTaxonomyForm" onsubmit="return quickTaxonomySave(event)">
                <div id="quickCategoryFields">
                    <div class="qt-field">
                        <label for="quickCategoryName"><span class="qt-label-icon"><i class="ri-price-tag-3-line"></i></span>Category Name *</label>
                        <input type="text" id="quickCategoryName" maxlength="100" placeholder="e.g. Perfume" required>
                    </div>
                    <div class="qt-field">
                        <label><span class="qt-label-icon"><i class="ri-magic-line"></i></span>Icon — Pick One</label>
                        <div class="qt-icon-grid" id="quickCategoryIcons">
                            @foreach($quickIcons as $icon)
                            <button type="button" class="qt-icon-option" data-value="{{ $icon['value'] }}" onclick="quickTaxonomyPickIcon(this)">
                                <span class="qt-icon"><i class="{{ $icon['class'] }}" aria-hidden="true"></i></span>
                                <small>{{ $icon['label'] }}</small>
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="quickCategoryIcon" value="{{ $quickIconDefault }}">
                    </div>
                    <div class="qt-field">
                        <label for="quickCategoryDescription"><span class="qt-label-icon"><i class="ri-file-list-3-line"></i></span>Description</label>
                        <textarea id="quickCategoryDescription" rows="3" maxlength="255" placeholder="Short description (optional)"></textarea>
                    </div>
                </div>
                <div id="quickSizeFields" hidden>
                    <div class="qt-field">
                        <label for="quickSizeName"><span class="qt-label-icon"><i class="ri-price-tag-3-line"></i></span>Size Label *</label>
                        <input type="text" id="quickSizeName" maxlength="50" placeholder="e.g. 50 ML">
                    </div>
                    <div class="qt-field">
                        <label for="quickSizeMl"><span class="qt-label-icon"><i class="ri-flask-line"></i></span>Volume (ML) *</label>
                        <input type="number" id="quickSizeMl" min="1" max="100000" step="1" inputmode="numeric" placeholder="e.g. 50">
                        <div class="qt-quick-row">
                            <button type="button" onclick="quickTaxonomySetSize(10)">10 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(30)">30 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(50)">50 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(100)">100 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(250)">250 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(500)">500 ML</button>
                            <button type="button" onclick="quickTaxonomySetSize(1000)">1 L</button>
                        </div>
                    </div>
                </div>
                <div class="qt-active">
                    <div><b>Visible on store</b><span id="quickActiveHelp">Customers can see this item</span></div>
                    <input type="checkbox" id="quickTaxonomyActive" checked aria-label="Visible on store">
                </div>
                <div class="qt-actions">
                    <button type="button" class="qt-cancel" onclick="quickTaxonomyClose()"><i class="ri-close-line"></i> Cancel</button>
                    <button type="submit" class="qt-save" id="quickTaxonomySave"><i class="ri-save-3-line"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var quickTaxonomyType = 'category';
    var quickTaxonomyTarget = null;
    var quickTaxonomyIcon = @json($quickIconDefault);
    var quickCategoryStore = @json(route('admin.categories.store'));
    var quickSizeStore = @json(route('admin.sizes.store'));

    function quickTaxonomyOpen(type, targetId) {
        quickTaxonomyType = type;
        quickTaxonomyTarget = document.getElementById(targetId);
        if (!quickTaxonomyTarget) return;
        var isCategory = type === 'category';
        document.getElementById('quickTaxonomyTitle').textContent = isCategory ? 'Add category' : 'Add size';
        document.getElementById('quickTaxonomyHelp').textContent = isCategory
            ? 'Create a category without leaving this product form.'
            : 'Create a size without leaving this product form.';
        document.getElementById('quickCategoryFields').hidden = !isCategory;
        document.getElementById('quickSizeFields').hidden = isCategory;
        document.getElementById('quickCategoryName').required = isCategory;
        document.getElementById('quickSizeName').required = !isCategory;
        document.getElementById('quickSizeMl').required = !isCategory;
        document.getElementById('quickActiveHelp').textContent = isCategory
            ? 'Customers can see this category'
            : 'Customers can pick this size';
        document.getElementById('quickTaxonomyForm').reset();
        document.getElementById('quickTaxonomyActive').checked = true;
        quickTaxonomyIcon = @json($quickIconDefault);
        document.getElementById('quickCategoryIcon').value = quickTaxonomyIcon;
        document.querySelectorAll('#quickCategoryIcons .qt-icon-option').forEach(function(button) {
            button.classList.toggle('selected', button.dataset.value === quickTaxonomyIcon);
        });
        document.body.classList.add('nb-lock');
        document.getElementById('quickTaxonomyModal').classList.add('show');
        setTimeout(function() {
            document.getElementById(isCategory ? 'quickCategoryName' : 'quickSizeName').focus();
        }, 0);
    }

    function quickTaxonomyClose() {
        var modal = document.getElementById('quickTaxonomyModal');
        if (modal) modal.classList.remove('show');
        var productModal = document.getElementById('prodModal');
        if (!productModal || !productModal.classList.contains('show')) document.body.classList.remove('nb-lock');
        quickTaxonomyTarget = null;
    }

    function quickTaxonomyPickIcon(button) {
        quickTaxonomyIcon = button.dataset.value || '';
        document.getElementById('quickCategoryIcon').value = quickTaxonomyIcon;
        document.querySelectorAll('#quickCategoryIcons .qt-icon-option').forEach(function(option) {
            option.classList.toggle('selected', option === button);
        });
    }

    function quickTaxonomySetSize(ml) {
        document.getElementById('quickSizeMl').value = ml;
        document.getElementById('quickSizeName').value = ml >= 1000 ? (ml / 1000) + ' Litre' : ml + ' ML';
    }

    function quickTaxonomyToast(icon, message) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true,
                icon: icon,
                title: message
            });
        } else {
            alert(message);
        }
    }

    function quickTaxonomySave(event) {
        event.preventDefault();
        if (!quickTaxonomyTarget) return false;
        var target = quickTaxonomyTarget;
        var isCategory = quickTaxonomyType === 'category';
        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var form = new FormData();
        form.append('_token', token);
        form.append('is_active', document.getElementById('quickTaxonomyActive').checked ? '1' : '0');
        if (isCategory) {
            form.append('name', document.getElementById('quickCategoryName').value.trim());
            form.append('icon', quickTaxonomyIcon);
            form.append('description', document.getElementById('quickCategoryDescription').value.trim());
        } else {
            form.append('name', document.getElementById('quickSizeName').value.trim());
            form.append('ml', document.getElementById('quickSizeMl').value);
        }

        var saveButton = document.getElementById('quickTaxonomySave');
        saveButton.disabled = true;
        fetch(isCategory ? quickCategoryStore : quickSizeStore, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: form
        }).then(function(response) {
            return response.json().catch(function() { return {}; }).then(function(data) {
                if (!response.ok) {
                    var message = data.message || 'Could not save this item.';
                    if (data.errors) {
                        var errors = Object.keys(data.errors);
                        if (errors.length) message = data.errors[errors[0]][0];
                    }
                    throw new Error(message);
                }
                return data;
            });
        }).then(function(data) {
            var item = isCategory ? data.category : data.size;
            if (!item || !item.id) throw new Error('The server response did not include the saved item.');
            document.querySelectorAll(isCategory ? 'select[data-quick-category]' : 'select[data-quick-size]').forEach(function(select) {
                if (!select.querySelector('option[value="' + item.id + '"]')) {
                    var label = isCategory ? item.name : (item.name + ' (' + Number(item.ml).toLocaleString() + ' ml)');
                    select.add(new Option(label, item.id));
                }
            });
            target.value = String(item.id);
            target.dispatchEvent(new Event('change', { bubbles: true }));
            if (quickTaxonomyTarget === target && quickTaxonomyType === (isCategory ? 'category' : 'size')) {
                quickTaxonomyClose();
            }
            quickTaxonomyToast('success', data.message || 'Saved successfully.');
        }).catch(function(error) {
            quickTaxonomyToast('error', error.message || 'Something went wrong.');
        }).finally(function() {
            saveButton.disabled = false;
        });
        return false;
    }

    if (!window.__quickTaxonomyEscapeBound) {
        window.__quickTaxonomyEscapeBound = true;
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') quickTaxonomyClose();
        });
    }
</script>
