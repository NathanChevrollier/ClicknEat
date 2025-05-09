@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / Commandes /</span> Ajouter une commande
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nouvelle commande</h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.orders.store') }}" method="POST">
                @csrf

                <!-- Client -->
                <div class="mb-3">
                    <label for="user_id" class="form-label">Client</label>
                    <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un client</option>
                        @foreach(\App\Models\User::where('role', 'client')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Restaurant -->
                <div class="mb-3">
                    <label for="restaurant_id" class="form-label">Restaurant</label>
                    <select id="restaurant_id" name="restaurant_id" class="form-select @error('restaurant_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un restaurant</option>
                        @foreach(\App\Models\Restaurant::all() as $restaurant)
                            <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                        @endforeach
                    </select>
                    @error('restaurant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Menus -->
                <div class="mb-3">
                    <label class="form-label">Menus</label>
                    <div id="menus-container" class="mb-2">
                        <!-- Les menus seront ajoutés ici dynamiquement -->
                    </div>
                    <a href="javascript:void(0);" onclick="addMenu()" class="btn btn-success btn-lg">
                        <i class="bx bx-plus me-1"></i> Ajouter un menu
                    </a>
                </div>

                <!-- Plats -->
                <div class="mb-3">
                    <label class="form-label">Plats</label>
                    <div id="items-container" class="mb-2">
                        <!-- Les plats seront ajoutés ici dynamiquement -->
                    </div>
                    <a href="javascript:void(0);" onclick="addItem()" class="btn btn-info btn-lg">
                        <i class="bx bx-plus me-1"></i> Ajouter un plat
                    </a>
                </div>

                <!-- Statut -->
                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="pending">En attente</option>
                        <option value="confirmed">Confirmée</option>
                        <option value="preparing">En préparation</option>
                        <option value="ready">Prête</option>
                        <option value="completed">Terminée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Adresse de livraison -->
                <div class="mb-3">
                    <label for="delivery_address" class="form-label">Adresse de livraison</label>
                    <textarea id="delivery_address" name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="3"></textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"></textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Créer la commande</button>
            </form>
        </div>
    </div>
</div>

<!-- Données pour le JavaScript -->
<script>
    // Pré-charger les données des restaurants, menus et plats
    const restaurantsData = {
        @foreach(\App\Models\Restaurant::all() as $restaurant)
            {{ $restaurant->id }}: {
                menus: [
                    @foreach(\App\Models\Menu::where('restaurant_id', $restaurant->id)->get() as $menu)
                        {
                            id: {{ $menu->id }},
                            name: "{{ $menu->name }}",
                            price: {{ $menu->price }}
                        },
                    @endforeach
                ],
                items: [
                    @foreach(\App\Models\Item::whereHas('category', function($query) use ($restaurant) {
                        $query->where('restaurant_id', $restaurant->id);
                    })->get() as $item)
                        {
                            id: {{ $item->id }},
                            name: "{{ $item->name }}",
                            price: {{ $item->price }}
                        },
                    @endforeach
                ]
            },
        @endforeach
    };
</script>

@push('scripts')
<script>
    // Variables globales
    let itemCount = 0;
    let menuCount = 0;
    let restaurantSelect;
    let itemsContainer;
    let menusContainer;

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        restaurantSelect = document.getElementById('restaurant_id');
        itemsContainer = document.getElementById('items-container');
        menusContainer = document.getElementById('menus-container');

        // Activer/désactiver les champs de plats en fonction de la sélection du restaurant
        restaurantSelect.addEventListener('change', function() {
            const restaurantId = this.value;
            
            // Réinitialiser les plats et les menus
            itemsContainer.innerHTML = '';
            menusContainer.innerHTML = '';
            itemCount = 0;
            menuCount = 0;
        });
    });

    // Fonction pour ajouter un plat (accessible globalement)
    function addItem() {
        console.log('Fonction addItem appelée');
        
        if (!restaurantSelect.value) {
            alert('Veuillez d\'abord sélectionner un restaurant');
            return;
        }

        console.log('Restaurant sélectionné:', restaurantSelect.value);

        const row = document.createElement('div');
        row.className = 'row mb-2 item-row';
        row.innerHTML = `
            <div class="col-md-6">
                <select name="items[${itemCount}][id]" class="form-select item-select" required>
                    <option value="">Sélectionner un plat</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemCount}][quantity]" class="form-control" placeholder="Quantité" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.item-row').remove();">
                    <i class="bx bx-trash"></i>
                </a>
            </div>
        `;
        
        console.log('Ajout de la ligne au conteneur');
        itemsContainer.appendChild(row);
        
        // Charger les plats du restaurant sélectionné
        const restaurantId = restaurantSelect.value;
        const itemSelect = row.querySelector('.item-select');
        
        // Utiliser les données pré-chargées
        const items = restaurantsData[restaurantId]?.items || [];
        console.log('Plats disponibles:', items);
        
        if (items.length === 0) {
            itemSelect.innerHTML = '<option value="">Aucun plat disponible</option>';
        } else {
            itemSelect.innerHTML = '<option value="">Sélectionner un plat</option>';
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} (${(item.price / 100).toFixed(2)} €)`;
                itemSelect.appendChild(option);
            });
        }
        
        itemCount++;
    }

    // Fonction pour ajouter un menu (accessible globalement)
    function addMenu() {
        console.log('Fonction addMenu appelée');
        
        if (!restaurantSelect.value) {
            alert('Veuillez d\'abord sélectionner un restaurant');
            return;
        }

        console.log('Restaurant sélectionné:', restaurantSelect.value);

        const row = document.createElement('div');
        row.className = 'row mb-2 menu-row';
        row.innerHTML = `
            <div class="col-md-6">
                <select name="menus[${menuCount}][id]" class="form-select menu-select" required>
                    <option value="">Sélectionner un menu</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="menus[${menuCount}][quantity]" class="form-control" placeholder="Quantité" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.menu-row').remove();">
                    <i class="bx bx-trash"></i>
                </a>
            </div>
        `;
        
        console.log('Ajout de la ligne au conteneur');
        menusContainer.appendChild(row);
        
        // Charger les menus du restaurant sélectionné
        const restaurantId = restaurantSelect.value;
        const menuSelect = row.querySelector('.menu-select');
        
        // Utiliser les données pré-chargées
        const menus = restaurantsData[restaurantId]?.menus || [];
        console.log('Menus disponibles:', menus);
        
        if (menus.length === 0) {
            menuSelect.innerHTML = '<option value="">Aucun menu disponible</option>';
        } else {
            menuSelect.innerHTML = '<option value="">Sélectionner un menu</option>';
            menus.forEach(menu => {
                const option = document.createElement('option');
                option.value = menu.id;
                option.textContent = `${menu.name} (${(menu.price / 100).toFixed(2)} €)`;
                menuSelect.appendChild(option);
            });
        }
        
        menuCount++;
    }
</script>
@endpush
@endsection
