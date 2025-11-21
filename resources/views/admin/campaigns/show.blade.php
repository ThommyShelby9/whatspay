@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row align-items-center">
                        <div class="col">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.tasks') }}">Campagnes</a>
                                    </li>
                                    <li class="breadcrumb-item active">Détails</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('admin.tasks') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-2"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques de la campagne</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <div class="display-4 text-primary mb-2">
                                    {{ number_format($viewData['stats']['total_views'] ?? 0) }}</div>
                                <h6 class="text-uppercase text-muted">Vues totales</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <div class="display-4 text-success mb-2">
                                    {{ number_format($viewData['stats']['unique_clicks'] ?? 0) }}</div>
                                <h6 class="text-uppercase text-muted">Clics</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <div class="display-4 text-info mb-2">{{ $stats['influencers'] ?? 0 }}</div>
                                <h6 class="text-uppercase text-muted">Diffuseurs</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <div class="display-4 text-warning mb-2">
                                    {{ number_format($viewData['stats']['click_rate'] ?? 0, 2) }}%</div>
                                <h6 class="text-uppercase text-muted">Taux de clic</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails de la campagne -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#details-tab" role="tab">
                                    <i class="fa fa-info-circle me-1"></i>Détails
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#performance-tab" role="tab">
                                    <i class="fa fa-chart-line me-1"></i>Performance
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Onglet Détails -->
                            <div class="tab-pane fade show active" id="details-tab" role="tabpanel">
                                <div class="col-xl-12">
                                    <div class=" mb-4">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">Détails de la campagne</h5>

                                            <div>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#editCampaignModal">
                                                    <i class="fa fa-edit me-1"></i>Modifier
                                                </button>

                                                <button type="button" class="btn btn-danger ms-2 delete-task"
                                                    data-task-id="{{ $task->id }}">
                                                    <i class="fa fa-trash me-1"></i>Supprimer
                                                </button>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <!-- Informations générales -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="text-uppercase text-muted mb-2">Informations générales</h6>

                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Nom:</div>
                                                        <div class="col-md-8">{{ $task->name }}</div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Budget:</div>
                                                        <div class="col-md-8">
                                                            {{ number_format($task->budget, 0, ',', ' ') }} F
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Statut:</div>
                                                        <div class="col-md-8">
                                                            @php
                                                                $statusClasses = [
                                                                    'PENDING' => 'bg-warning',
                                                                    'ACCEPTED' => 'bg-success',
                                                                    'REJECTED' => 'bg-danger',
                                                                    'PAID' => 'bg-info',
                                                                    'CLOSED' => 'bg-secondary',
                                                                ];
                                                                $statusLabel = [
                                                                    'PENDING' => 'En cours',
                                                                    'ACCEPTED' => 'Acceptée',
                                                                    'REJECTED' => 'Rejetée',
                                                                    'PAID' => 'bg-info',
                                                                    'CLOSED' => 'Clôturée',
                                                                ];
                                                                $statusClass =
                                                                    $statusClasses[$task->status] ?? 'bg-secondary';
                                                                $statusLabel = $statusLabel[$task->status] ?? 'Inconnu';
                                                            @endphp

                                                            <span class="badge {{ $statusClass }}">
                                                                {{ $statusLabel }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Période:</div>
                                                        <div class="col-md-8">
                                                            Du
                                                            {{ \Carbon\Carbon::parse($task->startdate)->format('d/m/Y') }}
                                                            au {{ \Carbon\Carbon::parse($task->enddate)->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Nombres de vues totales:</div>
                                                        <div class="col-md-8">
                                                            {{ $task->total_views_estimated . ' ' . 'vues' ?? 'N/A' }}
                                                        </div>
                                                    </div>

                                                    @php
                                                        use Carbon\Carbon;

                                                        $start = isset($task->startdate)
                                                            ? Carbon::parse($task->startdate)
                                                            : null;
                                                        $end = isset($task->enddate)
                                                            ? Carbon::parse($task->enddate)
                                                            : null;

                                                        $days =
                                                            $start && $end ? max(1, $start->diffInDays($end)) : null;
                                                    @endphp
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Nombres de vues journalier:</div>
                                                        <div class="col-md-8">
                                                            @if ($days && isset($task->total_views_estimated))
                                                                {{ round($task->total_views_estimated / $days) }}
                                                                vues
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Détails média -->
                                                <div class="col-md-6">
                                                    <h6 class="text-uppercase text-muted mb-2">Détails média</h6>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Type de média:</div>
                                                        <div class="col-md-8">

                                                            @php
                                                                $mediaTypes = [
                                                                    'image' => 'Image avec légende',
                                                                    'image_link' => 'Image avec légende et lien',
                                                                    'text' => 'Texte simple',
                                                                    'video' => 'Vidéo',
                                                                ];
                                                            @endphp

                                                            {{ $mediaTypes[$task->media_type] ?? $task->media_type }}

                                                        </div>
                                                    </div>
                                                    @if ($task->url)
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">URL:</div>
                                                            <div class="col-md-8">
                                                                <a href="{{ $task->url }}" target="_blank">
                                                                    {{ $task->url }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <h6 class="text-uppercase text-muted mb-2">Description</h6>
                                                    <div class="p-3 bg-light text-black rounded">
                                                        {{ $task->descriptipon ?? 'Aucune description fournie' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Légende -->
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <h6 class="text-uppercase text-muted mb-2">Légende</h6>
                                                    <div class="p-3 bg-light text-black rounded">
                                                        {{ $task->legend ?? 'Aucune légende fournie' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Localités & Professions -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="text-uppercase text-muted mb-2">Localités ciblées</h6>
                                                    <div class="p-3 bg-light text-black rounded">
                                                        @if (count($task->localities) > 0)
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach ($task->localities as $locality)
                                                                    <li>
                                                                        <i
                                                                            class="fas fa-map-marker-alt text-primary me-2"></i>
                                                                        {{ $locality->name }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            Aucune localité spécifiée
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <h6 class="text-uppercase text-muted mb-2">Professions ciblées</h6>
                                                    <div class="p-3 bg-light text-black rounded">
                                                        @if (count($task->occupations) > 0)
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach ($task->occupations as $occupation)
                                                                    <li>
                                                                        <i class="fas fa-briefcase text-primary me-2"></i>
                                                                        {{ $occupation->name }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            Aucune profession spécifiée
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Catégories -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="text-uppercase text-muted mb-2">Catégories</h6>

                                                    @if (count($task->categories) > 0)
                                                        @foreach ($task->categories as $category)
                                                            <span
                                                                class="badge bg-info me-2 mb-1">{{ $category->name }}</span>
                                                        @endforeach
                                                    @else
                                                        Aucune catégorie spécifiée
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Médias -->
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <h6 class="text-uppercase text-muted mb-2">Médias</h6>
                                                    <div class="row">
                                                        @if ($viewData['task']->files)
                                                            @php
                                                                $filesData = $viewData['task']->files;
                                                                if (
                                                                    is_string($filesData) &&
                                                                    str_starts_with(trim($filesData), '[') &&
                                                                    str_ends_with(trim($filesData), ']')
                                                                ) {
                                                                    $files = json_decode($filesData, true);
                                                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                                                        $files = [];
                                                                    }
                                                                } else {
                                                                    $files = [];
                                                                }
                                                            @endphp
                                                            @if (count($files) > 0)
                                                                @foreach ($files as $file)
                                                                    <div class="col-md-3 mb-3">
                                                                        <div class="card h-100">
                                                                            @if (isset($file['mime']) && str_contains($file['mime'], 'image'))
                                                                                <img src="{{ asset($file['path']) }}"
                                                                                    class="card-img-top"
                                                                                    style="height: 160px; object-fit: cover;">
                                                                            @elseif(isset($file['mime']) && str_contains($file['mime'], 'video'))
                                                                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                                                                    style="height: 160px;">
                                                                                    <i
                                                                                        class="fas fa-film fa-2x text-white"></i>
                                                                                </div>
                                                                            @else
                                                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                                                                    style="height: 160px;">
                                                                                    <i
                                                                                        class="fas fa-file fa-2x text-muted"></i>
                                                                                </div>
                                                                            @endif
                                                                            <div class="card-body p-2">
                                                                                <p
                                                                                    class="card-text small text-truncate mb-0">
                                                                                    {{ $file['original_name'] ?? ($file['name'] ?? 'Fichier') }}
                                                                                </p>
                                                                                @if (isset($file['path']))
                                                                                    <a href="{{ asset($file['path']) }}"
                                                                                        target="_blank"
                                                                                        class="btn btn-sm btn-light mt-2">
                                                                                        <i class="fas fa-eye me-1"></i>Voir
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div class="col-12">
                                                                    <div class="alert alert-light">Aucun média
                                                                        interprétable pour cette
                                                                        campagne</div>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="col-12">
                                                                <div class="alert alert-light">Aucun média attaché à cette
                                                                    campagne</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Diffuseurs assignés -->
                                    <div>
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Diffuseurs assignés</h5>
                                        </div>
                                        <div class="card-body">
                                            @if (count($viewData['assignments'] ?? []) > 0)
                                                <div class="table-responsive">
                                                    <table class="display table table-hover" id="items_datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>Diffuseur</th>
                                                                <th>Date d'attribution</th>
                                                                <th>Statut</th>
                                                                <th>Vues estimées</th>
                                                                <th>Vues obtenues</th>
                                                                <th>Date de soumission</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            @foreach ($viewData['assignments'] as $assignment)
                                                                <tr>
                                                                    <!-- Diffuseur -->
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div
                                                                                class="avatar avatar-sm me-2 bg-primary text-white">
                                                                                {{ substr($assignment->agent->firstname, 0, 1) }}
                                                                                {{ substr($assignment->agent->lastname, 0, 1) }}
                                                                            </div>

                                                                            <div>
                                                                                <div>
                                                                                    {{ $assignment->agent->firstname }}
                                                                                    {{ $assignment->agent->lastname }}
                                                                                </div>

                                                                                <small class="text-muted">
                                                                                    {{ $assignment->agent->email }}
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <!-- Attribution -->
                                                                    <td>
                                                                        {{ \Carbon\Carbon::parse($assignment->assignment_date)->format('d/m/Y H:i') }}
                                                                    </td>
                                                                    <!-- Statut -->
                                                                    <td>
                                                                        @php
                                                                            $statusClasses = [
                                                                                'PENDING' => 'bg-warning',
                                                                                'SUBMITED' => 'bg-success',
                                                                                'ASSIGNED' => 'bg-secondary',
                                                                                'SUBMISSION_ACCEPTED' => 'bg-success',
                                                                                'SUBMISSION_REJECTED' => 'bg-error',
                                                                            ];
                                                                            $statusLabel = [
                                                                                'PENDING' => 'En cours',
                                                                                'SUBMITED' => 'Terminée',
                                                                                'ASSIGNED' => 'Assignée',
                                                                                'SUBMISSION_ACCEPTED' =>
                                                                                    'Résultat validé',
                                                                                'SUBMISSION_REJECTED' =>
                                                                                    'Résultat rejeté',
                                                                            ];
                                                                            $statusClass =
                                                                                $statusClasses[$assignment->status] ??
                                                                                'bg-secondary';
                                                                            $statusLabel =
                                                                                $statusLabel[$assignment->status] ??
                                                                                'Inconnu';
                                                                        @endphp

                                                                        <span class="badge {{ $statusClass }}">
                                                                            {{ $statusLabel }}
                                                                        </span>
                                                                    </td>

                                                                    <!-- Vues -->
                                                                    <td>{{ $assignment->expected_views }}</td>
                                                                    <td>{{ $assignment->vues }}</td>

                                                                    <!-- Soumission -->
                                                                    <td>
                                                                        {{ $assignment->submission_date
                                                                            ? \Carbon\Carbon::parse($assignment->submission_date)->format('d/m/Y H:i')
                                                                            : 'Pas de soumissions' }}
                                                                    </td>

                                                                    <!-- Actions -->
                                                                    <td>
                                                                        <a href="{{ route('admin.show.result', $assignment->id) }}"
                                                                            class="btn btn-sm btn-light">
                                                                            <i class="fa fa-eye me-1"></i>Voir
                                                                        </a>
                                                                    </td>

                                                                </tr>
                                                            @endforeach
                                                        </tbody>

                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">
                                                    Aucun diffuseur n'est encore assigné à cette campagne.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Performance -->
                            <div class="tab-pane fade" id="performance-tab" role="tabpanel">
                                <!-- Graphique d'évolution des vues et clics -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <h6 class="text-muted mb-0 me-2">
                                            <i class="fa fa-chart-area me-1"></i>Évolution des vues et clics
                                        </h6>
                                        <small class="badge bg-info">7 derniers jours</small>
                                    </div>
                                    <div id="views-clicks-chart" style="height: 300px;" class="rounded border"></div>
                                </div>

                                <!-- Répartition par zone géographique -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <h6 class="text-muted mb-0">
                                            <i class="fa fa-globe me-1"></i>Répartition géographique
                                        </h6>
                                    </div>

                                    <div id="geo-chart" style="height: 400px; width:100%"
                                        class="rounded
                                      border bg-white">
                                    </div>
                                </div>

                                <!-- Répartition par appareil et Trafic par jour -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <h6 class="text-muted mb-0">
                                                <i class="fa fa-mobile me-1"></i>Répartition par appareil
                                            </h6>
                                        </div>
                                        <div id="device-chart" style="height: 280px;" class="rounded border bg-white">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <h6 class="text-muted mb-0">
                                                <i class="fa fa-calendar me-1"></i>Trafic par jour de la semaine
                                            </h6>
                                        </div>
                                        <div id="weekday-chart" style="height: 280px;" class="rounded border bg-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification -->
    <div class="modal fade" id="editCampaignModal" tabindex="-1" aria-labelledby="editCampaignModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="editCampaignModalLabel">Modifier la campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <form id="editCampaignForm" method="post" action="{{ route('admin.campaigns.update', $task->id) }}"
                        enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        <!-- Nom & Budget -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom de la campagne <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ $task->name }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Montant par vue (F)</label>
                                            <input type="number" step="0.1" class="form-control" id="viewPrice"
                                                name="view_price" value="{{ $task->view_price }}" required>
                                            <small class="text-muted">Par défaut 3.5 F / vue</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Budget (F) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="budget" name="budget"
                                                value="{{ $task->budget }}" required>
                                            <small class="text-muted">Minimum 1000F | <span id="viewsEstimated">Vues
                                                    estimées : 0</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Dates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date de début <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="startdate"
                                    value="{{ \Carbon\Carbon::parse($task->startdate)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="enddate"
                                    value="{{ \Carbon\Carbon::parse($task->enddate)->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $task->descriptipon }}</textarea>
                        </div>

                        <!-- Media type & URL -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de média <span class="text-danger">*</span></label>
                                <select class="form-select" name="media_type" required>
                                    <option value="">Sélectionnez le type de média</option>
                                    <option value="image" {{ $task->media_type == 'image' ? 'selected' : '' }}>Image avec
                                        légende</option>
                                    <option value="image_link" {{ $task->media_type == 'image_link' ? 'selected' : '' }}>
                                        Image + lien</option>
                                    <option value="text" {{ $task->media_type == 'text' ? 'selected' : '' }}>Texte
                                        simple</option>
                                    <option value="video" {{ $task->media_type == 'video' ? 'selected' : '' }}>Vidéo
                                    </option>
                                </select>
                            </div>

                            <!-- URL si image + lien -->
                            <div class="col-md-6 url-field"
                                style="{{ $task->media_type == 'image_link' ? '' : 'display:none;' }}">
                                <label class="form-label">Lien URL <span class="text-danger url-required">*</span></label>
                                <input type="url" class="form-control" name="url" value="{{ $task->url }}"
                                    placeholder="https://...">
                                <small class="text-muted">Lien vers lequel les utilisateurs seront dirigés</small>
                            </div>
                        </div>

                        <!-- Localités -->
                        <div class="mb-3">
                            <label class="form-label">Localités cibles <span class="text-danger">*</span></label>
                            <select class="form-select select2-modal" name="localities[]" multiple required>
                                @foreach ($viewData['localities'] as $locality)
                                    <option value="{{ $locality->id }}"
                                        {{ in_array($locality->id, $task->localities->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $locality->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Régions ciblées</small>
                        </div>

                        <!-- Professions -->
                        <div class="mb-3">
                            <label class="form-label">Professions cibles <span class="text-danger">*</span></label>
                            <select class="form-select select2-modal" name="occupations[]" multiple required>
                                @foreach ($viewData['occupations'] as $occupation)
                                    <option value="{{ $occupation->id }}"
                                        {{ in_array($occupation->id, $task->occupations->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $occupation->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Professions ciblées</small>
                        </div>

                        <!-- Légende -->
                        <div class="mb-3">
                            <label class="form-label">Légende <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="legend" rows="3" required>{{ $task->legend }}</textarea>
                            <small class="text-muted">Texte affiché avec le média</small>
                        </div>

                        <!-- Catégories -->
                        <div class="mb-4">
                            <label class="form-label">Catégories</label>
                            <div class="category-selector border rounded p-3">
                                <div class="row">
                                    @foreach ($viewData['categories'] as $category)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]"
                                                    id="edit-category-{{ $category->id }}" value="{{ $category->id }}"
                                                    {{ in_array($category->id, $task->categories->pluck('id')->toArray()) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="edit-category-{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted">Sélectionnez une ou plusieurs catégories</small>
                        </div>

                        <!-- Médias actuels -->
                        <div class="mb-3">
                            <label class="form-label">Médias actuels</label>
                            <div class="row">

                                @php
                                    $filesData = $viewData['task']->files;
                                    $files =
                                        is_string($filesData) && str_starts_with(trim($filesData), '[')
                                            ? json_decode($filesData, true)
                                            : [];
                                @endphp

                                @if (count($files) > 0)
                                    @foreach ($files as $index => $file)
                                        <div class="col-md-3 mb-3">
                                            <div class="card h-100">
                                                @if (isset($file['mime']) && str_contains($file['mime'], 'image'))
                                                    <img src="{{ asset($file['path']) }}" class="card-img-top"
                                                        style="height:120px;object-fit:cover;">
                                                @elseif(isset($file['mime']) && str_contains($file['mime'], 'video'))
                                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                                        style="height:120px;">
                                                        <i class="fas fa-film fa-2x text-white"></i>
                                                    </div>
                                                @else
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                                        style="height:120px;">
                                                        <i class="fas fa-file fa-2x text-muted"></i>
                                                    </div>
                                                @endif

                                                <div class="card-body p-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="keep_files[]" value="{{ $index }}"
                                                            id="keep-file-{{ $index }}" checked>
                                                        <label class="form-check-label"
                                                            for="keep-file-{{ $index }}">
                                                            Conserver
                                                        </label>
                                                    </div>

                                                    <p class="card-text small text-truncate">
                                                        {{ $file['original_name'] ?? ($file['name'] ?? 'Fichier') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-light">Aucun média interprétable</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Ajouter des fichiers -->
                        <div class="mb-3">
                            <label class="form-label">Ajouter de nouveaux médias</label>
                            <div class="p-3 border rounded bg-light text-center">

                                <i class="display-4 text-muted fa fa-cloud-upload-alt"></i>
                                <h5 class="mt-2">Sélectionnez les fichiers</h5>
                                <p class="text-muted media-type-hint-modal">Images ou vidéos selon le type choisi</p>

                                <input type="file" id="edit-campaign-files" name="new_campaign_files[]"
                                    class="form-control mt-3" multiple accept="image/*,video/*">

                                <div id="edit-file-preview" class="row mt-3"></div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la Campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteTaskForm" method="POST">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cette Campagne ?</p>
                        <p class="text-danger">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <script>
        window.GLOBAL_STATS = @json($viewData['stats']);
    </script>
@endsection
