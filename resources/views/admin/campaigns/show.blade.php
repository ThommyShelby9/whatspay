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
                <li class="breadcrumb-item"><a href="{{ route('admin.tasks') }}">Campagnes</a></li>
                <li class="breadcrumb-item active">Détails</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <a href="{{ route('admin.tasks') }}" class="btn btn-secondary">
              <i class="fa fa-arrow-left me-2"></i>Retour
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-12">
      <!-- Affichage des détails -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Détails de la campagne</h5>
          <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCampaignModal">
              <i class="fas fa-edit me-1"></i>Modifier
            </button>
            <button type="button" class="btn btn-danger ms-2">
              <i class="fas fa-trash-alt me-1"></i>Supprimer
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <h6 class="text-uppercase text-muted mb-2">Informations générales</h6>
              <div class="row mb-3">
                <div class="col-md-4 fw-bold">Nom:</div>
                <div class="col-md-8">{{ $task->name }}</div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4 fw-bold">Budget:</div>
                <div class="col-md-8">{{ number_format($task->budget, 0, ',', ' ') }} F</div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4 fw-bold">Statut:</div>
                <div class="col-md-8">
                  @php
                    $statusClasses = [
                      'PENDING' => 'bg-warning',
                      'APPROVED' => 'bg-success',
                      'REJECTED' => 'bg-danger',
                      'PAID' => 'bg-info',
                      'CLOSED' => 'bg-secondary'
                    ];
                    $statusClass = $statusClasses[$task->status] ?? 'bg-secondary';
                  @endphp
                  <span class="badge {{ $statusClass }}">{{ $task->status }}</span>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4 fw-bold">Période:</div>
                <div class="col-md-8">
                  Du {{ \Carbon\Carbon::parse($task->startdate)->format('d/m/Y') }} 
                  au {{ \Carbon\Carbon::parse($task->enddate)->format('d/m/Y') }}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase text-muted mb-2">Détails média</h6>
              <div class="row mb-3">
                <div class="col-md-4 fw-bold">Type de média:</div>
                <div class="col-md-8 text-black">
                  @php
                    $mediaTypes = [
                      'image' => 'Image avec légende',
                      'image_link' => 'Image avec légende et lien',
                      'text' => 'Texte simple',
                      'video' => 'Vidéo'
                    ];
                  @endphp
                  {{ $mediaTypes[$task->media_type] ?? $task->media_type }}
                </div>
              </div>
              @if($task->url)
              <div class="row mb-3 text-black">
                <div class="col-md-4 fw-bold">URL:</div>
                <div class="col-md-8">
                  <a href="{{ $task->url }}" target="_blank">{{ $task->url }}</a>
                </div>
              </div>
              @endif
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-12">
              <h6 class="text-uppercase text-muted mb-2">Description</h6>
              <div class="p-3 bg-light rounded text-black">
                {{ $task->descriptipon ?? 'Aucune description fournie' }}
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-12">
              <h6 class="text-uppercase text-muted mb-2">Légende</h6>
              <div class="p-3 bg-light rounded text-black">
                {{ $task->legend ?? 'Aucune légende fournie' }}
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6">
              <h6 class="text-uppercase text-muted mb-2">Localités ciblées</h6>
              <div class="p-3 bg-light rounded text-black">
                @if(count($task->localities) > 0)
                  <ul class="list-unstyled mb-0">
                    @foreach($task->localities as $locality)
                      <li><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $locality->name }}</li>
                    @endforeach
                  </ul>
                @else
                  Aucune localité spécifiée
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase text-muted mb-2">Professions ciblées</h6>
              <div class="p-3 bg-light rounded text-black">
                @if(count($task->occupations) > 0)
                  <ul class="list-unstyled mb-0">
                    @foreach($task->occupations as $occupation)
                      <li><i class="fas fa-briefcase text-primary me-2"></i>{{ $occupation->name }}</li>
                    @endforeach
                  </ul>
                @else
                  Aucune profession spécifiée
                @endif
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6">
              <h6 class="text-uppercase text-muted mb-2">Catégories</h6>
              <div>
                @if(count($task->categories) > 0)
                  @foreach($task->categories as $category)
                    <span class="badge bg-info me-2 mb-1">{{ $category->name }}</span>
                  @endforeach
                @else
                  Aucune catégorie spécifiée
                @endif
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <h6 class="text-uppercase text-muted mb-2">Médias</h6>
              <div class="row">
