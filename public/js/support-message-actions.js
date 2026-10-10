(function () {
    if (window.SupportMessageActions) return;

    var instances = new WeakMap();
    var activeMenu = null;

    var styles = document.createElement('style');
    styles.textContent = [
        '.support-actions-menu{position:fixed;z-index:2147483000;width:214px;max-height:min(72vh,520px);overflow:auto;padding:6px;border:1px solid #e2e8ee;border-radius:13px;background:#fff;color:#263547;box-shadow:0 18px 48px rgba(15,23,42,.2);font:500 13px/1.35 system-ui,sans-serif}',
        '.support-actions-menu[hidden]{display:none}',
        '.support-actions-menu button{display:flex;align-items:center;gap:11px;width:100%;min-height:37px;padding:8px 10px;border:0;border-radius:8px;background:transparent;color:inherit;text-align:left;font:inherit;cursor:pointer}',
        '.support-actions-menu button:hover,.support-actions-menu button:focus-visible{outline:0;background:#eef8f7;color:#116778}',
        '.support-actions-menu button i{width:17px;font-size:16px}',
        '.support-actions-menu .support-menu-danger{color:#b42318}',
        '.support-actions-menu hr{height:1px;margin:5px 4px;border:0;background:#e9edf1}',
        '.support-message-menu-trigger{display:none;position:absolute;top:3px;right:3px;z-index:2;width:29px;height:29px;place-items:center;border:1px solid rgba(22,35,60,.1);border-radius:9px;background:rgba(255,255,255,.94);color:#49606d;font-size:16px;cursor:pointer}',
        '.support-message:hover .support-message-menu-trigger,.chat-message:hover .support-message-menu-trigger,.support-message-menu-trigger:focus-visible{display:grid}',
        '.support-message,.chat-message{position:relative}',
        '.support-selected .support-bubble,.chat-message.support-selected .chat-bubble{outline:2px solid #38aab4;outline-offset:2px}',
        '.support-message-quote{display:flex;align-items:center;gap:9px;min-width:130px;margin:0 0 8px;padding:7px 9px;border-left:3px solid #2b9caa;border-radius:6px;background:rgba(255,255,255,.64);color:#405565}',
        '.support-message-quote-copy{min-width:0;flex:1}',
        '.support-message-quote-name{display:block;overflow:hidden;color:#14798a;font-size:10px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}',
        '.support-message-quote-text{display:block;overflow:hidden;margin-top:2px;color:#607280;font-size:10px;text-overflow:ellipsis;white-space:nowrap}',
        '.support-message-quote img{width:42px;height:42px;flex:0 0 42px;object-fit:cover;border-radius:6px;background:#fff}',
        '.support-message-quote-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:6px;background:#edf4f5;color:#357b88;font-size:20px}',
        '.support-message-quote.deleted .support-message-quote-text{font-style:italic}',
        '.support-reply-preview{display:flex;align-items:center;gap:10px;margin:0 0 8px;padding:8px 10px;border-left:3px solid #2b9caa;border-radius:9px;background:#eff8f7;color:#405565}',
        '.support-reply-preview[hidden]{display:none}',
        '.support-reply-preview-copy{min-width:0;flex:1}',
        '.support-reply-preview-name{display:block;color:#14798a;font-size:10px;font-weight:800}',
        '.support-reply-preview-text{display:block;overflow:hidden;margin-top:2px;color:#607280;font-size:11px;text-overflow:ellipsis;white-space:nowrap}',
        '.support-reply-preview img{width:46px;height:46px;flex:0 0 46px;object-fit:cover;border-radius:7px;background:#fff}',
        '.support-reply-preview-icon{width:46px;height:46px;flex:0 0 46px;display:grid;place-items:center;border-radius:7px;background:#e1f0f0;color:#357b88;font-size:20px}',
        '.support-reply-preview button{width:30px;height:30px;flex:0 0 30px;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:#657784;font-size:18px;cursor:pointer}',
        '.support-reply-preview button:hover{background:rgba(20,121,138,.1)}',
        '.support-message-flags{display:flex;justify-content:flex-end;gap:5px;margin-top:4px;color:#398b91;font-size:10px}',
        '.support-message-flag{display:inline-flex;align-items:center;gap:3px}',
        '.support-selection-bar{position:sticky;top:0;z-index:3;display:flex;align-items:center;gap:8px;margin:0 0 10px;padding:8px 10px;border:1px solid #cde6e4;border-radius:10px;background:rgba(255,255,255,.96);box-shadow:0 5px 15px rgba(15,23,42,.08);font:600 12px system-ui,sans-serif}',
        '.support-selection-bar[hidden]{display:none}',
        '.support-selection-bar span{flex:1}',
        '.support-selection-bar button{padding:6px 9px;border:1px solid #dce8eb;border-radius:8px;background:#fff;color:#235f70;font:inherit;cursor:pointer}',
        '.support-input-dialog,.support-actions-dialog{position:fixed!important;inset:auto!important;left:50%!important;top:50%!important;transform:translate(-50%,-50%)!important;width:min(480px,calc(100vw - 32px));max-width:none;max-height:calc(100dvh - 32px);overflow:auto;margin:0!important;padding:0;border:1px solid #dbe5e9;border-radius:20px;background:#fff;color:#253245;box-shadow:0 24px 70px rgba(15,23,42,.28);font:500 14px/1.5 system-ui,sans-serif}',
        '.support-input-dialog::backdrop,.support-actions-dialog::backdrop{background:rgba(15,23,42,.5);backdrop-filter:blur(5px)}',
        '.support-actions-dialog form{padding:22px}',
        '.support-actions-dialog h2{margin:3px 0 0;color:#1b3544;font-size:19px;line-height:1.3}',
        '.support-actions-dialog p{margin:8px 0 16px;color:#64748b;font-size:12px;line-height:1.6}',
        '.support-delete-dialog-head{display:flex;align-items:center;gap:12px}',
        '.support-delete-dialog-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:13px;background:#fff1f0;color:#c0443d;font-size:20px}',
        '.support-dialog-eyebrow{color:#398d93;font-size:9px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase}',
        '.support-delete-options{display:grid;gap:9px}',
        '.support-delete-option{display:grid;grid-template-columns:38px minmax(0,1fr) 18px;align-items:center;gap:11px;width:100%;padding:11px 12px;border:1px solid #e3eaee;border-radius:14px;background:#fff;color:#253245;text-align:left;cursor:pointer;transition:border-color .18s,background .18s,transform .18s}',
        '.support-delete-option:hover,.support-delete-option:focus-visible{border-color:#72c5c6;background:#f4fbfa;outline:0;transform:translateY(-1px)}',
        '.support-delete-option.everyone:hover,.support-delete-option.everyone:focus-visible{border-color:#e5a4a0;background:#fff8f7}',
        '.support-delete-option-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#edf7f6;color:#287f85;font-size:17px}',
        '.support-delete-option.everyone .support-delete-option-icon{background:#fff0ee;color:#b83d35}',
        '.support-delete-option strong{display:block;font-size:12px}.support-delete-option small{display:block;margin-top:2px;color:#718091;font-size:10px;line-height:1.4}',
        '.support-delete-option>i:last-child{color:#84919d;font-size:16px}',
        '.support-dialog-footer{display:flex;justify-content:flex-end;margin-top:15px}',
        '.support-dialog-footer button,.support-confirm-actions button{min-height:38px;padding:9px 14px;border:1px solid #dce5e9;border-radius:10px;background:#fff;color:#516171;font:600 12px system-ui,sans-serif;cursor:pointer}',
        '.support-confirm-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}',
        '.support-confirm-actions button.danger{border-color:#b83d35;background:#b83d35;color:#fff}',
        '.support-dialog-footer button:hover,.support-confirm-actions button:not(.danger):hover{background:#f5f8fa}',
        '@keyframes supportDialogIn{from{opacity:0}to{opacity:1}}',
        '.support-actions-dialog[open],.support-input-dialog[open]{animation:supportDialogIn .16s ease-out}',
        '.support-input-dialog form{padding:22px}.support-input-dialog h2{margin:0;color:#1b3544;font-size:18px}.support-input-dialog p{margin:7px 0 14px;color:#64748b;font-size:13px}',
        '.support-input-dialog textarea{width:100%;min-height:112px;resize:vertical;padding:11px 12px;border:1px solid #d8e2e7;border-radius:11px;background:#f9fbfc;color:#243746;font:inherit;outline:none}',
        '.support-input-dialog textarea:focus{border-color:#36a6ad;box-shadow:0 0 0 3px rgba(54,166,173,.14)}',
        '.support-input-dialog .support-dialog-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:15px}',
        '.support-input-dialog button{padding:9px 14px;border:1px solid #dce5e9;border-radius:10px;background:#fff;color:#516171;font:600 13px system-ui,sans-serif;cursor:pointer}',
        '.support-input-dialog button[type=submit]{border-color:#147a87;background:#147a87;color:#fff}',
        'body.dark .support-actions-menu{border-color:#26364c;background:#111c2e;color:#e7edf5}',
        'body.dark .support-actions-menu button:hover{background:#172f3e;color:#64d6e0}',
        'body.dark .support-message-quote,body.dark .support-reply-preview{background:#172c36;color:#e7edf5}',
        'body.dark .support-message-quote-text,body.dark .support-reply-preview-text{color:#b7c4ce}',
        'body.dark .support-selection-bar{border-color:#294154;background:#111c2e;color:#e7edf5}',
        'body.dark .support-input-dialog,body.dark .support-actions-dialog{border-color:#293c51;background:#111c2e;color:#e7edf5}',
        'body.dark .support-actions-dialog h2{color:#e7edf5}',
        'body.dark .support-actions-dialog p,body.dark .support-delete-option small{color:#aab7c5}',
        'body.dark .support-delete-option{border-color:#293c51;background:#152236;color:#e7edf5}',
        'body.dark .support-delete-option:hover{border-color:#3c8189;background:#17303a}',
        '@media(max-width:480px){.support-actions-dialog form{padding:18px}.support-delete-option{grid-template-columns:34px minmax(0,1fr) 16px;gap:9px;padding:10px}.support-delete-option-icon{width:33px;height:33px}}',
        '@media(hover:none){.support-message-menu-trigger,.chat-message .support-message-menu-trigger{display:grid}}'
    ].join('');
    document.head.appendChild(styles);

    function textFor(message) {
        return String(message && message.body || '').trim();
    }

    function attachmentFor(message) {
        return message && message.attachment || null;
    }

    function previewFor(message) {
        var attachment = attachmentFor(message);
        if (attachment && attachment.is_image && attachment.preview_url) {
            return { image: attachment.preview_url, label: attachment.name || 'Photo' };
        }
        var context = message && message.context;
        var item = context && context.items && context.items[0];
        var imageUrl = item && item.image_url || (context && context.image_url);
        if (imageUrl) return { image: imageUrl, label: (item && item.product_name) || context.product_name || 'Product photo' };
        if (attachment) return { icon: 'ri-file-text-line', label: attachment.name || 'Attachment' };
        return { icon: 'ri-chat-1-line', label: textFor(message) || 'Message' };
    }

    function buildQuote(message, compact) {
        var preview = previewFor(message);
        var wrapper = document.createElement('div');
        wrapper.className = compact ? 'support-reply-preview' : 'support-message-quote';
        if (message.deleted) wrapper.classList.add('deleted');

        if (preview.image) {
            var image = document.createElement('img');
            image.src = preview.image;
            image.alt = preview.label;
            image.loading = 'lazy';
            wrapper.appendChild(image);
        } else {
            var icon = document.createElement('span');
            icon.className = 'support-message-quote-icon';
            var glyph = document.createElement('i');
            glyph.className = preview.icon;
            icon.appendChild(glyph);
            wrapper.appendChild(icon);
        }

        var copy = document.createElement('span');
        copy.className = compact ? 'support-reply-preview-copy' : 'support-message-quote-copy';
        var name = document.createElement('strong');
        name.className = compact ? 'support-reply-preview-name' : 'support-message-quote-name';
        name.textContent = message.deleted ? 'Unavailable message' : (message.sender_name || 'Support');
        var label = document.createElement('span');
        label.className = compact ? 'support-reply-preview-text' : 'support-message-quote-text';
        label.textContent = message.deleted ? 'This message was deleted' : (textFor(message) || preview.label);
        copy.append(name, label);
        wrapper.appendChild(copy);

        if (compact) {
            var close = document.createElement('button');
            close.type = 'button';
            close.setAttribute('aria-label', 'Cancel reply');
            close.innerHTML = '<i class="ri-close-line"></i>';
            close.addEventListener('click', function () {
                wrapper.hidden = true;
                wrapper.dataset.messageId = '';
                wrapper.replaceChildren();
                wrapper.dispatchEvent(new CustomEvent('support:reply-cancel', { bubbles: true }));
            });
            wrapper.appendChild(close);
        }
        return wrapper;
    }

    function toast(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type || 'info');
        } else if (typeof window.nbToast === 'function') {
            window.nbToast(message, 'ri-information-line');
        } else if (window.Swal) {
            window.Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2800, icon: type || 'info', title: message });
        } else {
            window.alert(message);
        }
    }

    function confirmAction(title, message) {
        if (window.Swal) {
            return window.Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Continue',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) { return result.isConfirmed; });
        }
        return Promise.resolve(window.confirm(title + '\n\n' + message));
    }

    function requestText(title, message, initialValue, required, confirmLabel) {
        if (window.Swal) {
            return window.Swal.fire({
                title: title,
                text: message,
                input: 'textarea',
                inputValue: initialValue || '',
                inputAttributes: { 'aria-label': title, maxlength: 2000 },
                showCancelButton: true,
                confirmButtonText: confirmLabel,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                inputValidator: function (value) {
                    if (required && !String(value || '').trim()) return 'Please enter a reason.';
                }
            }).then(function (result) { return result.isConfirmed ? result.value : null; });
        }

        return new Promise(function (resolve) {
            var dialog = document.createElement('dialog');
            dialog.className = 'support-input-dialog';
            var form = document.createElement('form');
            form.method = 'dialog';
            var heading = document.createElement('h2');
            heading.textContent = title;
            var description = document.createElement('p');
            description.textContent = message;
            var input = document.createElement('textarea');
            input.maxLength = 2000;
            input.setAttribute('aria-label', title);
            input.value = initialValue || '';
            input.required = required;
            var actions = document.createElement('div');
            actions.className = 'support-dialog-actions';
            var cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.textContent = 'Cancel';
            cancel.addEventListener('click', function () { dialog.close('cancel'); });
            var submit = document.createElement('button');
            submit.type = 'submit';
            submit.value = 'submit';
            submit.textContent = confirmLabel;
            actions.append(cancel, submit);
            form.append(heading, description, input, actions);
            dialog.appendChild(form);
            document.body.appendChild(dialog);

            dialog.addEventListener('close', function () {
                var result = dialog.returnValue === 'submit' ? input.value : null;
                dialog.remove();
                resolve(result);
            }, { once: true });
            form.addEventListener('submit', function (event) {
                if (required && !input.value.trim()) {
                    event.preventDefault();
                    input.setCustomValidity('Please enter a reason.');
                    input.reportValidity();
                    input.addEventListener('input', function clearInputError() {
                        input.setCustomValidity('');
                        input.removeEventListener('input', clearInputError);
                    });
                    return;
                }
                input.setCustomValidity('');
            });
            dialog.showModal();
            input.focus();
        });
    }

    function requestDeleteScope() {
        return new Promise(function (resolve) {
            var dialog = document.createElement('dialog');
            dialog.className = 'support-actions-dialog';
            dialog.setAttribute('aria-labelledby', 'supportDeleteTitle');
            var form = document.createElement('form');
            form.method = 'dialog';
            form.innerHTML = '<div class="support-delete-dialog-head"><span class="support-delete-dialog-icon"><i class="ri-delete-bin-6-line"></i></span><div><span class="support-dialog-eyebrow">Message controls</span><h2 id="supportDeleteTitle">Delete this message?</h2></div></div><p>Choose how widely you want this message removed. You can hide it just for yourself or remove it from the whole conversation.</p><div class="support-delete-options"><button type="submit" value="me" class="support-delete-option"><span class="support-delete-option-icon"><i class="ri-eye-off-line"></i></span><span><strong>Delete for me</strong><small>Hide this message from your view only.</small></span><i class="ri-arrow-right-s-line"></i></button><button type="submit" value="everyone" class="support-delete-option everyone"><span class="support-delete-option-icon"><i class="ri-delete-bin-6-line"></i></span><span><strong>Delete for everyone</strong><small>Remove it from both sides of the support chat.</small></span><i class="ri-arrow-right-s-line"></i></button></div><div class="support-dialog-footer"><button type="button" value="cancel">Keep message</button></div>';
            dialog.appendChild(form);
            document.body.appendChild(dialog);
            dialog.addEventListener('close', function () {
                var value = dialog.returnValue === 'me' || dialog.returnValue === 'everyone' ? dialog.returnValue : null;
                dialog.remove();
                resolve(value);
            }, { once: true });
            form.querySelector('[value="cancel"]').addEventListener('click', function () { dialog.close('cancel'); });
            dialog.showModal();
        });
    }

    function requestConversationDelete() {
        return new Promise(function (resolve) {
            var dialog = document.createElement('dialog');
            dialog.className = 'support-actions-dialog';
            var form = document.createElement('form');
            form.method = 'dialog';
            form.innerHTML = '<div class="support-delete-dialog-head"><span class="support-delete-dialog-icon"><i class="ri-chat-delete-line"></i></span><div><span class="support-dialog-eyebrow">Conversation controls</span><h2>Delete this conversation?</h2></div></div><p>This permanently removes the conversation, including its attachments, from both you and Cosmetics Support.</p><div class="support-confirm-actions"><button type="button" value="cancel">Keep conversation</button><button type="submit" value="delete" class="danger">Delete conversation</button></div>';
            dialog.appendChild(form);
            document.body.appendChild(dialog);
            dialog.addEventListener('close', function () {
                var confirmed = dialog.returnValue === 'delete';
                dialog.remove();
                resolve(confirmed);
            }, { once: true });
            form.querySelector('[value="cancel"]').addEventListener('click', function () { dialog.close('cancel'); });
            dialog.showModal();
        });
    }

    function plainCopy(value) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(value);
        }
        var input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        var copied = document.execCommand('copy');
        input.remove();
        return copied ? Promise.resolve() : Promise.reject(new Error('Clipboard access is unavailable.'));
    }

    function closeMenu() {
        if (!activeMenu) return;
        activeMenu.menu.remove();
        activeMenu = null;
    }

    function createMenu(instance, row, x, y) {
        closeMenu();
        var message = row.__supportMessage || {};
        var attachment = attachmentFor(message);
        var mine = row.dataset.mine === '1';
        var actions = instance.options.isAdmin
            ? [
                ['reply', 'ri-reply-line', 'Reply'],
                ['copy', 'ri-file-copy-line', 'Copy'],
                ['delete', 'ri-delete-bin-line', 'Delete', true]
            ]
            : [
                ['reply', 'ri-reply-line', 'Reply'],
                ['copy', 'ri-file-copy-line', 'Copy'],
                ['forward', 'ri-share-forward-line', 'Forward'],
                ['pin', 'ri-pushpin-line', message.is_pinned ? 'Unpin' : 'Pin'],
                ['star', 'ri-star-line', message.is_starred ? 'Unstar' : 'Star'],
                ['select', 'ri-checkbox-multiple-line', 'Select']
            ];
        if (!instance.options.isAdmin) {
            if (attachment) actions.push(['save', 'ri-download-2-line', 'Save as']);
            actions.push(['share', 'ri-share-line', 'Share']);
            if (attachment && attachment.url) actions.push(['open', 'ri-external-link-line', 'Open attachment']);
            if (!mine) actions.push(['report', 'ri-flag-line', 'Report']);
            if (mine && textFor(message)) actions.push(['edit', 'ri-edit-line', 'Edit']);
            if (mine) actions.push(['delete', 'ri-delete-bin-line', 'Delete', true]);
        }

        var menu = document.createElement('div');
        menu.className = 'support-actions-menu';
        menu.setAttribute('role', 'menu');
        actions.forEach(function (action) {
            var button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('role', 'menuitem');
            button.dataset.action = action[0];
            if (action[3]) button.classList.add('support-menu-danger');
            button.innerHTML = '<i class="' + action[1] + '" aria-hidden="true"></i><span></span>';
            button.querySelector('span').textContent = action[2];
            menu.appendChild(button);
            if (action[0] === 'forward') {
                var separator = document.createElement('hr');
                menu.appendChild(separator);
            }
        });
        document.body.appendChild(menu);
        var rect = menu.getBoundingClientRect();
        menu.style.left = Math.max(8, Math.min(x, window.innerWidth - rect.width - 8)) + 'px';
        menu.style.top = Math.max(8, Math.min(y, window.innerHeight - rect.height - 8)) + 'px';
        activeMenu = { menu: menu, instance: instance, row: row };
        menu.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-action]');
            if (!button) return;
            runAction(instance, row, message, button.dataset.action);
        });
        menu.querySelector('button')?.focus();
    }

    function runAction(instance, row, message, action) {
        closeMenu();
        var options = instance.options;
        if (action === 'reply' || action === 'forward') {
            if (options.onReply) options.onReply(message, action === 'forward');
            return;
        }
        if (action === 'copy') {
            plainCopy(textFor(message) || (attachmentFor(message) && attachmentFor(message).name) || '')
                .then(function () { toast('Message copied.', 'success'); })
                .catch(function (error) { toast(error.message, 'error'); });
            return;
        }
        if (action === 'select') {
            instance.selectionMode = true;
            var selectedId = String(message.id);
            if (instance.selected.has(selectedId)) instance.selected.delete(selectedId);
            else instance.selected.add(selectedId);
            row.classList.toggle('support-selected', instance.selected.has(selectedId));
            updateSelectionBar(instance);
            return;
        }
        if (action === 'save') {
            var attachment = attachmentFor(message);
            if (!attachment || !attachment.url) return;
            var link = document.createElement('a');
            link.href = attachment.url;
            link.download = attachment.name || 'support-attachment';
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            link.remove();
            return;
        }
        if (action === 'open') {
            var openAttachment = attachmentFor(message);
            if (openAttachment && openAttachment.url) window.open(openAttachment.preview_url || openAttachment.url, '_blank', 'noopener');
            return;
        }
        if (action === 'share') {
            var shareText = textFor(message) || (attachmentFor(message) && attachmentFor(message).name) || '';
            if (navigator.share) {
                navigator.share({ title: 'Support message', text: shareText }).catch(function (error) {
                    if (error.name !== 'AbortError') toast(error.message || 'Could not share this message.', 'error');
                });
            } else {
                plainCopy(shareText).then(function () { toast('Sharing is unavailable here; message copied instead.', 'info'); })
                    .catch(function (error) { toast(error.message, 'error'); });
            }
            return;
        }
        if (action === 'edit') {
            return requestText('Edit your message', 'Update the text of your message.', textFor(message), true, 'Save changes')
                .then(function (edited) {
                    if (edited === null || edited.trim() === textFor(message)) return;
                    return options.request(options.urlFor(message, 'edit'), {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ body: edited.trim() })
                    }).then(function (data) {
                        if (options.onUpdate) options.onUpdate(data.message);
                        toast('Message updated.', 'success');
                    });
                }).catch(function (error) { toast(error.message, 'error'); });
        }
        if (action === 'delete') {
            return requestDeleteScope()
                .then(function (scope) {
                    if (!scope) return;
                    return options.request(options.urlFor(message, 'delete'), {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ scope: scope })
                    }).then(function () {
                        if (options.onDelete) options.onDelete(message.id);
                        toast(scope === 'me' ? 'Message hidden for you.' : 'Message deleted for everyone.', 'success');
                    });
                }).catch(function (error) { toast(error.message, 'error'); });
        }
        if (action === 'pin' || action === 'star') {
            return options.request(options.urlFor(message, action), { method: 'POST' }).then(function (data) {
                message.is_pinned = data.is_pinned === undefined ? message.is_pinned : data.is_pinned;
                message.is_starred = data.is_starred === undefined ? message.is_starred : data.is_starred;
                if (options.onUpdate) options.onUpdate(message);
                toast(action === 'pin' ? (message.is_pinned ? 'Message pinned.' : 'Message unpinned.') : (message.is_starred ? 'Message starred.' : 'Star removed.'), 'success');
            }).catch(function (error) { toast(error.message, 'error'); });
        }
        if (action === 'report') {
            return requestText('Report this message', 'Tell the support team why this message should be reviewed.', '', true, 'Send report')
                .then(function (reason) {
                    if (reason === null) return;
                    return options.request(options.urlFor(message, 'report'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ reason: reason.trim() })
                    }).then(function () { toast('Report sent to the support team.', 'success'); });
                }).catch(function (error) { toast(error.message, 'error'); });
        }
    }

    function updateSelectionBar(instance) {
        var messages = instance.options.messages;
        if (!messages || !messages.isConnected) return;
        var bar = messages.querySelector('.support-selection-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'support-selection-bar';
            bar.innerHTML = '<span></span><button type="button" data-selection-copy>Copy selected</button><button type="button" data-selection-clear>Done</button>';
            messages.prepend(bar);
        }
        bar.hidden = !instance.selectionMode;
        bar.querySelector('span').textContent = instance.selected.size + ' selected';
        bar.querySelector('[data-selection-copy]').disabled = instance.selected.size === 0;
        if (!instance.selected.size) {
            instance.selectionMode = false;
            bar.hidden = true;
        }
    }

    function attach(root, options) {
        var instance = instances.get(root);
        if (!instance) {
            instance = { options: options, selected: new Set(), selectionMode: false, timer: null };
            instances.set(root, instance);
            root.addEventListener('contextmenu', function (event) {
                var row = event.target.closest('.support-message,.chat-message');
                if (!row || !root.contains(row)) return;
                event.preventDefault();
                createMenu(instance, row, event.clientX, event.clientY);
            });
            root.addEventListener('pointerdown', function (event) {
                var row = event.target.closest('.support-message,.chat-message');
                if (!row || !root.contains(row) || event.target.closest('button,a,input,textarea')) return;
                clearTimeout(instance.timer);
                instance.timer = setTimeout(function () {
                    createMenu(instance, row, event.clientX || 18, event.clientY || 18);
                }, 550);
            });
            ['pointerup', 'pointercancel', 'pointermove'].forEach(function (type) {
                root.addEventListener(type, function () { clearTimeout(instance.timer); });
            });
            root.addEventListener('click', function (event) {
                var trigger = event.target.closest('.support-message-menu-trigger');
                if (trigger) {
                    var row = trigger.closest('.support-message,.chat-message');
                    if (row) {
                        event.preventDefault();
                        createMenu(instance, row, trigger.getBoundingClientRect().left, trigger.getBoundingClientRect().bottom);
                    }
                    return;
                }
                var copy = event.target.closest('[data-selection-copy]');
                if (copy) {
                    var text = Array.from(instance.selected).map(function (id) {
                        var selectedRow = instance.options.messages.querySelector('[data-message-id="' + id + '"]');
                        return selectedRow ? textFor(selectedRow.__supportMessage) : '';
                    }).filter(Boolean).join('\n');
                    plainCopy(text).then(function () { toast('Selected messages copied.', 'success'); })
                        .catch(function (error) { toast(error.message, 'error'); });
                    return;
                }
                if (event.target.closest('[data-selection-clear]')) {
                    instance.selected.clear();
                    instance.selectionMode = false;
                    instance.options.messages.querySelectorAll('.support-selected').forEach(function (row) { row.classList.remove('support-selected'); });
                    updateSelectionBar(instance);
                    return;
                }
                if (instance.selectionMode) {
                    var row = event.target.closest('.support-message,.chat-message');
                    if (row && root.contains(row) && !event.target.closest('button,a,input,textarea')) {
                        var id = String(row.dataset.messageId);
                        if (instance.selected.has(id)) {
                            instance.selected.delete(id);
                            row.classList.remove('support-selected');
                        } else {
                            instance.selected.add(id);
                            row.classList.add('support-selected');
                        }
                        updateSelectionBar(instance);
                    }
                }
            });
            root.addEventListener('click', function (event) {
                if (!event.target.closest('[data-support-delete-conversation]')) return;
                event.preventDefault();
                requestConversationDelete()
                    .then(function (confirmed) {
                        if (!confirmed) return;
                        return instance.options.request(instance.options.deleteConversationUrl, { method: 'DELETE' })
                            .then(function () {
                                instance.selected.clear();
                                instance.selectionMode = false;
                                instance.options.onConversationDelete();
                                toast('Conversation deleted.', 'success');
                            });
                    }).catch(function (error) { toast(error.message, 'error'); });
            });
        } else {
            if (instance.options.messages !== options.messages) {
                instance.selected.clear();
                instance.selectionMode = false;
            }
            instance.options = options;
        }
        updateSelectionBar(instance);
    }

    document.addEventListener('click', function (event) {
        if (activeMenu && !activeMenu.menu.contains(event.target) && !event.target.closest('.support-message-menu-trigger')) closeMenu();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMenu();
    });

    window.SupportMessageActions = {
        attach: attach,
        quoteElement: function (message) { return message && message.reply ? buildQuote(message.reply, false) : null; },
        replyPreview: function (message) { return buildQuote(message, true); }
    };
})();
