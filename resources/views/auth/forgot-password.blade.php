<x-guest-layout>
    <h5 class="mb-3">Forgot password</h5>

    <p class="text-muted small">
        Enter your email address and we will email you a link to choose a new password.
    </p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a class="small" href="{{ route('login') }}">Back to login</a>
            <x-primary-button>Email Password Reset Link</x-primary-button>
        </div>
    </form>
</x-guest-layout>
