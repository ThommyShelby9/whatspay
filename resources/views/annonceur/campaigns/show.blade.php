<!-- File: resources/views/announcer/campaigns/show.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('admin.client.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('announcer.campaigns.index') }}">Campagnes</a></li>
                <li class="breadcrumb-item active">{{ $viewData["campaign"]->name ?? 'Détails' }}</li>
              </ol>
            </nav>
            <h4 class="mt-2">{{ $viewData["campaign"]->name ?? 'Détails de la campagne' }}</h4>
          </div>
          <div class="col-auto">
            <div class="d-flex">
              <div class="status-badge me-3">
                @if(($viewData["campaign"]->status ?? '') == 'PENDING')
                  <span class="badge rounded-pill bg-warning px-3 py-2 fs-6"><i class="fa fa-clock me-1"></i>En attente</span>
                @elseif(($viewData["campaign"]->status ?? '') == 'ACCEPTED')
                  <span class="badge rounded-pill bg-primary px-3 py-2 fs-6"><i class="fa fa-check-circle me-1"></i>Acceptée</span>
                @elseif(($viewData["campaign"]->status ?? '') == 'PAID')
                  <span class="badge rounded-pill bg-success px-3 py-2 fs-6"><i class="fa fa-check-double me-1"></i>Payée</span>
                @elseif(($viewData["campaign"]->status ?? '') == 'REJECTED')
                  <span class="badge rounded-pill bg-danger px-3 py-2 fs-6"><i class="fa fa-times-circle me-1"></i>Rejetée</span>
                @elseif(($viewData["campaign"]->status ?? '') == 'CLOSED')
                  <span class="badge rounded-pill bg-secondary px-3 py-2 fs-6"><i class="fa fa-lock me-1"></i>Fermée</span>
                @else
                  <span class="badge rounded-pill bg-secondary px-3 py-2 fs-6">{{ $viewData["campaign"]->status ?? 'N/A' }}</span>
                @endif
              </div>
              <div class="btn-group" role="group">
                @if(($viewData["campaign"]->status ?? '') == 'PENDING')
                <a href="{{ route('announcer.campaigns.edit', ['id' => $viewData["campaign"]->id]) }}" class="btn btn-warning">
                  <i class="fa fa-edit me-1"></i>Modifier
                </a>
                @endif
                <a href="{{ route('announcer.campaigns.index') }}" class="btn btn-secondary">
                  <i class="fa fa-arrow-left me-1"></i>Retour
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Cartes de résumé -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="card bg-pattern">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-primary-subtle rounded">
                <i class="fa fa-eye text-primary font-size-24"></i>
              </span>
            </div>
            <div class="flex-grow-1 ms-3">
              <h5 class="font-size-16 mt-0 mb-1">Vues Totales</h5>
              <h3 class="text-primary mb-0">{{ number_format($viewData["stats"]["total_views"] ?? 0) }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card bg-pattern">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-success-subtle rounded">
                <i class="fa fa-mouse-pointer text-success font-size-24"></i>
              </span>
            </div>
            <div class="flex-grow-1 ms-3">
              <h5 class="font-size-16 mt-0 mb-1">Clics Uniques</h5>
              <h3 class="text-success mb-0">{{ number_format($viewData["stats"]["unique_clicks"] ?? 0) }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card bg-pattern">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-info-subtle rounded">
                <i class="fa fa-percentage text-info font-size-24"></i>
              </span>
            </div>
            <div class="flex-grow-1 ms-3">
              <h5 class="font-size-16 mt-0 mb-1">Taux de clic</h5>
              <h3 class="text-info mb-0">{{ number_format($viewData["stats"]["click_rate"] ?? 0, 2) }}%</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
      <div class="card bg-pattern">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
              <span class="avatar-title bg-warning-subtle rounded">
                <i class="fa fa-wallet text-warning font-size-24"></i>
              </span>
            </div>
            <div class="flex-grow-1 ms-3">
              <h5 class="font-size-16 mt-0 mb-1">Budget</h5>
              <h3 class="text-warning mb-0">{{ number_format($viewData["campaign"]->budget ?? 0) }} F</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenu de la campagne et performances -->
  <div class="row mb-4">
    <!-- Aperçu du contenu -->
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Contenu de la campagne</h5>
          <span class="badge bg-primary text-black">{{ $viewData["campaign"]->media_type ?? 'N/A' }}</span>
        </div>
        <div class="card-body">
          <!-- Aperçu du média -->
          <div class="media-preview mb-4 text-center">
            @php
              $mediaFiles = json_decode($viewData["campaign"]->files ?? '[]', true);
              $mediaType = $viewData["campaign"]->media_type ?? '';
            @endphp

            @if(is_array($mediaFiles) && count($mediaFiles) > 0)
              @if(in_array($mediaType, ['image', 'image_link']))
                <!-- Affichage d'image avec carrousel si multiple -->
                <div id="campaign-carousel" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    @foreach($mediaFiles as $index => $file)
                      <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ isset($file['url']) ? $file['url'] : asset('storage/uploads/' . ($file['name'] ?? $file['file_name'] ?? '')) }}" 
                             alt="Media {{ $index + 1 }}" class="img-fluid rounded" style="max-height: 300px; width: auto;">
                      </div>
                    @endforeach
                  </div>
                  @if(count($mediaFiles) > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#campaign-carousel" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                      <span class="visually-hidden">Précédent</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#campaign-carousel" data-bs-slide="next">
                      <span class="carousel-control-next-icon" aria-hidden="true"></span>
                      <span class="visually-hidden">Suivant</span>
                    </button>
                  @endif
                </div>
              @elseif($mediaType === 'video')
                <!-- Affichage de vidéo -->
                <video controls class="img-fluid rounded" style="max-height: 300px; width: auto;">
                  <source src="{{ isset($mediaFiles[0]['url']) ? $mediaFiles[0]['url'] : asset('storage/uploads/' . ($mediaFiles[0]['name'] ?? $mediaFiles[0]['file_name'] ?? '')) }}" type="video/mp4">
                  Votre navigateur ne prend pas en charge la lecture de vidéos.
                </video>
              @elseif($mediaType === 'text')
                <!-- Contenu texte -->
                <div class="text-content p-4 bg-light rounded">
                  <p class="mb-0">{{ $viewData["campaign"]->text ?? 'Contenu texte non disponible' }}</p>
                </div>
              @endif
            @else
              <div class="no-media alert alert-light text-center">
                <i class="fa fa-image fa-3x text-muted mb-2"></i>
                <p class="mb-0">Aucun média disponible</p>
              </div>
            @endif
          </div>

          <!-- Légende et détails du média -->
          <div class="media-details">
            <h6 class="text-muted mb-2">Légende</h6>
            <p class="p-3 bg-light rounded text-black">{{ $viewData["campaign"]->legend ?? 'Aucune légende disponible' }}</p>
            
            @if(($viewData["campaign"]->media_type ?? '') == 'image_link')
            <div class="mb-3">
              <h6 class="text-muted mb-2">Lien</h6>
              <div class="input-group">
                <input type="text" class="form-control" value="{{ $viewData["campaign"]->url ?? '' }}" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $viewData["campaign"]->url ?? '' }}')">
                  <i class="fa fa-copy"></i>
                </button>
                <a href="{{ $viewData["campaign"]->url ?? '#' }}" target="_blank" class="btn btn-primary">
                  <i class="fa fa-external-link-alt"></i> Ouvrir
                </a>
              </div>
            </div>
            @endif

            @if($viewData["campaign"]->media_type === null || empty($viewData["mediaFiles"]))
  <div class="card mb-4">
    <div class="card-header bg-warning-subtle text-warning">
      <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Information manquante</h5>
    </div>
    <div class="card-body">
      <p>Cette campagne a été créée sans média associé. Pour une expérience optimale, veuillez mettre à jour la campagne avec les informations nécessaires:</p>
      <ul class="mb-3">
        <li>Type de média (image, vidéo, texte)</li>
        <li>Contenu média</li>
        <li>Légende</li>
        <li>Localité et profession ciblées</li>
      </ul>
      <a href="{{ route('announcer.campaigns.edit', ['id' => $viewData["campaign"]->id]) }}" class="btn btn-warning">
        <i class="fa fa-edit me-1"></i>Compléter la campagne
      </a>
    </div>
  </div>
