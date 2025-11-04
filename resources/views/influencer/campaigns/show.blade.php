<!-- File: resources/views/influencer/campaigns/show.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('influencer.campaigns.assigned') }}">Mes Missions</a></li>
                <li class="breadcrumb-item active">{{ $viewData["assignment"]->task_name ?? 'Détails' }}</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <div class="btn-group" role="group">
              @if(($viewData["assignment"]->status ?? '') == 'ACCEPTED')
              <a href="{{ route('influencer.campaigns.submit', ['id' => $viewData["assignment"]->id]) }}" class="btn btn-success">
                <i class="fa fa-check-circle me-1"></i>Soumettre les résultats
              </a>
              @endif
              <a href="{{ route('influencer.campaigns.assigned') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i>Retour
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Informations de la mission -->
    <div class="col-xl-8">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Informations de la campagne</h5>
            <span class="badge bg-primary fs-6">{{ number_format($viewData["assignment"]->gain ?? 0) }} F</span>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="mb-4">
                <h6 class="text-muted mb-1">Nom de la campagne</h6>
                <h5>{{ $viewData["assignment"]->task_name ?? 'N/A' }}</h5>
              </div>
              <div class="mb-4">
                <h6 class="text-muted mb-1">Statut</h6>
                @if(($viewData["assignment"]->status ?? '') == 'PENDING')
                  <span class="badge bg-warning">En attente</span>
                @elseif(($viewData["assignment"]->status ?? '') == 'ACCEPTED')
                  <span class="badge bg-primary">En cours</span>
                @elseif(($viewData["assignment"]->status ?? '') == 'COMPLETED')
                  <span class="badge bg-success">Terminée</span>
                @elseif(($viewData["assignment"]->status ?? '') == 'REJECTED')
                  <span class="badge bg-danger">Rejetée</span>
                @else
                  <span class="badge bg-secondary">{{ $viewData["assignment"]->status ?? 'N/A' }}</span>
                @endif
              </div>
              <div class="mb-4">
                <h6 class="text-muted mb-1">Période</h6>
                <p>
                  Du {{ isset($viewData["assignment"]->task_startdate) ? date('d/m/Y', strtotime($viewData["assignment"]->task_startdate)) : 'N/A' }} 
                  au {{ isset($viewData["assignment"]->task_enddate) ? date('d/m/Y', strtotime($viewData["assignment"]->task_enddate)) : 'N/A' }}
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-4">
                <h6 class="text-muted mb-1">Vues attendues</h6>
                <h5>{{ number_format($viewData["assignment"]->expected_views ?? 0) }}</h5>
              </div>
              <div class="mb-4">
                <h6 class="text-muted mb-1">Date d'assignation</h6>
                <p>{{ isset($viewData["assignment"]->created_at) ? date('d/m/Y H:i', strtotime($viewData["assignment"]->created_at)) : 'N/A' }}</p>
              </div>
              <div class="mb-4">
                <h6 class="text-muted mb-1">Annonceur</h6>
                <h6>{{ $viewData["assignment"]->client_name ?? 'N/A' }}</h6>
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <h6 class="text-muted mb-1">Description</h6>
            <p>{{ $viewData["assignment"]->task_description ?? 'Aucune description disponible' }}</p>
          </div>
          
          <div class="mb-4">
            <h6 class="text-muted mb-1">Instructions</h6>
            <div class="alert alert-info">
              <p class="mb-0">{{ $viewData["assignment"]->instructions ?? 'Aucune instruction spécifique pour cette campagne. Veuillez diffuser le contenu sur vos plateformes habituelles.' }}</p>
            </div>
          </div>
          
          @if(!empty($viewData["assignment"]->task_files))
          <div class="mb-4">
            <h6 class="text-muted mb-2">Fichiers de la campagne</h6>
            <div class="row g-3">
              @foreach(explode(',', $viewData["assignment"]->task_files) as $file)
              <div class="col-md-4">
                <div class="card">
                  <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                      <i class="fa fa-file-image fa-2x text-primary me-3"></i>
                      <div>
                        <p class="mb-1 text-truncate">{{ basename($file) }}</p>
                        <a href="{{ asset('storage/'.$file) }}" target="_blank" class="btn btn-sm btn-light">
                          <i class="fa fa-download me-1"></i>Télécharger
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
    
    <!-- Progression et résumé -->
    <div class="col-xl-4">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Progression</h5>
        </div>
        <div class="card-body">
          @php
            $progress = 0;
            if ($viewData["assignment"]->status == 'PENDING') {
              $progress = 25;
            } elseif ($viewData["assignment"]->status == 'ACCEPTED') {
              $progress = 50;
            } elseif ($viewData["assignment"]->status == 'COMPLETED') {
              $progress = 100;
            }
          @endphp
          
          <div class="progress-wizard">
            <div class="progress-wizard-bar">
              <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <ul class="progress-wizard-steps d-flex justify-content-between ps-0">
              <li class="{{ $progress >= 25 ? 'active' : '' }} text-center">
                <div class="step">
                  <div class="step-icon">
                    <i class="fa fa-check-circle"></i>
                  </div>
                </div>
                <p class="mt-2">Assignation</p>
              </li>
              <li class="{{ $progress >= 50 ? 'active' : '' }} text-center">
                <div class="step">
                  <div class="step-icon">
                    <i class="fa fa-play-circle"></i>
                  </div>
                </div>
                <p class="mt-2">En cours</p>
              </li>
              <li class="{{ $progress >= 100 ? 'active' : '' }} text-center">
                <div class="step">
                  <div class="step-icon">
                    <i class="fa fa-check-double"></i>
                  </div>
                </div>
                <p class="mt-2">Terminée</p>
              </li>
            </ul>
          </div>
          
          @if($viewData["assignment"]->status == 'ACCEPTED')
          <div class="alert alert-warning mt-4">
            <p class="mb-0">
              <i class="fa fa-clock me-2"></i>
              Il vous reste {{ now()->diffInDays(strtotime($viewData["assignment"]->task_enddate)) }} jours pour terminer cette mission.
            </p>
          </div>
          @endif
        </div>
      </div>
      
      @if($viewData["assignment"]->status == 'COMPLETED')
      <div class="card">
        <div class="card-header bg-success bg-soft">
          <h5 class="card-title text-success mb-0">Résultats</h5>
        </div>
        <div class="card-body">
          <div class="text-center mb-4">
            <div class="avatar-lg mx-auto mb-3 bg-success bg-opacity-10 rounded-circle">
              <span class="avatar-title text-success font-size-24">
                <i class="fa fa-chart-line"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ number_format($viewData["assignment"]->vues ?? 0) }}</h4>
            <p class="text-muted mb-0">Vues totales</p>
          </div>
          
          <div class="mb-4">
            <h6 class="text-muted mb-1">Date de soumission</h6>
            <p>{{ isset($viewData["assignment"]->completion_date) ? date('d/m/Y H:i', strtotime($viewData["assignment"]->completion_date)) : 'N/A' }}</p>
          </div>
          
          @if(isset($viewData["assignment"]->rating) && $viewData["assignment"]->rating > 0)
          <div class="mb-4">
            <h6 class="text-muted mb-2">Évaluation</h6>
            <div class="text-center">
              @for($i = 1; $i <= 5; $i++)
                @if($i <= $viewData["assignment"]->rating)
                  <i class="fa fa-star fa-2x text-warning"></i>
                @else
                  <i class="fa fa-star fa-2x text-muted"></i>
                @endif
              @endfor
            </div>
          </div>
          @endif
          
          @if(!empty($viewData["assignment"]->feedback))
          <div class="mb-0">
            <h6 class="text-muted mb-2">Commentaires de l'annonceur</h6>
            <div class="border rounded p-3 bg-light">
              <p class="mb-0 fst-italic">{{ $viewData["assignment"]->feedback }}</p>
            </div>
          </div>
          @endif
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@push('styles')
<style>
  .progress-wizard-steps {
    list-style: none;
    margin-top: 30px;
  }
  
  .progress-wizard-steps li {
    position: relative;
  }
  
  .progress-wizard-steps li .step {
    width: 50px;
    height: 50px;
    margin: 0 auto;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 10;
  }
  
  .progress-wizard-steps li .step .step-icon {
    font-size: 24px;
    color: #adb5bd;
  }
  
  .progress-wizard-steps li.active .step {
    background-color: #d4edda;
  }
  
  .progress-wizard-steps li.active .step .step-icon {
    color: #28a745;
  }
  
  .progress-wizard-bar {
    position: relative;
    margin: 30px 0;
    height: 10px;
  }
  
  .progress-wizard-bar .progress {
    height: 8px;
    border-radius: 4px;
  }
</style>
@endpush
@endsection