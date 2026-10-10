<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" id="confirmPasswordForm">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <p id="confirmFeedback" class="hidden mt-3 text-sm text-red-600" role="alert"></p>

        <div class="flex justify-end mt-4">
            <x-primary-button id="confirmPasswordButton">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.getElementById('confirmPasswordForm').addEventListener('submit', async function (event) {
            event.preventDefault();
            const form = event.currentTarget;
            const button = document.getElementById('confirmPasswordButton');
            const feedback = document.getElementById('confirmFeedback');
            button.disabled = true;
            feedback.classList.add('hidden');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json().catch(function () { return {}; });
                if (!response.ok) {
                    const errors = data.errors ? Object.values(data.errors).flat() : [];
                    throw new Error(errors[0] || data.message || 'Password confirmation failed.');
                }
                if (!data.redirect) throw new Error('The server did not return the next page URL.');
                window.location.assign(data.redirect);
            } catch (error) {
                feedback.textContent = error.message || 'Something went wrong. Please try again.';
                feedback.classList.remove('hidden');
                button.disabled = false;
            }
        });
    </script>
</x-guest-layout>
