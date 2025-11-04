<!-- File: resources/views/annonceur/campaigns/edit.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('announcer.campaigns.show', ['id' => $viewData["campaign"]->id]) }}">{{ $viewData["campaign"]->name }}</a></li>
                <li class="breadcrumb-item active">Modifier</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <a href="{{ route('announcer.campaigns.show', ['id' => $viewData["campaign"]->id]) }}" class="btn btn-secondary">
              <i class="fa fa-arrow-left me-2"></i>Retour
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulaire de modification -->
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Modifier la campagne</h5>
        </div>
        <div class="card-body">
          <form id="editCampaignForm" method="post" action="{{ route('announcer.campaigns.update', ['id' => $viewData["campaign"]->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Nom de la campagne <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="name" value="{{ $viewData["campaign"]->name }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Budget (F) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="budget" value="{{ $viewData["campaign"]->budget }}" required>
                  <small class="text-muted">Minimum 1000F</small>
                </div>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Date de début <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="startdate" value="{{ isset($viewData["campaign"]->startdate) ? date('Y-m-d', strtotime($viewData["campaign"]->startdate)) : '' }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="enddate" value="{{ isset($viewData["campaign"]->enddate) ? date('Y-m-d', strtotime($viewData["campaign"]->enddate)) : '' }}" required>
                </div>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-12">
                <div class="form-group mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="descriptipon" rows="3" placeholder="Décrivez l'objectif de votre campagne...">{{ $viewData["campaign"]->descriptipon }}</textarea>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Type de média <span class="text-danger">*</span></label>
                  <select class="form-select" name="media_type" required>
                    <option value="">Sélectionnez le type de média</option>
                    <option value="image" {{ $viewData["campaign"]->media_type == 'image' ? 'selected' : '' }}>Image avec légende</option>
                    <option value="image_link" {{ $viewData["campaign"]->media_type == 'image_link' ? 'selected' : '' }}>Image avec légende et lien</option>
                    <option value="text" {{ $viewData["campaign"]->media_type == 'text' ? 'selected' : '' }}>Texte simple</option>
                    <option value="video" {{ $viewData["campaign"]->media_type == 'video' ? 'selected' : '' }}>Vidéo</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 url-field">
                <div class="form-group mb-3">
                  <label class="form-label">Lien URL <span class="text-danger url-required">*</span></label>
                  <input type="url" class="form-control" name="url" value="{{ $viewData["campaign"]->url }}" placeholder="https://...">
                  <small class="text-muted">Lien vers lequel les utilisateurs seront dirigés</small>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Localité cible <span class="text-danger">*</span></label>
                  <select class="form-select select2" name="locality_id" required>
                    <option value="">Sélectionnez une localité...</option>
                    @foreach($viewData["localities"] ?? [] as $locality)
                    <option value="{{ $locality->id }}" {{ $viewData["campaign"]->locality_id == $locality->id ? 'selected' : '' }}>{{ $locality->name }}</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Région où la campagne sera diffusée</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Profession cible <span class="text-danger">*</span></label>
                  <select class="form-select select2" name="occupation_id" required>
                    <option value="">Sélectionnez une profession...</option>
                    @foreach($viewData["occupations"] ?? [] as $occupation)
                    <option value="{{ $occupation->id }}" {{ $viewData["campaign"]->occupation_id == $occupation->id ? 'selected' : '' }}>{{ $occupation->name }}</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Profession des diffuseurs ciblés</small>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-12">
                <div class="form-group mb-3">
                  <label class="form-label">Légende <span class="text-danger">*</span></label>
                  <textarea class="form-control" name="legend" rows="3" required placeholder="Texte qui accompagnera votre média...">{{ $viewData["campaign"]->legend }}</textarea>
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
                      @php
                        $selectedCategories = [];
                        foreach($viewData["campaignCategories"] ?? [] as $cat) {
                          $selectedCategories[] = $cat->id;
                        }
                      @endphp
                      
                      @foreach($viewData["categories"] ?? [] as $category)
                      <div class="col-md-4 mb-2">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="categories[]" 
                                id="category-{{ $category->id }}" value="{{ $category->id }}"
                                {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                          <label class="form-check-label" for="category-{{ $category->id }}">
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
                  <label class="form-label">Média <span class="text-danger media-required">*</span></label>
                  <div class="file-upload-container p-3 border rounded bg-light">
                    <div class="text-center mb-3">
                      <i class="display-4 text-muted fa fa-cloud-upload-alt"></i>
                      <h5 class="mt-2">Téléchargez vos fichiers</h5>
                      <p class="text-muted media-type-hint">(Images, vidéos selon le type de média choisi)</p>
                    </div>
                    
                    @if(!empty($viewData["mediaFiles"]))
                      <div class="current-media mb-3">
                        <h6 class="text-muted mb-2">Médias actuels</h6>
                        <div class="row">
                          @foreach($viewData["mediaFiles"] as $media)
                            <div class="col-md-3 mb-3">
                              <div class="card">
                                @if(isset($media['url']) && strpos($media['url'], '.mp4') !== false || strpos($media['url'], '.mov') !== false)
                                  <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="fa fa-film fa-2x text-light"></i>
                                  </div>
                                @else
                                  <img src="{{ $media['url'] ?? asset('storage/uploads/' . ($media['name'] ?? $media['file_name'] ?? '')) }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                @endif
                                <div class="card-body p-2">
                                  <p class="card-text small text-truncate">{{ $media['original_name'] ?? ($media['name'] ?? $media['file_name'] ?? 'Média') }}</p>
                                </div>
                              </div>
                            </div>
                          @endforeach
                        </div>
                        <p class="text-muted mb-3">Ces médias seront remplacés si vous en téléchargez de nouveaux.</p>
                      </div>
                    @endif
                    
                    <div class="custom-file mb-3">
                      <input type="file" class="form-control" id="campaign-files" name="campaign_files[]" multiple>
                      <small class="form-text text-muted">Vous pouvez sélectionner plusieurs fichiers à la fois.</small>
                    </div>
                    
                    <!-- Aperçu des fichiers sélectionnés -->
                    <div id="file-preview" class="row mt-3"></div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row mt-4">
              <div class="col-12 text-end">
                <a href="{{ route('announcer.campaigns.show', ['id' => $viewData["campaign"]->id]) }}" class="btn btn-light me-2">Annuler</a>
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-check-circle me-1"></i>Mettre à jour la campagne
                </button>
              </div>
            </div>
          </form>
        </div>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
      theme: 'bootstrap-5',
      placeholder: 'Sélectionnez une option',
      allowClear: true
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
      if (mediaType === 'image' || mediaType === 'image_link') {
        $('.media-type-hint').text('(Images uniquement, formats JPG, PNG, GIF)');
      } else if (mediaType === 'video') {
        $('.media-type-hint').text('(Vidéos uniquement, formats MP4, MOV)');
      } else if (mediaType === 'text') {
        $('.media-type-hint').text('(Aucun fichier nécessaire pour ce type)');
        $('.media-required').hide();
      } else {
        $('.media-type-hint').text('(Images, vidéos selon le type de média choisi)');
      }
    }).trigger('change');
    
    // Date minimum = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    // Pour l'édition, on ne restreint pas la date de début si elle est déjà dans le passé
    
    // La date de fin doit être >= date de début
    $('input[name="startdate"]').on('change', function() {
      const startDate = $(this).val();
      $('input[name="enddate"]').attr('min', startDate);
      
      if ($('input[name="enddate"]').val() < startDate) {
        $('input[name="enddate"]').val(startDate);
      }
    });
    
    // Gestion de la prévisualisation des fichiers
    $('#campaign-files').on('change', function() {
      const fileInput = this;
      const previewContainer = $('#file-preview');
      previewContainer.empty();
      
      if (fileInput.files && fileInput.files.length > 0) {
        // Afficher le nombre de fichiers sélectionnés
        const fileCount = fileInput.files.length;
        
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
            card.prepend('<div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 120px;"><i class="fa fa-film fa-2x text-white"></i></div>');
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
    
    // Validation du formulaire
    $('#editCampaignForm').submit(function(e) {
      var mediaType = $('select[name="media_type"]').val();
      
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
      
      return true;
    });
  });
</script>
@endpush
@endsection