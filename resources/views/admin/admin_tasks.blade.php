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
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Campagnes</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary">
              <i class="fa fa-plus me-2"><a href="{{ route('admin.campaigns.create') }}">Dashboard</a></i>Nouvelle Campagne
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row">
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Total</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['total'] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-primary">
                <i class="fa fa-tasks text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">En attente</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['pending'] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-warning">
                <i class="fa fa-clock text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Acceptées</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['accepted'] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-info">
                <i class="fa fa-thumbs-up text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Payées</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['paid'] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-success">
                <i class="fa fa-money-bill-wave text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Rejetées</h6>
              <h4 class="mb-0">{{ $viewData['taskStats']['rejected'] ?? 0 }}</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-danger align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-danger">
                <i class="fa fa-times text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4">
      <div class="card mini-stats">
        <div class="card-body">
          <div class="d-flex">
            <div class="flex-grow-1">
              <h6 class="mb-2 text-uppercase text-muted">Budget total</h6>
              <h4 class="mb-0">{{ number_format($viewData['taskStats']['total_budget'] ?? 0) }} F</h4>
            </div>
            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
              <span class="avatar-title rounded-circle bg-primary">
                <i class="fa fa-money-bill-alt text-white"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header pb-0">
          <h4>Filtres</h4>
          <span>Options de filtre</span>
        </div>
        <div class="card-body">
          <form class="form theme-form" method="post" action="{{ route('admin.tasks') }}">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label class="col-form-label">Statut</label>
                  <select class="form-select" id="filtre_status" name="filtre_status">
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
                <div class="form-group">
                  <label class="col-form-label">Client</label>
                  <select class="form-select" id="filtre_client" name="filtre_client">
                    <option value="">Tous les clients</option>
                    @foreach($viewData["clients"] ?? [] as $client)
                    <option value="{{ $client->id }}" {{ isset($viewData["filtre_client"]) && $viewData["filtre_client"] == $client->id ? 'selected' : '' }}>
                      {{ $client->firstname }} {{ $client->lastname }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-group">
                  <label class="col-form-label">Catégorie</label>
                  <select class="form-select" id="filtre_category" name="filtre_category">
                    <option value="">Toutes les catégories</option>
                    @foreach($viewData["categories"] ?? [] as $category)
                    <option value="{{ $category->id }}" {{ isset($viewData["filtre_category"]) && $viewData["filtre_category"] == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-group">
                  <label class="col-form-label">Période</label>
                  <div class="input-group">
                    <input type="date" class="form-control" id="filtre_start_date" name="filtre_start_date" value="{{ $viewData['filtre_start_date'] ?? '' }}" placeholder="Date début">
                    <input type="date" class="form-control" id="filtre_end_date" name="filtre_end_date" value="{{ $viewData['filtre_end_date'] ?? '' }}" placeholder="Date fin">
                  </div>
                </div>
                <div class="form-group mt-4">
                  <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
              </div>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Tasks List -->
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header pb-0">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4>Liste des Campagnes</h4>
              <span>{{ count($viewData["tasks"] ?? []) }} Campagnes trouvées</span>
            </div>
            <div>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-download me-1"></i> Exporter
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
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tasks-datatable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Campagne</th>
                  <th>Client</th>
                  <th>Budget</th>
                  <th>Période</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($viewData["tasks"] ?? [] as $task)
                <tr>
                  <td>{{ $task->id }}</td>
                  <td>
                    <h6 class="mb-0">{{ $task->name }}</h6>
                    <small class="text-muted">{{ Str::limit($task->descriptipon ?? '', 50) }}</small>
                  </td>
                  <td>
                    {{ $task->client_name ?? 'N/A' }}
                  </td>
                  <td>
                    <span class="badge bg-success">{{ number_format($task->budget ?? 0) }} F</span>
                  </td>
                  <td>
                    @if(isset($task->startdate) && isset($task->enddate))
                    <div>Début: {{ date('d/m/Y', strtotime($task->startdate)) }}</div>
                    <div>Fin: {{ date('d/m/Y', strtotime($task->enddate)) }}</div>
                    @else
                    <span class="text-muted">Non défini</span>
                    @endif
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
                      <button class="btn btn-warning btn-sm edit-task" data-task-id="{{ $task->id }}">
                        <i class="fa fa-edit"></i>
                      </button>
                      @if($task->status == 'PENDING')
                      <button class="btn btn-success btn-sm approve-task" data-task-id="{{ $task->id }}">
                        <i class="fa fa-check"></i>
                      </button>
                      <button class="btn btn-danger btn-sm reject-task" data-task-id="{{ $task->id }}">
                        <i class="fa fa-times"></i>
                      </button>
                      @endif
                      <button class="btn btn-danger btn-sm delete-task" data-task-id="{{ $task->id }}">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center">Aucune Campagne trouvée</td>
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

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Créer une nouvelle Campagne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="createTaskForm" method="post" action="" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Nom de la Campagne <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select class="form-select" name="client_id" required>
                  <option value="">Sélectionner un client</option>
                  @foreach($viewData["clients"] ?? [] as $client)
                  <option value="{{ $client->id }}">{{ $client->firstname }} {{ $client->lastname }}</option>
                  @endforeach
                </select>
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

<!-- Approve Task Modal -->
<div class="modal fade" id="approveTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approuver la Campagne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approveTaskForm" method="post" action="">
        <div class="modal-body">
          <p>Êtes-vous sûr de vouloir approuver cette Campagne ?</p>
          <p>L'approbation permettra de passer cette Campagne au statut "Acceptée".</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Approuver</button>
        </div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      </form>
    </div>
  </div>
</div>

<!-- Reject Task Modal -->
<div class="modal fade" id="rejectTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rejeter la Campagne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectTaskForm" method="post" action="">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="form-label">Motif de rejet <span class="text-danger">*</span></label>
            <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Rejeter</button>
        </div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      </form>
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
      <form id="deleteTaskForm" method="post" action="">
        <div class="modal-body">
          <p>Êtes-vous sûr de vouloir supprimer cette Campagne ?</p>
          <p class="text-danger">Cette action est irréversible.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#tasks-datatable').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[0, 'desc']]
    });
    
    // Edit task
    $('.edit-task').on('click', function() {
      const taskId = $(this).data('task-id');
      window.location.href = "{{ url('admin/task') }}/" + taskId;
    });
    
    // Approve task
    $('.approve-task').on('click', function() {
      const taskId = $(this).data('task-id');
      $('#approveTaskForm').attr('action', "{{ url('admin/task') }}/" + taskId + "/approve");
      $('#approveTaskModal').modal('show');
    });
    
    // Reject task
    $('.reject-task').on('click', function() {
      const taskId = $(this).data('task-id');
      $('#rejectTaskForm').attr('action', "{{ url('admin/task') }}/" + taskId + "/reject");
      $('#rejectTaskModal').modal('show');
    });
    
    // Delete task
    $('.delete-task').on('click', function() {
      const taskId = $(this).data('task-id');
      $('#deleteTaskForm').attr('action', "{{ url('admin/task') }}/" + taskId + "/delete");
      $('#deleteTaskModal').modal('show');
    });
    
    // Validate date range in filters
    $('#filtre_start_date, #filtre_end_date').on('change', function() {
      const startDate = $('#filtre_start_date').val();
      const endDate = $('#filtre_end_date').val();
      
      if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('La date de début doit être antérieure à la date de fin.');
        $(this).val('');
      }
    });
    
    // Export functions - disabled for now as routes may not exist
    $('#exportCSV, #exportExcel, #exportPDF').on('click', function(e) {
      e.preventDefault();
      alert('Fonctionnalité d\'export à implémenter');
      
      // Once implemented, uncomment this:
      /*
      const format = $(this).attr('id').replace('export', '').toLowerCase();
      window.location.href = "{{ url('admin/tasks/export') }}/" + format + "?" + $('form').serialize();
      */
    });
  });
</script>
@endpush

@endsection