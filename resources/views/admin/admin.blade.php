@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h4 class="page-title">Gestion des Utilisateurs</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Utilisateurs</li>
                            </ol>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                        id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-filter me-1"></i>Type d'utilisateurs
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                        <li><a class="dropdown-item {{ request()->route('group') == 'all' ? 'active' : '' }}"
                                                href="{{ route('admin.users', ['group' => 'all']) }}">Tous</a></li>
                                        <li><a class="dropdown-item {{ request()->route('group') == 'admin' ? 'active' : '' }}"
                                                href="{{ route('admin.users', ['group' => 'admin']) }}">Administrateurs</a>
                                        </li>
                                        <li><a class="dropdown-item {{ request()->route('group') == 'annonceur' ? 'active' : '' }}"
                                                href="{{ route('admin.users', ['group' => 'annonceur']) }}">Annonceurs</a>
                                        </li>
                                        <li><a class="dropdown-item {{ request()->route('group') == 'diffuseur' ? 'active' : '' }}"
                                                href="{{ route('admin.users', ['group' => 'diffuseur']) }}">Diffuseurs</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.users', ['group' => request()->route('group')]) }}" method="GET"
                            class="row g-3">
                            <div class="col-md-3">
                                <label for="country" class="form-label">Pays</label>
                                <select class="form-select" id="country" name="country_id">
                                    <option value="">Tous les pays</option>
                                    @foreach ($viewData['countries'] ?? [] as $country)
                                        <option value="{{ $country->id }}"
                                            {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="locality" class="form-label">Localité</label>
                                <select class="form-select" id="locality" name="locality_id">
                                    <option value="">Toutes les localités</option>
                                    @foreach ($viewData['localities'] ?? [] as $locality)
                                        <option value="{{ $locality->id }}"
                                            @if (isset($viewData['filtre_country']) && $locality->id == $viewData['filtre_country']) selected @endif>{{ $locality->name }}
                                            {{ $locality->emoji ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (request()->route('group') == 'diffuseur')
                                <div class="col-md-3">
                                    <label for="category" class="form-label">Catégorie</label>
                                    <select class="form-select" id="category" name="category_id">
                                        <option value="">Toutes les catégories</option>
                                        @foreach ($viewData['categories'] ?? [] as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                                <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                    class="btn btn-outline-secondary">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Liste des Utilisateurs
                            @if (request()->route('group') == 'all')
                                (Tous)
                            @elseif(request()->route('group') == 'admin')
                                (Administrateurs)
                            @elseif(request()->route('group') == 'annonceur')
                                (Annonceurs)
                            @elseif(request()->route('group') == 'diffuseur')
                                (Diffuseurs)
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Profil</th>
                                        <th>Pays</th>
                                        @if (request()->route('group') == 'diffuseur')
                                            <th>Vues</th>
                                            <th>Catégories</th>
                                        @endif
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewData['users'] ?? [] as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if (strpos($user->profiles ?? '', 'DIFFUSEUR') !== false)
                                                    <span class="badge bg-primary">Diffuseur</span>
                                                @elseif(strpos($user->profiles ?? '', 'ANNONCEUR') !== false)
                                                    <span class="badge bg-success">Annonceur</span>
                                                @elseif(strpos($user->profiles ?? '', 'ADMIN') !== false)
                                                    <span class="badge bg-danger">Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $user->profiles ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->country }}</td>
                                            @if (request()->route('group') == 'diffuseur')
                                                <td>{{ number_format($user->vuesmoyen ?? 0) }}</td>
                                                <td>{{ $user->category ?? 'N/A' }}</td>
                                            @endif
                                            <td>
                                                <form
                                                    action="{{ route('admin.users.update', ['group' => request()->route('group')]) }}"
                                                    method="POST" class="toggle-status-form">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input toggle-status" type="checkbox"
                                                            role="switch" name="enabled" value="1"
                                                            onchange="this.form.submit()"
                                                            {{ $user->enabled ? 'checked' : '' }}>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'view', 'id' => $user->id]) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'edit', 'id' => $user->id]) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ request()->route('group') == 'diffuseur' ? 9 : 7 }}"
                                                class="text-center">Aucun utilisateur trouvé</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination (if available) -->
                        @if (isset($viewData['pagination']))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $viewData['pagination'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details (if viewing a user) -->
        @if (request('action') == 'view' && isset($viewData['userDetails']))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Détails de l'utilisateur</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Informations personnelles</h6>
                                    <div class="mb-3">
                                        <label class="fw-bold">Nom complet:</label>
                                        <p>{{ $viewData['userDetails']->firstname }}
                                            {{ $viewData['userDetails']->lastname }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Email:</label>
                                        <p>{{ $viewData['userDetails']->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Téléphone:</label>
                                        <p>{{ $viewData['userDetails']->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Localisation</h6>
                                    <div class="mb-3">
                                        <label class="fw-bold">Pays:</label>
                                        <p>{{ $viewData['userDetails']->country }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Localité:</label>
                                        <p>{{ $viewData['userDetails']->locality ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Profil</h6>
                                    <div class="mb-3">
                                        <label class="fw-bold">Type:</label>
                                        <p>{{ $viewData['userDetails']->profiles }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Date d'inscription:</label>
                                        <p>{{ date('d/m/Y', strtotime($viewData['userDetails']->created_at)) }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">État du compte</h6>
                                    <div class="mb-3">
                                        <label class="fw-bold">Statut:</label>
                                        <p>{!! $viewData['userDetails']->enabled
                                            ? '<span class="badge bg-success">Actif</span>'
                                            : '<span class="badge bg-danger">Inactif</span>' !!}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Email vérifié:</label>
                                        <p>{!! $viewData['userDetails']->email_verified_at
                                            ? '<span class="badge bg-success">Oui</span>'
                                            : '<span class="badge bg-danger">Non</span>' !!}</p>
                                    </div>
                                </div>
                            </div>

                            @if (strpos($viewData['userDetails']->profiles ?? '', 'DIFFUSEUR') !== false)
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Informations Diffuseur</h6>
                                        <div class="mb-3">
                                            <label class="fw-bold">Vues moyennes:</label>
                                            <p>{{ $viewData['userDetails']->vuesmoyen }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-bold">Langue:</label>
                                            <p>{{ $viewData['userDetails']->lang ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-bold">Niveau d'étude:</label>
                                            <p>{{ $viewData['userDetails']->study ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Catégories</h6>
                                        <div class="mb-3">
                                            <p>{{ $viewData['userDetails']->category ?? 'Aucune catégorie' }}</p>
                                        </div>
                                        <h6 class="text-primary">Types de contenu</h6>
                                        <div class="mb-3">
                                            <p>{{ $viewData['userDetails']->contenttype ?? 'Aucun type de contenu' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                    class="btn btn-secondary">Retour à la liste</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit User Form (if editing a user) -->
        @if (request('action') == 'edit' && isset($viewData['userDetails']))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Modifier l'utilisateur</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.update', ['group' => request()->route('group')]) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $viewData['userDetails']->id }}">
                                <input type="hidden" name="action" value="update_user">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">Prénom</label>
                                            <input type="text" class="form-control" id="firstname" name="firstname"
                                                value="{{ $viewData['userDetails']->firstname }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lastname" class="form-label">Nom</label>
                                            <input type="text" class="form-control" id="lastname" name="lastname"
                                                value="{{ $viewData['userDetails']->lastname }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ $viewData['userDetails']->email }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                value="{{ $viewData['userDetails']->phone }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country_id" class="form-label">Pays</label>
                                            <select class="form-select" id="country_id" name="country_id">
                                                @foreach ($viewData['countries'] ?? [] as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ $viewData['userDetails']->country_id == $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="locality_id" class="form-label">Localité</label>
                                            <select class="form-select" id="locality_id" name="locality_id">
                                                <option value="">Sélectionnez une localité</option>
                                                @foreach ($viewData['localities'] ?? [] as $locality)
                                                    <option value="{{ $locality->id }}"
                                                        {{ $viewData['userDetails']->locality_id == $locality->id ? 'selected' : '' }}>
                                                        {{ $locality->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                @if (strpos($viewData['userDetails']->profiles ?? '', 'DIFFUSEUR') !== false)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vuesmoyen" class="form-label">Vues moyennes</label>
                                                <input type="number" class="form-control" id="vuesmoyen"
                                                    name="vuesmoyen" value="{{ $viewData['userDetails']->vuesmoyen }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="lang_id" class="form-label">Langue</label>
                                                <select class="form-select" id="lang_id" name="lang_id">
                                                    <option value="">Sélectionnez une langue</option>
                                                    @foreach ($viewData['langs'] ?? [] as $lang)
                                                        <option value="{{ $lang->id }}"
                                                            {{ $viewData['userDetails']->lang_id == $lang->id ? 'selected' : '' }}>
                                                            {{ $lang->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="study_id" class="form-label">Niveau d'étude</label>
                                                <select class="form-select" id="study_id" name="study_id">
                                                    <option value="">Sélectionnez un niveau d'étude</option>
                                                    @foreach ($viewData['studies'] ?? [] as $study)
                                                        <option value="{{ $study->id }}"
                                                            {{ $viewData['userDetails']->study_id == $study->id ? 'selected' : '' }}>
                                                            {{ $study->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nouveau mot de passe (laisser vide
                                                pour ne pas changer)</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmer le mot de
                                                passe</label>
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                        class="btn btn-secondary me-2">Annuler</a>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
