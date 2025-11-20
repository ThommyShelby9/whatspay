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
                                    <li class="breadcrumb-item active">Dashboard</li>
                                </ol>
                            </nav>
                            <p class="text-muted">
                                Bienvenue, {{ $viewData['userfirstname'] }} {{ $viewData['userlastname'] }}
                                - Découvrez votre espace annonceur
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('announcer.campaigns.create') }}">
                                <button class="btn btn-primary">
                                    <i class="fa fa-plus me-2"></i>Nouvelle Campagne
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des campagnes -->
        <div class="row">
            @php
                $taskStats = $viewData['taskStats'];
                $assignmentStats = $viewData['assignmentStats'];
            @endphp
            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Total Campagnes</h6>
                                <h4 class="mb-0">{{ $taskStats['total'] ?? 0 }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="fa fa-bullhorn text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Campagnes actives</h6>
                                <h4 class="mb-0">{{ $taskStats['pending'] ?? 0 }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="fa fa-check-circle text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Diffuseurs touchés</h6>
                                <h4 class="mb-0">{{ $assignmentStats['total'] ?? 0 }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-info">
                                    <i class="fa fa-users text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Budget total</h6>
                                <h4 class="mb-0">{{ number_format($taskStats['total_budget'] ?? 0) }} F</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="fa fa-money-bill-wave text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques et Statistiques -->
        <div class="row">
            <!-- Utilisation du budget -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aperçu du budget</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div id="budget-overview-chart" style="height: 320px;"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="budget-stats">
                                    @php
                                        $totalBudget = $taskStats['total_budget'] ?? 0;
                                        $paidBudget = $assignmentStats['paid_budget'] ?? 0;
                                        $pendingBudget = $assignmentStats['pending_budget'] ?? 0;
                                        $remainingBudget = max(0, $totalBudget - $paidBudget - $pendingBudget);
                                        $paidPercentage = $totalBudget > 0 ? ($paidBudget / $totalBudget) * 100 : 0;
                                        $pendingPercentage =
                                            $totalBudget > 0 ? ($pendingBudget / $totalBudget) * 100 : 0;
                                        $remainingPercentage =
                                            $totalBudget > 0 ? ($remainingBudget / $totalBudget) * 100 : 0;
                                    @endphp

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Budget total</span>
                                            <span class="font-weight-bold">{{ number_format($totalBudget) }} F</span>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Budget dépensé</span>
                                            <span class="font-weight-bold">{{ number_format($paidBudget) }} F</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $paidPercentage }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Budget en cours</span>
                                            <span class="font-weight-bold">{{ number_format($pendingBudget) }} F</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning" role="progressbar"
                                                style="width: {{ $pendingPercentage }}%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Budget restant</span>
                                            <span class="font-weight-bold">{{ number_format($remainingBudget) }} F</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: {{ $remainingPercentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statut des campagnes -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statut des campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div id="campaign-status-chart" style="height: 320px;"></div>
                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <div class="d-flex justify-content-center align-items-center">
                                    <i class="fa fa-circle text-primary me-2"></i>
                                    <span>En attente</span>
                                </div>
                                <h5 class="mt-2 mb-0">{{ $taskStats['pending'] ?? 0 }}</h5>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center align-items-center">
                                    <i class="fa fa-circle text-success me-2"></i>
                                    <span>Acceptées</span>
                                </div>
                                <h5 class="mt-2 mb-0">{{ $taskStats['accepted'] ?? 0 }}</h5>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center align-items-center">
                                    <i class="fa fa-circle text-danger me-2"></i>
                                    <span>Rejetées</span>
                                </div>
                                <h5 class="mt-2 mb-0">{{ $taskStats['rejected'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres de recherche -->
        {{-- <div class="row mb-4">
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
                                                    {{ request()->get('filtre_status') == 'PAID' ? 'selected' : '' }}>
                                                    Payées
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
                                </div>
                                <div class="col-md-2 d-flex align-items-center mt-2">
                                    <button type="reset" id="resetFilters"
                                        class="btn btn-light me-2">Réinitialiser</button>
                                    <button type="submit" class="btn btn-primary w-100 d-flex">
                                        <i class="fa fa-search me-2"></i>Filtrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Liste des campagnes -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Vos campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-hover" id="items_datatable">
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
                                    @forelse($viewData["recentTasks"] ?? [] as $task)
                                        <tr>
                                            <td>{{ $task->id }}</td>
                                            <td>
                                                <h6 class="mb-0">{{ $task->name }}</h6>
                                                <small
                                                    class="text-muted">{{ Str::limit($task->descriptipon ?? '', 40) }}</small>
                                            </td>
                                            <td><span class="badge bg-success">{{ number_format($task->budget ?? 0) }}
                                                    F</span></td>
                                            <td>
                                                @if (isset($task->startdate) && isset($task->enddate))
                                                    <div>{{ date('d/m/Y', strtotime($task->startdate)) }}</div>
                                                    <div>{{ date('d/m/Y', strtotime($task->enddate)) }}</div>
                                                @else
                                                    <span class="text-muted">Non défini</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $assignmentCount = $viewData['assignmentCounts'][$task->id] ?? 0;
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
                                                @if ($task->status == 'PENDING')
                                                    <span class="badge bg-warning">En attente</span>
                                                @elseif($task->status == 'ACCEPTED')
                                                    <span class="badge bg-primary">Acceptée</span>
                                                @elseif($task->status == 'PAID')
                                                    <span class="badge bg-success">Payée</span>
                                                @elseif($task->status == 'REJECTED')
                                                    <span class="badge bg-danger">Rejetée</span>
                                                @elseif($task->status == 'CLOSED')
                                                    <span class="badge bg-secondary">Fermée</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $task->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('announcer.campaigns.show', ['id' => $task->id]) }}"
                                                        class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                                                    @if ($task->status == 'PENDING')
                                                        <a href="#" class="btn btn-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#editTaskModal"
                                                            data-task-id="{{ $task->id }}"><i
                                                                class="fa fa-edit"></i></a>
                                                    @endif
                                                    <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#assignmentsModal"
                                                        data-task-id="{{ $task->id }}"><i
                                                            class="fa fa-users"></i></a>
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

        <!-- Create Task Modal -->
        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Créer une nouvelle campagne</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="createTaskForm" method="post" action="" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nom de la campagne <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="descriptipon" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date de début <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="startdate" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="enddate" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Budget (F) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="budget" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Catégories</label>
                                        <select class="form-select" name="categories[]" multiple>
                                            @foreach ($viewData['categories'] ?? [] as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Fichiers</label>
                                        <input type="file" class="form-control" name="files[]" multiple>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="client_id" value="{{ $viewData['userid'] }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Créer</button>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    </form>
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

    </div>

    <script>
        window.taskStats = @json($taskStats);
        window.paidBudget = @json($paidBudget);
        window.pendingBudget = @json($pendingBudget);
        window.remainingBudget = @json($remainingBudget);
    </script>
@endsection
