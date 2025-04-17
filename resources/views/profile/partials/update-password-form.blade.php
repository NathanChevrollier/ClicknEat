<section>
    <p class="mb-4 text-muted">
        Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester en sécurité.
    </p>

    <form method="post" action="{{ route('password.update') }}" class="mb-3">
        @csrf
        @method('put')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="update_password_current_password" class="form-label">Mot de passe actuel</label>
                <div class="input-group input-group-merge">
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('current_password', 'updatePassword')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="update_password_password" class="form-label">Nouveau mot de passe</label>
                <div class="input-group input-group-merge">
                    <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('password', 'updatePassword')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="update_password_password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <div class="input-group input-group-merge">
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">
                <i class="bx bx-lock-alt me-1"></i> Mettre à jour le mot de passe
            </button>

            @if (session('status') === 'password-updated')
                <div class="alert alert-success d-inline-block mt-2 mb-0 py-1 px-2">
                    <i class="bx bx-check me-1"></i> Mot de passe mis à jour !
                </div>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePasswordIcons = document.querySelectorAll('.input-group-text');
            
            togglePasswordIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const iconElement = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        iconElement.classList.remove('bx-hide');
                        iconElement.classList.add('bx-show');
                    } else {
                        input.type = 'password';
                        iconElement.classList.remove('bx-show');
                        iconElement.classList.add('bx-hide');
                    }
                });
            });
        });
    </script>
</section>
