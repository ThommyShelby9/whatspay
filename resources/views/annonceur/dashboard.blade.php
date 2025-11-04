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
            <p class="text-muted">Bienvenue, {{ $viewData['userfirstname'] }} {{ $viewData['userlastname'] }} - Découvrez votre espace annonceur</p>
          </div>
          <div class="col-auto">
            <a href="{{ route('announcer.campaigns.create') }}">
              <button class="btn btn-primary" ">
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
    <div class="col-xl-3 col-md-6">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Total Campagnes</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['total'] ?? 0 }}</h4>
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
              <h4 class="mb-0">{{ $viewData['taskStats']['pending'] ?? 0 }}</h4>
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
              <h4 class="mb-0">{{ $viewData['assignmentStats']['total'] ?? 0 }}</h4>
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
              <h4 class="mb-0">{{ number_format($viewData['taskStats']['total_budget'] ?? 0) }} F</h4>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div>
                    <h6 class="mb-0">Budget total</h6>
                    <h4 class="text-primary mt-2">{{ number_format($viewData['taskStats']['total_budget'] ?? 0) }} F</h4>
                  </div>
                </div>

                <div class="mb-4">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Budget dépensé</span>
                    <span class="font-weight-bold">{{ number_format($viewData['assignmentStats']['paid_budget'] ?? 0) }} F</span>
                  </div>
@php
    $totalBudget = $viewData['taskStats']['total_budget'] ?? 0;
    $paidBudget = $viewData['assignmentStats']['paid_budget'] ?? 0;
    $pendingBudget = $viewData['assignmentStats']['pending_budget'] ?? 0;
    $remainingBudget = max(0, $totalBudget - $paidBudget - $pendingBudget);
    
    // Éviter la division par zéro
    $paidPercentage = ($totalBudget > 0) ? ($paidBudget / $totalBudget * 100) : 0;
    $pendingPercentage = ($totalBudget > 0) ? ($pendingBudget / $totalBudget * 100) : 0;
    $remainingPercentage = ($totalBudget > 0) ? ($remainingBudget / $totalBudget * 100) : 0;
@endphp
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercentage }}%"></div>
                  </div>
                </div>

                <div class="mb-4">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Budget en cours</span>
                    <span class="font-weight-bold">{{ number_format($viewData['assignmentStats']['pending_budget'] ?? 0) }} F</span>
                  </div>
@php
  $pendingPercentage = ($totalBudget > 0) 
    ? (($viewData['assignmentStats']['pending_budget'] ?? 0) / $totalBudget * 100) 
    : 0;
@endphp

                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercentage }}%"></div>
                  </div>
                </div>

                <div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Budget restant</span>
@php
  $remainingPercentage = ($totalBudget > 0)
    ? ($remainingBudget / $totalBudget * 100)
    : 0;
@endphp
                    <span class="font-weight-bold">{{ number_format($remainingBudget) }} F</span>
                  </div>
@php
  $remainingPercentage = ($totalBudget > 0)
    ? ($remainingBudget / $totalBudget * 100)
    : 0;
