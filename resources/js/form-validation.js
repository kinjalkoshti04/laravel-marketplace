/**
 * jQuery Validation for every form with a `data-validate` attribute.
 *
 * The browser's built-in validation is turned off (`novalidate` on the forms) so these
 * messages are shown instead. Rules come from the normal HTML attributes (required,
 * minlength, maxlength, min, max, type="email", type="number") plus `data-rule-*`
 * attributes for the custom methods below. Laravel validates everything again on the
 * server; the password rules here mirror Password::defaults() in AppServiceProvider.
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
    { label: 'Too weak', width: '10%', color: 'bg-danger' },
    { label: 'Weak', width: '25%', color: 'bg-danger' },
    { label: 'Fair', width: '50%', color: 'bg-warning' },
    { label: 'Good', width: '75%', color: 'bg-info' },
    { label: 'Strong', width: '100%', color: 'bg-success' },
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

/* ------------------------------------------------------------------ custom methods */

$.validator.addMethod('strongPassword', (value) => passesAll(value), 'Password does not meet all the requirements.');

$.validator.addMethod(
    'phone',
    function (value, element) {
        return this.optional(element) || /^\+?[0-9][0-9\s-]{6,18}$/.test(value.trim());
    },
    'Please enter a valid phone number.',
);

// data-rule-filetypes="jpg,jpeg,png,webp"
$.validator.addMethod(
    'fileTypes',
    (value, element, types) => {
        const allowed = String(types).split(',');
        return [...element.files].every((f) => allowed.includes(f.name.split('.').pop().toLowerCase()));
    },
    (types) => `Only ${String(types).toUpperCase().replaceAll(',', ', ')} files are allowed.`,
);

// data-rule-maxfilesize="4" (MB, per file)
$.validator.addMethod(
    'maxFileSize',
    (value, element, mb) => [...element.files].every((f) => f.size <= mb * 1024 * 1024),
    (mb) => `Each photo must be ${mb} MB or smaller.`,
);

// data-rule-photolimit="5": photos kept (not ticked for removal) + new uploads.
$.validator.addMethod(
    'photoLimit',
    (value, element, limit) => {
        const kept = $(element.form).find('input[name="remove_images[]"]:not(:checked)').length;
        return kept + element.files.length <= limit;
    },
    (limit) => `A listing can have at most ${limit} photos in total.`,
);

// data-rule-notlessthan="#min_price": only checked when both fields have a value.
$.validator.addMethod(
    'notLessThan',
    function (value, element, other) {
        const otherValue = $(other).val();
        return this.optional(element) || otherValue === '' || Number(value) >= Number(otherValue);
    },
    'Max price must be greater than or equal to min price.',
);

$.extend($.validator.messages, {
    required: 'This field is required.',
    email: 'Please enter a valid email address.',
    number: 'Please enter a valid number.',
    step: 'Please enter a valid amount (up to 2 decimals).',
    min: $.validator.format('Please enter a value of at least {0}.'),
    max: $.validator.format('Please enter a value no greater than {0}.'),
    minlength: $.validator.format('Please enter at least {0} characters.'),
    maxlength: $.validator.format('Please enter no more than {0} characters.'),
    equalTo: 'The two passwords do not match.',
});

/* ------------------------------------------------------------------ defaults */

const INVALID = 'is-invalid';
const FIELDS = 'input, select, textarea';

$.validator.setDefaults({
    errorElement: 'div',
    errorClass: 'invalid-feedback',
    errorPlacement(error, element) {
        // `js-error` makes it visible even when it is not right after the input
        // (radios, checkboxes); jQuery Validation still hides it with an inline style.
        error.addClass('js-error');

        if (element.is(':radio, :checkbox')) {
            error.appendTo(element.closest('.mb-3'));
        } else if (element.parent().hasClass('input-group')) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    },
    highlight(element) {
        $(element.form).find(`[name="${element.name}"]`).addClass(INVALID).attr('aria-invalid', 'true');
    },
    unhighlight(element) {
        $(element.form).find(`[name="${element.name}"]`).removeClass(INVALID).attr('aria-invalid', 'false');
    },
    submitHandler(form) {
        $(form).find('button[type=submit]').prop('disabled', true);
        form.submit();
    },
});

