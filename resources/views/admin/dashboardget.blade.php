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
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active">Bienvenue sur le tableau de bord WhatsPAY</li>
                            </ol>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.tasks') }}" class="btn btn-primary">
                                <i class="fa fa-plus-circle me-2"></i>Nouvelle Campagne
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row mt-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 mb-4">
                    <div class="card-body bg-primary-subtle rounded">
                        <div class="row align-items-center">
                            <div class="col-4 text-center">
                                <i class="fa fa-user text-primary fa-3x"></i>
                            </div>
                            <div class="col-8">
                                <h3 class="font-weight-bold mb-0">{{ $viewData['userStats']['total'] ?? 0 }}</h3>
                                <span class="h6">Utilisateurs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 mb-4">
                    <div class="card-body bg-success-subtle rounded">
                        <div class="row align-items-center">
                            <div class="col-4 text-center">
                                <i class="fa fa-tasks text-success fa-3x"></i>
                            </div>
                            <div class="col-8">
                                <h3 class="font-weight-bold mb-0">{{ $viewData['taskStats']['total'] ?? 0 }}</h3>
                                <span class="h6">Campagnes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 mb-4">
                    <div class="card-body bg-info-subtle rounded">
                        <div class="row align-items-center">
                            <div class="col-4 text-center">
                                <i class="fa fa-eye text-info fa-3x"></i>
                            </div>
                            <div class="col-8">
                                <h3 class="font-weight-bold mb-0">
                                    {{ number_format($viewData['userStats']['total_vuesmoyen'] ?? 0) }}</h3>
                                <span class="h6">Vues Potentielles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 mb-4">
                    <div class="card-body bg-warning-subtle rounded">
                        <div class="row align-items-center">
                            <div class="col-4 text-center">
                                <i class="fa fa-money text-warning fa-3x"></i>
                            </div>
                            <div class="col-8">
                                <h3 class="font-weight-bold mb-0">
                                    {{ number_format($viewData['taskStats']['total_budget'] ?? 0) }} F</h3>
                                <span class="h6">Budget Total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Status & User Type Distribution -->
        <div class="row">
            <!-- Task Status -->
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statut des Campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress-status mb-4">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">En attente</span>
                                        <span class="text-dark">{{ $viewData['taskStats']['pending'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            style="width: {{ $viewData['taskStats']['total'] > 0 ? (($viewData['taskStats']['pending'] ?? 0) / $viewData['taskStats']['total']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Acceptées</span>
                                        <span class="text-dark">{{ $viewData['taskStats']['accepted'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $viewData['taskStats']['total'] > 0 ? (($viewData['taskStats']['accepted'] ?? 0) / $viewData['taskStats']['total']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Payées</span>
                                        <span class="text-dark">{{ $viewData['taskStats']['paid'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $viewData['taskStats']['total'] > 0 ? (($viewData['taskStats']['paid'] ?? 0) / $viewData['taskStats']['total']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Rejetées</span>
                                        <span class="text-dark">{{ $viewData['taskStats']['rejected'] ?? 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: {{ $viewData['taskStats']['total'] > 0 ? (($viewData['taskStats']['rejected'] ?? 0) / $viewData['taskStats']['total']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="chart-container" style="height: 250px;">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Distribution -->
            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Répartition des Utilisateurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="userDistributionChart"></canvas>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <i class="fa fa-circle text-primary me-2"></i>
                                    <span class="text-muted">Diffuseurs</span>
                                </div>
                                <span class="text-dark">{{ $viewData['userStats']['influencers'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <i class="fa fa-circle text-success me-2"></i>
                                    <span class="text-muted">Annonceurs</span>
                                </div>
                                <span class="text-dark">{{ $viewData['userStats']['announcers'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="fa fa-circle text-danger me-2"></i>
                                    <span class="text-muted">Administrateurs</span>
                                </div>
                                <span class="text-dark">{{ $viewData['userStats']['admins'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <!-- Recent Tasks -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">Campagnes récentes</h5>
                        <a href="{{ route('admin.tasks') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Client</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($viewData['recentTasks']) && count($viewData['recentTasks']) > 0)
                                        @foreach ($viewData['recentTasks'] as $task)
                                            <tr>
                                                <td>
                                                    <a
                                                        href="{{ route('admin.task', ['id' => $task->id]) }}">{{ $task->name }}</a>
                                                </td>
                                                <td>{{ $task->client_name ?? 'N/A' }}</td>
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
                                                        <span class="badge bg-secondary">Clôturée</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $task->status }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ date('d/m/Y', strtotime($task->created_at)) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-3">Aucune Campagne récente</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">Utilisateurs récents</h5>
                        <a href="{{ route('admin.users', ['group' => 'all']) }}"
                            class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Profil</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($viewData['recentUsers']) && count($viewData['recentUsers']) > 0)
                                        @foreach ($viewData['recentUsers'] as $user)
                                            <tr>
                                                <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if (strpos($user->profiles, 'DIFFUSEUR') !== false)
                                                        <span class="badge bg-primary">Diffuseur</span>
                                                    @elseif(strpos($user->profiles, 'ANNONCEUR') !== false)
                                                        <span class="badge bg-success">Annonceur</span>
                                                    @elseif(strpos($user->profiles, 'ADMIN') !== false)
                                                        <span class="badge bg-danger">Admin</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $user->profiles }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-3">Aucun utilisateur récent</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary-subtle rounded p-3 me-3">
                                <i class="fa fa-user text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Gestion des Utilisateurs</h5>
                                <p class="card-text text-muted">Gérer les comptes utilisateurs</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.users', ['group' => 'all']) }}"
                                class="btn btn-primary btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle rounded p-3 me-3">
                                <i class="fa fa-tasks text-success fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Gestion des Campagnes</h5>
                                <p class="card-text text-muted">Créer et suivre les Campagnes</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.tasks') }}" class="btn btn-success btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-info-subtle rounded p-3 me-3">
                                <i class="fa fa-whatsapp text-info fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">WhatsApp</h5>
                                <p class="card-text text-muted">Gérer les numéros WhatsApp</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.whatsapp_numbers') }}" class="btn btn-info btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.TASK_STATS = @json($viewData['taskStats']);
        window.USER_STATS = @json($viewData['userStats']);
    </script>
@endsection
