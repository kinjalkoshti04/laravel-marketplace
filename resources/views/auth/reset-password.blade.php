<x-guest-layout>
    <h5 class="mb-3">Reset password</h5>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="New Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <div class="form-text">At least 8 characters with uppercase and lowercase letters, a number and a symbol.</div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="text-end">
            <x-primary-button>Reset Password</x-primary-button>
        </div>
    </form>
</x-guest-layout>
