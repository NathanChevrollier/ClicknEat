<section>
    <p class="mb-4 text-muted">
        Une fois que votre compte est supprimé, toutes ses ressources et données seront définitivement supprimées. Avant de supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.
    </p>

    <div class="mt-4">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            <i class="bx bx-trash me-1"></i> Supprimer le compte
        </button>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
                @csrf
                @method('delete')
                
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">Êtes-vous sûr de vouloir supprimer votre compte ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <p>Une fois votre compte supprimé, toutes ses ressources et données seront définitivement supprimées. Veuillez entrer votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.</p>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group input-group-merge">
                            <input id="password" name="password" type="password" class="form-control" placeholder="Entrez votre mot de passe" />
                            <span class="input-group-text cursor-pointer toggle-password"><i class="bx bx-hide"></i></span>
                        </div>
                        @error('password', 'userDeletion')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer le compte</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
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
            }
        });
    </script>
</section>
