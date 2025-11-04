<!-- File: resources/views/influencer/campaigns/assigned.blade.php -->
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
                <li class="breadcrumb-item active">Mes Missions</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <a href="{{ route('influencer.campaigns.available') }}" class="btn btn-primary">
              <i class="fa fa-search me-1"></i>Trouver des campagnes
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs de navigation -->
  <div class="row mb-4">
    <div class="col-12">
      <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#active-missions" role="tab">
            <span class="d-block d-sm-none"><i class="fa fa-clock"></i></span>
            <span class="d-none d-sm-block">En cours</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#completed-missions" role="tab">
            <span class="d-block d-sm-none"><i class="fa fa-check-circle"></i></span>
            <span class="d-none d-sm-block">Terminées</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#all-missions" role="tab">
            <span class="d-block d-sm-none"><i class="fa fa-list"></i></span>
            <span class="d-none d-sm-block">Toutes</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Contenu des tabs -->
  <div class="tab-content">
    <!-- Missions actives -->
    <div class="tab-pane active" id="active-missions" role="tabpanel">
      <div class="row">
        @php
          $activeMissions = collect($viewData["assignments"] ?? [])->filter(function($assignment) {
            return in_array($assignment->status, ['PENDING', 'ACCEPTED']);
          });
        @endphp
        
        @forelse($activeMissions as $assignment)
        <div class="col-xl-4 col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-header bg-primary bg-soft">
              <h5 class="card-title text-primary mb-0">{{ $assignment->task_name ?? 'Sans titre' }}</h5>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="avatar-md bg-light rounded-circle text-center me-3">
                  <span class="avatar-title text-primary font-size-24">
                    <i class="fa fa-bullhorn"></i>
                  </span>
                </div>
                <div>
                  <span class="badge bg-primary">{{ number_format($assignment->task_budget ?? 0) }} F</span>
                  <p class="text-muted mb-0 mt-1">
                    Date limite: {{ isset($assignment->task_enddate) ? date('d/m/Y', strtotime($assignment->task_enddate)) : 'N/A' }}
                  </p>
                </div>
              </div>
              
              <div class="mb-3">
                <h6 class="text-muted mb-1">Statut</h6>
                <div>
                  @if($assignment->status == 'PENDING')
                    <span class="badge bg-warning">En attente</span>
                  @elseif($assignment->status == 'ACCEPTED')
                    <span class="badge bg-primary">En cours</span>
                  @endif
                </div>
              </div>
              
              <div class="mb-3">
                <h6 class="text-muted mb-1">Progression</h6>
                <div class="progress" style="height: 8px;">
                  @php
                    $progress = 0;
                    if ($assignment->status == 'ACCEPTED') {
                      $progress = 50;
                    } elseif ($assignment->status == 'COMPLETED') {
                      $progress = 100;
                    }
                  @endphp
                  <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
              
              <div class="mt-3 text-center">
                <a href="{{ route('influencer.campaigns.show', ['id' => $assignment->id]) }}" class="btn btn-primary btn-sm me-2">
                  <i class="fa fa-eye me-1"></i>Voir détails
                </a>
                @if($assignment->status == 'ACCEPTED')
                <a href="{{ route('influencer.campaigns.submit', ['id' => $assignment->id]) }}" class="btn btn-success btn-sm">
                  <i class="fa fa-check-circle me-1"></i>Soumettre
                </a>
                @endif
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="card">
            <div class="card-body text-center py-5">
              <img src="{{ asset('images/empty-state.svg') }}" alt="Aucune mission" height="120" class="mb-4">
              <h5>Aucune mission en cours</h5>
              <p class="text-muted">Vous n'avez pas de missions actives pour le moment.</p>
              <a href="{{ route('influencer.campaigns.available') }}" class="btn btn-primary mt-2">Trouver des campagnes</a>
            </div>
          </div>
        </div>
        @endforelse
      </div>
    </div>
    
    <!-- Missions terminées -->
    <div class="tab-pane" id="completed-missions" role="tabpanel">
      <div class="row">
        @php
          $completedMissions = collect($viewData["assignments"] ?? [])->filter(function($assignment) {
            return in_array($assignment->status, ['COMPLETED']);
          });
        @endphp
        
        @forelse($completedMissions as $assignment)
        <div class="col-xl-4 col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-header bg-success bg-soft">
              <h5 class="card-title text-success mb-0">{{ $assignment->task_name ?? 'Sans titre' }}</h5>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="avatar-md bg-light rounded-circle text-center me-3">
                  <span class="avatar-title text-success font-size-24">
                    <i class="fa fa-check-circle"></i>
                  </span>
                </div>
                <div>
                  <span class="badge bg-success">{{ number_format($assignment->gain ?? 0) }} F</span>
                  <p class="text-muted mb-0 mt-1">
                    Terminée le: {{ isset($assignment->completion_date) ? date('d/m/Y', strtotime($assignment->completion_date)) : 'N/A' }}
                  </p>
                </div>
              </div>
              
              <div class="mb-3">
                <h6 class="text-muted mb-1">Vues</h6>
                <h5 class="mb-0">{{ number_format($assignment->vues ?? 0) }}</h5>
              </div>
              
              <div class="mb-3">
                <h6 class="text-muted mb-1">Évaluation</h6>
                <div>
                  @if(isset($assignment->rating) && $assignment->rating > 0)
                    @for($i = 1; $i <= 5; $i++)
                      @if($i <= $assignment->rating)
                        <i class="fa fa-star text-warning"></i>
                      @else
                        <i class="fa fa-star text-muted"></i>
                      @endif
                    @endfor
                  @else
                    <span class="text-muted">Non évalué</span>
                  @endif
                </div>
              </div>
              
              <div class="mt-3 text-center">
                <a href="{{ route('influencer.campaigns.show', ['id' => $assignment->id]) }}" class="btn btn-primary btn-sm">
                  <i class="fa fa-eye me-1"></i>Voir détails
                </a>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="card">
            <div class="card-body text-center py-5">
              <img src="{{ asset('images/empty-state.svg') }}" alt="Aucune mission" height="120" class="mb-4">
              <h5>Aucune mission terminée</h5>
              <p class="text-muted">Vous n'avez pas encore terminé de missions.</p>
            </div>
          </div>
        </div>
        @endforelse
      </div>
    </div>
    
    <!-- Toutes les missions -->
    <div class="tab-pane" id="all-missions" role="tabpanel">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="all-missions-table">
              <thead>
                <tr>
                  <th>Campagne</th>
                  <th>Dates</th>
                  <th>Gain</th>
                  <th>Vues</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($viewData["assignments"] ?? [] as $assignment)
                <tr>
                  <td>
                    <h6 class="mb-0">{{ $assignment->task_name ?? 'N/A' }}</h6>
                    <small class="text-muted">{{ $assignment->task_type ?? 'N/A' }}</small>
                  </td>
                  <td>
                    <div>Début: {{ isset($assignment->task_startdate) ? date('d/m/Y', strtotime($assignment->task_startdate)) : 'N/A' }}</div>
                    <div>Fin: {{ isset($assignment->task_enddate) ? date('d/m/Y', strtotime($assignment->task_enddate)) : 'N/A' }}</div>
                  </td>
                  <td>{{ number_format($assignment->gain ?? 0) }} F</td>
                  <td>{{ number_format($assignment->vues ?? 0) }}</td>
                  <td>
                    @if($assignment->status == 'PENDING')
                      <span class="badge bg-warning">En attente</span>
                    @elseif($assignment->status == 'ACCEPTED')
                      <span class="badge bg-primary">En cours</span>
                    @elseif($assignment->status == 'COMPLETED')
                      <span class="badge bg-success">Terminée</span>
                    @elseif($assignment->status == 'REJECTED')
                      <span class="badge bg-danger">Rejetée</span>
                    @else
                      <span class="badge bg-secondary">{{ $assignment->status }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="{{ route('influencer.campaigns.show', ['id' => $assignment->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i>
                      </a>
                      @if($assignment->status == 'ACCEPTED')
                      <a href="{{ route('influencer.campaigns.submit', ['id' => $assignment->id]) }}" class="btn btn-success btn-sm">
                        <i class="fa fa-check-circle"></i>
                      </a>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center">Aucune mission trouvée</td>
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

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#all-missions-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[1, 'desc']],
      pageLength: 10
    });
  });
</script>
@endpush
@endsection