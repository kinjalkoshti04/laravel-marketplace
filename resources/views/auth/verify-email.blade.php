<x-guest-layout>
    <p class="text-muted small">
        Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
        If you didn't receive the email, we will gladly send you another.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 small">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Resend Verification Email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link btn-sm">Log Out</button>
        </form>
    </div>
</x-guest-layout>
