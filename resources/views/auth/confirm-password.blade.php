<x-guest-layout>
    <p class="text-muted small">
        This is a secure area of the application. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="text-end">
            <x-primary-button>Confirm</x-primary-button>
        </div>
    </form>
</x-guest-layout>
