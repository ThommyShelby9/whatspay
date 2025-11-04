<!-- File: resources/views/influencer/campaigns/submit.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('influencer.campaigns.show', ['id' => $viewData["assignment"]->id]) }}">{{ $viewData["assignment"]->task_name ?? 'Détails' }}</a></li>
                <li class="breadcrumb-item active">Soumettre</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulaire de soumission -->
  <div class="row">
    <div class="col-xl-8">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Soumettre les résultats pour : {{ $viewData["assignment"]->task_name ?? 'Campagne' }}</h5>
        </div>
        <div class="card-body">
          <form id="submitResultsForm" method="post" action="{{ route('influencer.campaigns.storeSubmission', ['id' => $viewData["assignment"]->id]) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row mb-4">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label">Nombre de vues <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="vues" required min="0">
                  <div class="form-text">
                    Indiquez le nombre total de vues que cette campagne a généré.
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row mb-4">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label">Preuves de diffusion <span class="text-danger">*</span></label>
                  <div class="dropzone" id="proofDropzone">
                    <div class="dz-message needsclick">
                      <div class="mb-3">
                        <i class="display-4 text-muted fa fa-cloud-upload-alt"></i>
                      </div>
                      <h5>Déposez les fichiers ici ou cliquez pour télécharger</h5>
                      <span class="text-muted">(Captures d'écran, photos, vidéos, etc.)</span>
                    </div>
                  </div>
                  <input type="hidden" name="files" id="proof-files">
                </div>
              </div>
            </div>
            
            <div class="row mb-4">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label">Commentaires (optionnel)</label>
                  <textarea class="form-control" name="comments" rows="4" placeholder="Ajoutez des commentaires ou des détails sur les résultats de la campagne..."></textarea>
                </div>
              </div>
            </div>
            
            <div class="alert alert-info">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <i class="fa fa-info-circle fa-2x me-3"></i>
                </div>
                <div>
                  <h5 class="alert-heading">Important</h5>
                  <p class="mb-0">En soumettant vos résultats, vous confirmez que les informations fournies sont exactes. La soumission sera examinée par l'annonceur avant validation finale et paiement.</p>
                </div>
              </div>
            </div>
            
            <div class="text-end mt-4">
              <a href="{{ route('influencer.campaigns.show', ['id' => $viewData["assignment"]->id]) }}" class="btn btn-light me-2">Annuler</a>
              <button type="submit" class="btn btn-success">
                <i class="fa fa-check-circle me-1"></i>Soumettre les résultats
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Résumé de la mission</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-bullhorn text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Campagne</p>
                  <h6 class="mb-0">{{ $viewData["assignment"]->task_name ?? 'N/A' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-calendar-alt text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Période</p>
                  <h6 class="mb-0">
                    {{ isset($viewData["assignment"]->task_startdate) ? date('d/m/Y', strtotime($viewData["assignment"]->task_startdate)) : 'N/A' }} 
                    - 
                    {{ isset($viewData["assignment"]->task_enddate) ? date('d/m/Y', strtotime($viewData["assignment"]->task_enddate)) : 'N/A' }}
                  </h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-money-bill-wave text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Gain prévu</p>
                  <h6 class="mb-0">{{ number_format($viewData["assignment"]->gain ?? 0) }} F</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-chart-line text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Vues attendues</p>
                  <h6 class="mb-0">{{ number_format($viewData["assignment"]->expected_views ?? 0) }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-user text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Annonceur</p>
                  <h6 class="mb-0">{{ $viewData["assignment"]->client_name ?? 'N/A' }}</h6>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Conseils</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0">
              <i class="fa fa-check-circle text-success me-2"></i>
              Fournissez des captures d'écran claires des statistiques
            </li>
            <li class="list-group-item px-0">
              <i class="fa fa-check-circle text-success me-2"></i>
              Ajoutez des photos ou vidéos montrant le contenu diffusé
            </li>
            <li class="list-group-item px-0">
              <i class="fa fa-check-circle text-success me-2"></i>
              Mentionnez les réactions/commentaires notables
            </li>
            <li class="list-group-item px-0">
              <i class="fa fa-check-circle text-success me-2"></i>
              Expliquez tout résultat extraordinaire
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script>
  $(document).ready(function() {
    Dropzone.autoDiscover = false;
    
    var myDropzone = new Dropzone("#proofDropzone", {
      url: "{{ route('influencer.campaigns.storeSubmission', ['id' => $viewData['assignment']->id]) }}", // Placeholder URL
      autoProcessQueue: false,
      uploadMultiple: true,
      addRemoveLinks: true,
      maxFiles: 10,
      acceptedFiles: "image/*,video/*,application/pdf",
      init: function() {
        var dz = this;
        
        this.on("addedfile", function(file) {
          // Update hidden input with file information
          updateFilesList();
        });
        
        this.on("removedfile", function(file) {
          // Update hidden input with file information
          updateFilesList();
        });
        
        // For demo purposes only
        this.on("error", function(file, errorMessage) {
          console.log(errorMessage);
        });
      }
    });
    
    function updateFilesList() {
      // In a real implementation, you would update the hidden field with file IDs or paths
      var files = myDropzone.files;
      var fileNames = [];
      
      for (var i = 0; i < files.length; i++) {
        fileNames.push(files[i].name);
      }
      
      document.getElementById('proof-files').value = fileNames.join(',');
    }
    
    // Submit form handler
    $('#submitResultsForm').submit(function(e) {
      e.preventDefault();
      
      // For demo purposes, show success message
      alert('Résultats soumis avec succès!');
      window.location.href = "{{ route('influencer.campaigns.assigned') }}";
    });
  });
</script>
@endpush
@endsection