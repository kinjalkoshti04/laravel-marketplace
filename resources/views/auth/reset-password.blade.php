<x-guest-layout>
    <h5 class="mb-3">Reset password</h5>

    <form method="POST" action="{{ route('password.store') }}" data-validate="reset-password" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required maxlength="255" autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="password" value="New Password" />
                <a href="#" data-toggle-password="password" class="small" aria-pressed="false" aria-controls="password">Show</a>
            </div>
            <x-text-input id="password" type="password" name="password" required data-rule-strongpassword="true" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
            <x-password-help />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required data-rule-equalto="#password" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="text-end">
            <x-primary-button>Reset Password</x-primary-button>
        </div>
    </form>
</x-guest-layout>
