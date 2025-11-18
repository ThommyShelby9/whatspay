@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row mb-4">
                        <div class="col">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">{{ $pagetilte }}</li>
                                @if (request()->has('action') && request()->has('id'))
                                    <li class="breadcrumb-item active">
                                        @if (request('action') == 'view')
                                            Détail
                                        @elseif(request('action') == 'edit')
                                            Édition
                                        @elseif(request('action') == 'stats')
                                            Statistiques
                                        @elseif(request('action') == 'campaigns')
                                            Campagnes
                                        @endif
                                    </li>
                                @endif
                            </ol>
                        </div>
                        <div class="col-auto">
                            @if (!request()->has('action'))
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
                                    <i class="fa fa-filter me-2"></i>Filtres
                                </button>
                            @else
                                <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                    class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i>Retour
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION LISTE: Filtres et tableau des utilisateurs -->
        @if (!request()->has('action'))
            <!-- Filtres (Collapsible) -->
            <div class="collapse mb-4" id="collapseFilters">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Options de filtrage</h5>
                    </div>
                    <div class="card-body">
                        <form class="form theme-form" method="get"
                            action="{{ route('admin.users', ['group' => request()->route('group')]) }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Pays de résidence</label>
                                        <select class="form-select select2" id="filtre_country" name="filtre_country">
                                            <option value="">Tous les pays</option>
                                            @foreach ($viewData['countries'] as $item)
                                                <option value="{{ $item->id }}" @selected($item->id == $viewData['filtre_country'])>
                                                    {{ $item->name }} {{ $item->emoji }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Localité de résidence</label>
                                        <select class="form-select select2" id="filtre_locality" name="filtre_locality"
                                            data-selected="{{ $viewData['filtre_locality'] ?? '' }}">
                                            <option value="">Toutes les localités</option>

                                            @if ($viewData['filtre_country'])
                                                @foreach ($viewData['localities'] as $item)
                                                    @if ($item->country_id == $viewData['filtre_country'])
                                                        <option value="{{ $item->id }}" @selected($item->id == $viewData['filtre_locality'])>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Profil</label>
                                        <select class="form-select" id="filtre_profile" name="filtre_profile">
                                            <option value="all">Tous les profils</option>
                                            <option value="DIFFUSEUR" @if (isset($viewData['filtre_profile']) && $viewData['filtre_profile'] == 'DIFFUSEUR') selected @endif>
                                                Diffuseur</option>
                                            <option value="ANNONCEUR" @if (isset($viewData['filtre_profile']) && $viewData['filtre_profile'] == 'ANNONCEUR') selected @endif>
                                                Annonceur</option>
                                            <option value="ADMIN" @if (isset($viewData['filtre_profile']) && $viewData['filtre_profile'] == 'ADMIN') selected @endif>Admin
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Statut</label>
                                        <select class="form-select" id="filtre_status" name="filtre_status">
                                            <option value="all">Tous les statuts</option>
                                            <option value="1" @if (isset($viewData['filtre_status']) && $viewData['filtre_status'] == '1') selected @endif>Actif
                                            </option>
                                            <option value="0" @if (isset($viewData['filtre_status']) && $viewData['filtre_status'] == '0') selected @endif>
                                                Inactif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                            class="btn btn-light me-2">Réinitialiser</a>
                                        <button type="submit" class="btn btn-primary me-2">Appliquer les filtres</button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        </form>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">{{ $pagetilte }}</h5>
                            <small class="text-muted">{{ $pagecardtilte }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped" id="items_datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Identité</th>
                                    <th>Profil</th>
                                    <th>Adresse</th>
                                    <th>Contact</th>
                                    <th>Statut</th>
                                    <th>Inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($viewData['items'] ?? [] as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light-primary rounded-circle text-center me-2">
                                                    <span class="font-size-18">{{ substr($item->firstname, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $item->firstname }} {{ $item->lastname }}</h6>
                                                    @if (isset($item->profession))
                                                        <small class="text-muted">{{ $item->profession }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if (isset($item->profiles))
                                                @if (strpos($item->profiles, 'DIFFUSEUR') !== false)
                                                    <span class="badge bg-primary">Diffuseur</span>
                                                @elseif(strpos($item->profiles, 'ANNONCEUR') !== false)
                                                    <span class="badge bg-success">Annonceur</span>
                                                @elseif(strpos($item->profiles, 'ADMIN') !== false)
                                                    <span class="badge bg-danger">Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $item->profiles }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($item->country))
                                                <div class="mb-1">
                                                    <i class="fa fa-globe-africa text-muted me-1"></i>
                                                    {{ $item->country }}
                                                </div>
                                            @endif
                                            @if (isset($item->locality))
                                                <div>
                                                    <i class="fa fa-map-marker-alt text-muted me-1"></i>
                                                    {{ $item->locality }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <i class="fa fa-envelope text-muted me-1"></i> {{ $item->email }}
                                            </div>
                                            <div>
                                                <i class="fa fa-phone text-muted me-1"></i> {{ $item->phone }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" type="checkbox"
                                                    role="switch" data-user-id="{{ $item->id }}"
                                                    @if (isset($item->enabled) && $item->enabled) checked @endif>
                                                <label class="form-check-label status-label">
                                                    @if (isset($item->enabled) && $item->enabled)
                                                        <span class="text-success">Actif</span>
                                                    @else
                                                        <span class="text-danger">Inactif</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            @if (isset($item->created_at))
                                                {{ date('d/m/Y', strtotime($item->created_at)) }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button" id="dropdownMenuButton-{{ $item->id }}"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    data-user-id="{{ $item->id }}">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu"
                                                    aria-labelledby="dropdownMenuButton-{{ $item->id }}">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'view', 'id' => $item->id]) }}"><i
                                                                class="fa fa-eye me-2"></i>Voir</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'edit', 'id' => $item->id]) }}"><i
                                                                class="fa fa-edit me-2"></i>Modifier</a></li>
                                                    @if (strpos($item->profiles ?? '', 'DIFFUSEUR') !== false)
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'stats', 'id' => $item->id]) }}"><i
                                                                    class="fa fa-chart-line me-2"></i>Statistiques</a></li>
                                                    @endif
                                                    @if (strpos($item->profiles ?? '', 'ANNONCEUR') !== false)
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'campaigns', 'id' => $item->id]) }}"><i
                                                                    class="fa fa-tasks me-2"></i>Campagnes</a></li>
                                                    @endif
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger delete-user-btn"
                                                            href="#" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-user-id="{{ $item->id }}"
                                                            data-user-name="{{ $item->firstname }} {{ $item->lastname }}"><i
                                                                class="fa fa-trash me-2"></i>Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(request('action') == 'view' && isset($viewData['userDetails']))
            <!-- SECTION DETAIL: Affichage des détails d'un utilisateur -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Détail de l'utilisateur</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                <div class="avatar-xl mx-auto bg-light-primary rounded-circle text-center mb-3">
                                    <span
                                        class="font-size-24">{{ substr($viewData['userDetails']->firstname, 0, 1) }}</span>
                                </div>
                                <h5 class="mb-0">{{ $viewData['userDetails']->firstname }}
                                    {{ $viewData['userDetails']->lastname }}</h5>
                                <p class="text-muted mt-2">
                                    @if (isset($viewData['userDetails']->profiles))
                                        @if (strpos($viewData['userDetails']->profiles, 'DIFFUSEUR') !== false)
                                            <span class="badge bg-primary">Diffuseur</span>
                                        @elseif(strpos($viewData['userDetails']->profiles, 'ANNONCEUR') !== false)
                                            <span class="badge bg-success">Annonceur</span>
                                        @elseif(strpos($viewData['userDetails']->profiles, 'ADMIN') !== false)
                                            <span class="badge bg-danger">Admin</span>
                                        @endif
                                    @elseif(isset($viewData['userRoles']))
                                        @foreach ($viewData['userRoles'] as $role)
                                            <span class="badge bg-primary me-1">{{ $role }}</span>
                                        @endforeach
                                    @endif
                                </p>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Informations personnelles</h6>
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">Email:</p>
                                        <h6>{{ $viewData['userDetails']->email }}</h6>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">Téléphone:</p>
                                        <h6>{{ $viewData['userDetails']->phone }}</h6>
                                    </div>
                                    @if (isset($viewData['userDetails']->profession))
                                        <div class="mt-3">
                                            <p class="text-muted mb-1">Profession:</p>
                                            <h6>{{ $viewData['userDetails']->profession }}</h6>
                                        </div>
                                    @endif
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">Statut:</p>
                                        <h6>
                                            @if ($viewData['userDetails']->enabled)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">Date d'inscription:</p>
                                        <h6>{{ date('d/m/Y', strtotime($viewData['userDetails']->created_at)) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-title">Localisation</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted mb-1">Pays:</p>
                                            <h6>{{ $viewData['userDetails']->country ?? 'Non spécifié' }}</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted mb-1">Localité:</p>
                                            <h6>{{ $viewData['userDetails']->locality ?? 'Non spécifiée' }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (isset($viewData['userRoles']) && in_array('DIFFUSEUR', $viewData['userRoles']))
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">Informations du diffuseur</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-1">Vues moyennes:</p>
                                                <h6>{{ number_format($viewData['userDetails']->vuesmoyen ?? 0, 0, ',', ' ') }}
                                                </h6>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="text-muted mb-1">Niveau d'étude:</p>
                                                <h6>{{ $viewData['userDetails']->study ?? 'Non spécifié' }}</h6>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="text-muted mb-1">Langue:</p>
                                                <h6>{{ $viewData['userDetails']->lang ?? 'Non spécifiée' }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('admin.users', ['group' => request()->route('group'), 'action' => 'edit', 'id' => $viewData['userDetails']->id]) }}"
                                    class="btn btn-primary me-2">
                                    <i class="fa fa-edit me-1"></i>Modifier
                                </a>
                                <a href="#" class="btn btn-danger delete-user-btn" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-user-id="{{ $viewData['userDetails']->id }}"
                                    data-user-name="{{ $viewData['userDetails']->firstname }} {{ $viewData['userDetails']->lastname }}">
                                    <i class="fa fa-trash me-1"></i>Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(request('action') == 'edit' && isset($viewData['userDetails']))
            <!-- SECTION EDITION: Formulaire d'édition d'un utilisateur -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier l'utilisateur</h5>
                </div>
                <div class="card-body">
                    <form
                        action="{{ route('admin.users.update', ['group' => request()->route('group'), 'userId' => $viewData['userDetails']->id]) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="{{ $viewData['userDetails']->id }}">

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
                                    <small class="text-muted">L'adresse email ne peut pas être modifiée</small>
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
                                        <option value="">Sélectionner un pays</option>
                                        @foreach ($viewData['countries'] ?? [] as $country)
                                            <option value="{{ $country->id }}"
                                                @if ($viewData['userDetails']->country_id == $country->id) selected @endif>
                                                {{ $country->name }} {{ $country->emoji ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="locality_id" class="form-label">Localité</label>
                                    <select class="form-select" id="locality_id" name="locality_id">
                                        <option value="">Sélectionner une localité</option>
                                        @foreach ($viewData['localities'] ?? [] as $locality)
                                            @if (!isset($viewData['userDetails']->country_id) || $locality->country_id == $viewData['userDetails']->country_id)
                                                <option value="{{ $locality->id }}"
                                                    @if ($viewData['userDetails']->locality_id == $locality->id) selected @endif>
                                                    {{ $locality->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if (isset($viewData['userRoles']) && in_array('DIFFUSEUR', $viewData['userRoles']))
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="vuesmoyen" class="form-label">Vues moyennes</label>
                                        <input type="number" class="form-control" id="vuesmoyen" name="vuesmoyen"
                                            value="{{ $viewData['userDetails']->vuesmoyen ?? 0 }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="study_id" class="form-label">Niveau d'étude</label>
                                        <select class="form-select" id="study_id" name="study_id">
                                            <option value="">Sélectionner un niveau</option>
                                            @foreach ($viewData['studies'] ?? [] as $study)
                                                <option value="{{ $study->id }}"
                                                    @if (isset($viewData['userDetails']->study_id) && $viewData['userDetails']->study_id == $study->id) selected @endif>
                                                    {{ $study->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lang_id" class="form-label">Langue</label>
                                        <select class="form-select" id="lang_id" name="lang_id">
                                            <option value="">Sélectionner une langue</option>
                                            @foreach ($viewData['langs'] ?? [] as $lang)
                                                <option value="{{ $lang->id }}"
                                                    @if (isset($viewData['userDetails']->lang_id) && $viewData['userDetails']->lang_id == $lang->id) selected @endif>
                                                    {{ $lang->name }}
                                                </option>
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
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Laisser vide pour ne pas modifier">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmation du mot de
                                        passe</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut</label>
                                    <div class="form-check form-switch ">
                                        <input class="form-check-input relative!" type="checkbox" role="switch"
                                            id="enabled" name="enabled" value="1"
                                            @if ($viewData['userDetails']->enabled) checked @endif>
                                        <label class="form-check-label" for="enabled">
                                            <span id="status-text"
                                                class="@if ($viewData['userDetails']->enabled) text-success @else text-danger @endif">
                                                @if ($viewData['userDetails']->enabled)
                                                    Actif
                                                @else
                                                    Inactif
                                                @endif
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('admin.users', ['group' => request()->route('group')]) }}"
                                class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif(request('action') == 'stats' && isset($viewData['stats']) && isset($viewData['userDetails']))
            <!-- SECTION STATISTIQUES: Affichage des statistiques d'un diffuseur -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques du diffuseur</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary ">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Campagnes totales</h5>
                                    <h2 class="mt-3 mb-0">{{ $viewData['stats']['totalCampaigns'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success ">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Campagnes terminées</h5>
                                    <h2 class="mt-3 mb-0">{{ $viewData['stats']['completedCampaigns'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning ">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Campagnes actives</h5>
                                    <h2 class="mt-3 mb-0">{{ $viewData['stats']['activeCampaigns'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info ">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Revenus totaux</h5>
                                    <h2 class="mt-3 mb-0">
                                        {{ number_format($viewData['stats']['totalEarnings'], 0, ',', ' ') }} FCFA</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Évolution mensuelle</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyStatsChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(request('action') == 'campaigns' && isset($viewData['campaigns']) && isset($viewData['userDetails']))
            <!-- SECTION CAMPAGNES: Affichage des campagnes d'un annonceur -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Campagnes de l'annonceur</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Budget</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($viewData['campaigns'] as $campaign)
                                    <tr>
                                        <td>{{ $campaign->name }}</td>
                                        <td>{{ date('d/m/Y', strtotime($campaign->startdate)) }}</td>
                                        <td>{{ date('d/m/Y', strtotime($campaign->enddate)) }}</td>
                                        <td>{{ number_format($campaign->budget, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            @if ($campaign->status == 'ACTIVE')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($campaign->status == 'PENDING')
                                                <span class="badge bg-warning">En attente</span>
                                            @elseif($campaign->status == 'COMPLETED')
                                                <span class="badge bg-info">Terminée</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $campaign->status ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.campaigns.show', ['id' => $campaign->id]) }}"
                                                class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                                            {{-- <a href="#" class="btn btn-sm btn-warning"><i
                                                    class="fa fa-edit"></i></a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucune campagne trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($viewData['campaigns'], 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $viewData['campaigns']->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Delete User Modal - Version améliorée avec débogage -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <span id="delete-user-name" class="fw-bold">cet
                            utilisateur</span> ?</p>
                    <p class="text-danger">Cette action est irréversible.</p>

                    <form id="delete-form" method="POST"
                        action="{{ route('admin.users', ['group' => request()->route('group')]) }}">
                        @csrf
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="delete-user-id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="delete-form" class="btn btn-danger">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="bjId" id="bjId" value="{{ $viewData['bjId'] ?? '' }}">
    <input type="hidden" name="localitiesJson" id="localitiesJson" value="{{ $viewData['localitiesJson'] ?? '[]' }}">
    <input type="hidden" name="countriesJson" id="countriesJson" value="{{ $viewData['countriesJson'] ?? '[]' }}">
    @if (isset($viewData['contenttypesJson']))
        <input type="hidden" name="contenttypesJson" id="contenttypesJson"
            value="{{ $viewData['contenttypesJson'] }}">
    @endif
    @if (isset($viewData['studiesJson']))
        <input type="hidden" name="studiesJson" id="studiesJson" value="{{ $viewData['studiesJson'] }}">
    @endif
    @if (isset($viewData['categoriesJson']))
        <input type="hidden" name="categoriesJson" id="categoriesJson" value="{{ $viewData['categoriesJson'] }}">
    @endif

    <!-- jQuery doit être chargé en premier -->
    <script src="{{ asset('design/admin/assets/js/jquery.min.js') }}"></script>

    <!-- Puis DataTables et ses extensions -->
    <script src="{{ asset('design/admin/assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('design/admin/assets/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('design/admin/assets/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('design/admin/assets/js/buttons.html5.min.js') }}"></script>

    <!-- Script personnalisé pour le modal de suppression -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation de DataTable (uniquement si nous sommes en mode liste)
            @if (!request()->has('action'))
                // Vérifier si DataTables est disponible
                if (typeof $.fn.DataTable === 'function') {
                    try {
                        /* const dataTable = $('#items_datatable').DataTable({
                            responsive: true,
                            dom: 'Bfrtip',
                            buttons: [
                                'copyHtml5',
                                'excelHtml5',
                                'csvHtml5',
                                'pdfHtml5'
                            ],
                            lengthMenu: [
                                [10, 25, 50, -1],
                                [10, 25, 50, 'Tous']
                            ],
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                            }
                        }); */

                        // Boutons d'exportation
                        document.getElementById('export-excel')?.addEventListener('click', function() {
                            dataTable.button('.buttons-excel').trigger();
                        });

                        document.getElementById('export-pdf')?.addEventListener('click', function() {
                            dataTable.button('.buttons-pdf').trigger();
                        });

                        document.getElementById('export-csv')?.addEventListener('click', function() {
                            dataTable.button('.buttons-csv').trigger();
                        });
                    } catch (error) {
                        console.error('Erreur lors de l\'initialisation de DataTables:', error);
                    }
                } else {
                    console.error('DataTables non disponible');
                }

                const countriesList = @json($viewData['countriesList']);
                const localitiesList = @json($viewData['localitiesList']);
            @endif


            // Interaction entre pays et localités pour les filtres
            const countrySelect = document.getElementById("filtre_country");
            const localitySelect = document.getElementById("filtre_locality");

            const DEFAULT_LOCALITY_OPTION_HTML =
                '<option value="">Toutes les localités</option>';

            // === Fonction principale ===
            function populateLocalitiesForCountry(countryId, selectedLocality = "") {

                // Reset
                localitySelect.innerHTML = DEFAULT_LOCALITY_OPTION_HTML;

                // Trouver le pays sélectionné
                const country = countriesList.find(
                    (c) => String(c.id) === String(countryId)
                );

                if (!country || country.name !== "Benin") {
                    // Si ce n'est pas le Bénin → localités vides
                    $("#filtre_locality").trigger("change.select2");
                    return;
                }

                // Filtrer les localités du pays
                const filtered = localitiesList.filter(
                    (l) => String(l.country_id) === String(countryId)
                );

                // Ajouter options
                filtered.forEach((loc) => {
                    const option = document.createElement("option");
                    option.value = loc.id;
                    option.textContent = loc.name;

                    if (String(loc.id) === String(selectedLocality)) {
                        option.selected = true;
                    }

                    localitySelect.appendChild(option);
                });

                $("#filtre_locality").trigger("change.select2");
            }

            // === Listener du select pays ===
            if (countrySelect && localitySelect) {
                $("#filtre_country").on("select2:select", function() {
                    const countryId = this.value;

                    const selectedLocality = localitySelect.dataset.selected || "";
                    populateLocalitiesForCountry(countryId, selectedLocality);
                });

                // Initialisation au chargement
                const initCountry = countrySelect.value || "";
                const selectedLocality = localitySelect.dataset.selected || "";

                if (initCountry !== "") {
                    populateLocalitiesForCountry(initCountry, selectedLocality);
                }
            }

            // Activer/désactiver un utilisateur (toggle)
            const toggleStatusElements = document.querySelectorAll('.toggle-status');
            toggleStatusElements.forEach(element => {
                element.addEventListener('change', function() {
                    const userId = this.dataset.userId;
                    const statusLabel = this.nextElementSibling;

                    if (this.checked) {
                        statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                    } else {
                        statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                    }

                    // Envoyer la requête AJAX pour mettre à jour le statut
                    fetch(window.location.pathname, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'toggle_status',
                                user_id: userId,
                                enabled: this.checked ? 1 : 0
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                // Réinitialiser le toggle en cas d'erreur
                                this.checked = !this.checked;
                                if (this.checked) {
                                    statusLabel.innerHTML =
                                        '<span class="text-success">Actif</span>';
                                } else {
                                    statusLabel.innerHTML =
                                        '<span class="text-danger">Inactif</span>';
                                }

                                // Afficher l'erreur
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            // Réinitialiser le toggle en cas d'erreur
                            this.checked = !this.checked;
                            if (this.checked) {
                                statusLabel.innerHTML =
                                    '<span class="text-success">Actif</span>';
                            } else {
                                statusLabel.innerHTML =
                                    '<span class="text-danger">Inactif</span>';
                            }

                            alert('Une erreur est survenue lors de la modification du statut');
                        });
                });
            });

            // Modal de suppression
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');

                    console.log('Modal ouvert pour utilisateur:', userId, userName);

                    document.getElementById('delete-user-name').textContent = userName || 'cet utilisateur';
                    document.getElementById('delete-user-id').value = userId;

                    // Vérification que l'ID est bien passé
                    console.log('ID utilisateur défini dans le formulaire:', document.getElementById(
                        'delete-user-id').value);
                });

                // Vérification avant soumission du formulaire
                const deleteForm = document.getElementById('delete-form');
                if (deleteForm) {
                    deleteForm.addEventListener('submit', function(event) {
                        const userId = document.getElementById('delete-user-id').value;
                        console.log('Soumission du formulaire avec ID utilisateur:', userId);

                        if (!userId) {
                            event.preventDefault();
                            alert('Erreur: ID utilisateur manquant. Veuillez réessayer.');
                            console.error('ID utilisateur manquant lors de la soumission du formulaire');
                        }
                    });
                }
            }

            // Toggle statut dans le formulaire d'édition
            const enabledToggle = document.getElementById('enabled');
            const statusText = document.getElementById('status-text');

            if (enabledToggle && statusText) {
                enabledToggle.addEventListener('change', function() {
                    if (this.checked) {
                        statusText.className = 'text-success';
                        statusText.textContent = 'Actif';
                    } else {
                        statusText.className = 'text-danger';
                        statusText.textContent = 'Inactif';
                    }
                });
            }

            // Initialisation du graphique pour les statistiques
            @if (request('action') == 'stats' && isset($viewData['stats']))
                const monthlyStatsChart = document.getElementById('monthlyStatsChart');
                if (monthlyStatsChart) {
                    const ctx = monthlyStatsChart.getContext('2d');
                    const monthlyStats = @json($viewData['stats']['monthlyStats']);

                    const months = monthlyStats.map(stat => stat.month);
                    const earnings = monthlyStats.map(stat => stat.earnings);
                    const completions = monthlyStats.map(stat => stat.completions);

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: months,
                            datasets: [{
                                    label: 'Revenus (FCFA)',
                                    data: earnings,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Campagnes terminées',
                                    data: completions,
                                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1,
                                    type: 'line',
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Revenus (FCFA)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Nombre de campagnes'
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            }
                        }
                    });
                }
            @endif

            // Initialiser Select2 pour les dropdowns si disponible
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }
        });
    </script>

@endsection