@if($viewData["task"]->files)
  @php
    $filesData = $viewData["task"]->files;
    // Si la chaîne commence par [ et se termine par ], c'est probablement du JSON
    if (is_string($filesData) && substr(trim($filesData), 0, 1) === '[' && substr(trim($filesData), -1) === ']') {
      $files = json_decode($filesData, true);
      // Vérifier si le décodage a réussi
      if (json_last_error() !== JSON_ERROR_NONE) {
        $files = [];
      }
    } else {
      $files = [];
    }
  @endphp
  
  @if(count($files) > 0)
    @foreach($files as $file)
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          @if(isset($file['mime']) && strpos($file['mime'], 'image') !== false)
            <img src="{{ asset($file['path']) }}" class="card-img-top" style="height: 160px; object-fit: cover;">
          @elseif(isset($file['mime']) && strpos($file['mime'], 'video') !== false)
            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 160px;">
              <i class="fas fa-film fa-2x text-white"></i>
            </div>
          @else
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
              <i class="fas fa-file fa-2x text-muted"></i>
            </div>
          @endif
          <div class="card-body p-2">
            <p class="card-text small text-truncate mb-0">{{ $file['original_name'] ?? ($file['name'] ?? 'Fichier') }}</p>
            @if(isset($file['path']))
              <a href="{{ asset($file['path']) }}" target="_blank" class="btn btn-sm btn-light mt-2">
                <i class="fas fa-eye me-1"></i>Voir
              </a>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  @else
    <div class="col-12">
      <div class="alert alert-light">Aucun média interprétable pour cette campagne</div>
    </div>
  @endif
@else
  <div class="col-12">
    <div class="alert alert-light">Aucun média attaché à cette campagne</div>
  </div>
