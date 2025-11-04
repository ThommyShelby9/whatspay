<!-- File: resources/views/influencer/earnings/index.blade.php -->
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
                <li class="breadcrumb-item active">Mes Gains</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <div class="dropdown">
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

  <!-- Résumé des gains -->
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-lg bg-soft-primary rounded-circle text-center">
                <i class="fa fa-wallet text-primary font-size-24 mt-4"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-4">
              <h3 class="fw-bold">Gains totaux</h3>
              <h1 class="mb-3 text-primary">{{ number_format($viewData["earningsStats"]["total_earnings"] ?? 0) }} F</h1>
              <div class="row">
                <div class="col-6">
                  <h6 class="text-muted mb-1">Ce mois-ci</h6>
                  <h5>{{ number_format($viewData["earningsStats"]["current_month"] ?? 0) }} F</h5>
                </div>
                <div class="col-6">
                  <h6 class="text-muted mb-1">Mois précédent</h6>
                  <h5>{{ number_format($viewData["earningsStats"]["last_month"] ?? 0) }} F</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-soft-success">
          <h5 class="card-title text-success mb-0">Prochains paiements</h5>
        </div>
        <div class="card-body">
          <h4 class="mb-1">{{ number_format($viewData["earningsStats"]["pending_payment"] ?? 0) }} F</h4>
          <p class="text-muted">En attente de paiement</p>
          <hr>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Prochaine date de paiement</span>
            <span class="fw-bold">{{ date('d/m/Y', strtotime('first day of next month')) }}</span>
          </div>
          <button type="button" class="btn btn-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
            <i class="fa fa-money-bill-wave me-1"></i>Demander un retrait
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Graphique des gains -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Évolution des gains</h5>
          <div>
            <button type="button" class="btn btn-sm btn-light active" data-period="6months">6 mois</button>
            <button type="button" class="btn btn-sm btn-light" data-period="year">1 an</button>
            <button type="button" class="btn btn-sm btn-light" data-period="all">Tout</button>
          </div>
        </div>
        <div class="card-body">
          <div id="earnings-chart" style="height: 350px;"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Historique des transactions -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Historique des transactions</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" id="transactions-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Référence</th>
                  <th>Montant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <!-- Données d'exemple - à remplacer par les données réelles -->
               
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Demande de retrait -->
<div class="modal fade" id="withdrawalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Demande de retrait</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="withdrawalForm">
          <div class="mb-3">
            <label class="form-label">Montant à retirer</label>
            <input type="number" class="form-control" min="5000" step="1000" required>
            <div class="form-text">Montant minimum: 5 000 F</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Méthode de retrait</label>
            <select class="form-select" required>
              <option value="">Sélectionner une méthode</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="bank">Compte bancaire</option>
            </select>
          </div>
          
          <div class="alert alert-info">
            <p class="mb-0">
              <i class="fa fa-info-circle me-2"></i>
              Les demandes de retrait sont traitées dans un délai de 48 heures ouvrables.
            </p>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-success">Confirmer le retrait</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#transactions-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[0, 'desc']],
      pageLength: 10
    });
    
    // Earnings Chart
    var options = {
      series: [{
        name: 'Gains',
        data: [38000, 52000, 45000, 62000, 57000, 89000]
      }],
      chart: {
        height: 350,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          borderRadius: 4,
          columnWidth: '60%',
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 2
      },
      xaxis: {
        categories: ['Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct'],
      },
      yaxis: {
        title: {
          text: 'F CFA'
        }
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val.toLocaleString() + " F";
          }
        }
      },
      colors: ['#45cb85']
    };

    var earningsChart = new ApexCharts(document.querySelector("#earnings-chart"), options);
    earningsChart.render();
    
    // Period buttons
    $('[data-period]').click(function() {
      $('[data-period]').removeClass('active');
      $(this).addClass('active');
      
      var period = $(this).data('period');
      
      // Update chart data based on period
      var categories, data;
      
      if (period === '6months') {
        categories = ['Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct'];
        data = [38000, 52000, 45000, 62000, 57000, 89000];
      } else if (period === 'year') {
        categories = ['Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct'];
        data = [32000, 45000, 28000, 35000, 42000, 50000, 38000, 52000, 45000, 62000, 57000, 89000];
      } else { // all
        categories = ['2023-T4', '2024-T1', '2024-T2', '2024-T3', '2024-T4', '2025-T1', '2025-T2', '2025-T3'];
        data = [80000, 105000, 150000, 180000, 220000, 270000, 320000, 390000];
      }
      
      earningsChart.updateOptions({
        xaxis: {
          categories: categories
        },
        series: [{
          name: 'Gains',
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