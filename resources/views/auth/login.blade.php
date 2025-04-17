<x-auth-layout>
    <!-- Register -->
    <div class="card">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
                <a href="{{ route('dashboard') }}" class="app-brand-link gap-2">
                    <span class="app-brand-text demo text-body fw-bolder">Click'n Eat</span>
                </a>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-3">
                    {{ session('status') }}
                </div>
            @endif

            <h4 class="mb-2">Bienvenue sur Click'n Eat! 👋</h4>
            <p class="mb-4">Veuillez vous connecter pour accéder à votre compte</p>

            <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="text"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        placeholder="Entrez votre email"
                        value="{{ old('email') }}"
                        autofocus
                    />
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3 form-password-toggle">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">Mot de passe</label>
                        @if (\Illuminate\Support\Facades\Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                <small>Mot de passe oublié?</small>
                            </a>
                        @endif
                    </div>
                    <div class="input-group input-group-merge">
                        <input
                            type="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                            aria-describedby="password"
                        />
                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember" />
                        <label class="form-check-label" for="remember_me"> Se souvenir de moi </label>
                    </div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Se connecter</button>
                </div>
            </form>

            <p class="text-center">
                <span>Nouveau sur Click'n Eat?</span>
                <a href="{{ route('register') }}">
                    <span>Créer un compte</span>
                </a>
            </p>
        </div>
    </div>
    <!-- /Register -->
</x-auth-layout>
