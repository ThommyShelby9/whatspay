<!-- File: resources/views/influencer/performance/index.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('influencer.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Performances</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <div class="btn-group">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i>Exporter
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="exportCSV">CSV</a></li>
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
          <form method="get" action="{{ route('influencer.performance') }}" class="d-flex align-items-end">
            <div class="flex-grow-1 me-3">
              <label class="form-label">Période d'analyse</label>
              <div class="input-group">
                <input type="date" class="form-control" name="start_date" value="{{ request()->get('start_date') }}">
                <span class="input-group-text">au</span>
                <input type="date" class="form-control" name="end_date" value="{{ request()->get('end_date') }}">
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

  <!-- Cartes de statistiques -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Missions complétées</h6>
              <h4 class="mb-0">{{ $viewData["assignmentStats"]["completed_count"] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-primary">
                <i class="fa fa-check-circle text-white"></i>
              </span>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0">
            <span class="text-success me-2">
              <i class="fa fa-arrow-up me-1"></i>8.2%
            </span>
            <span class="text-nowrap">Par rapport au mois dernier</span>
          </p>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Vues totales</h6>
              <h4 class="mb-0">{{ number_format($viewData["assignmentStats"]["total_views"] ?? 0) }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-success">
                <i class="fa fa-eye text-white"></i>
              </span>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0">
            <span class="text-success me-2">
              <i class="fa fa-arrow-up me-1"></i>16.4%
            </span>
            <span class="text-nowrap">Par rapport au mois dernier</span>
          </p>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Vues moyennes</h6>
              @php
                $completedCount = $viewData["assignmentStats"]["completed_count"] ?? 0;
                $totalViews = $viewData["assignmentStats"]["total_views"] ?? 0;
                $averageViews = $completedCount > 0 ? $totalViews / $completedCount : 0;
              @endphp
              <h4 class="mb-0">{{ number_format($averageViews) }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-info">
                <i class="fa fa-chart-line text-white"></i>
              </span>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0">
            <span class="text-success me-2">
              <i class="fa fa-arrow-up me-1"></i>4.6%
            </span>
            <span class="text-nowrap">Par rapport au mois dernier</span>
          </p>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Taux de satisfaction</h6>
              <h4 class="mb-0">{{ $viewData["assignmentStats"]["satisfaction_rate"] ?? 95 }}%</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-warning">
                <i class="fa fa-star text-white"></i>
              </span>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0">
            <span class="text-success me-2">
              <i class="fa fa-arrow-up me-1"></i>2.1%
            </span>
            <span class="text-nowrap">Par rapport au mois dernier</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Graphiques -->
  <div class="row mb-4">
    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Évolution des vues</h5>
          <div>
            <button type="button" class="btn btn-sm btn-light active" data-period="week">Semaine</button>
            <button type="button" class="btn btn-sm btn-light" data-period="month">Mois</button>
            <button type="button" class="btn btn-sm btn-light" data-period="year">Année</button>
          </div>
        </div>
        <div class="card-body">
          <div id="views-chart" style="height: 350px;"></div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Répartition par catégorie</h5>
        </div>
        <div class="card-body">
          <div id="category-chart" style="height: 350px;"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tableau des performances -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Détails des performances</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="performance-table">
              <thead>
                <tr>
                  <th>Campagne</th>
                  <th>Date</th>
                  <th>Vues</th>
                  <th>Objectif</th>
                  <th>Performance</th>
                  <th>Évaluation</th>
                </tr>
              </thead>
              <tbody>
                @php
                  // Exemple de données de performance - à remplacer par les données réelles
                  $performanceData = [
                    [
                      'campaign' => 'Promo Été 2025',
                      'date' => '2025-07-15',
                      'views' => 5200,
                      'target' => 5000,
                      'performance' => 104,
                      'rating' => 5
                    ],
                    [
                      'campaign' => 'Lancement Produit',
                      'date' => '2025-06-20',
                      'views' => 3800,
                      'target' => 4000,
                      'performance' => 95,
                      'rating' => 4
                    ],
                    [
                      'campaign' => 'Offre Spéciale',
                      'date' => '2025-05-10',
                      'views' => 6200,
                      'target' => 5000,
                      'performance' => 124,
                      'rating' => 5
                    ]
                  ];
                @endphp
                
                @foreach($performanceData as $performance)
                <tr>
                  <td>{{ $performance['campaign'] }}</td>
                  <td>{{ date('d/m/Y', strtotime($performance['date'])) }}</td>
                  <td>{{ number_format($performance['views']) }}</td>
                  <td>{{ number_format($performance['target']) }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress flex-grow-1 me-2" style="height: 5px;">
                        <div class="progress-bar {{ $performance['performance'] >= 100 ? 'bg-success' : 'bg-warning' }}" 
                             role="progressbar" 
                             style="width: {{ min(100, $performance['performance']) }}%">
                        </div>
                      </div>
                      <span>{{ $performance['performance'] }}%</span>
                    </div>
                  </td>
                  <td>
                    @for($i = 1; $i <= 5; $i++)
                      @if($i <= $performance['rating'])
                        <i class="fa fa-star text-warning"></i>
                      @else
                        <i class="fa fa-star text-muted"></i>
                      @endif
                    @endfor
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .apexcharts-tooltip {
    background-color: #fff !important;
    border: 1px solid #e9e9e9 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#performance-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[1, 'desc']],
      pageLength: 10
    });
    
    // Views Chart
    var options = {
      series: [{
        name: 'Vues',
        data: [3500, 4200, 3800, 5000, 4800, 6200, 5200]
      }],
      chart: {
        height: 350,
        type: 'area',
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      xaxis: {
        categories: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val.toLocaleString() + " vues";
          }
        }
      },
      colors: ['#3b5de7'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.3,
          stops: [0, 90, 100]
        }
      }
    };

    var viewsChart = new ApexCharts(document.querySelector("#views-chart"), options);
    viewsChart.render();
    
    // Category Chart
    var categoryOptions = {
      series: [35, 25, 20, 15, 5],
      chart: {
        type: 'donut',
        height: 350,
      },
      labels: ['Mode', 'Technologie', 'Alimentation', 'Beauté', 'Autres'],
      colors: ['#3b5de7', '#45cb85', '#eeb902', '#ff715b', '#8891a1'],
      legend: {
        position: 'bottom'
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    };

    var categoryChart = new ApexCharts(document.querySelector("#category-chart"), categoryOptions);
    categoryChart.render();
    
    // Period buttons
    $('[data-period]').click(function() {
      $('[data-period]').removeClass('active');
      $(this).addClass('active');
      
      var period = $(this).data('period');
      
      // Update chart data based on period
      var categories, data;
      
      if (period === 'week') {
        categories = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        data = [3500, 4200, 3800, 5000, 4800, 6200, 5200];
      } else if (period === 'month') {
        categories = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'];
        data = [15000, 18000, 22000, 20000];
      } else { // year
        categories = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        data = [65000, 70000, 80000, 75000, 85000, 90000, 95000, 100000, 92000, 88000, 96000, 102000];
      }
      
      viewsChart.updateOptions({
        xaxis: {
          categories: categories
        },
        series: [{
          name: 'Vues',
          data: data
        }]
      });
    });
    
    // Export buttons
    $('#exportCSV, #exportPDF').on('click', function(e) {
      e.preventDefault();
      alert('Fonctionnalité d\'exportation à implémenter');
    });
  });
</script>
@endpush
@endsection