@endif
          </div>
        </div>
      </div>
    </div>

    <!-- Détails et Performance -->
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#details-tab" role="tab">
                <i class="fa fa-info-circle me-1"></i>Détails
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#performance-tab" role="tab">
                <i class="fa fa-chart-line me-1"></i>Performance
              </a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <!-- Onglet Détails -->
            <div class="tab-pane fade show active" id="details-tab" role="tabpanel">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-4">
                    <h6 class="text-muted fw-normal mb-1">Période de diffusion</h6>
                    <div class="d-flex align-items-center">
                      <i class="fa fa-calendar-alt text-primary me-2"></i>
                      <p class="mb-0 fw-medium">
                        {{ isset($viewData["campaign"]->startdate) ? date('d/m/Y', strtotime($viewData["campaign"]->startdate)) : 'N/A' }}
                        <i class="fa fa-arrow-right mx-2 text-muted"></i>
                        {{ isset($viewData["campaign"]->enddate) ? date('d/m/Y', strtotime($viewData["campaign"]->enddate)) : 'N/A' }}
                      </p>
                    </div>
                  </div>
                  
<div class="mb-4">
  <h6 class="text-muted fw-normal mb-1">Catégories</h6>
  <div class="category-tags">
    @forelse($viewData["categories"] ?? [] as $category)
      <span class="category-tag" data-bs-toggle="tooltip" title="{{ $category->name }}">
        {{ Str::limit($category->name, 15) }}
      </span>
    @empty
      <span class="text-muted">Aucune catégorie</span>
    @endforelse
  </div>
