<section>
    <h5>Update Password</h5>
    <p class="text-muted small">Ensure your account is using a strong password to stay secure.</p>

    <form method="post" action="{{ route('password.update') }}" data-validate="update-password" novalidate>
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" value="Current Password" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" required autocomplete="current-password" data-msg-required="Please enter your current password." />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="update_password_password" value="New Password" />
                <a href="#" data-toggle-password="update_password_password" class="small" aria-pressed="false" aria-controls="update_password_password">Show</a>
            </div>
            <x-text-input id="update_password_password" name="password" type="password" required data-rule-strongpassword="true" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
            <x-password-help password="update_password_password" confirm="update_password_password_confirmation" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password_confirmation" value="Confirm Password" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" required data-rule-equalto="#update_password_password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <x-primary-button>Save</x-primary-button>

        @if (session('status') === 'password-updated')
            <span class="text-success small ms-2">Saved.</span>
        @endif
    </form>
</section>
