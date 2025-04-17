@extends('layouts.public')

@section('content')
<!-- Hero Section -->
<div class="hero-section" style="background-image: url('{{ asset('assets/img/elements/food-bg.jpg') }}'); height: 400px; background-size: cover; background-position: center; position: relative;">
    <div class="hero-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);"></div>
    <div class="container hero-content text-center" style="position: relative; z-index: 1; padding-top: 120px; color: white;">
        <h1 class="display-4 fw-bold mb-3">Click'n Eat</h1>
        <p class="lead mb-4">La plateforme de commande de repas en ligne</p>
    </div>
</div>

<!-- How It Works Section -->
<div class="container py-5">
    <h2 class="text-center mb-5">Comment ça marche ?</h2>
    <div class="row g-4">
        <div class="col-md-4 text-center">
            <div class="feature-icon" style="font-size: 3rem; color: #696cff; margin-bottom: 1rem;">
                <i class="bx bx-search"></i>
            </div>
            <h3>Découvrez</h3>
            <p>Parcourez notre sélection de restaurants et trouvez vos plats préférés.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="feature-icon" style="font-size: 3rem; color: #696cff; margin-bottom: 1rem;">
                <i class="bx bx-cart"></i>
            </div>
            <h3>Commandez</h3>
            <p>Commandez facilement en quelques clics et personnalisez votre repas.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="feature-icon" style="font-size: 3rem; color: #696cff; margin-bottom: 1rem;">
                <i class="bx bx-dish"></i>
            </div>
            <h3>Dégustez</h3>
            <p>Récupérez votre commande et savourez votre repas où que vous soyez.</p>
        </div>
    </div>
</div>
@endsection