</div>
                  
                  <div class="mb-4">
                    <h6 class="text-muted fw-normal mb-1">Localité cible</h6>
                    <div class="d-flex align-items-center">
                      <i class="fa fa-map-marker-alt text-danger me-2"></i>
                      <p class="mb-0 fw-medium">{{ $viewData["locality"]->name ?? 'Toutes les localités' }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="mb-4">
                    <h6 class="text-muted fw-normal mb-1">Date de création</h6>
                    <div class="d-flex align-items-center">
                      <i class="fa fa-calendar-plus text-success me-2"></i>
                      <p class="mb-0 fw-medium">{{ isset($viewData["campaign"]->created_at) ? date('d/m/Y H:i', strtotime($viewData["campaign"]->created_at)) : 'N/A' }}</p>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <h6 class="text-muted fw-normal mb-1">Profession cible</h6>
                    <div class="d-flex align-items-center">
                      <i class="fa fa-briefcase text-primary me-2"></i>
                      <p class="mb-0 fw-medium">{{ $viewData["occupation"]->name ?? 'Toutes les professions' }}</p>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <h6 class="text-muted fw-normal mb-1">Diffuseurs actifs</h6>
                    <div class="d-flex align-items-center">
                      <i class="fa fa-users text-info me-2"></i>
                      <p class="mb-0 fw-medium">{{ count($viewData["assignments"] ?? []) }} diffuseur(s)</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-12">
                  <h6 class="text-muted fw-normal mb-2">Description</h6>
                  <div class="p-3 bg-light rounded">
                    <p class="mb-0 text-black">{{ $viewData["campaign"]->descriptipon ?? 'Aucune description disponible' }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Onglet Performance -->
            <div class="tab-pane fade" id="performance-tab" role="tabpanel">
              <!-- Graphique d'évolution des vues et clics -->
              <div class="mb-4">
                <h6 class="text-muted mb-3">Évolution des vues et clics</h6>
                <div id="views-clicks-chart" style="height: 250px;"></div>
              </div>
              
              <!-- Répartition par appareil -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <h6 class="text-muted mb-2">Répartition par appareil</h6>
                  <div id="device-chart" style="height: 200px;"></div>
                </div>
                
                <div class="col-md-6">
                  <h6 class="text-muted mb-2">Trafic par jour de la semaine</h6>
                  <div id="weekday-chart" style="height: 200px;"></div>
                </div>
              </div>

              <!-- Informations supplémentaires -->
              <div class="bg-light p-3 rounded">
                <div class="row text-center">
                  <div class="col-4">
                    <h4 class="mb-1">{{ number_format($viewData["stats"]["conversion_rate"] ?? 0, 2) }}%</h4>
                    <p class="text-muted small mb-0">Taux de conversion</p>
                  </div>
                  <div class="col-4">
                    <h4 class="mb-1">{{ gmdate('H:i:s', $viewData["stats"]["avg_time"] ?? 0) }}</h4>
                    <p class="text-muted small mb-0">Temps moyen</p>
                  </div>
                  <div class="col-4">
                    <h4 class="mb-1">{{ number_format($viewData["stats"]["engagement_rate"] ?? 0, 2) }}%</h4>
                    <p class="text-muted small mb-0">Taux d'engagement</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Liste des diffuseurs -->
<!--   <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Diffuseurs de la campagne</h5>
          <div class="actions">
            <button type="button" class="btn btn-sm btn-light" id="refresh-table">
              <i class="fa fa-sync-alt"></i> Actualiser
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-striped" id="assignments-table">
              <thead>
                <tr>
                  <th style="width: 25%;">Diffuseur</th>
                  <th style="width: 15%;">Vues</th>
                  <th style="width: 15%;">Gain</th>
                  <th style="width: 15%;">Statut</th>
                  <th style="width: 15%;">Date de soumission</th>
                  <th style="width: 15%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($viewData["assignments"] ?? [] as $assignment)
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-2 bg-info-subtle rounded-circle">
                        <span class="avatar-text text-info">{{ substr($assignment->agent_name ?? 'N/A', 0, 1) }}</span>
                      </div>
                      <div>
                        {{ $assignment->agent_name ?? 'N/A' }}
                        @if($assignment->agent_email)
                          <div class="small text-muted">{{ $assignment->agent_email }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>{{ number_format($assignment->vues ?? 0) }}</td>
                  <td>{{ number_format($assignment->gain ?? 0) }} F</td>
                  <td>
                    @if($assignment->status == 'PENDING')
                      <span class="badge bg-warning text-dark">En attente</span>
                    @elseif($assignment->status == 'ACCEPTED')
                      <span class="badge bg-primary">Acceptée</span>
                    @elseif($assignment->status == 'COMPLETED')
                      <span class="badge bg-success">Terminée</span>
                    @elseif($assignment->status == 'REJECTED')
                      <span class="badge bg-danger">Rejetée</span>
                    @else
                      <span class="badge bg-secondary">{{ $assignment->status }}</span>
                    @endif
                  </td>
                  <td>
                    {{ isset($assignment->submission_date) ? date('d/m/Y H:i', strtotime($assignment->submission_date)) : '-' }}
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-info view-details" data-id="{{ $assignment->id }}">
                        <i class="fa fa-eye"></i>
                      </button>
                      @if($assignment->status == 'COMPLETED')
                      <button type="button" class="btn btn-sm btn-success approve-submission" data-id="{{ $assignment->id }}">
                        <i class="fa fa-check"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger reject-submission" data-id="{{ $assignment->id }}">
                        <i class="fa fa-times"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center">Aucun diffuseur assigné à cette campagne</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</div>

<!-- Modal pour afficher les détails d'une soumission -->
<div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Détails de la soumission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="submission-details-content">
          <!-- Le contenu sera chargé dynamiquement -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.css" rel="stylesheet">
<style>
  .avatar {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .avatar-text {
    font-weight: bold;
  }
  
  .media-preview {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .bg-pattern {
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f8f9fa' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }
  
  .font-size-24 {
    font-size: 24px;
  }
  
  .font-size-16 {
    font-size: 16px;
  }
  
  .fw-medium {
    font-weight: 500;
  }

  .category-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 5px;
}

.category-tag {
  background-color: #f0f9ff;
  color: #0369a1;
  border: 1px solid #0ea5e9;
  border-radius: 50px;
  padding: 3px 12px;
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
  transition: all 0.2s;
}

.category-tag:hover {
  background-color: #e0f2fe;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#assignments-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      pageLength: 10,
      responsive: true,
      order: [[4, 'desc']] // Trier par date de soumission par défaut
    });
    
    // Fonction pour copier dans le presse-papiers
    window.copyToClipboard = function(text) {
      navigator.clipboard.writeText(text).then(function() {
        alert('Lien copié dans le presse-papiers !');
      }, function(err) {
        console.error('Erreur lors de la copie : ', err);
      });
    };

    // Ajouter dans le $(document).ready
// Initialiser les tooltips
$('[data-bs-toggle="tooltip"]').tooltip();
    
    // Charts
    
    // Views & Clicks Evolution Chart
    var viewsClicksOptions = {
      series: [{
        name: 'Vues',
        data: [12, 34, 45, 56, 33, 56, 78]
      }, {
        name: 'Clics',
        data: [5, 12, 23, 12, 8, 15, 19]
      }],
      chart: {
        type: 'area',
        height: 250,
        toolbar: {
          show: false
        },
        zoom: {
          enabled: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      colors: ['#3b82f6', '#10b981'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.2,
          stops: [0, 100]
        }
      },
      xaxis: {
        categories: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
      },
      tooltip: {
        x: {
          format: 'dd/MM/yy'
        },
      }
    };

    var viewsClicksChart = new ApexCharts(document.querySelector("#views-clicks-chart"), viewsClicksOptions);
    viewsClicksChart.render();
    
    // Device Distribution Chart
    var deviceOptions = {
      series: [
        {{ ($viewData["stats"]["devices"]["desktop"] ?? 0) }}, 
        {{ ($viewData["stats"]["devices"]["mobile"] ?? 0) }}, 
        {{ ($viewData["stats"]["devices"]["tablet"] ?? 0) }}, 
        {{ ($viewData["stats"]["devices"]["unknown"] ?? 0) }}
      ],
      chart: {
        type: 'donut',
        height: 200
      },
      labels: ['Desktop', 'Mobile', 'Tablet', 'Autres'],
      colors: ['#3b82f6', '#10b981', '#06b6d4', '#6b7280'],
      legend: {
        position: 'bottom',
        fontFamily: 'inherit',
        fontSize: '12px'
      },
      plotOptions: {
        pie: {
          donut: {
            size: '60%'
          }
        }
      }
    };

    var deviceChart = new ApexCharts(document.querySelector("#device-chart"), deviceOptions);
    deviceChart.render();
    
    // Weekday Chart
    var weekdayOptions = {
      series: [{
        name: 'Clics',
        data: [8, 12, 15, 20, 25, 30, 10]
      }],
      chart: {
        type: 'bar',
        height: 200,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          columnWidth: '60%',
          borderRadius: 3
        }
      },
      colors: ['#06b6d4'],
      xaxis: {
        categories: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
      },
      yaxis: {
        title: {
          text: 'Clics'
        }
      },
      dataLabels: {
        enabled: false
      }
    };

    var weekdayChart = new ApexCharts(document.querySelector("#weekday-chart"), weekdayOptions);
    weekdayChart.render();
    
    // Gestionnaires d'événements pour les boutons d'action
    $('.view-details').on('click', function() {
      var id = $(this).data('id');
      // Simulation de chargement des détails
      $('.submission-details-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Chargement...</p></div>');
      $('#submissionDetailsModal').modal('show');
      
      // Dans une implémentation réelle, vous feriez un appel AJAX pour récupérer les détails
      setTimeout(function() {
        $('.submission-details-content').html(`
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <h6 class="text-muted mb-1">Diffuseur</h6>
                <p>John Doe</p>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Vues déclarées</h6>
                <p>1,234</p>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Date de soumission</h6>
                <p>01/11/2025 08:30</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <h6 class="text-muted mb-1">Gain</h6>
                <p>1,234 F</p>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Statut</h6>
                <span class="badge bg-success">Terminé</span>
              </div>
            </div>
          </div>
          <hr>
          <div class="row mt-3">
            <div class="col-12">
              <h6 class="text-muted mb-2">Captures d'écran</h6>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <img src="https://via.placeholder.com/300x600" class="img-fluid rounded" alt="Capture d'écran">
                </div>
                <div class="col-md-4 mb-3">
                  <img src="https://via.placeholder.com/300x600" class="img-fluid rounded" alt="Capture d'écran">
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12">
              <h6 class="text-muted mb-2">Commentaire</h6>
              <div class="p-3 bg-light rounded">
                <p class="mb-0">Excellente campagne avec un bon taux d'engagement. Plusieurs personnes ont demandé des informations supplémentaires.</p>
              </div>
            </div>
          </div>
        `);
      }, 1000);
    });
    
    // Bouton pour approuver une soumission
    $('.approve-submission').on('click', function() {
      var id = $(this).data('id');
      if(confirm('Êtes-vous sûr de vouloir approuver cette soumission ?')) {
        // Implémentation AJAX pour approuver la soumission
        alert('Soumission approuvée avec succès !');
      }
    });
    
    // Bouton pour rejeter une soumission
    $('.reject-submission').on('click', function() {
      var id = $(this).data('id');
      if(confirm('Êtes-vous sûr de vouloir rejeter cette soumission ? Cette action ne peut pas être annulée.')) {
        // Implémentation AJAX pour rejeter la soumission
        alert('Soumission rejetée avec succès !');
      }
    });
  });
</script>
@endpush
@endsection