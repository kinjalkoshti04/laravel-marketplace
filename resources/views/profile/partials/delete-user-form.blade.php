<section>
    <h5 class="text-danger">Delete Account</h5>
    <p class="text-muted small">
        Once your account is deleted, all of your ads, photos and data will be permanently deleted.
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        Delete Account
    </button>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUserDeletionLabel">Are you sure you want to delete your account?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="small text-muted">Please enter your password to confirm you would like to permanently delete your account.</p>
                    <x-input-label for="delete_password" value="Password" class="visually-hidden" />
                    <x-text-input id="delete_password" name="password" type="password" placeholder="Password" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" />
                </div>

                <div class="modal-footer">
                    <x-secondary-button data-bs-dismiss="modal">Cancel</x-secondary-button>
                    <x-danger-button>Delete Account</x-danger-button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->userDeletion->isNotEmpty())
        {{-- Re-open the modal when the password was wrong. --}}
        <script type="module">
            bootstrap.Modal.getOrCreateInstance('#confirmUserDeletion').show();
        </script>
    @endif
</section>
