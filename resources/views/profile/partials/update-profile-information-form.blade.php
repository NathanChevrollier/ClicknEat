<section>
    <p class="mb-4 text-muted">
        Mettez à jour les informations de votre profil et votre adresse e-mail.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mb-3">
        @csrf
        @method('patch')

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nom</label>
                <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                @error('email')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning mt-2">
                        <div class="d-flex">
                            <i class="bx bx-error-circle me-2"></i>
                            <div>
                                <div>Votre adresse e-mail n'est pas vérifiée.</div>
                                <button form="send-verification" class="btn btn-sm btn-outline-primary mt-2">
                                    Cliquez ici pour renvoyer l'e-mail de vérification.
                                </button>
                            </div>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2">
                            Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">
                <i class="bx bx-save me-1"></i> Enregistrer
            </button>

            @if (session('status') === 'profile-updated')
                <div class="alert alert-success d-inline-block mt-2 mb-0 py-1 px-2">
                    <i class="bx bx-check me-1"></i> Enregistré !
                </div>
            @endif
        </div>
    </form>
</section>
