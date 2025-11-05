<!-- File: resources/views/influencer/profile/index.blade.php -->
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
                <li class="breadcrumb-item active">Profil</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Informations de profil -->
    <div class="col-xl-4 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <div class="mb-4">
            <div class="mt-3">
             
            </div>
          </div>
          
          <h5 class="mb-1">{{ $viewData["userObject"]->firstname ?? '' }} {{ $viewData["userObject"]->lastname ?? '' }}</h5>
          <p class="text-muted">Diffuseur de contenu</p>
          
          <div class="d-flex justify-content-center mt-3 mb-2">
            <div class="text-center px-3">
              <h5>{{ number_format($viewData["userObject"]->vuesmoyen ?? 0) }}</h5>
              <p class="text-muted mb-0">Vues moyennes</p>
            </div>
            <div class="text-center px-3 border-start">
              <h5>{{ $viewData["assignmentStats"]["completed_count"] ?? 0 }}</h5>
              <p class="text-muted mb-0">Campagnes</p>
            </div>
            
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Informations personnelles</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
<li class="py-2 border-bottom">
  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="fa fa-globe text-primary me-2"></i>
    </div>
    <div class="flex-grow-1">
      <p class="text-muted mb-0">Pays</p>
      <h6 class="mb-0">{{ $viewData["userObject"]->country ?? 'Non spécifié' }}</h6>
    </div>
  </div>
</li>
<li class="py-2 border-bottom">
  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="fa fa-map-marker-alt text-primary me-2"></i>
    </div>
    <div class="flex-grow-1">
      <p class="text-muted mb-0">Localité</p>
      <h6 class="mb-0">{{ $viewData["userObject"]->locality ?? 'Non spécifié' }}</h6>
    </div>
  </div>
