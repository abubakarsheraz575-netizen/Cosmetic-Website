(function() {
    if (window.__ajaxNavigationRouter) return;
    window.__ajaxNavigationRouter = true;

    var excludedPathPrefixes = [
        '/logout',
        '/checkout/paystack/callback'
    ];
    var pageStates = new Map();
    var loadedScripts = new Set();
    var prefetchedPages = new Map();
    var prefetchTimers = new WeakMap();
    var navigationPending = false;
    var activeNavigationController = null;
    var customSelectSequence = 0;
    var activeCustomSelect = null;
    var customSelectStates = [];
    var pageIntervals = [];

    document.querySelectorAll('script[src]').forEach(function(script) {
        loadedScripts.add(script.src);
    });

    function shouldUseRegularNavigation(url) {
        return excludedPathPrefixes.some(function(prefix) {
            return url.pathname === prefix || url.pathname.indexOf(prefix + '/') === 0;
        });
    }

    function pageRequest(url, signal) {
        return fetch(url.href, {
            credentials: 'same-origin',
            signal: signal,
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function(response) {
            return response.text().then(function(html) {
                return {
                    html: html,
                    finalUrl: response.url || url.href,
                    status: response.status,
                    contentType: response.headers.get('content-type') || ''
                };
            });
        });
    }

    function pageCacheKey(url) {
        return url.origin + url.pathname + url.search;
    }

    function prefetchPage(url) {
        var key = pageCacheKey(url);
        var cached = prefetchedPages.get(key);
        if (cached && Date.now() - cached.createdAt < 5000) return;
        if (cached) prefetchedPages.delete(key);
        var entry = { createdAt: Date.now(), promise: null };
        entry.promise = pageRequest(url).then(function(result) {
            if (result.status < 200 || result.status >= 300 || result.contentType.indexOf('text/html') === -1) {
                prefetchedPages.delete(key);
                return null;
            }
            return result;
        }).catch(function() {
            prefetchedPages.delete(key);
            return null;
        });
        prefetchedPages.set(key, entry);
        while (prefetchedPages.size > 12) {
            prefetchedPages.delete(prefetchedPages.keys().next().value);
        }
    }

    function fetchPage(url, signal) {
        var key = pageCacheKey(url);
        var cached = prefetchedPages.get(key);
        if (cached && Date.now() - cached.createdAt < 5000) {
            return cached.promise.then(function(result) {
                return result || pageRequest(url, signal);
            });
        }
        return pageRequest(url, signal);
    }

    function sidebarScrollTarget() {
        var sidebar = document.querySelector('.sidebar');
        var nav = sidebar && sidebar.querySelector('.s-nav');
        if (nav && nav.scrollHeight > nav.clientHeight && /^(auto|scroll)$/.test(getComputedStyle(nav).overflowY)) return nav;
        if (sidebar && sidebar.scrollHeight > sidebar.clientHeight && /^(auto|scroll)$/.test(getComputedStyle(sidebar).overflowY)) return sidebar;
        return nav || sidebar;
    }

    function captureSidebarScroll() {
        var target = sidebarScrollTarget();
        return target ? target.scrollTop : 0;
    }

    function restoreSidebarScroll(scrollTop) {
        var target = sidebarScrollTarget();
        if (!target) return;
        target.scrollTop = scrollTop || 0;
        var activeLink = target.querySelector('.k-sub-active, .k-link.active, .side-link.active');
        if (!activeLink) return;
        var targetRect = target.getBoundingClientRect();
        var activeRect = activeLink.getBoundingClientRect();
        if (activeRect.top < targetRect.top) target.scrollTop -= targetRect.top - activeRect.top;
        else if (activeRect.bottom > targetRect.bottom) target.scrollTop += activeRect.bottom - targetRect.bottom;
    }

    function capturePageState() {
        return {
            fields: Array.prototype.map.call(document.querySelectorAll('input,textarea,select'), function(field, index) {
                return {
                    index: index,
                    id: field.id || '',
                    type: field.type || '',
                    value: field.type === 'file' ? '' : field.value,
                    checked: !!field.checked,
                    selectedIndex: field.selectedIndex
                };
            }),
            scrollY: window.scrollY,
            sidebarScrollTop: captureSidebarScroll()
        };
    }

    function savePageState(id, state) {
        if (!id) return;
        pageStates.delete(id);
        pageStates.set(id, state);
        while (pageStates.size > 12) {
            pageStates.delete(pageStates.keys().next().value);
        }
    }

    function restorePageState(state) {
        if (!state) return;
        if (state.fields) {
            var fields = document.querySelectorAll('input,textarea,select');
            state.fields.forEach(function(item) {
                var field = item.id ? document.getElementById(item.id) : fields[item.index];
                if (!field || field.type === 'file' || field.type !== item.type) return;
                if (field.type === 'checkbox' || field.type === 'radio') field.checked = item.checked;
                else if (field.tagName === 'SELECT') field.selectedIndex = item.selectedIndex;
                else field.value = item.value;
                field.dispatchEvent(new Event(field.tagName === 'SELECT' || field.type === 'checkbox' ? 'change' : 'input', { bubbles: true }));
            });
            window.scrollTo(0, state.scrollY || 0);
        }
        restoreSidebarScroll(state.sidebarScrollTop);
    }

    function showError(error) {
        var message = error && error.message ? error.message : 'Could not load this page. Please try again.';
        window.showToast(message, 'error');
    }

    window.showToast = function(message, type) {
        var container = document.getElementById('siteToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'siteToastContainer';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'false');
        }
        if (container.parentNode !== document.documentElement) {
            document.documentElement.appendChild(container);
        }

        var toast = document.createElement('div');
        var tone = ['success', 'warning', 'error', 'info'].indexOf(type) !== -1 ? type : 'info';
        var icons = { success: '✓', warning: '!', error: '×', info: 'i' };
        toast.className = 'site-toast site-toast-' + tone;
        toast.setAttribute('role', tone === 'error' ? 'alert' : 'status');
        toast.innerHTML = '<span class="site-toast-icon" aria-hidden="true">' + icons[tone] + '</span><span class="site-toast-message"></span><button type="button" class="site-toast-close" aria-label="Dismiss notification">×</button>';
        toast.querySelector('.site-toast-message').textContent = String(message || 'Something went wrong.');
        container.appendChild(toast);

        var dismiss = function() {
            toast.classList.add('site-toast-dismissed');
            window.setTimeout(function() { toast.remove(); }, 180);
        };
        toast.querySelector('.site-toast-close').addEventListener('click', dismiss);
        window.setTimeout(dismiss, tone === 'error' || tone === 'warning' ? 6500 : 4200);
    }

    function toastType(icon) {
        if (icon === 'error' || icon === 'warning' || icon === 'info' || icon === 'success') return icon;
        if (typeof icon === 'string' && /error|close|forbid|delete|fail/i.test(icon)) return 'error';
        if (typeof icon === 'string' && /warning|alert/i.test(icon)) return 'warning';
        return 'success';
    }

    function toastFromSweetAlert(options) {
        var message = options.title || options.text || options.html || '';
        window.showToast(typeof message === 'string' ? message.replace(/<[^>]*>/g, '') : String(message), toastType(options.icon));
        return Promise.resolve({ isConfirmed: false, isDenied: false, isDismissed: true });
    }

    function installSweetAlertToastBridge() {
        var swal = window.Swal;
        if (!swal || swal.__siteToastBridge) return;

        var originalFire = swal.fire.bind(swal);
        swal.fire = function(options) {
            if (options && options.toast) return toastFromSweetAlert(options);
            return originalFire(options);
        };

        var originalMixin = swal.mixin.bind(swal);
        swal.mixin = function(defaults) {
            var instance = originalMixin(defaults);
            var originalInstanceFire = instance.fire.bind(instance);
            instance.fire = function(options) {
                var merged = Object.assign({}, defaults || {}, options || {});
                if (merged.toast) return toastFromSweetAlert(merged);
                return originalInstanceFire(options);
            };
            return instance;
        };

        Object.defineProperty(swal, '__siteToastBridge', { value: true });
    }

    installSweetAlertToastBridge();

    function setPending(pending) {
        navigationPending = pending;
        document.documentElement.setAttribute('aria-busy', pending ? 'true' : 'false');
        var indicator = document.getElementById('ajaxNavigationProgress');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'ajaxNavigationProgress';
            indicator.setAttribute('aria-hidden', 'true');
            document.body.appendChild(indicator);
        }
        indicator.classList.toggle('active', pending);
    }

    function replaceHead(doc) {
        document.title = doc.title;
        document.documentElement.lang = doc.documentElement.lang || 'en';

        document.querySelectorAll('head style[data-ajax-page]').forEach(function(style) {
            style.remove();
        });
        doc.head.querySelectorAll('style').forEach(function(style) {
            var replacement = document.createElement('style');
            replacement.textContent = style.textContent;
            replacement.setAttribute('data-ajax-page', '');
            document.head.appendChild(replacement);
        });

        var currentStylesheets = new Map();
        document.head.querySelectorAll('link[rel="stylesheet"]').forEach(function(link) {
            var href = new URL(link.href, location.href).href;
            if (!currentStylesheets.has(href)) currentStylesheets.set(href, link);
        });
        var incomingStylesheets = [];
        var incomingHrefs = new Set();
        doc.head.querySelectorAll('link[rel="stylesheet"]').forEach(function(link) {
            var href = new URL(link.getAttribute('href'), location.href).href;
            if (incomingHrefs.has(href)) return;
            incomingHrefs.add(href);
            var stylesheet = currentStylesheets.get(href);
            if (!stylesheet) {
                stylesheet = document.createElement('link');
                Array.prototype.forEach.call(link.attributes, function(attribute) {
                    stylesheet.setAttribute(attribute.name, attribute.value);
                });
            }
            incomingStylesheets.push(stylesheet);
        });
        document.head.querySelectorAll('link[rel="stylesheet"]').forEach(function(link) {
            if (!incomingHrefs.has(new URL(link.href, location.href).href)) link.remove();
        });
        incomingStylesheets.forEach(function(stylesheet) {
            document.head.appendChild(stylesheet);
        });

        var incomingIcons = Array.prototype.slice.call(doc.head.querySelectorAll('link[rel~="icon"]'));
        if (incomingIcons.length) {
            document.head.querySelectorAll('link[rel~="icon"]').forEach(function(link) {
                link.remove();
            });
            incomingIcons.forEach(function(link) {
                document.head.appendChild(link.cloneNode(true));
            });
        }

        ['csrf-token', 'theme-color', 'viewport', 'description'].forEach(function(name) {
            var incoming = doc.head.querySelector('meta[name="' + name + '"]');
            var current = document.head.querySelector('meta[name="' + name + '"]');
            if (incoming && current) current.setAttribute('content', incoming.content);
            else if (incoming && !current) document.head.appendChild(incoming.cloneNode(true));
            else if (!incoming && current && name === 'description') current.remove();
        });
    }

    function loadPageScripts(doc) {
        var sources = Array.prototype.slice.call(doc.head.querySelectorAll('script[src]'));
        var seen = new Set();
        var pending = sources.map(function(original) {
            var src = new URL(original.getAttribute('src'), location.href).href;
            if (loadedScripts.has(src) || seen.has(src)) return Promise.resolve();
            seen.add(src);

            return new Promise(function(resolve, reject) {
                var script = document.createElement('script');
                Array.prototype.forEach.call(original.attributes, function(attribute) {
                    script.setAttribute(attribute.name, attribute.value);
                });
                if (!script.hasAttribute('async') && script.type !== 'module') script.async = false;
                script.onload = function() {
                    loadedScripts.add(src);
                    installSweetAlertToastBridge();
                    resolve();
                };
                script.onerror = function() {
                    loadedScripts.delete(src);
                    reject(new Error('Could not load page script: ' + src));
                };
                document.head.appendChild(script);
            });
        });

        return Promise.all(pending);
    }

    function runPageScripts() {
        var originalDocumentAdd = document.addEventListener.bind(document);
        var originalWindowAdd = window.addEventListener.bind(window);
        var originalSetInterval = window.setInterval;
        var domReadyCallbacks = [];
        var loadCallbacks = [];

        document.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded') {
                domReadyCallbacks.push(listener);
                return;
            }
            return originalDocumentAdd(type, listener, options);
        };
        window.addEventListener = function(type, listener, options) {
            if (type === 'load') {
                loadCallbacks.push(listener);
                return;
            }
            return originalWindowAdd(type, listener, options);
        };
        window.setInterval = function() {
            var interval = originalSetInterval.apply(window, arguments);
            pageIntervals.push(interval);
            return interval;
        };

        function invoke(callbacks) {
            callbacks.forEach(function(callback) {
                try {
                    if (typeof callback === 'function') callback({});
                    else if (callback && typeof callback.handleEvent === 'function') callback.handleEvent({});
                } catch (error) {
                    console.error('[ajax-navigation] Page initialization failed:', error);
                    showError(error);
                }
            });
        }

        var scripts = Array.prototype.slice.call(document.body.querySelectorAll('script'));
        return scripts.reduce(function(sequence, original) {
            return sequence.then(function() {
                if (original.src) {
                    var src = original.src;
                    if (loadedScripts.has(src)) {
                        original.remove();
                        return;
                    }
                    return new Promise(function(resolve, reject) {
                        var script = document.createElement('script');
                        Array.prototype.forEach.call(original.attributes, function(attribute) {
                            script.setAttribute(attribute.name, attribute.value);
                        });
                        if (!script.hasAttribute('async') && script.type !== 'module') script.async = false;
                        script.onload = function() {
                            loadedScripts.add(src);
                            installSweetAlertToastBridge();
                            resolve();
                        };
                        script.onerror = function() {
                            loadedScripts.delete(src);
                            reject(new Error('Could not load page script: ' + src));
                        };
                        original.parentNode.replaceChild(script, original);
                    });
                }
                var script = document.createElement('script');
                Array.prototype.forEach.call(original.attributes, function(attribute) {
                    script.setAttribute(attribute.name, attribute.value);
                });
                script.textContent = original.textContent;
                original.parentNode.replaceChild(script, original);
            });
        }, Promise.resolve()).then(function() {
            invoke(domReadyCallbacks);
            invoke(loadCallbacks);
        }).finally(function() {
            document.addEventListener = originalDocumentAdd;
            window.addEventListener = originalWindowAdd;
            window.setInterval = originalSetInterval;
        });
    }

    function clearPageIntervals() {
        pageIntervals.forEach(function(interval) {
            window.clearInterval(interval);
        });
        pageIntervals = [];
    }

    function initializePage() {
        initializeCustomSelects();

        document.querySelectorAll('.reveal:not(.show)').forEach(function(element, index) {
            element.style.transitionDelay = (index * 0.04) + 's';
            window.requestAnimationFrame(function() {
                element.classList.add('show');
            });
        });

        document.querySelectorAll('[data-count]').forEach(function(element) {
            if (element.dataset.done) return;
            element.dataset.done = '1';

            var target = parseFloat(element.dataset.count) || 0;
            var decimals = parseInt(element.dataset.decimals, 10) || 0;
            var prefix = element.dataset.prefix || '';
            var suffix = element.dataset.suffix || '';
            var startedAt = performance.now();
            var duration = 650;

            function animate(now) {
                var progress = Math.min((now - startedAt) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = target * eased;
                element.textContent = prefix + value.toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;
                if (progress < 1) window.requestAnimationFrame(animate);
            }

            window.requestAnimationFrame(animate);
        });
    }

    function initializeCustomSelects() {
        document.querySelectorAll('select:not([multiple]):not([size]):not([data-native-select])').forEach(function(select) {
            if (
                select.dataset.siteSelectReady === 'true' ||
                select.hidden ||
                select.closest('.swal2-container') ||
                getComputedStyle(select).display === 'none'
            ) return;

            customSelectSequence += 1;
            var id = 'siteSelect' + customSelectSequence;
            var wrapper = document.createElement('span');
            wrapper.className = 'site-select-wrap';
            var trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.id = id + 'Trigger';
            trigger.className = 'site-select-trigger';
            trigger.setAttribute('role', 'combobox');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            var menu = document.createElement('div');
            menu.id = id + 'Options';
            menu.className = 'site-select-menu';
            menu.setAttribute('role', 'listbox');
            menu.hidden = true;
            trigger.setAttribute('aria-controls', menu.id);
            trigger.innerHTML = '<span class="site-select-value"></span><span class="site-select-chevron" aria-hidden="true"></span>';

            var labels = Array.prototype.slice.call(select.labels || []);
            var labelText = labels.map(function(label) {
                return (label.innerText || label.textContent || '').replace(/\s+/g, ' ').trim();
            }).filter(Boolean).join(' ').trim();
            var labelContainer = select.parentElement;
            while (!labelText && labelContainer && labelContainer !== select.form) {
                var nearbyLabel = labelContainer.querySelector(':scope > label');
                if (nearbyLabel) labelText = (nearbyLabel.innerText || nearbyLabel.textContent || '').replace(/\s+/g, ' ').trim();
                labelContainer = labelContainer.parentElement;
            }
            trigger.setAttribute('aria-label', select.getAttribute('aria-label') || labelText || select.id || select.name || 'Select an option');

            var state = { id: id, select: select, wrapper: wrapper, trigger: trigger, menu: menu };
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            wrapper.appendChild(trigger);
            document.body.appendChild(menu);
            select.dataset.siteSelectReady = 'true';
            select.classList.add('site-select-native');
            select.setAttribute('aria-hidden', 'true');
            select.tabIndex = -1;

            labels.forEach(function(label) {
                if (label.htmlFor === select.id) label.htmlFor = trigger.id;
                label.addEventListener('click', function(event) {
                    if (event.target === select) return;
                    event.preventDefault();
                    trigger.focus();
                });
            });

            function options() {
                return Array.prototype.slice.call(menu.querySelectorAll('[role="option"]'));
            }

            function syncValue() {
                var selected = select.options[select.selectedIndex];
                trigger.querySelector('.site-select-value').textContent = selected ? selected.textContent.trim() : 'Select an option';
                trigger.disabled = select.disabled;
                trigger.setAttribute('aria-required', select.required ? 'true' : 'false');
                trigger.setAttribute('aria-invalid', select.getAttribute('aria-invalid') || 'false');
                options().forEach(function(optionButton) {
                    var selectedOption = Number(optionButton.dataset.index) === select.selectedIndex;
                    optionButton.setAttribute('aria-selected', selectedOption ? 'true' : 'false');
                    optionButton.classList.toggle('selected', selectedOption);
                });
            }

            function buildOptions() {
                menu.replaceChildren();
                Array.prototype.forEach.call(select.options, function(option, index) {
                    var optionButton = document.createElement('button');
                    optionButton.type = 'button';
                    optionButton.className = 'site-select-option';
                    optionButton.setAttribute('role', 'option');
                    optionButton.setAttribute('aria-selected', 'false');
                    optionButton.setAttribute('aria-disabled', option.disabled ? 'true' : 'false');
                    optionButton.disabled = option.disabled;
                    optionButton.dataset.index = String(index);
                    optionButton.textContent = option.textContent.trim();
                    optionButton.addEventListener('click', function() {
                        select.selectedIndex = index;
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        select.removeAttribute('aria-invalid');
                        closeCustomSelect(state, false);
                        trigger.focus();
                    });
                    menu.appendChild(optionButton);
                });
                syncValue();
            }

            select.addEventListener('change', syncValue);
            select.addEventListener('invalid', function(event) {
                event.preventDefault();
                select.setAttribute('aria-invalid', 'true');
                syncValue();
                trigger.focus();
                window.showToast('Please choose an option for ' + trigger.getAttribute('aria-label') + '.', 'warning');
            });
            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                if (activeCustomSelect === state) closeCustomSelect(state, false);
                else openCustomSelect(state);
            });
            trigger.addEventListener('keydown', function(event) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openCustomSelect(state);
                    focusCustomOption(state, select.selectedIndex, event.key === 'ArrowUp' ? -1 : 1);
                }
            });
            menu.addEventListener('keydown', function(event) {
                var current = Number(event.target.dataset.index);
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeCustomSelect(state, true);
                } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    focusCustomOption(state, current, event.key === 'ArrowDown' ? 1 : -1);
                } else if (event.key === 'Home' || event.key === 'End') {
                    event.preventDefault();
                    focusCustomOption(state, event.key === 'Home' ? -1 : options().length, event.key === 'Home' ? 1 : -1);
                } else if (event.key === 'Tab') {
                    closeCustomSelect(state, false);
                }
            });

            var observer = new MutationObserver(buildOptions);
            observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'label'] });
            state.observer = observer;
            customSelectStates.push(state);
            buildOptions();
        });
    }

    function focusCustomOption(state, currentIndex, direction) {
        var available = Array.prototype.slice.call(state.menu.querySelectorAll('[role="option"]:not(:disabled)'));
        if (!available.length) return;

        var next = available.find(function(option) {
            return Number(option.dataset.index) === currentIndex;
        });
        if (!next) {
            next = direction < 0 ? available[available.length - 1] : available[0];
        } else if (document.activeElement !== state.trigger) {
            var index = available.indexOf(next) + direction;
            next = available[Math.max(0, Math.min(index, available.length - 1))];
        }
        next.focus();
    }

    function positionCustomSelect(state) {
        if (!state || state.menu.hidden) return;
        var rect = state.trigger.getBoundingClientRect();
        var margin = 8;
        var width = Math.min(rect.width, window.innerWidth - margin * 2);
        state.menu.style.width = width + 'px';
        state.menu.style.left = Math.max(margin, Math.min(rect.left, window.innerWidth - width - margin)) + 'px';
        var menuHeight = Math.min(state.menu.scrollHeight, 280);
        var below = window.innerHeight - rect.bottom;
        var top = below >= menuHeight + 8 || rect.top < menuHeight + 8
            ? rect.bottom + 5
            : rect.top - menuHeight - 5;
        state.menu.style.top = Math.max(margin, Math.min(top, window.innerHeight - menuHeight - margin)) + 'px';
    }

    function openCustomSelect(state) {
        if (activeCustomSelect && activeCustomSelect !== state) closeCustomSelect(activeCustomSelect, false);
        activeCustomSelect = state;
        state.menu.hidden = false;
        state.trigger.setAttribute('aria-expanded', 'true');
        positionCustomSelect(state);
        focusCustomOption(state, state.select.selectedIndex, 1);
    }

    function closeCustomSelect(state, returnFocus) {
        if (!state) return;
        state.menu.hidden = true;
        state.trigger.setAttribute('aria-expanded', 'false');
        if (activeCustomSelect === state) activeCustomSelect = null;
        if (returnFocus) state.trigger.focus();
    }

    document.addEventListener('click', function(event) {
        if (!activeCustomSelect) return;
        if (event.target.closest('.site-select-wrap') || event.target.closest('.site-select-menu')) return;
        closeCustomSelect(activeCustomSelect, false);
    });
    window.addEventListener('resize', function() {
        positionCustomSelect(activeCustomSelect);
    });
    window.addEventListener('scroll', function() {
        positionCustomSelect(activeCustomSelect);
    }, true);
    document.addEventListener('reset', function(event) {
        window.setTimeout(function() {
            event.target.querySelectorAll('select[data-site-select-ready="true"]').forEach(function(select) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }, 0);
    });

    function scrollToHash(hash) {
        if (!hash) return;
        var id;
        try {
            id = decodeURIComponent(hash.slice(1));
        } catch (error) {
            id = hash.slice(1);
        }
        var target = document.getElementById(id);
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function replacePage(doc, finalUrl, stateId, push, preserveDark) {
        var previousUrl = location.href;
        var sidebarScrollTop = captureSidebarScroll();
        clearPageIntervals();
        if (activeCustomSelect) closeCustomSelect(activeCustomSelect, false);
        customSelectStates.forEach(function(state) {
            if (state.observer) state.observer.disconnect();
        });
        customSelectStates = [];
        window.dispatchEvent(new CustomEvent('ajax:navigating', {
            detail: { from: location.href, to: finalUrl.href }
        }));
        var nextBodyChildren = Array.prototype.slice.call(doc.body.childNodes).map(function(node) {
            return document.adoptNode(node);
        });
        document.body.replaceChildren.apply(document.body, nextBodyChildren);
        document.body.className = doc.body.className;
        if (preserveDark) document.body.classList.add('dark');
        replaceHead(doc);

        if (push) {
            var nextStateId = Date.now().toString(36) + Math.random().toString(36).slice(2);
            history.pushState({ ajaxPageId: nextStateId }, '', finalUrl.href);
        } else if (finalUrl.href !== location.href) {
            history.replaceState(history.state || {}, '', finalUrl.href);
        }

        return runPageScripts().then(function() {
            initializePage();
            var targetState = pageStates.get(stateId);
            if (stateId) restorePageState(targetState);
            else restoreSidebarScroll(sidebarScrollTop);
            window.dispatchEvent(new CustomEvent('ajax:page-ready', {
                detail: { from: previousUrl, to: finalUrl.href }
            }));
            if (stateId) {
                window.scrollTo(0, (pageStates.get(stateId) || {}).scrollY || 0);
            } else if (finalUrl.hash) {
                window.requestAnimationFrame(function() {
                    scrollToHash(finalUrl.hash);
                });
                } else if (push) {
                window.scrollTo(0, 0);
            }
        });
    }

    function feedbackFromDocument(doc, selectors) {
        var element = doc.body.querySelector(selectors);
        return element ? (element.innerText || element.textContent || '').replace(/\s+/g, ' ').trim() : '';
    }

    function responseErrorMessage(response, payload) {
        if (payload && payload.errors) {
            var errors = Object.keys(payload.errors).reduce(function(messages, key) {
                return messages.concat(payload.errors[key]);
            }, []);
            if (errors.length) return errors.join(' ');
        }
        if (payload && (payload.message || payload.error)) return payload.message || payload.error;
        return 'The request failed (' + response.status + '). Please review your changes and try again.';
    }

    function submitAjaxForm(event) {
        if (event.defaultPrevented) return;
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-native-submit') || form.target && form.target !== '_self') return;

        var method = (form.method || 'GET').toUpperCase();
        if (method === 'DIALOG') return;
        var action;
        try {
            action = new URL(form.action || location.href, location.href);
        } catch (error) {
            return;
        }
        if (action.origin !== location.origin || shouldUseRegularNavigation(action)) return;

        if (method === 'GET') {
            if (form.querySelector('input[type="file"]')) return;
            var query = new URLSearchParams(new FormData(form));
            action.search = query.toString();
            event.preventDefault();
            navigateTo(action, true, null);
            return;
        }
        if (method !== 'POST') return;

        event.preventDefault();
        if (form.dataset.ajaxPending === 'true') return;

        var formData = new FormData(form);
        var submitter = event.submitter;
        if (submitter && submitter.name && !formData.has(submitter.name)) {
            formData.append(submitter.name, submitter.value);
        }
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken && !formData.has('_token')) formData.append('_token', csrfToken.content);

        var stateId = currentPageStateId();
        savePageState(stateId, capturePageState());
        var preserveDark = document.body.classList.contains('dark');
        var submitButton = submitter && /^(BUTTON|INPUT)$/.test(submitter.tagName) ? submitter : form.querySelector('button[type="submit"],input[type="submit"]');
        var previousDisabled = submitButton ? submitButton.disabled : false;
        form.dataset.ajaxPending = 'true';
        form.setAttribute('aria-busy', 'true');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('ajax-submitting');
        }
        setPending(true);

        fetch(action.href, {
            method: method,
            body: formData,
            credentials: 'same-origin',
            headers: {
                'Accept': 'text/html, application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function(response) {
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return response.json().then(function(payload) {
                    if (!response.ok) throw new Error(responseErrorMessage(response, payload));
                    window.showToast(payload.message || 'Your changes were saved.', 'success');
                    if (payload.redirect) return window.ajaxNavigate(payload.redirect, true);
                    return false;
                });
            }
            if (contentType.indexOf('text/html') === -1) {
                throw new Error('The server returned an unexpected response. Your changes may not have been saved.');
            }
            return response.text().then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                if (!response.ok) {
                    var responseFeedback = feedbackFromDocument(doc, '[role="alert"],.alert-danger,.alert-error');
                    throw new Error(responseFeedback || responseErrorMessage(response));
                }

                var finalUrl = new URL(response.url || action.href, location.href);
                if (finalUrl.origin !== location.origin) {
                    window.location.assign(finalUrl.href);
                    return false;
                }

                var errorFeedback = feedbackFromDocument(doc, '[data-toast-error],.alert-danger,.alert-error,.contacts-alert.error,[role="alert"]');
                var warningFeedback = feedbackFromDocument(doc, '[data-toast-warning],.alert-warning');
                var successFeedback = feedbackFromDocument(doc, '[data-toast-success],.flash-success,.blog-flash,.alert-ok,.alert-success,.contacts-alert.success');
                var hasValidationErrors = doc.body.querySelector('[aria-invalid="true"],.is-invalid,.invalid-feedback:not(:empty)');

                return replacePage(doc, finalUrl, null, finalUrl.href !== location.href, preserveDark).then(function() {
                    if (errorFeedback || hasValidationErrors) {
                        window.showToast(errorFeedback || 'Please review the highlighted fields and try again.', 'error');
                    } else if (warningFeedback) {
                        window.showToast(warningFeedback, 'warning');
                    } else {
                        window.showToast(successFeedback || 'Your changes were saved.', 'success');
                    }
                    return true;
                });
            });
        }).catch(function(error) {
            showError(error);
            return false;
        }).finally(function() {
            form.dataset.ajaxPending = 'false';
            form.removeAttribute('aria-busy');
            if (submitButton) {
                submitButton.disabled = previousDisabled;
                submitButton.classList.remove('ajax-submitting');
            }
            setPending(false);
        });
    }

    function navigateTo(url, push, stateId) {
        if (navigationPending && !activeNavigationController) return Promise.resolve(false);
        if (activeNavigationController) activeNavigationController.abort();
        var requestController = new AbortController();
        activeNavigationController = requestController;
        if (push) savePageState(currentPageStateId(), capturePageState());
        var preserveDark = document.body.classList.contains('dark');
        setPending(true);

        return fetchPage(url, requestController.signal).then(function(result) {
            if (result.status < 200 || result.status >= 300) throw new Error('Page request failed (' + result.status + ').');
            if (result.contentType.indexOf('text/html') === -1) throw new Error('The requested page did not return HTML.');
            return result;
        }).then(function(result) {
            var finalUrl = new URL(result.finalUrl, location.href);
            if (finalUrl.origin !== location.origin || shouldUseRegularNavigation(finalUrl)) {
                window.location.assign(finalUrl.href);
                return null;
            }
            if (!finalUrl.hash && url.hash) finalUrl.hash = url.hash;

            var doc = new DOMParser().parseFromString(result.html, 'text/html');
            return loadPageScripts(doc).then(function() {
                return {
                    doc: doc,
                    finalUrl: finalUrl,
                    stateId: stateId,
                    push: push
                };
            });
        }).then(function(result) {
            if (!result || activeNavigationController !== requestController) return;
            return replacePage(result.doc, result.finalUrl, result.stateId, result.push, preserveDark).then(function() {
                if (!result.stateId && !result.finalUrl.hash) window.scrollTo(0, 0);
                return true;
            });
        }).catch(function(error) {
            if (error.name === 'AbortError') return false;
            console.error('[ajax-navigation] Falling back to a full page load:', error);
            window.location.assign(url.href);
            return false;
        }).finally(function() {
            if (activeNavigationController === requestController) {
                activeNavigationController = null;
                setPending(false);
            }
        });
    }

    function currentPageStateId() {
        var state = history.state || {};
        if (state.ajaxPageId) return state.ajaxPageId;
        var id = Date.now().toString(36) + Math.random().toString(36).slice(2);
        history.replaceState(Object.assign({}, state, { ajaxPageId: id }), '', location.href);
        return id;
    }

    window.ajaxNavigate = function(url, push) {
        var target = new URL(url, location.href);
        if (target.origin !== location.origin) {
            window.location.assign(target.href);
            return Promise.resolve(false);
        }
        return navigateTo(target, push !== false, null);
    };

    var style = document.createElement('style');
    style.textContent = '#ajaxNavigationProgress{position:fixed;z-index:2147483647;left:0;top:0;width:0;height:3px;background:linear-gradient(90deg,#38aab4,#3ec5e0);opacity:0;transition:width .22s ease,opacity .22s ease;pointer-events:none}#ajaxNavigationProgress.active{width:82%;opacity:1}';
    style.setAttribute('data-ajax-navigation', '');
    document.head.appendChild(style);
    var toastStyle = document.createElement('style');
    toastStyle.setAttribute('data-site-toast', '');
    toastStyle.setAttribute('data-ajax-navigation', '');
    toastStyle.textContent = [
        '#siteToastContainer{position:fixed;right:20px;top:20px;z-index:2147483646;display:grid;gap:10px;width:min(390px,calc(100vw - 32px));pointer-events:none}',
        '.site-toast{display:flex;align-items:center;gap:11px;padding:13px 14px;border:1px solid #d9e7ec;border-radius:14px;background:#fff;color:#173748;box-shadow:0 12px 34px rgba(8,35,49,.18);font:600 13px/1.45 system-ui,sans-serif;pointer-events:auto;animation:site-toast-in .18s ease-out}',
        '.site-toast-icon{display:grid;place-items:center;flex:0 0 25px;height:25px;border-radius:50%;background:#dcf5eb;color:#087a50;font-weight:800}',
        '.site-toast-message{flex:1;min-width:0}.site-toast-close{border:0;background:transparent;color:#64748b;font-size:19px;line-height:1;cursor:pointer}',
        '.site-toast-warning{border-color:#f4d38a}.site-toast-warning .site-toast-icon{background:#fff2cf;color:#9a6700}',
        '.site-toast-error{border-color:#f3b9b9}.site-toast-error .site-toast-icon{background:#fee2e2;color:#b42318}',
        '.site-toast-info .site-toast-icon{background:#e0f2fe;color:#0369a1}',
        '.site-toast-dismissed{opacity:0;transform:translateY(-5px);transition:opacity .18s,transform .18s}',
        '@keyframes site-toast-in{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:translateY(0)}}',
        'body.dark .site-toast{background:#111c2e;color:#e7edf5;border-color:#294154}'
    ].join('');
    document.head.appendChild(toastStyle);
    var controlStyle = document.createElement('style');
    controlStyle.setAttribute('data-site-controls', '');
    controlStyle.textContent = [
        'select:not([multiple]):not([size]){appearance:none;-webkit-appearance:none;min-height:42px;padding:10px 40px 10px 14px;border:1px solid var(--border,#dce5eb);border-radius:12px;background-color:var(--card,#fff);background-image:url(\"data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%2218%22%20height=%2218%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20stroke=%22%2368788f%22%20stroke-width=%222%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22%3E%3Cpath%20d=%22m6%209%206%206%206-6%22/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 12px center;background-size:16px;color:var(--text,#172b3a);font-family:inherit;font-size:13px;font-weight:600;line-height:1.35;box-shadow:0 1px 2px rgba(15,35,50,.04);cursor:pointer;transition:border-color .2s ease,box-shadow .2s ease,background-color .2s ease}',
        'select:not([multiple]):not([size]):hover{border-color:var(--lime,#3ec5e0)}',
        'select:not([multiple]):not([size]):focus-visible{outline:0;border-color:var(--lime,#3ec5e0);box-shadow:0 0 0 4px rgba(62,197,224,.16)}',
        'select:not([multiple]):not([size]):disabled{opacity:.58;cursor:not-allowed}',
        'select:not([multiple]):not([size]) option{background:#fff;color:#172b3a}',
        'body.dark select:not([multiple]):not([size]){color-scheme:dark;background-color:var(--card,#111c2e)}',
        'body.dark select:not([multiple]):not([size]) option{background:#111c2e;color:#e7edf5}',
        'button.ajax-submitting{opacity:.7;cursor:wait}',
        '.site-select-wrap{position:relative;display:grid;grid-template-columns:minmax(0,1fr);width:100%;min-width:0;vertical-align:middle}',
        '.site-select-native{position:absolute!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;clip-path:inset(50%)!important;white-space:nowrap!important;border:0!important}',
        '.site-select-trigger{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-width:0;max-width:100%;box-sizing:border-box;min-height:42px;padding:10px 14px;border:1px solid var(--border,#dce5eb);border-radius:12px;background:var(--card,#fff);color:var(--text,#172b3a);font-family:inherit;font-size:13px;font-weight:600;line-height:1.35;text-align:left;box-shadow:0 1px 2px rgba(15,35,50,.04);cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s}',
        '.site-select-trigger:hover,.site-select-trigger[aria-expanded=\"true\"]{border-color:var(--lime,#3ec5e0)}',
        '.site-select-trigger:focus-visible{outline:0;border-color:var(--lime,#3ec5e0);box-shadow:0 0 0 4px rgba(62,197,224,.16)}',
        '.site-select-trigger:disabled{opacity:.58;cursor:not-allowed}',
        '.site-select-value{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
        '.site-select-chevron{display:block;box-sizing:border-box;align-self:center;flex:0 0 8px;width:8px;height:8px;margin:0 3px 0 0;border-right:2px solid var(--muted,#68788f);border-bottom:2px solid var(--muted,#68788f);transform:rotate(45deg);transform-origin:center;transition:transform .18s}',
        '.site-select-trigger[aria-expanded=\"true\"] .site-select-chevron{transform:rotate(225deg)}',
        '.site-select-menu{position:fixed;z-index:2147483645;max-height:280px;overflow:auto;padding:5px;border:1px solid var(--border,#dce5eb);border-radius:13px;background:var(--card,#fff);box-shadow:0 16px 40px rgba(16,24,40,.2);overscroll-behavior:contain}',
        '.site-select-menu[hidden]{display:none}',
        '.site-select-option{display:block;width:100%;padding:10px 11px;border:0;border-radius:8px;background:transparent;color:var(--text,#172b3a);font-family:inherit;font-size:13px;font-weight:500;line-height:1.4;text-align:left;cursor:pointer;transition:background .15s,color .15s}',
        '.site-select-option:hover,.site-select-option:focus-visible{outline:0;background:var(--lime-soft,#e4f6fb);color:var(--forest,#0e5a75)}',
        '.site-select-option.selected{background:var(--lime-soft,#e4f6fb);color:var(--forest,#0e5a75);font-weight:700}',
        '.site-select-option:disabled{opacity:.5;cursor:not-allowed}',
        'body.dark .site-select-menu{border-color:var(--border,#1e2c42)}',
        'body.dark .site-select-option:hover,body.dark .site-select-option:focus-visible,body.dark .site-select-option.selected{background:rgba(62,197,224,.12);color:#4ddcf5}',
        '.k-link{min-width:0}.k-link .s-label{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.k-link .k-badge{flex:0 0 auto;margin-left:auto}',
        '.k-sub-link{min-width:0}.k-sub-link .s-label{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.k-sub-link .k-badge{flex:0 0 auto;margin-left:auto}',
        '@media(max-width:600px){select:not([multiple]):not([size]){min-height:44px;font-size:16px}}'
    ].join('');
    document.head.appendChild(controlStyle);
    document.head.querySelectorAll('style:not([data-ajax-navigation])').forEach(function(pageStyle) {
        if (!pageStyle.hasAttribute('data-site-controls')) pageStyle.setAttribute('data-ajax-page', '');
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePage, { once: true });
    } else {
        initializePage();
    }
    new MutationObserver(function() {
        initializeCustomSelects();
    }).observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', function(event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var anchor = event.target.closest && event.target.closest('a');
        if (!anchor || !anchor.href || anchor.target === '_blank' || anchor.hasAttribute('download')) return;

        var target;
        try {
            target = new URL(anchor.href, location.href);
        } catch (error) {
            return;
        }
        if (target.origin !== location.origin || shouldUseRegularNavigation(target)) return;
        if (target.pathname === location.pathname && target.search === location.search && target.hash) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        navigateTo(target, true, null);
    });

    document.addEventListener('pointerover', function(event) {
        var anchor = event.target.closest && event.target.closest('a');
        if (!anchor || event.relatedTarget && anchor.contains(event.relatedTarget)) return;
        var target;
        try {
            target = new URL(anchor.href, location.href);
        } catch (error) {
            return;
        }
        if (target.origin !== location.origin || shouldUseRegularNavigation(target) || anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        var timer = window.setTimeout(function() {
            prefetchTimers.delete(anchor);
            if (anchor.isConnected && !navigationPending) prefetchPage(target);
        }, 120);
        prefetchTimers.set(anchor, timer);
    }, true);

    document.addEventListener('pointerout', function(event) {
        var anchor = event.target.closest && event.target.closest('a');
        if (!anchor || event.relatedTarget && anchor.contains(event.relatedTarget)) return;
        var timer = prefetchTimers.get(anchor);
        if (timer) {
            window.clearTimeout(timer);
            prefetchTimers.delete(anchor);
        }
    }, true);

    document.addEventListener('touchstart', function(event) {
        var anchor = event.target.closest && event.target.closest('a');
        if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        try {
            var target = new URL(anchor.href, location.href);
            if (target.origin === location.origin && !shouldUseRegularNavigation(target) && !navigationPending) prefetchPage(target);
        } catch (error) {
            return;
        }
    }, { capture: true, passive: true });

    document.addEventListener('focusin', function(event) {
        var anchor = event.target.closest && event.target.closest('a');
        if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        try {
            var target = new URL(anchor.href, location.href);
            if (target.origin === location.origin && !shouldUseRegularNavigation(target) && !navigationPending) prefetchPage(target);
        } catch (error) {
            return;
        }
    });

    document.addEventListener('submit', submitAjaxForm);

    window.addEventListener('error', function(event) {
        if (event.error && event.error.message) showError(event.error);
        else if (event.message) showError(new Error(event.message));
    });
    window.addEventListener('unhandledrejection', function(event) {
        var reason = event.reason;
        showError(reason instanceof Error ? reason : new Error(String(reason || 'A page request failed unexpectedly.')));
    });

    window.addEventListener('popstate', function(event) {
        event.stopImmediatePropagation();
        navigateTo(new URL(location.href), false, event.state && event.state.ajaxPageId);
    });
})();
