@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">{{ $pagetilte }}</li>
                            </ol>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
                                <i class="fa fa-filter me-2"></i>Filtres
                            </button>
                            {{-- <a href="#" class="btn btn-success ms-2">
                                <i class="fa fa-plus me-2"></i>Ajouter
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary-subtle">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar-md bg-white rounded-circle">
                                    <i
                                        class="fa fa-eye fa-2x text-primary d-flex justify-content-center align-items-center h-100"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-1">Total des vues moyennes journalières</h5>
                                <h2 class="mb-0">{{ number_format($viewData['vuesmoyen']) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres (Collapsible) -->
        <div class="collapse mb-4" id="collapseFilters">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Options de filtrage avancées</h5>
                </div>
                <div class="card-body">
                    <form class="form theme-form" method="get" action="" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Column 1: Location -->
                            <div class="col-md-4">
                                <h6 class="text-primary mb-3">Localisation</h6>
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

                            <!-- Column 2: Profile -->
                            <div class="col-md-4">
                                <h6 class="text-primary mb-3">Profil</h6>
                                <div class="mb-3">
                                    <label class="form-label">Profession</label>
                                    <select class="form-select select2-multiple" id="filtre_occupation"
                                        name="filtre_occupation[]" multiple>
                                        @foreach ($viewData['occupations'] as $item)
                                            <option value="{{ $item->id }}"
                                                @foreach ($viewData['filtre_occupation'] as $item2)
                            @if ($item->id == $item2) selected @endif @endforeach>
                                                {{ $item->name }}</option>
                                        @endforeach
                                        <option value="other"
                                            @foreach ($viewData['filtre_occupation'] as $item2)
                            @if ($item2 == 'other') selected @endif @endforeach>
                                            Autre</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Niveau d'étude</label>
                                    <select class="form-select select2-multiple" id="filtre_study" name="filtre_study[]"
                                        multiple>
                                        @foreach ($viewData['studies'] as $item)
                                            <option value="{{ $item->id }}"
                                                @foreach ($viewData['filtre_study'] as $item2)
                            @if ($item->id == $item2) selected @endif @endforeach>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Langues</label>
                                    <select class="form-select select2-multiple" id="filtre_lang" name="filtre_lang[]"
                                        multiple>
                                        @foreach ($viewData['langs'] as $item)
                                            <option value="{{ $item->id }}"
                                                @foreach ($viewData['filtre_lang'] as $item2)
                            @if ($item->id == $item2) selected @endif @endforeach>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Column 3: Content -->
                            <div class="col-md-4">
                                <h6 class="text-primary mb-3">Contenu</h6>
                                <div class="mb-3">
                                    <label class="form-label">Catégories de publication</label>
                                    <select class="form-select select2-multiple" id="filtre_category"
                                        name="filtre_category[]" multiple>
                                        @foreach ($viewData['categories'] as $item)
                                            <option value="{{ $item->id }}"
                                                @foreach ($viewData['filtre_category'] as $item2)
                            @if ($item->id == $item2) selected @endif @endforeach>
                                                {{ Str::limit($item->name, 15) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Types de contenu dominants</label>
                                    <select class="form-select select2-multiple" id="filtre_contenu" name="filtre_contenu[]"
                                        multiple>
                                        @foreach ($viewData['contenttypes'] as $item)
                                            <option value="{{ $item->id }}"
                                                @foreach ($viewData['filtre_contenu'] as $item2)
                            @if ($item->id == $item2) selected @endif @endforeach>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Range filter for views -->
                                <div class="mb-3">
                                    <label class="form-label">Plage de vues moyennes</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">Min</span>
                                                <input type="number" class="form-control" id="filtre_vues_min"
                                                    name="filtre_vues_min"
                                                    value="{{ $viewData['filtre_vues_min'] ?? '' }}" placeholder="Min">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">Max</span>
                                                <input type="number" class="form-control" id="filtre_vues_max"
                                                    name="filtre_vues_max"
                                                    value="{{ $viewData['filtre_vues_max'] ?? '' }}" placeholder="Max">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="reset" class="btn btn-light me-2">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter me-1"></i>Appliquer les filtres
                                </button>
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
                                <th>Profession</th>
                                <th>Adresse</th>
                                <th>Contact</th>
                                <th>Vues moy.</th>
                                <th>Étude & Langue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($viewData['items'] as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light-primary rounded-circle text-center me-2">
                                                <span class="font-size-18">{{ substr($item->firstname, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $item->firstname }} {{ $item->lastname }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if (!empty($item->profession))
                                            <span>{{ $item->profession }}</span>
                                        @else
                                            <span class="badge bg-danger">Non spécifiée</span>
                                        @endif
                                        @if (!empty($item->occupation))
                                            <p class="text-muted mb-0">{{ $item->occupation }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><i class="fa fa-globe-africa text-muted me-1"></i>
                                                {{ $item->country }}</span>
                                            @if (!empty($item->locality))
                                                <span class="text-muted"><i class="fa fa-map-marker-alt me-1"></i>
                                                    {{ $item->locality }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><i class="fa fa-envelope text-muted me-1"></i>
                                                {{ $item->email }}</span>
                                            <span><i class="fa fa-phone text-muted me-1"></i> {{ $item->phone }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span>{{ number_format($item->vuesmoyen) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><i class="fa fa-graduation-cap text-muted me-1"></i>
                                                {{ $item->study }}</span>
                                            <span><i class="fa fa-language text-muted me-1"></i>
                                                {{ $item->lang }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('admin.users', ['group' => 'all', 'action' => 'view', 'id' => $item->id]) }}"><i
                                                            class="fa fa-eye me-2"></i>Voir</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('admin.users', ['group' => 'all', 'action' => 'edit', 'id' => $item->id]) }}"><i
                                                            class="fa fa-edit me-2"></i>Modifier</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('admin.users', ['group' => 'all', 'action' => 'stats', 'id' => $item->id]) }}"><i
                                                            class="fa fa-bars me-2"></i>Statistiques</a>
                                                </li>
                                                {{-- <li><a class="dropdown-item" href="#"><i
                                                            class="fa fa-comment me-2"></i>Contacter</a></li>
                                                <li> --}}
                                                <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item text-danger" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->firstname }} {{ $item->lastname }}">
                                                        <i class="fa fa-trash me-2"></i>Supprimer
                                                    </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Identité</th>
                                <th>Profession</th>
                                <th>Adresse</th>
                                <th>Contact</th>
                                <th>Vues moy.</th>
                                <th>Étude & Langue</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">

            <form id="delete-user-form" method="POST">
                @csrf

                <input type="hidden" id="delete-user-id" name="user_id">

                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer le diffuseur <strong id="delete-user-name"></strong> ?</p>
                        <p class="text-danger"><i class="fa fa-exclamation-triangle me-2"></i>Cette action est
                            irréversible et supprimera toutes les données associées.</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </div>

            </form>

        </div>
    </div>


    <!-- Hidden inputs for JavaScript -->
    <input type="hidden" name="bjId" id="bjId" value="{{ $viewData['bjId'] }}">
    {{-- <input type="hidden" id="countriesJson" value="{{ $viewData['countriesJson'] }}"> --}}
    {{-- <input type="hidden" id="localitiesJson" value='@json($viewData['localitiesJson'] ?? [])'> --}}
    <input type="hidden" name="contenttypesJson" id="contenttypesJson" value="{{ $viewData['contenttypesJson'] }}">
    <input type="hidden" name="studiesJson" id="studiesJson" value="{{ $viewData['studiesJson'] }}">
    <input type="hidden" name="categoriesJson" id="categoriesJson" value="{{ $viewData['categoriesJson'] }}">

    <script>
        window.COUNTRIES_LIST = @json($viewData['countriesList'] ?? []);
        window.LOCALITIES_LIST = @json($viewData['localitiesList'] ?? []);
    </script>
@endsection
