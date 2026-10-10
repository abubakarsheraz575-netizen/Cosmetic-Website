<style>
    .media-picker-modal{position:fixed;inset:0;z-index:100000;display:none;place-items:center;padding:18px;background:rgba(8,20,30,.62)}
    .media-picker-modal.open{display:grid}
    .media-picker-box{display:grid;grid-template-rows:auto auto minmax(0,1fr);gap:14px;width:min(920px,100%);max-height:min(760px,90vh);padding:20px;border-radius:18px;background:#fff;box-shadow:0 30px 90px #06121c66}
    .media-picker-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .media-picker-head h2{margin:0;color:#173640;font:800 18px 'Plus Jakarta Sans',sans-serif}
    .media-picker-close{width:36px;height:36px;border:0;border-radius:10px;background:#eff4f6;color:#375764;font-size:20px;cursor:pointer}
    .media-picker-search{width:100%;min-height:42px;padding:9px 12px;border:1px solid #dce6eb;border-radius:10px;font:inherit;font-size:12px}
    .media-picker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;overflow:auto;padding:2px}
    .media-picker-item{display:grid;gap:6px;padding:7px;border:1px solid #e0e8ec;border-radius:11px;background:#fff;text-align:left;cursor:pointer}
    .media-picker-item:hover{border-color:#1686a4;box-shadow:0 5px 16px #137f991f}
    .media-picker-item img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:7px}
    .media-picker-item span{overflow:hidden;color:#314e5d;font-size:10px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
    .media-picker-empty{grid-column:1/-1;padding:25px;border:1px dashed #d5e0e5;border-radius:11px;color:#72848e;text-align:center;font-size:12px}
    @media(max-width:600px){.media-picker-modal{padding:8px}.media-picker-box{max-height:94vh;padding:13px}.media-picker-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<div class="media-picker-modal" id="mediaPickerModal" role="dialog" aria-modal="true" aria-labelledby="mediaPickerTitle">
    <section class="media-picker-box">
        <header class="media-picker-head"><h2 id="mediaPickerTitle">Choose from Media library</h2><button type="button" class="media-picker-close" id="mediaPickerClose" aria-label="Close">&times;</button></header>
        <input class="media-picker-search" id="mediaPickerSearch" type="search" placeholder="Search local media..." aria-label="Search media">
        <div class="media-picker-grid" id="mediaPickerGrid" aria-live="polite"></div>
    </section>
</div>
<script>
    (function () {
        var modal = document.getElementById('mediaPickerModal');
        var search = document.getElementById('mediaPickerSearch');
        var grid = document.getElementById('mediaPickerGrid');
        var callback = null;
        var pickerUrl = @json(route('admin.media.picker'));
        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (character) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character];
            });
        }
        function loadImages() {
            grid.innerHTML = '<div class="media-picker-empty">Loading local media…</div>';
            fetch(pickerUrl + '?q=' + encodeURIComponent(search.value), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(function (response) {
                    if (!response.ok) throw new Error('Could not load the media library.');
                    return response.json();
                })
                .then(function (data) {
                    grid.replaceChildren();
                    if (!data.images || !data.images.length) {
                        grid.innerHTML = '<div class="media-picker-empty">No matching images. Upload files from Admin → Media first.</div>';
                        return;
                    }
                    data.images.forEach(function (image) {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'media-picker-item';
                        button.innerHTML = '<img src="' + escapeHtml(image.url) + '" alt=""><span>' + escapeHtml(image.name) + '</span>';
                        button.addEventListener('click', function () {
                            if (callback) callback(image);
                            closePicker();
                        });
                        grid.appendChild(button);
                    });
                })
                .catch(function (error) {
                    grid.innerHTML = '<div class="media-picker-empty">' + escapeHtml(error.message) + '</div>';
                });
        }
        function closePicker() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
        window.openMediaPicker = function (onSelect) {
            callback = onSelect;
            search.value = '';
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
            loadImages();
            search.focus();
        };
        document.getElementById('mediaPickerClose').addEventListener('click', closePicker);
        modal.addEventListener('click', function (event) { if (event.target === modal) closePicker(); });
        search.addEventListener('input', function () {
            window.clearTimeout(search.__mediaTimer);
            search.__mediaTimer = window.setTimeout(loadImages, 180);
        });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closePicker(); });
    })();
</script>
