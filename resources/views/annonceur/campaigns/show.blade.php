<!-- File: resources/views/announcer/campaigns/show.blade.php -->
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
                                    <li class="breadcrumb-item"><a href="{{ route('admin.client.dashboard') }}">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('announcer.campaigns.index') }}">Campagnes</a></li>
                                    <li class="breadcrumb-item active">
                                        {{ $viewData['campaign']->name ?? 'Détails' }}</li>
                                </ol>
                            </nav>
                            <h4 class="mt-2">
                                {{ $viewData['campaign']->name ?? 'Détails de la campagne' }}
                            </h4>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex">
                                <div class="status-badge me-3">
                                    @if (($viewData['campaign']->status ?? '') == 'PENDING')
                                        <span class="badge rounded-pill bg-warning px-3 py-2 fs-6"><i
                                                class="fa fa-clock me-1"></i>En attente</span>
                                    @elseif(($viewData['campaign']->status ?? '') == 'ACCEPTED')
                                        <span class="badge rounded-pill bg-primary px-3 py-2 fs-6"><i
                                                class="fa fa-check-circle me-1"></i>Acceptée</span>
                                    @elseif(($viewData['campaign']->status ?? '') == 'PAID')
                                        <span class="badge rounded-pill bg-success px-3 py-2 fs-6"><i
                                                class="fa fa-check-double me-1"></i>Payée</span>
                                    @elseif(($viewData['campaign']->status ?? '') == 'REJECTED')
                                        <span class="badge rounded-pill bg-danger px-3 py-2 fs-6"><i
                                                class="fa fa-times-circle me-1"></i>Rejetée</span>
                                    @elseif(($viewData['campaign']->status ?? '') == 'CLOSED')
                                        <span class="badge rounded-pill bg-secondary px-3 py-2 fs-6"><i
                                                class="fa fa-lock me-1"></i>Fermée</span>
                                    @else
                                        <span
                                            class="badge rounded-pill bg-secondary px-3 py-2 fs-6">{{ $viewData['campaign']->status ?? 'N/A' }}</span>
                                    @endif
                                </div>
                                <div class="btn-group" role="group">
                                    @if (($viewData['campaign']->status ?? '') == 'PENDING')
                                        <a href="{{ route('announcer.campaigns.edit', ['id' => $viewData['campaign']->id]) }}"
                                            class="btn btn-warning">
                                            <i class="fa fa-edit me-1"></i>Modifier
                                        </a>
                                    @endif
                                    <a href="{{ route('announcer.campaigns.index') }}" class="btn btn-secondary">
                                        <i class="fa fa-arrow-left me-1"></i>Retour
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de résumé -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-pattern">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded">
                                    <i class="fa fa-eye text-primary font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="font-size-16 mt-0 mb-1">Vues Totales</h5>
                                <h3 class="text-primary mb-0">
                                    {{ number_format($viewData['stats']['total_views'] ?? 0) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-pattern">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded">
                                    <i class="fa fa-mouse-pointer text-success font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="font-size-16 mt-0 mb-1">Clics Uniques</h5>
                                <h3 class="text-success mb-0">
                                    {{ number_format($viewData['stats']['unique_clicks'] ?? 0) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-pattern">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded">
                                    <i class="fa fa-percentage text-info font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="font-size-16 mt-0 mb-1">Taux de clic</h5>
                                <h3 class="text-info mb-0">
                                    {{ number_format($viewData['stats']['click_rate'] ?? 0, 2) }}%
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card bg-pattern">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class="fa fa-wallet text-warning font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="font-size-16 mt-0 mb-1">Budget</h5>
                                <h3 class="text-warning mb-0">
                                    {{ number_format($viewData['campaign']->budget ?? 0) }} F
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu de la campagne et performances -->
        <div class="row mb-4">
            <!-- Aperçu du contenu -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Contenu de la campagne</h5>
                        <span class="badge bg-primary text-black">{{ $viewData['campaign']->media_type ?? 'N/A' }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Aperçu du média -->
                        <div class="media-preview mb-4 text-center">
                            @php
                                $mediaFiles = json_decode($viewData['campaign']->files ?? '[]', true);
                                $mediaType = $viewData['campaign']->media_type ?? '';
                            @endphp

                            @if (is_array($mediaFiles) && count($mediaFiles) > 0)
                                @if (in_array($mediaType, ['image', 'image_link']))
                                    <!-- Affichage d'image avec carrousel si multiple -->
                                    <div id="campaign-carousel" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            @foreach ($mediaFiles as $index => $file)
                                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                    <img src="{{ isset($file['url']) ? $file['url'] : asset('storage/uploads/' . ($file['name'] ?? ($file['file_name'] ?? ''))) }}"
                                                        alt="Media {{ $index + 1 }}" class="img-fluid rounded"
                                                        style="max-height: 300px; width: auto;">
                                                </div>
                                            @endforeach
                                        </div>
                                        @if (count($mediaFiles) > 1)
                                            <button class="carousel-control-prev" type="button"
                                                data-bs-target="#campaign-carousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Précédent</span>
                                            </button>
                                            <button class="carousel-control-next" type="button"
                                                data-bs-target="#campaign-carousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Suivant</span>
                                            </button>
                                        @endif
                                    </div>
                                @elseif($mediaType === 'video')
                                    <!-- Affichage de vidéo -->
                                    <video controls class="img-fluid rounded" style="max-height: 300px; width: auto;">
                                        <source
                                            src="{{ isset($mediaFiles[0]['url']) ? $mediaFiles[0]['url'] : asset('storage/uploads/' . ($mediaFiles[0]['name'] ?? ($mediaFiles[0]['file_name'] ?? ''))) }}"
                                            type="video/mp4">
                                        Votre navigateur ne prend pas en charge la lecture de vidéos.
                                    </video>
                                @elseif($mediaType === 'text')
                                    <!-- Contenu texte -->
                                    <div class="text-content p-4 bg-light rounded">
                                        <p class="mb-0">
                                            {{ $viewData['campaign']->text ?? 'Contenu texte non disponible' }}</p>
                                    </div>
                                @endif
                            @else
                                <div class="no-media alert alert-light text-center">
                                    <i class="fa fa-image fa-3x text-muted mb-2"></i>
                                    <p class="mb-0">Aucun média disponible</p>
                                </div>
                            @endif
                        </div>

                        <!-- Légende et détails du média -->
                        <div class="media-details">
                            <h6 class="text-muted mb-2">Légende</h6>
                            <p class="p-3 bg-light rounded text-black">
                                {{ $viewData['campaign']->legend ?? 'Aucune légende disponible' }}
                            </p>

                            @if (($viewData['campaign']->media_type ?? '') == 'image_link')
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Lien</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="{{ $viewData['campaign']->url ?? '' }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="copyToClipboard('{{ $viewData['campaign']->url ?? '' }}')">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                        <a href="{{ $viewData['campaign']->url ?? '#' }}" target="_blank"
                                            class="btn btn-primary">
                                            <i class="fa fa-external-link-alt"></i> Ouvrir
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if ($viewData['campaign']->media_type === null || empty($viewData['mediaFiles']))
                                <div class="card mb-4">
                                    <div class="card-header bg-warning-subtle text-warning">
                                        <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Information
                                            manquante</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Cette campagne a été créée sans média associé. Pour une expérience optimale,
                                            veuillez mettre à jour la campagne avec les informations nécessaires:</p>
                                        <ul class="mb-3">
                                            <li>Type de média (image, vidéo, texte)</li>
                                            <li>Contenu média</li>
                                            <li>Légende</li>
                                            <li>Localité et profession ciblées</li>
                                        </ul>
                                        <a href="{{ route('announcer.campaigns.edit', $viewData['campaign']->id) }}"
                                            class="btn btn-warning">
                                            <i class="fa fa-edit me-1"></i>Compléter la campagne
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails et Performance -->
            <div class="col-lg-7">
                <div class="card h-100">
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Période de diffusion</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-calendar-alt text-primary me-2"></i>

                                                <p class="mb-0 fw-medium">
                                                    {{ isset($viewData['campaign']->startdate) ? date('d/m/Y', strtotime($viewData['campaign']->startdate)) : 'N/A' }}
                                                    <i class="fa fa-arrow-right mx-2 text-muted"></i>
                                                    {{ isset($viewData['campaign']->enddate) ? date('d/m/Y', strtotime($viewData['campaign']->enddate)) : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Catégories</h6>
                                            <div class="category-tags">

                                                @forelse($viewData["categories"] ?? [] as $category)
                                                    <span class="category-tag" data-bs-toggle="tooltip"
                                                        title="{{ $category->name }}">
                                                        {{ Str::limit($category->name, 15) }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted">Aucune catégorie</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Localité cible <i
                                                    class="fa fa-map-marker text-danger me-2"></i></h6>
                                            <div class=" align-items-start">
                                                @if ($viewData['localities']->isNotEmpty())
                                                    @foreach ($viewData['localities'] as $locality)
                                                        <span class="mb-0 fw-medium d-block">
                                                            {{ $locality->name }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="mb-0 fw-medium d-block">
                                                        Toutes les localités
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Date de création</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-calendar text-success me-2"></i>
                                                <p class="mb-0 fw-medium">
                                                    {{ isset($viewData['campaign']->created_at) ? date('d/m/Y H:i', strtotime($viewData['campaign']->created_at)) : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Profession cible <i
                                                    class="fa fa-briefcase text-primary me-2"></i></h6>
                                            <div class=" align-items-center">

                                                @if ($viewData['occupations']->isNotEmpty())
                                                    @foreach ($viewData['occupations'] as $occupation)
                                                        <p class="mb-0 fw-medium">
                                                            {{ $occupation->name }}
                                                        </p>
                                                    @endforeach
                                                @else
                                                    <p class="mb-0 fw-medium">
                                                        Aucune profession
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h6 class="text-muted fw-normal mb-1">Diffuseurs actifs</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-users text-info me-2"></i>
                                                <p class="mb-0 fw-medium">
                                                    {{ count($viewData['assignments'] ?? []) }}
                                                    diffuseur(s)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-12">
                                        <h6 class="text-muted fw-normal mb-2">Description</h6>
                                        <div class="p-3 bg-light rounded">
                                            <p class="mb-0 text-black">
                                                {{ $viewData['campaign']->descriptipon ?? 'Aucune description disponible' }}
                                            </p>
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
                                    <div id="geo-chart" style="height: 500px; width:100%"
                                        class="rounded border bg-white"></div>
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

                                <!-- Statistiques détaillées -->
                                {{-- <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-primary-subtle rounded text-center">
                                            <h5 class="text-primary mb-1">
                                                {{ number_format($viewData['stats']['conversion_rate'] ?? 0, 2) }}%
                                            </h5>
                                            <p class="text-muted small mb-0">Taux de conversion</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-success-subtle rounded text-center">
                                            <h5 class="text-success mb-1">
                                                {{ gmdate('H:i:s', $viewData['stats']['avg_time'] ?? 0) }}
                                            </h5>
                                            <p class="text-muted small mb-0">Temps moyen</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-info-subtle rounded text-center">
                                            <h5 class="text-info mb-1">
                                                {{ number_format($viewData['stats']['engagement_rate'] ?? 0, 2) }}%
                                            </h5>
                                            <p class="text-muted small mb-0">Taux d'engagement</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-warning-subtle rounded text-center">
                                            <h5 class="text-warning mb-1">
                                                {{ number_format($viewData['stats']['bounce_rate'] ?? 0, 2) }}%
                                            </h5>
                                            <p class="text-muted small mb-0">Taux de rebond</p>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des diffuseurs -->
    {{-- <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Diffuseurs de la campagne</h5>
                    <div class="actions">
                        <button type="button" class="btn btn-sm btn-light" id="refresh-table">
                            <i class="fa fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="assignments-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Diffuseur</th>
                                    <th style="width: 15%;">Vues</th>
                                    <th style="width: 15%;">Gain</th>
                                    <th style="width: 15%;">Statut</th>
                                    <th style="width: 15%;">Date de soumission</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($viewData["assignments"] ?? [] as $assignment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2 bg-info-subtle rounded-circle">
                                                    <span
                                                        class="avatar-text text-info">{{ substr($assignment->agent_name ?? 'N/A', 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    {{ $assignment->agent_name ?? 'N/A' }}
                                                    @if ($assignment->agent_email)
                                                        <div class="small text-muted">{{ $assignment->agent_email }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($assignment->vues ?? 0) }}</td>
                                        <td>{{ number_format($assignment->gain ?? 0) }} F</td>
                                        <td>
                                            @if ($assignment->status == 'PENDING')
                                                <span class="badge bg-warning text-dark">En attente</span>
                                            @elseif($assignment->status == 'ACCEPTED')
                                                <span class="badge bg-primary">Acceptée</span>
                                            @elseif($assignment->status == 'COMPLETED')
                                                <span class="badge bg-success">Terminée</span>
                                            @elseif($assignment->status == 'REJECTED')
                                                <span class="badge bg-danger">Rejetée</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ isset($assignment->submission_date) ? date('d/m/Y H:i', strtotime($assignment->submission_date)) : '-' }}
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info view-details"
                                                    data-id="{{ $assignment->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                @if ($assignment->status == 'COMPLETED')
                                                    <button type="button"
                                                        class="btn btn-sm btn-success approve-submission"
                                                        data-id="{{ $assignment->id }}">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-submission"
                                                        data-id="{{ $assignment->id }}">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun diffuseur assigné à cette campagne
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Modal pour afficher les détails d'une soumission -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la soumission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="submission-details-content">
                        <!-- Le contenu sera chargé dynamiquement -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.GLOBAL_STATS = @json($viewData['stats'])
    </script>
@endsection
