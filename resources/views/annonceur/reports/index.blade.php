<!-- File: resources/views/announcer/reports/index.blade.php -->
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
                                    <li class="breadcrumb-item active">Rapports</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-auto">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres de période -->
        <div class="row mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get" action="{{ route('announcer.reports.index') }}" class="d-flex align-items-end">
                            <div class="flex-grow-1 me-3">
                                <label class="form-label">Période d'analyse</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="filtre_start_date"
                                        value="{{ request()->get('filtre_start_date') }}">
                                    <span class="input-group-text">au</span>
                                    <input type="date" class="form-control" name="filtre_end_date"
                                        value="{{ request()->get('filtre_end_date') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter me-1"></i>Appliquer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-uppercase text-muted">Campagnes</h6>
                                <h4 class="mb-0">{{ $viewData['globalStats'] ? count($viewData['globalStats']) : 0 }}</h4>
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
                                <h6 class="mb-2 text-uppercase text-muted">Vues totales</h6>
                                @php
                                    $totalViews = 0;
                                    if (!empty($viewData['globalStats'])) {
                                        foreach ($viewData['globalStats'] as $stat) {
                                            $totalViews += $stat->total_views ?? 0;
                                        }
                                    }
                                @endphp
                                <h4 class="mb-0">{{ number_format($totalViews) }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="fa fa-eye text-white"></i>
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
                                <h6 class="mb-2 text-uppercase text-muted">Clics uniques</h6>
                                @php
                                    $totalUniqueClicks = 0;
                                    if (!empty($viewData['globalStats'])) {
                                        foreach ($viewData['globalStats'] as $stat) {
                                            $totalUniqueClicks += $stat->unique_clicks ?? 0;
                                        }
                                    }
                                @endphp
                                <h4 class="mb-0">{{ number_format($totalUniqueClicks) }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-info">
                                    <i class="fa fa-mouse-pointer text-white"></i>
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
                                <h6 class="mb-2 text-uppercase text-muted">Taux de clic moyen</h6>
                                @php
                                    $avgCTR = 0;
                                    $count = 0;
                                    if (!empty($viewData['globalStats'])) {
                                        foreach ($viewData['globalStats'] as $stat) {
                                            if (isset($stat->click_rate)) {
                                                $avgCTR += $stat->click_rate;
                                                $count++;
                                            }
                                        }
                                        $avgCTR = $count > 0 ? $avgCTR / $count : 0;
                                    }
                                @endphp
                                <h4 class="mb-0">{{ number_format($avgCTR, 2) }}%</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="fa fa-percentage text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphs -->
        <div class="row mb-4">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Performance des campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div id="campaign-performance-chart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Taux de clic par campagne</h5>
                    </div>
                    <div class="card-body">
                        <div id="ctr-chart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Détails des campagnes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="campaigns-stats-table">
                                <thead>
                                    <tr>
                                        <th>Campagne</th>
                                        <th>Vues</th>
                                        <th>Clics totaux</th>
                                        <th>Clics uniques</th>
                                        <th>Taux de clic</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewData["globalStats"] ?? [] as $stat)
                                        <tr>
                                            <td>{{ $stat->task_name ?? 'N/A' }}</td>
                                            <td>{{ number_format($stat->total_views ?? 0) }}</td>
                                            <td>{{ number_format($stat->total_clicks ?? 0) }}</td>
                                            <td>{{ number_format($stat->unique_clicks ?? 0) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 5px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                            style="width: {{ min(100, $stat->click_rate ?? 0) }}%"></div>
                                                    </div>
                                                    <span>{{ number_format($stat->click_rate ?? 0, 2) }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('announcer.campaigns.show', ['id' => $stat->task_id]) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Aucune donnée disponible</td>
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

    <script>
        window.GLOBAL_STATS = @json($viewData['globalStats']);
    </script>
@endsection
