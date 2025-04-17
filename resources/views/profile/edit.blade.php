@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Compte /</span> Profil</h4>

    <div class="row">
        <div class="col-md-12">
            <!-- Profile Information Card -->
            <div class="card mb-4">
                <h5 class="card-header">Informations du profil</h5>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
            <!-- /Profile Information Card -->

            <!-- Update Password Card -->
            <div class="card mb-4">
                <h5 class="card-header">Mettre à jour le mot de passe</h5>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <!-- /Update Password Card -->

            <!-- Delete Account Card -->
            <div class="card mb-4 border-danger">
                <h5 class="card-header text-danger">Zone dangereuse</h5>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            <!-- /Delete Account Card -->
        </div>
    </div>
</div>
@endsection
