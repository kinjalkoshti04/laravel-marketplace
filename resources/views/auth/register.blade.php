<x-guest-layout>
    <h5 class="mb-3">Create an account</h5>

    {{-- novalidate: jQuery Validation shows the messages instead of the browser popups --}}
    <form method="POST" action="{{ route('register') }}" data-validate="register" novalidate>
        @csrf

        <div class="mb-3">
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required minlength="2" maxlength="255" autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required maxlength="255" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="phone" value="Phone (optional)" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" data-rule-phone="true" autocomplete="tel" placeholder="e.g. 9876543210" />
            <div class="form-text">Shown to logged-in buyers on your ads.</div>
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="password" value="Password" />
                <a href="#" id="toggle-password" data-toggle-password="password" class="small" aria-pressed="false" aria-controls="password">Show</a>
            </div>
            <x-text-input id="password" type="password" name="password" required data-rule-strongpassword="true" autocomplete="new-password" aria-describedby="password-help" />
            <x-input-error :messages="$errors->get('password')" />
            <x-password-help />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required data-rule-equalto="#password" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a class="small" href="{{ route('login') }}">Already registered?</a>
            <x-primary-button>Register</x-primary-button>
        </div>
    </form>
</x-guest-layout>