@endif
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Section Statistiques -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Statistiques de la campagne</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 mb-3">
              <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                  <div class="display-4 text-primary mb-2">{{ $stats['views'] ?? 0 }}</div>
                  <h6 class="text-uppercase text-muted">Vues totales</h6>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                  <div class="display-4 text-success mb-2">{{ $stats['clicks'] ?? 0 }}</div>
                  <h6 class="text-uppercase text-muted">Clics</h6>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                  <div class="display-4 text-info mb-2">{{ $stats['influencers'] ?? 0 }}</div>
                  <h6 class="text-uppercase text-muted">Diffuseurs</h6>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                  <div class="display-4 text-warning mb-2">{{ $stats['ctr'] ?? '0%' }}</div>
                  <h6 class="text-uppercase text-muted">Taux de clic</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Section Diffuseurs -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Diffuseurs assignés</h5>
        </div>
        <div class="card-body">
          @if(count($assignments ?? []) > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Diffuseur</th>
                    <th>Date d'attribution</th>
                    <th>Statut</th>
                    <th>Vues</th>
                    <th>Date de soumission</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($assignments as $assignment)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-2 bg-primary text-white">
                            {{ substr($assignment->agent_firstname, 0, 1) }}{{ substr($assignment->agent_lastname, 0, 1) }}
                          </div>
                          <div>
                            <div>{{ $assignment->agent_firstname }} {{ $assignment->agent_lastname }}</div>
                            <small class="text-muted">{{ $assignment->agent_email }}</small>
                          </div>
                        </div>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($assignment->assignment_date)->format('d/m/Y H:i') }}</td>
                      <td>
                        @php
                          $statusClasses = [
                            'PENDING' => 'bg-warning',
                            'COMPLETED' => 'bg-success',
                            'REJECTED' => 'bg-danger'
                          ];
                          $statusClass = $statusClasses[$assignment->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $assignment->status }}</span>
                      </td>
                      <td>{{ $assignment->vues }}</td>
                      <td>{{ $assignment->submission_date ? \Carbon\Carbon::parse($assignment->submission_date)->format('d/m/Y H:i') : '-' }}</td>
                      <td>
                        <button class="btn btn-sm btn-light">
                          <i class="fas fa-eye me-1"></i>Voir
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              Aucun diffuseur n'est encore assigné à cette campagne.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de modification -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" aria-labelledby="editCampaignModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editCampaignModalLabel">Modifier la campagne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editCampaignForm" method="post" action="{{ route('admin.campaigns.update', $task->id) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Nom de la campagne <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ $task->name }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Budget (F) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="budget" value="{{ $task->budget }}" required>
                <small class="text-muted">Minimum 1000F</small>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Date de début <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="startdate" value="{{ \Carbon\Carbon::parse($task->startdate)->format('Y-m-d') }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="enddate" value="{{ \Carbon\Carbon::parse($task->enddate)->format('Y-m-d') }}" required>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Décrivez l'objectif de votre campagne...">{{ $task->descriptipon }}</textarea>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Type de média <span class="text-danger">*</span></label>
                <select class="form-select" name="media_type" required>
                  <option value="">Sélectionnez le type de média</option>
                  <option value="image" {{ $task->media_type == 'image' ? 'selected' : '' }}>Image avec légende</option>
                  <option value="image_link" {{ $task->media_type == 'image_link' ? 'selected' : '' }}>Image avec légende et lien</option>
                  <option value="text" {{ $task->media_type == 'text' ? 'selected' : '' }}>Texte simple</option>
                  <option value="video" {{ $task->media_type == 'video' ? 'selected' : '' }}>Vidéo</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 url-field" style="{{ $task->media_type == 'image_link' ? '' : 'display: none;' }}">
              <div class="form-group mb-3">
                <label class="form-label">Lien URL <span class="text-danger url-required">*</span></label>
                <input type="url" class="form-control" name="url" value="{{ $task->url }}" placeholder="https://...">
                <small class="text-muted">Lien vers lequel les utilisateurs seront dirigés</small>
              </div>
            </div>
          </div>

          <!-- Localités cibles -->
          <div class="form-group mb-3">
            <label class="form-label">Localités cibles <span class="text-danger">*</span></label>
            <select class="form-select select2-modal" name="localities[]" multiple required>
              @foreach($viewData["localities"] ?? [] as $locality)
                <option value="{{ $locality->id }}" {{ in_array($locality->id, $task->localities->pluck('id')->toArray()) ? 'selected' : '' }}>
                  {{ $locality->name }}
                </option>
              @endforeach
            </select>
            <small class="text-muted">Régions où la campagne sera diffusée</small>
          </div>

          <!-- Professions cibles -->
          <div class="form-group mb-3">
            <label class="form-label">Professions cibles <span class="text-danger">*</span></label>
            <select class="form-select select2-modal" name="occupations[]" multiple required>
              @foreach($viewData["occupations"] ?? [] as $occupation)
                <option value="{{ $occupation->id }}" {{ in_array($occupation->id, $task->occupations->pluck('id')->toArray()) ? 'selected' : '' }}>
                  {{ $occupation->name }}
                </option>
              @endforeach
            </select>
            <small class="text-muted">Professions des diffuseurs ciblés</small>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label class="form-label">Légende <span class="text-danger">*</span></label>
                <textarea class="form-control" name="legend" rows="3" required placeholder="Texte qui accompagnera votre média...">{{ $task->legend }}</textarea>
                <small class="text-muted">Ce texte sera affiché avec votre média dans les statuts WhatsApp</small>
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label class="form-label">Catégories</label>
                <div class="category-selector border rounded p-3">
                  <div class="row">
                    @foreach($viewData["categories"] ?? [] as $category)
                      <div class="col-md-4 mb-2">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="categories[]" 
                                id="edit-category-{{ $category->id }}" value="{{ $category->id }}"
                                {{ in_array($category->id, $task->categories->pluck('id')->toArray()) ? 'checked' : '' }}>
                          <label class="form-check-label" for="edit-category-{{ $category->id }}">
                            {{ $category->name }}
                          </label>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
                <small class="text-muted">Sélectionnez une ou plusieurs catégories</small>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label class="form-label">Médias actuels</label>
                <div class="row">
