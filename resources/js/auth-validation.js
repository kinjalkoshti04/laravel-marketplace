/**
 * jQuery Validation for the login and register forms.
 *
 * The browser's built-in validation is turned off (`novalidate` on the forms) so these
 * messages are shown instead. Laravel validates everything again on the server; the
 * password rules here mirror Password::defaults() in AppServiceProvider.
 */
import $ from 'jquery';
import 'jquery-validation';

/* ------------------------------------------------------------------ password rules */

// Same character classes Laravel's Password rule uses (\p{Lu}, \p{Ll}, \p{N}, \p{Z}\p{S}\p{P}).
export const PASSWORD_RULES = {
    length: (v) => v.length >= 8 && v.length <= 64,
    case: (v) => /\p{Lu}/u.test(v) && /\p{Ll}/u.test(v),
    number: (v) => /\p{N}/u.test(v),
    symbol: (v) => /[\p{Z}\p{S}\p{P}]/u.test(v),
};

const passesAll = (value) => Object.values(PASSWORD_RULES).every((test) => test(value));

const STRENGTH = [
    { label: 'Too weak', width: '10%', color: 'bg-red-500' },
    { label: 'Weak', width: '25%', color: 'bg-red-500' },
    { label: 'Fair', width: '50%', color: 'bg-amber-500' },
    { label: 'Good', width: '75%', color: 'bg-lime-500' },
    { label: 'Strong', width: '100%', color: 'bg-green-600' },
];

function strengthOf(value) {
    if (!value) return null;
    const met = Object.values(PASSWORD_RULES).filter((test) => test(value)).length;
    // 4 rules met = Good; a long password that meets them all = Strong.
    return met === 4 && value.length >= 12 ? 4 : Math.min(met, 3);
}

/** Generates a 16-character password that satisfies every rule. */
function suggestPassword() {
    const sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%^&*?-_+='];
    const random = (max) => crypto.getRandomValues(new Uint32Array(1))[0] % max;
    const chars = sets.map((set) => set[random(set.length)]); // one from each set
    const all = sets.join('');
    while (chars.length < 16) chars.push(all[random(all.length)]);
    for (let i = chars.length - 1; i > 0; i--) {
        const j = random(i + 1);
        [chars[i], chars[j]] = [chars[j], chars[i]];
    }
    return chars.join('');
}

/* ------------------------------------------------------------------ jQuery Validation */

$.validator.addMethod('strongPassword', (value, element) => passesAll(value), 'Password does not meet all the requirements below.');
$.validator.addMethod(
    'phone',
    function (value, element) {
        return this.optional(element) || /^\+?[0-9][0-9\s-]{6,18}$/.test(value.trim());
    },
    'Please enter a valid phone number.',
);

const INVALID = 'border-red-500 focus:border-red-500 focus:ring-red-500';

$.validator.setDefaults({
    errorElement: 'p',
    errorClass: 'js-error',
    errorPlacement(error, element) {
        error.addClass('mt-2 text-sm text-red-600').insertAfter(element);
    },
    highlight(element) {
        $(element).addClass(INVALID).attr('aria-invalid', 'true');
    },
    unhighlight(element) {
        $(element).removeClass(INVALID).attr('aria-invalid', 'false');
    },
    submitHandler(form) {
        $(form).find('button[type=submit]').prop('disabled', true);
        form.submit();
    },
});

$(function () {
    // Fields with a server-side (Laravel) error get the same red border; the error
    // is removed as soon as the user edits that field.
    $('form[data-validate] [data-server-error]').each(function () {
        $(this).closest('div').find('input').first().addClass(INVALID);
    });
    $('form[data-validate]').on('input change', 'input', function () {
        const $serverError = $(this).closest('div').find('[data-server-error]');
        if ($serverError.length) {
            $serverError.remove();
            $(this).removeClass(INVALID);
        }
    });

    $('form[data-validate="login"]').validate({
        rules: {
            email: { required: true, email: true },
            password: { required: true },
        },
        messages: {
            email: { required: 'Please enter your email address.', email: 'Please enter a valid email address.' },
            password: { required: 'Please enter your password.' },
        },
    });

    const $register = $('form[data-validate="register"]');
    if (!$register.length) return;

    $register.validate({
        rules: {
            name: { required: true, minlength: 2, maxlength: 255, normalizer: (v) => v.trim() },
            email: { required: true, email: true, maxlength: 255, normalizer: (v) => v.trim() },
            phone: { phone: true },
            password: { required: true, strongPassword: true },
            password_confirmation: { required: true, equalTo: '#password' },
        },
        messages: {
            name: {
                required: 'Please enter your name.',
                minlength: 'Name must be at least 2 characters.',
            },
            email: { required: 'Please enter your email address.', email: 'Please enter a valid email address.' },
            password: { required: 'Please enter a password.' },
            password_confirmation: {
                required: 'Please confirm your password.',
                equalTo: 'The two passwords do not match.',
            },
        },
    });

    /* Live password checklist + strength meter */
    const $password = $('#password');
    const $confirm = $('#password_confirmation');
    const $meter = $('#password-strength');

    function refreshHelp() {
        const value = $password.val();

        $('#password-help [data-rule]').each(function () {
            const met = PASSWORD_RULES[$(this).data('rule')](value);
            $(this)
                .toggleClass('text-green-700', met)
                .toggleClass('text-gray-500', !met)
                .find('[data-icon]')
                .text(met ? '✓' : '○');
        });

        const level = strengthOf(value);
        $meter.toggleClass('hidden', level === null);
        if (level !== null) {
            const s = STRENGTH[level];
            $meter.find('[data-bar]').attr('class', `h-1.5 rounded-full transition-all ${s.color}`).css('width', s.width);
            $meter.find('[data-label]').text(s.label);
        }

        // Re-check the confirmation once the user has typed in it.
        if ($confirm.val()) $confirm.valid();
    }

    $password.on('input', refreshHelp);
    refreshHelp();

    /* Show / hide both password fields */
    $('#toggle-password').on('click', function () {
        const show = $password.attr('type') === 'password';
        $password.add($confirm).attr('type', show ? 'text' : 'password');
        $(this).text(show ? 'Hide' : 'Show').attr('aria-pressed', show);
    });

    /* Suggest a strong password: fills both fields and shows it so it can be saved */
    $('#suggest-password').on('click', function () {
        const suggestion = suggestPassword();
        $password.val(suggestion);
        $confirm.val(suggestion);
        $password.add($confirm).attr('type', 'text');
        $('#toggle-password').text('Hide').attr('aria-pressed', true);
        $('#suggested-note').removeClass('hidden');
        refreshHelp();
        $password.valid();
        $confirm.valid();
    });
});