@endphp
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $remainingPercentage }}%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistiques des campagnes -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Statut des campagnes</h5>
        </div>
        <div class="card-body">
          <div id="campaign-status-chart" style="height: 320px;"></div>
          <div class="row text-center mt-3">
            <div class="col-4">
              <div class="d-flex justify-content-center">
                <div class="d-flex align-items-center">
                  <i class="fa fa-circle text-primary me-2"></i>
                  <span>En attente</span>
                </div>
              </div>
              <h5 class="mt-2 mb-0">{{ $viewData['taskStats']['pending'] ?? 0 }}</h5>
            </div>
            <div class="col-4">
              <div class="d-flex justify-content-center">
                <div class="d-flex align-items-center">
                  <i class="fa fa-circle text-success me-2"></i>
                  <span>Acceptées</span>
                </div>
              </div>
              <h5 class="mt-2 mb-0">{{ $viewData['taskStats']['accepted'] ?? 0 }}</h5>
            </div>
            <div class="col-4">
              <div class="d-flex justify-content-center">
                <div class="d-flex align-items-center">
                  <i class="fa fa-circle text-danger me-2"></i>
                  <span>Rejetées</span>
                </div>
              </div>
              <h5 class="mt-2 mb-0">{{ $viewData['taskStats']['rejected'] ?? 0 }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Campagnes récentes et filtres -->
  <div class="row">
    <!-- Filtres de recherche -->
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Filtrer vos campagnes</h5>
          <a class="btn btn-sm btn-link" data-bs-toggle="collapse" href="#collapseFilter" role="button" aria-expanded="false">
            <i class="fa fa-chevron-down"></i>
          </a>
        </div>
        <div class="collapse" id="collapseFilter">
          <div class="card-body">
            <form class="form theme-form" method="post" action="">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group mb-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="filtre_status">
                      <option value="">Tous les statuts</option>
                      <option value="PENDING" {{ isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "PENDING" ? 'selected' : '' }}>En attente</option>
                      <option value="ACCEPTED" {{ isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "ACCEPTED" ? 'selected' : '' }}>Acceptées</option>
                      <option value="PAID" {{ isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "PAID" ? 'selected' : '' }}>Payées</option>
                      <option value="REJECTED" {{ isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "REJECTED" ? 'selected' : '' }}>Rejetées</option>
                      <option value="CLOSED" {{ isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "CLOSED" ? 'selected' : '' }}>Fermées</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-3">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select" name="filtre_category">
                      <option value="">Toutes les catégories</option>
                      @foreach($viewData["categories"] ?? [] as $category)
                      <option value="{{ $category->id }}" {{ isset($viewData["filtre_category"]) && $viewData["filtre_category"] == $category->id ? 'selected' : '' }}>
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
                      <input type="date" class="form-control" name="filtre_start_date" value="{{ $viewData['filtre_start_date'] ?? '' }}">
                      <span class="input-group-text">au</span>
                      <input type="date" class="form-control" name="filtre_end_date" value="{{ $viewData['filtre_end_date'] ?? '' }}">
                    </div>
                  </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-2"></i>Filtrer
                  </button>
                </div>
              </div>
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Liste des campagnes -->
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Vos campagnes</h5>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownExport" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa fa-download me-1"></i>Exporter
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownExport">
              <li><a class="dropdown-item" href="#" id="exportCSV">CSV</a></li>
              <li><a class="dropdown-item" href="#" id="exportExcel">Excel</a></li>
              <li><a class="dropdown-item" href="#" id="exportPDF">PDF</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="client-tasks-table">
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
                    <small class="text-muted">{{ Str::limit($task->descriptipon ?? '', 40) }}</small>
                  </td>
                  <td>
                    <span class="badge bg-success">{{ number_format($task->budget ?? 0) }} F</span>
                  </td>
                  <td>
                    @if(isset($task->startdate) && isset($task->enddate))
                    <div>{{ date('d/m/Y', strtotime($task->startdate)) }}</div>
                    <div>{{ date('d/m/Y', strtotime($task->enddate)) }}</div>
                    @else
                    <span class="text-muted">Non défini</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $assignmentCount = isset($viewData["assignmentCounts"][$task->id]) ? $viewData["assignmentCounts"][$task->id] : 0;
                    @endphp
                    <div class="d-flex align-items-center">
                      <span class="badge bg-info me-2">{{ $assignmentCount }}</span>
                      <div class="progress flex-grow-1" style="height: 5px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $assignmentCount * 5) }}%"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($task->status == 'PENDING')
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
                      <a href="{{ route('admin.task', ['id' => $task->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i>
                      </a>
                      @if($task->status == 'PENDING')
                      <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTaskModal" data-task-id="{{ $task->id }}">
                        <i class="fa fa-edit"></i>
                      </a>
                      @endif
                      <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#assignmentsModal" data-task-id="{{ $task->id }}">
                        <i class="fa fa-users"></i>
                      </a>
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

  <!-- Diffuseurs recommandés -->
  
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
                <label class="form-label">Nom de la campagne <span class="text-danger">*</span></label>
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
                  @foreach($viewData["categories"] ?? [] as $category)
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#client-tasks-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[0, 'desc']],
      pageLength: 10
    });
    
    // Budget Overview Chart
    const budgetData = {
      totalBudget: {{ $viewData['taskStats']['total_budget'] ?? 0 }},
      paidBudget: {{ $viewData['assignmentStats']['paid_budget'] ?? 0 }},
      pendingBudget: {{ $viewData['assignmentStats']['pending_budget'] ?? 0 }},
      remainingBudget: {{ $totalBudget - ($viewData['assignmentStats']['paid_budget'] ?? 0) - ($viewData['assignmentStats']['pending_budget'] ?? 0) }}
    };
    
    if (document.getElementById('budget-overview-chart')) {
      const budgetChart = new ApexCharts(document.getElementById('budget-overview-chart'), {
        series: [{
          name: 'Budget',
          data: [
            budgetData.paidBudget,
            budgetData.pendingBudget,
            budgetData.remainingBudget
          ]
        }],
        chart: {
          type: 'bar',
          height: 320,
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#28a745', '#ffc107', '#007bff'],
        xaxis: {
          categories: ['Dépensé', 'En cours', 'Restant'],
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val.toLocaleString() + " F";
            }
          }
        }
      });
      
      budgetChart.render();
    }
    
    // Campaign Status Chart
    const campaignData = {
      pending: {{ $viewData['taskStats']['pending'] ?? 0 }},
      accepted: {{ $viewData['taskStats']['accepted'] ?? 0 }},
      rejected: {{ $viewData['taskStats']['rejected'] ?? 0 }}
    };
    
    if (document.getElementById('campaign-status-chart')) {
      const campaignChart = new ApexCharts(document.getElementById('campaign-status-chart'), {
        series: [
          campaignData.pending,
          campaignData.accepted,
          campaignData.rejected
        ],
        chart: {
          type: 'donut',
          height: 320
        },
        labels: ['En attente', 'Acceptées', 'Rejetées'],
        colors: ['#007bff', '#28a745', '#dc3545'],
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
        }],
        tooltip: {
          y: {
            formatter: function(val) {
              return val + " campagnes";
            }
          }
        }
      });
      
      campaignChart.render();
    }
    
    // Load assignments for a task
    $('#assignmentsModal').on('show.bs.modal', function (e) {
      const button = $(e.relatedTarget);
      const taskId = button.data('task-id');
      const loadingDiv = $('#assignments-loading');
      const contentDiv = $('#assignments-content');
      
      loadingDiv.removeClass('d-none');
      contentDiv.addClass('d-none');
      
      // AJAX call to get assignments data
      $.ajax({
        url: '{{ url("admin/task") }}/' + taskId + '/assignments',
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
                  statusBadge = '<span class="badge bg-warning">En attente</span>';
                } else if (assignment.status === 'ACCEPTED') {
                  statusBadge = '<span class="badge bg-primary">Acceptée</span>';
                } else if (assignment.status === 'COMPLETED') {
                  statusBadge = '<span class="badge bg-success">Terminée</span>';
                } else if (assignment.status === 'REJECTED') {
                  statusBadge = '<span class="badge bg-danger">Rejetée</span>';
                }
                
                html += `
                  <tr>
                    <td>${assignment.agent_name}</td>
                    <td>${assignment.views ?? 'N/A'}</td>
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
            contentDiv.html('<div class="alert alert-danger">Impossible de charger les données</div>');
          }
        },
        error: function() {
          loadingDiv.addClass('d-none');
          contentDiv.removeClass('d-none');
          contentDiv.html('<div class="alert alert-danger">Une erreur est survenue lors du chargement des données</div>');
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