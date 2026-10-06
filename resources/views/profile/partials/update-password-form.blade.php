<section>
    <h5>Update Password</h5>
    <p class="text-muted small">
        Use at least 8 characters with uppercase and lowercase letters, a number and a symbol.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" value="Current Password" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password" value="New Password" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password_confirmation" value="Confirm Password" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <x-primary-button>Save</x-primary-button>

        @if (session('status') === 'password-updated')
            <span class="text-success small ms-2">Saved.</span>
        @endif
    </form>
</section>