/* ------------------------------------------------------------------ password helper */

/**
 * Live checklist, strength meter, show/hide and "suggest a strong password" for a
 * `[data-password-help]` box. Works for any password + confirmation pair.
 */
function initPasswordHelp($help) {
    const $password = $($help.data('password'));
    const $confirm = $($help.data('confirm'));
    const $meter = $help.find('[data-strength]');
    const $toggle = $(`[data-toggle-password="${$password.attr('id')}"]`);

    function refresh() {
        const value = $password.val();

        $help.find('[data-rule]').each(function () {
            const met = PASSWORD_RULES[$(this).data('rule')](value);
            $(this).toggleClass('text-success', met).toggleClass('text-muted', !met).find('[data-icon]').text(met ? '✓' : '○');
        });

        const level = strengthOf(value);
        $meter.toggleClass('d-none', level === null);
        if (level !== null) {
            const s = STRENGTH[level];
            $meter.find('[data-bar]').attr('class', `progress-bar ${s.color}`).css('width', s.width);
            $meter.find('[data-label]').text(s.label);
        }

        // Re-check the confirmation once the user has typed in it.
        if ($confirm.val() && $confirm.closest('form').data('validator')) $confirm.valid();
    }

    $password.on('input', refresh);
    refresh();

    $toggle.on('click', function (event) {
        event.preventDefault();
        const show = $password.attr('type') === 'password';
        $password.add($confirm).attr('type', show ? 'text' : 'password');
        $(this).text(show ? 'Hide' : 'Show').attr('aria-pressed', show);
    });

    $help.find('[data-suggest-password]').on('click', function (event) {
        event.preventDefault();
        const suggestion = suggestPassword();
        $password.val(suggestion).trigger('input');
        $confirm.val(suggestion);
        $password.add($confirm).attr('type', 'text');
        $toggle.text('Hide').attr('aria-pressed', true);
        $help.find('[data-suggested-note]').removeClass('d-none');
        $password.valid();
        $confirm.valid();
    });
}

/* ------------------------------------------------------------------ per-form options */

const FORM_OPTIONS = {
    login: {
        messages: {
            email: { required: 'Please enter your email address.' },
            password: { required: 'Please enter your password.' },
        },
    },
    register: {
        rules: {
            name: { normalizer: (v) => v.trim() },
            email: { normalizer: (v) => v.trim() },
        },
        messages: {
            name: { required: 'Please enter your name.', minlength: 'Name must be at least 2 characters.' },
            email: { required: 'Please enter your email address.' },
            password: { required: 'Please enter a password.', strongPassword: 'Password does not meet all the requirements below.' },
            password_confirmation: { required: 'Please confirm your password.' },
        },
    },
};

/* ------------------------------------------------------------------ init */

$(function () {
    const $forms = $('form[data-validate]');

    // Fields with a server-side (Laravel) error get the same red border.
    $forms.find('[data-server-error]').each(function () {
        $(this).parent().find(FIELDS).first().addClass(INVALID);
    });

    $forms.on('input change', FIELDS, function () {
        // The server error is removed as soon as the user edits that field.
        const $wrapper = $(this).closest('.mb-2, .mb-3');
        const $serverError = ($wrapper.length ? $wrapper : $(this).closest('div')).find('[data-server-error]');
        if ($serverError.length) {
            $serverError.remove();
            $(this).removeClass(INVALID);
        }

        // jQuery Validation re-checks on keyup only, so a value that is pasted, autofilled
        // or picked from a select would keep its old error until blur.
        const validator = $(this.form).data('validator');
        if (validator && this.name && (this.name in validator.submitted || this.name in validator.invalid)) {
            validator.element(this);
        }
    });

    $forms.each(function () {
        $(this).validate(FORM_OPTIONS[$(this).data('validate')] ?? {});
    });

    // Ticking "remove" on an existing photo changes how many new photos are allowed.
    $('input[name="remove_images[]"]').on('change', function () {
        const $images = $(this.form).find('#images');
        if ($images.val()) $images.valid();
    });

    $('[data-password-help]').each(function () {
        initPasswordHelp($(this));
    });
});
