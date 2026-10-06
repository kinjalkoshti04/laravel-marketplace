<x-guest-layout>
    <h5 class="mb-3">Create an account</h5>

    {{-- novalidate: jQuery Validation shows the messages instead of the browser popups --}}
    <form method="POST" action="{{ route('register') }}" data-validate="register" novalidate>
        @csrf

        <div class="mb-3">
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="phone" value="Phone (optional)" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="e.g. 9876543210" />
            <div class="form-text">Shown to logged-in buyers on your ads.</div>
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="password" value="Password" />
                <a href="#" id="toggle-password" class="small" aria-pressed="false" aria-controls="password">Show</a>
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" aria-describedby="password-help" />
            <x-input-error :messages="$errors->get('password')" />

            <div id="password-help" class="border rounded bg-light p-2 mt-2 small">
                <div id="password-strength" class="mb-2 d-none" aria-live="polite">
                    <div class="progress" style="height: 6px;">
                        <div data-bar class="progress-bar" role="progressbar"></div>
                    </div>
                    <div class="text-muted mt-1">Strength: <strong data-label></strong></div>
                </div>

                <div class="fw-semibold">Your password must have:</div>
                <ul class="list-unstyled mb-1">
                    @foreach ([
                        'length' => 'At least 8 characters',
                        'case' => 'Uppercase and lowercase letters (A-z)',
                        'number' => 'At least one number (0-9)',
                        'symbol' => 'At least one symbol (e.g. ! @ # $ %)',
                    ] as $rule => $label)
                        <li data-rule="{{ $rule }}" class="text-muted">
                            <span data-icon aria-hidden="true">○</span> {{ $label }}
                        </li>
                    @endforeach
                </ul>

                <a href="#" id="suggest-password">Suggest a strong password</a>
                <div id="suggested-note" class="text-warning-emphasis d-none mt-1">
                    Save this password somewhere safe before registering.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a class="small" href="{{ route('login') }}">Already registered?</a>
            <x-primary-button>Register</x-primary-button>
        </div>
    </form>

    @vite('resources/js/auth-validation.js')
</x-guest-layout>
