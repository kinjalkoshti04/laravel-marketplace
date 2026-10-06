@props(['password' => 'password', 'confirm' => 'password_confirmation'])

{{-- Live checklist + strength meter + "suggest a strong password" (see resources/js/form-validation.js). --}}
<div id="{{ $password }}-help" data-password-help data-password="#{{ $password }}" data-confirm="#{{ $confirm }}"
     class="border rounded bg-light p-2 mt-2 small">
    <div id="{{ $password }}-strength" data-strength class="mb-2 d-none" aria-live="polite">
        <div class="progress" style="height: 6px;">
            <div data-bar class="progress-bar" role="progressbar"></div>
        </div>
        <div class="text-muted mt-1">Strength: <strong data-label></strong></div>
    </div>

    <div class="fw-semibold">Your password must have:</div>
    <ul class="list-unstyled mb-1">
        @foreach ([
            'length' => 'At least 8 characters',
            'case' => 'Uppercase and lowercase letters (A-z)',
            'number' => 'At least one number (0-9)',
            'symbol' => 'At least one symbol (e.g. ! @ # $ %)',
        ] as $rule => $label)
            <li data-rule="{{ $rule }}" class="text-muted">
                <span data-icon aria-hidden="true">○</span> {{ $label }}
            </li>
        @endforeach
    </ul>

    <a href="#" id="suggest-{{ $password }}" data-suggest-password>Suggest a strong password</a>
    <div id="suggested-{{ $password }}-note" data-suggested-note class="text-warning-emphasis d-none mt-1">
        Save this password somewhere safe before continuing.
    </div>
</div>
