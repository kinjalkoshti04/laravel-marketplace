<x-guest-layout>
    <h5 class="mb-3">Log in</h5>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    {{-- novalidate: jQuery Validation shows the messages instead of the browser popups --}}
    <form method="POST" action="{{ route('login') }}" data-validate="login" novalidate>
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required maxlength="255" autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Remember me</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a class="small" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif

            <x-primary-button>Log in</x-primary-button>
        </div>
    </form>

    <hr>
    <p class="text-center small mb-0">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
</x-guest-layout>