@if($viewData["task"]->files)
  @php
    $filesData = $viewData["task"]->files;
    // Si la chaîne commence par [ et se termine par ], c'est probablement du JSON
    if (is_string($filesData) && substr(trim($filesData), 0, 1) === '[' && substr(trim($filesData), -1) === ']') {
      $files = json_decode($filesData, true);
      // Vérifier si le décodage a réussi
      if (json_last_error() !== JSON_ERROR_NONE) {
        $files = [];
      }
    } else {
      $files = [];
    }
  @endphp
  
  @if(count($files) > 0)
    @foreach($files as $index => $file)
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          @if(isset($file['mime']) && strpos($file['mime'], 'image') !== false)
            <img src="{{ asset($file['path']) }}" class="card-img-top" style="height: 120px; object-fit: cover;">
          @elseif(isset($file['mime']) && strpos($file['mime'], 'video') !== false)
            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 120px;">
              <i class="fas fa-film fa-2x text-white"></i>
            </div>
          @else
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
              <i class="fas fa-file fa-2x text-muted"></i>
            </div>
          @endif
          <div class="card-body p-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="keep_files[]" value="{{ $index }}" id="keep-file-{{ $index }}" checked>
              <label class="form-check-label" for="keep-file-{{ $index }}">
                Conserver
              </label>
            </div>
            <p class="card-text small text-truncate mb-0">{{ $file['original_name'] ?? ($file['name'] ?? 'Fichier') }}</p>
          </div>
        </div>
      </div>
    @endforeach
  @else
    <div class="col-12">
      <div class="alert alert-light">Aucun média interprétable pour cette campagne</div>
    </div>
  @endif
@else
  <div class="col-12">
    <div class="alert alert-light">Aucun média attaché à cette campagne</div>
  </div>
