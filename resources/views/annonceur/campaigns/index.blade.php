<!-- File: resources/views/announcer/campaigns/index.blade.php -->
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
                                    <li class="breadcrumb-item active">Campagnes</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('announcer.campaigns.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i>Nouvelle Campagne
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="row mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Filtrer vos campagnes</h5>
                        <a class="btn btn-sm btn-link" data-bs-toggle="collapse" href="#collapseFilter" role="button"
                            aria-expanded="false">
                            <i class="fa fa-chevron-down"></i>
                        </a>
                    </div>
                    <div class="collapse" id="collapseFilter">
                        <div class="card-body">
                            <form method="get" action="{{ route('announcer.campaigns.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Statut</label>
                                            <select class="form-select" name="filtre_status">
                                                <option value="">Tous les statuts</option>
                                                <option value="PENDING"
                                                    {{ request()->get('filtre_status') == 'PENDING' ? 'selected' : '' }}>En
                                                    attente</option>
                                                <option value="ACCEPTED"
                                                    {{ request()->get('filtre_status') == 'ACCEPTED' ? 'selected' : '' }}>
                                                    Acceptées</option>
                                                <option value="PAID"
                                                    {{ request()->get('filtre_status') == 'PAID' ? 'selected' : '' }}>Payées
                                                </option>
                                                <option value="REJECTED"
                                                    {{ request()->get('filtre_status') == 'REJECTED' ? 'selected' : '' }}>
                                                    Rejetées</option>
                                                <option value="CLOSED"
                                                    {{ request()->get('filtre_status') == 'CLOSED' ? 'selected' : '' }}>
                                                    Fermées</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Catégorie</label>
                                            <select class="form-select" name="filtre_category">
                                                <option value="">Toutes les catégories</option>
                                                @foreach ($viewData['categories'] ?? [] as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ request()->get('filtre_category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Période</label>
                                            <div class="input-group">
                                                <input type="date" class="form-control" name="filtre_start_date"
                                                    value="{{ request()->get('filtre_start_date') }}">
                                                <span class="input-group-text">au</span>
                                                <input type="date" class="form-control" name="filtre_end_date"
                                                    value="{{ request()->get('filtre_end_date') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center">
                                        <button type="submit" class="btn btn-primary w-100 mt-2">
                                            <i class="fa fa-search me-2"></i>Filtrer
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des campagnes -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Vos campagnes</h5>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-download me-1"></i>Exporter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="#" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="#" id="exportPDF">PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="campaigns-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Campagne</th>
                                        <th>Budget</th>
                                        <th>Période</th>
                                        <th>Diffuseurs</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewData["campaigns"] ?? [] as $campaign)
                                        <tr>
                                            <td>{{ $campaign->id }}</td>
                                            <td>
                                                <h6 class="mb-0">{{ $campaign->name }}</h6>
                                                <small
                                                    class="text-muted">{{ Str::limit($campaign->descriptipon ?? '', 40) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ number_format($campaign->budget ?? 0) }}
                                                    F</span>
                                            </td>
                                            <td>
                                                @if (isset($campaign->startdate) && isset($campaign->enddate))
                                                    <div>{{ date('d/m/Y', strtotime($campaign->startdate)) }}</div>
                                                    <div>{{ date('d/m/Y', strtotime($campaign->enddate)) }}</div>
                                                @else
                                                    <span class="text-muted">Non défini</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $assignmentCount =
                                                        $viewData['assignmentCounts'][$campaign->id] ?? 0;
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-2">{{ $assignmentCount }}</span>
                                                    <div class="progress flex-grow-1" style="height: 5px;">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ min(100, $assignmentCount * 5) }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($campaign->status == 'PENDING')
                                                    <span class="badge bg-warning">En attente</span>
                                                @elseif($campaign->status == 'ACCEPTED')
                                                    <span class="badge bg-primary">Acceptée</span>
                                                @elseif($campaign->status == 'PAID')
                                                    <span class="badge bg-success">Payée</span>
                                                @elseif($campaign->status == 'REJECTED')
                                                    <span class="badge bg-danger">Rejetée</span>
                                                @elseif($campaign->status == 'CLOSED')
                                                    <span class="badge bg-secondary">Fermée</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $campaign->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('announcer.campaigns.show', ['id' => $campaign->id]) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    @if ($campaign->status == 'PENDING')
                                                        <a href="{{ route('announcer.campaigns.edit', ['id' => $campaign->id]) }}"
                                                            class="btn btn-warning btn-sm">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#assignmentsModal"
                                                        data-campaign-id="{{ $campaign->id }}">
                                                        <i class="fa fa-users"></i>
                                                    </a>
                                                    @if ($campaign->status == 'PENDING')
                                                        <button class="btn btn-danger btn-sm delete-campaign"
                                                            data-campaign-id="{{ $campaign->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Aucune campagne trouvée</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Modal -->
    <div class="modal fade" id="assignmentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diffuseurs de la campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="assignments-loading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div id="assignments-content" class="d-none">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div class="modal fade" id="deleteCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Supprimer la Campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteCampaignForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cette Campagne ?</p>
                        <p class="text-danger">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                $('#campaigns-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                    },
                    order: [
                        [0, 'desc']
                    ],
                    pageLength: 10
                });

                // Load assignments for a campaign
                $('#assignmentsModal').on('show.bs.modal', function(e) {
                    const button = $(e.relatedTarget);
                    const campaignId = button.data('campaign-id');
                    const loadingDiv = $('#assignments-loading');
                    const contentDiv = $('#assignments-content');

                    loadingDiv.removeClass('d-none');
                    contentDiv.addClass('d-none');

                    // AJAX call to get assignments data
                    $.ajax({
                        url: '/admin/task/' + campaignId + '/assignments',
                        type: 'GET',
                        success: function(response) {
                            loadingDiv.addClass('d-none');
                            contentDiv.removeClass('d-none');

                            if (response.success) {
                                let html = `
              <h6 class="mb-3">Campagne: ${response.task.name}</h6>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Diffuseur</th>
                      <th>Vues</th>
                      <th>Statut</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
            `;

                                if (response.assignments.length > 0) {
                                    response.assignments.forEach(function(assignment) {
                                        let statusBadge = '';
                                        if (assignment.status === 'PENDING') {
                                            statusBadge =
                                                '<span class="badge bg-warning">En attente</span>';
                                        } else if (assignment.status === 'ACCEPTED') {
                                            statusBadge =
                                                '<span class="badge bg-primary">Acceptée</span>';
                                        } else if (assignment.status === 'COMPLETED') {
                                            statusBadge =
                                                '<span class="badge bg-success">Terminée</span>';
                                        } else if (assignment.status === 'REJECTED') {
                                            statusBadge =
                                                '<span class="badge bg-danger">Rejetée</span>';
                                        }

                                        html += `
                  <tr>
                    <td>${assignment.agent_name}</td>
                    <td>${assignment.vues ?? 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${new Date(assignment.created_at).toLocaleDateString()}</td>
                  </tr>
                `;
                                    });
                                } else {
                                    html += `
                <tr>
                  <td colspan="4" class="text-center">Aucun diffuseur assigné à cette campagne</td>
                </tr>
              `;
                                }

                                html += `
                  </tbody>
                </table>
              </div>
            `;

                                contentDiv.html(html);
                            } else {
                                contentDiv.html(
                                    '<div class="alert alert-danger">Impossible de charger les données</div>'
                                );
                            }
                        },
                        error: function() {
                            loadingDiv.addClass('d-none');
                            contentDiv.removeClass('d-none');
                            contentDiv.html(
                                '<div class="alert alert-danger">Une erreur est survenue lors du chargement des données</div>'
                            );
                        }
                    });
                });

                // Export functions - placeholder
                $('#exportCSV, #exportExcel, #exportPDF').on('click', function(e) {
                    e.preventDefault();
                    alert('Fonctionnalité d\'export à implémenter');
                });
            });
        </script>
    @endpush
@endsection