</li>
            <li class="py-2 border-bottom">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <i class="fa fa-phone text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Téléphone</p>
                  <h6 class="mb-0">{{ $viewData["userObject"]->phone ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <i class="fa fa-envelope text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Email</p>
                  <h6 class="mb-0">{{ $viewData["userObject"]->email ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <i class="fa fa-calendar text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Date d'inscription</p>
                  <h6 class="mb-0">{{ isset($viewData["userObject"]->created_at) ? date('d/m/Y', strtotime($viewData["userObject"]->created_at)) : 'N/A' }}</h6>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Formulaire de modification -->
    <div class="col-xl-8">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Modifier mon profil</h5>
        </div>
        <div class="card-body">
          <form id="profileForm" method="post" action="{{ route('influencer.profile.update') }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Prénom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="firstname" value="{{ $viewData['userObject']->firstname ?? '' }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Nom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="lastname" value="{{ $viewData['userObject']->lastname ?? '' }}" required>
                </div>
              </div>
            </div>
            
<!-- Modifier ces sections dans le formulaire pour utiliser les données réelles -->
<div class="row mb-3">
  <div class="col-md-6">
    <div class="form-group mb-3">
      <label class="form-label">Pays</label>
      <select class="form-select" name="country_id">
        <option value="">Sélectionner un pays</option>
        @foreach($viewData["countries"] ?? [] as $country)
          <option value="{{ $country->id }}" {{ ($viewData['userObject']->country_id ?? '') == $country->id ? 'selected' : '' }}>
            {{ $country->name }}
          </option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group mb-3">
      <label class="form-label">Localité</label>
      <select class="form-select" name="locality_id">
        <option value="">Sélectionner une localité</option>
        @foreach($viewData["localities"] ?? [] as $locality)
          <option value="{{ $locality->id }}" {{ ($viewData['userObject']->locality_id ?? '') == $locality->id ? 'selected' : '' }}>
            {{ $locality->name }}
          </option>
        @endforeach
      </select>
    </div>
  </div>
</div>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Téléphone</label>
                  <input type="tel" class="form-control" name="phone" value="{{ $viewData['userObject']->phone ?? '' }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label class="form-label">Vues moyennes <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="vuesmoyen" value="{{ $viewData['userObject']->vuesmoyen ?? 0 }}" required min="0">
                  <div class="form-text">
                    Nombre moyen de vues par publication sur vos plateformes.
                  </div>
                </div>
              </div>
            </div>
            
<div class="row mb-4">
  <div class="col-md-12">
    <div class="form-group">
      <label class="form-label">Catégories de contenu</label>
      
      <div class="category-selector p-3 border rounded bg-light text-black">
        <div class="row">
          @foreach($viewData["categories"] ?? [] as $category)
          <div class="col-md-4 col-sm-6 mb-2">
            <div class="form-check category-item">
              <input class="form-check-input" type="checkbox" 
                     name="categories[]" 
                     value="{{ $category->id }}" 
                     id="category-{{ $category->id }}"
                     {{ isset($viewData["userCategories"]) && in_array($category->id, $viewData["userCategories"]) ? 'checked' : '' }}>
              <label class="form-check-label" for="category-{{ $category->id }}" 
                     data-bs-toggle="tooltip" 
                     title="{{ $category->name }}">
                {{ \Illuminate\Support\Str::limit($category->name, 30) }}
              </label>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      
      <div class="form-text mt-2">
        Sélectionnez les catégories dans lesquelles vous créez du contenu.
      </div>
    </div>
  </div>
</div>
            
            <div class="row mb-4">
              <div class="col-md-12">

              </div>
            </div>
            
            
            <div class="row mb-4">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label">Liens des réseaux sociaux</label>
                  <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                    <input type="text" class="form-control" name="instagram" placeholder="Nom d'utilisateur Instagram" value="{{ $viewData['user']->instagram ?? '' }}">
                  </div>
                  <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fab fa-tiktok"></i></span>
                    <input type="text" class="form-control" name="tiktok" placeholder="Nom d'utilisateur TikTok" value="{{ $viewData['user']->tiktok ?? '' }}">
                  </div>
                  <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                    <input type="text" class="form-control" name="youtube" placeholder="Chaîne YouTube" value="{{ $viewData['user']->youtube ?? '' }}">
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save me-1"></i>Enregistrer les modifications
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
    max-height: 300px;
    overflow-y: auto;
  }
  
  .category-item {
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.2s ease;
  }
  
  .category-item:hover {
    background-color: rgba(0, 123, 255, 0.1);
  }
  
  .category-item .form-check-input:checked + .form-check-label {
    color: #0d6efd;
    font-weight: 500;
  }
  
  .form-check-label {
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    display: inline-block;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize Select2
    $('.select2-multiple').select2({
      theme: 'bootstrap-5',
      placeholder: 'Sélectionnez les catégories',
      allowClear: true
    });

    // Ajouter à votre script $(document).ready
// Initialiser les tooltips pour voir les noms complets au survol
$('[data-bs-toggle="tooltip"]').tooltip();

// Ajouter une fonctionnalité pour sélectionner/désélectionner rapidement
$('.category-selector').prepend(`
  <div class="mb-3 d-flex justify-content-end">
    <button type="button" class="btn btn-sm btn-outline-primary me-2 select-all-btn">
      <i class="fa fa-check-square me-1"></i>Tout sélectionner
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary deselect-all-btn">
      <i class="fa fa-square me-1"></i>Tout désélectionner
    </button>
  </div>
`);

// Gérer les boutons de sélection/désélection
$('.select-all-btn').click(function() {
  $('.category-item input[type="checkbox"]').prop('checked', true);
});

$('.deselect-all-btn').click(function() {
  $('.category-item input[type="checkbox"]').prop('checked', false);
});
    
    // Handle profile picture change
    $('#changeProfilePicture').click(function() {
      $('#profilePictureInput').click();
    });
    
    $('#profilePictureInput').change(function() {
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
          $('.avatar-xl').attr('src', e.target.result);
        }
        
        reader.readAsDataURL(this.files[0]);
      }
    });
  });
</script>
@endpush
@endsection