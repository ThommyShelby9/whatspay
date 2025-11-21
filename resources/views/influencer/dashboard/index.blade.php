<!-- File: resources/views/influencer/dashboard/index.blade.php -->
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
                            <p class="text-muted">Bienvenue, {{ $viewData['userfirstname'] }} {{ $viewData['userlastname'] }}
                                - Suivez vos performances et vos missions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Missions actives</h6>
                                <h4 class="mb-0">{{ $viewData['assignmentStats']['active'] ?? 0 }}</h4>
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
                                <h6 class="mb-2 text-uppercase text-muted">Missions complétées</h6>
                                <h4 class="mb-0">{{ $viewData['assignmentStats']['completed'] ?? 0 }}</h4>
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
                                <h6 class="mb-2 text-uppercase text-muted">Gains totaux</h6>
                                <h4 class="mb-0">{{ number_format($viewData['earningsStats']['total_gain'] ?? 0) }} F
                                </h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-info">
                                    <i class="fa fa-money-bill-wave text-white"></i>
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
                                <h6 class="mb-2 text-uppercase text-muted">Taux d'acceptation</h6>
                                @php
                                    $totalAssignments = $viewData['assignmentStats']['total'] ?? 0;
                                    $completedAssignments = $viewData['assignmentStats']['completed'] ?? 0;
                                    $acceptanceRate =
                                        $totalAssignments > 0
                                            ? round(($completedAssignments / $totalAssignments) * 100)
                                            : 0;
                                @endphp
                                <h4 class="mb-0">{{ $acceptanceRate }}%</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="fa fa-chart-line text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campagnes récentes et actions -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Missions récentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Campagne</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Gain prévu</th>
                                        <th>Gain obtenu</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewData["recentAssignments"] ?? [] as $assignment)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0">{{ $assignment->task->name ?? 'N/A' }}</h6>
                                                <small
                                                    class="text-muted">{{ $assignment->task->media_type ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ isset($assignment->created_at) ? date('d/m/Y', strtotime($assignment->created_at)) : 'N/A' }}
                                            </td>
                                            <td>
                                                @if ($assignment->status == 'PENDING')
                                                    <span class="badge bg-warning">En cours</span>
                                                @elseif($assignment->status == 'ASSIGNED')
                                                    <span class="badge bg-primary">Disponible</span>
                                                @elseif($assignment->status == 'SUBMITED')
                                                    <span class="badge bg-success">Terminée</span>
                                                @elseif($assignment->status == 'REJECTED')
                                                    <span class="badge bg-danger">Rejetée</span>
                                                @elseif($assignment->status == 'SUBMISSION_ACCEPTED')
                                                    <span class="badge bg-success">Résultat validé</span>
                                                @elseif($assignment->status == 'SUBMISSION_REJECTED')
                                                    <span class="badge bg-danger">Résultat rejeté</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($assignment->expected_gain ?? 0) }} F </td>
                                            <td>
                                                @if ($assignment->status == 'SUBMISSION_ACCEPTED')
                                                    {{ number_format($assignment->gain ?? 0) }} F
                                                @else
                                                    0 F
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('influencer.campaigns.show', ['id' => $assignment->id]) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Aucune mission récente</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('influencer.campaigns.available') }}"
                                    class="btn btn-primary btn-lg w-100 d-flex flex-column align-items-center p-3">
                                    <i class="fa fa-search fa-2x mb-2"></i>
                                    <span>Parcourir les campagnes</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('influencer.campaigns.accepted') }}"
                                    class="btn btn-success btn-lg w-100 d-flex flex-column align-items-center p-3">
                                    <i class="fa fa-tasks fa-2x mb-2"></i>
                                    <span>Mes missions</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('influencer.earnings') }}"
                                    class="btn btn-info btn-lg w-100 d-flex flex-column align-items-center p-3">
                                    <i class="fa fa-money fa-2x mb-2"></i>
                                    <span>Mes gains</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('influencer.profile') }}"
                                    class="btn btn-warning btn-lg w-100 d-flex flex-column align-items-center p-3">
                                    <i class="fa fa-user fa-2x mb-2"></i>
                                    <span>Mon profil</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistiques de performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="text-muted mb-1">Campagnes terminées</p>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $completionPercent =
                                        $totalAssignments > 0 ? ($completedAssignments / $totalAssignments) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $completionPercent }}%" aria-valuenow="{{ $completionPercent }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small>{{ $completedAssignments }} terminées</small>
                                <small>{{ $totalAssignments }} total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
