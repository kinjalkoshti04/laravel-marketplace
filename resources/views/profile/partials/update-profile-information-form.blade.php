<section>
    <h5>Profile Information</h5>
    <p class="text-muted small">Update your name, email address and phone number.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" data-validate="profile" novalidate>
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required minlength="2" maxlength="255" autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required maxlength="255" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="small mt-2 mb-0">
                    Your email address is unverified.
                    <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">Click here to re-send the verification email.</button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="small text-success mt-2 mb-0">A new verification link has been sent to your email address.</p>
                @endif
            @endif
        </div>

        <div class="mb-3">
            <x-input-label for="phone" value="Phone (optional)" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone', $user->phone)" data-rule-phone="true" autocomplete="tel" placeholder="e.g. 9876543210" />
            <div class="form-text">Shown to logged-in buyers on your ads.</div>
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <x-primary-button>Save</x-primary-button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small ms-2">Saved.</span>
        @endif
    </form>
</section>