@endif
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label class="form-label">Ajouter de nouveaux médias</label>
                <div class="p-3 border rounded bg-light text-center">
                  <div class="mb-3">
                    <i class="display-4 text-muted fa fa-cloud-upload-alt"></i>
                  </div>
                  <h5>Sélectionnez les fichiers à ajouter</h5>
                  <p class="text-muted media-type-hint-modal">(Images, vidéos selon le type de média choisi)</p>
                  
                  <div class="mt-3">
                    <input type="file" id="edit-campaign-files" name="new_campaign_files[]" class="form-control" multiple accept="image/*,video/*">
                  </div>
                  
                  <div id="edit-file-preview" class="row mt-3">
                    <!-- Les aperçus des fichiers seront affichés ici -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
  .category-selector {
    max-height: 200px;
    overflow-y: auto;
    background-color: #f8f9fa;
  }
  
  .form-check {
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.2s;
  }
  
  .form-check:hover {
    background-color: #e9ecef;
  }
  
  .select2-container .select2-selection--single {
    height: 38px;
    padding: 5px;
  }
  
  .avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  // Initialize Select2 pour la vue principale
  $('.select2').select2({
    theme: 'bootstrap-5',
    placeholder: 'Sélectionnez une ou plusieurs options',
    allowClear: true
  });
  
  // Initialiser Select2 spécifiquement pour le modal
  $('#editCampaignModal').on('shown.bs.modal', function () {
    $('.select2-modal').select2({
      theme: 'bootstrap-5',
      placeholder: 'Sélectionnez une ou plusieurs options',
      allowClear: true,
      dropdownParent: $('#editCampaignModal')
    });
  });
  
  // Afficher/masquer le champ URL selon le type de média
  $('select[name="media_type"]').on('change', function() {
    var mediaType = $(this).val();
    
    // Gestion du champ URL
    if (mediaType === 'image_link') {
      $('.url-field').show();
      $('input[name="url"]').attr('required', true);
      $('.url-required').show();
    } else {
      $('.url-field').hide();
      $('input[name="url"]').attr('required', false);
      $('.url-required').hide();
    }
    
    // Mise à jour du texte d'aide pour le média
    updateMediaTypeHint(mediaType, '.media-type-hint-modal');
  });
  
  // Fonction pour mettre à jour l'indication du type de média
  function updateMediaTypeHint(mediaType, selector) {
    if (mediaType === 'image' || mediaType === 'image_link') {
      $(selector).text('(Images uniquement, formats JPG, PNG, GIF)');
    } else if (mediaType === 'video') {
      $(selector).text('(Vidéos uniquement, formats MP4, MOV)');
    } else if (mediaType === 'text') {
      $(selector).text('(Aucun fichier nécessaire pour ce type)');
    } else {
      $(selector).text('(Images, vidéos selon le type de média choisi)');
    }
  }
  
  // La date de fin doit être >= date de début
  $('input[name="startdate"]').on('change', function() {
    const startDate = $(this).val();
    $('input[name="enddate"]').attr('min', startDate);
    
    if ($('input[name="enddate"]').val() < startDate) {
      $('input[name="enddate"]').val(startDate);
    }
  });
  
  // Gestion de la prévisualisation des fichiers
  $('#edit-campaign-files').on('change', function() {
    const fileInput = this;
    const previewContainer = $('#edit-file-preview');
    previewContainer.empty();
    
    if (fileInput.files && fileInput.files.length > 0) {
      // Créer la prévisualisation pour chaque fichier
      Array.from(fileInput.files).forEach(function(file, index) {
        // Créer l'élément d'aperçu
        const previewItem = $('<div class="col-md-3 mb-3"></div>');
        const card = $('<div class="card h-100"></div>');
        const cardBody = $('<div class="card-body p-2"></div>');
        
        // Si c'est une image, afficher une miniature
        if (file.type.match('image.*')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            card.prepend(`<img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">`);
          };
          reader.readAsDataURL(file);
        } else if (file.type.match('video.*')) {
          // Pour les vidéos, afficher une icône
          card.prepend('<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 120px;"><i class="fa fa-film fa-2x text-white"></i></div>');
        } else {
          // Pour les autres types de fichiers
          card.prepend('<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;"><i class="fa fa-file fa-2x text-muted"></i></div>');
        }
        
        // Ajouter le nom et la taille du fichier
        cardBody.append(`<p class="card-text small text-truncate mb-0">${file.name}</p>`);
        cardBody.append(`<p class="card-text small text-muted">${formatFileSize(file.size)}</p>`);
        
        card.append(cardBody);
        previewItem.append(card);
        previewContainer.append(previewItem);
      });
    }
  });
  
  // Fonction utilitaire pour formater la taille des fichiers
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // Validation du formulaire d'édition avant soumission
  $('#editCampaignForm').submit(function(e) {
    const mediaType = $(this).find('select[name="media_type"]').val();
    
    // Vérifier si un type de média a été sélectionné
    if (!mediaType) {
      e.preventDefault();
      alert('Veuillez sélectionner un type de média.');
      return false;
    }
    
    // Vérifier que l'URL est renseignée pour le type image_link
    if (mediaType === 'image_link' && !$('input[name="url"]').val()) {
      e.preventDefault();
      alert('Veuillez renseigner une URL pour ce type de média.');
      return false;
    }
    
    // Si tout est valide
    return true;
  });
});
</script>
@endpush
@endsection