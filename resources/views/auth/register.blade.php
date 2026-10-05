<x-guest-layout>
    {{-- novalidate: jQuery Validation shows the messages instead of the browser popups --}}
    <form method="POST" action="{{ route('register') }}" data-validate="register" novalidate>
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone (optional)')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="e.g. 9876543210" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Shown to logged-in buyers on your ads.') }}</p>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                <button type="button" id="toggle-password" aria-pressed="false" aria-controls="password"
                        class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Show') }}</button>
            </div>

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" aria-describedby="password-help" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <div id="password-help" class="mt-2 rounded-md bg-gray-50 p-3 text-sm">
                <div id="password-strength" class="mb-2 hidden" aria-live="polite">
                    <div class="h-1.5 w-full rounded-full bg-gray-200">
                        <div data-bar class="h-1.5 rounded-full"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">{{ __('Strength:') }} <span data-label class="font-semibold"></span></p>
                </div>

                <p class="font-medium text-gray-700">{{ __('Your password must have:') }}</p>
                <ul class="mt-1 space-y-0.5">
                    @foreach ([
                        'length' => __('At least 8 characters'),
                        'case' => __('Uppercase and lowercase letters (A-z)'),
                        'number' => __('At least one number (0-9)'),
                        'symbol' => __('At least one symbol (e.g. ! @ # $ %)'),
                    ] as $rule => $label)
                        <li data-rule="{{ $rule }}" class="flex items-center gap-2 text-gray-500">
                            <span data-icon class="w-4 text-center" aria-hidden="true">○</span>{{ $label }}
                        </li>
                    @endforeach
                </ul>

                <p class="mt-2 text-xs text-gray-500">
                    {{ __('Tip: a short sentence like "Chai@7pm-daily!" is easy to remember and hard to guess.') }}
                </p>
                <button type="button" id="suggest-password" class="mt-2 text-sm font-medium text-indigo-600 hover:underline">
                    {{ __('Suggest a strong password') }}
                </button>
                <p id="suggested-note" class="mt-1 hidden text-xs text-amber-700">
                    {{ __('Save this password somewhere safe (e.g. your password manager) before registering.') }}
                </p>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    @vite('resources/js/auth-validation.js')
</x-guest-layout>
