<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <div id="profile-toast" role="status" aria-live="polite"
         class="fixed bottom-5 right-5 z-50 hidden rounded-lg bg-gray-900 px-5 py-3 text-sm text-white shadow-lg"></div>

    <script>
        (function () {
            var toast = document.getElementById('profile-toast');
            var toastTimer;

            function notify(message) {
                toast.textContent = message;
                toast.classList.remove('hidden');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function () { toast.classList.add('hidden'); }, 3500);
            }

            function clearErrors(form) {
                form.querySelectorAll('[data-ajax-error]').forEach(function (element) {
                    element.textContent = '';
                    element.classList.add('hidden');
                });
                form.querySelectorAll('[aria-invalid="true"]').forEach(function (input) {
                    input.removeAttribute('aria-invalid');
                });
            }

            document.querySelectorAll('form[data-ajax-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    clearErrors(form);
                    var submit = form.querySelector('button[type="submit"], button:not([type])');
                    if (submit) submit.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) {
                        return response.json().catch(function () { return {}; }).then(function (data) {
                            return { ok: response.ok, status: response.status, data: data };
                        });
                    })
                    .then(function (result) {
                        if (result.ok && result.data.ok !== false) {
                            if (form.dataset.ajaxDelete) {
                                window.location.assign(result.data.redirect || '/');
                                return;
                            }
                            if (form.action.indexOf('verification') !== -1) {
                                notify(result.data.message || 'A new verification link has been sent.');
                                return;
                            }
                            if (form.action.indexOf('password') !== -1) form.reset();
                            notify(result.data.message || form.dataset.ajaxSuccess || 'Changes saved.');
                            return;
                        }

                        var errors = result.data.errors || {};
                        Object.keys(errors).forEach(function (field) {
                            var input = form.querySelector('[name="' + field + '"]');
                            var error = form.querySelector('[data-ajax-error="' + field + '"]');
                            if (input) input.setAttribute('aria-invalid', 'true');
                            if (error) {
                                error.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                                error.classList.remove('hidden');
                            }
                        });
                        if (!Object.keys(errors).length) {
                            notify(result.data.message || 'Unable to save. Please try again.');
                        }
                    })
                    .catch(function () { notify('Network error. Please try again.'); })
                    .finally(function () {
                        if (submit) submit.disabled = false;
                    });
                });
            });
        })();
    </script>
</x-app-layout>
