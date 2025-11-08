@extends('admin.layout')

@section('pagecontent')
@include('alert')

<div class="container-fluid">
  <!-- Page Header -->
  <div class="row">
    <div class="col-sm-12">
      <div class="page-title-box">
        <div class="row">
          <div class="col">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item active">{{$pagetilte}}</li>
            </ol>
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
              <i class="fa fa-filter me-2"></i>Filtrer les diffuseurs
            </button>
            <a href="#" class="btn btn-success ms-2">
              <i class="fa fa-plus me-2"></i>Ajouter
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Summary Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-primary-subtle">
        <div class="card-body p-3">
          <div class="row align-items-center">
            <div class="col-auto">
              <div class="avatar-md bg-white rounded-circle">
                <i class="fa fa-eye fa-2x text-primary d-flex justify-content-center align-items-center h-100"></i>
              </div>
            </div>
            <div class="col">
              <h5 class="mb-1">Total des vues moyennes journalières</h5>
              <h2 class="mb-0">{{number_format($viewData["vuesmoyen"])}}</h2>
            </div>
            <div class="col-auto">
              <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                  <li><a class="dropdown-item" href="#" id="export-excel"><i class="fa fa-file-excel me-2"></i>Excel</a></li>
                  <li><a class="dropdown-item" href="#" id="export-pdf"><i class="fa fa-file-pdf me-2"></i>PDF</a></li>
                  <li><a class="dropdown-item" href="#" id="export-csv"><i class="fa fa-file-csv me-2"></i>CSV</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtres (Collapsible) -->
  <div class="collapse mb-4" id="collapseFilters">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Options de filtrage avancées</h5>
      </div>
      <div class="card-body">
        <form class="form theme-form" method="post" action="" enctype="multipart/form-data">
          <div class="row">
            <!-- Column 1: Location -->
            <div class="col-md-4">
              <h6 class="text-primary mb-3">Localisation</h6>
              <div class="mb-3">
                <label class="form-label">Pays de résidence</label>
                <select class="form-select select2" id="filtre_country" name="filtre_country">
                  <option value="">Tous les pays</option>
                  @foreach($viewData["countries"] as $item)
                  <option value="{{$item->id}}"
                          @if($item->id == $viewData["filtre_country"]) selected @endif
                  >{{$item->name}} {{$item->emoji}}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Localité de résidence</label>
                <select class="form-select select2" id="filtre_locality" name="filtre_locality">
                  <option value="">Toutes les localités</option>
                  @if($viewData["filtre_country"] != "")
                    @foreach($viewData["localities"] as $item)
                      @if($item->country_id == $viewData["filtre_country"])
                      <option value="{{$item->id}}"
                              @if($item->id == $viewData["filtre_locality"]) selected @endif
                        >{{$item->name}}</option>
                      @endif
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            
            <!-- Column 2: Profile -->
            <div class="col-md-4">
              <h6 class="text-primary mb-3">Profil</h6>
              <div class="mb-3">
                <label class="form-label">Profession</label>
                <select class="form-select select2-multiple" id="filtre_occupation" name="filtre_occupation[]" multiple>
                  @foreach($viewData["occupations"] as $item)
                  <option value="{{$item->id}}"
                          @foreach($viewData["filtre_occupation"] as $item2)
                            @if($item->id == $item2) selected @endif
                          @endforeach
                  >{{$item->name}}</option>
                  @endforeach
                  <option value="other"
                          @foreach($viewData["filtre_occupation"] as $item2)
                            @if($item2 == "other") selected @endif
                          @endforeach
                  >Autre</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Niveau d'étude</label>
                <select class="form-select select2-multiple" id="filtre_study" name="filtre_study[]" multiple>
                  @foreach($viewData["studies"] as $item)
                  <option value="{{$item->id}}"
                          @foreach($viewData["filtre_study"] as $item2)
                            @if($item->id == $item2) selected @endif
                          @endforeach
                  >{{$item->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Langues</label>
                <select class="form-select select2-multiple" id="filtre_lang" name="filtre_lang[]" multiple>
                  @foreach($viewData["langs"] as $item)
                  <option value="{{$item->id}}"
                          @foreach($viewData["filtre_lang"] as $item2)
                            @if($item->id == $item2) selected @endif
                          @endforeach
                  >{{$item->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <!-- Column 3: Content -->
            <div class="col-md-4">
              <h6 class="text-primary mb-3">Contenu</h6>
              <div class="mb-3">
                <label class="form-label">Catégories de publication</label>
                <select class="form-select select2-multiple" id="filtre_category" name="filtre_category[]" multiple>
                  @foreach($viewData["categories"] as $item)
                  <option value="{{$item->id}}"
                          @foreach($viewData["filtre_category"] as $item2)
                            @if($item->id == $item2) selected @endif
                          @endforeach
                  >{{ Str::limit($item->name, 15) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Types de contenu dominants</label>
                <select class="form-select select2-multiple" id="filtre_contenu" name="filtre_contenu[]" multiple>
                  @foreach($viewData["contenttypes"] as $item)
                  <option value="{{$item->id}}"
                          @foreach($viewData["filtre_contenu"] as $item2)
                            @if($item->id == $item2) selected @endif
                          @endforeach
                  >{{$item->name}}</option>
                  @endforeach
                </select>
              </div>
              
              <!-- Range filter for views -->
              <div class="mb-3">
                <label class="form-label">Plage de vues moyennes</label>
                <div class="row">
                  <div class="col-6">
                    <div class="input-group">
                      <span class="input-group-text">Min</span>
                      <input type="number" class="form-control" id="filtre_vues_min" name="filtre_vues_min" 
                             value="{{ $viewData['filtre_vues_min'] ?? '' }}" placeholder="Min">
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="input-group">
                      <span class="input-group-text">Max</span>
                      <input type="number" class="form-control" id="filtre_vues_max" name="filtre_vues_max"
                             value="{{ $viewData['filtre_vues_max'] ?? '' }}" placeholder="Max">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Filter Actions -->
          <div class="row mt-3">
            <div class="col-12 d-flex justify-content-end">
              <button type="reset" class="btn btn-light me-2">Réinitialiser</button>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-filter me-1"></i>Appliquer les filtres
              </button>
            </div>
          </div>
          
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        </form>
      </div>
    </div>
  </div>

  <!-- Users Table -->
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0">{{$pagetilte}}</h5>
        <small class="text-muted">{{$pagecardtilte}}</small>
      </div>
      <div>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-file-export me-1"></i>Exporter
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" id="export-excel">Excel</a></li>
            <li><a class="dropdown-item" href="#" id="export-pdf">PDF</a></li>
            <li><a class="dropdown-item" href="#" id="export-csv">CSV</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="display table table-striped" id="items_datatable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Identité</th>
            <th>Profession</th>
            <th>Adresse</th>
            <th>Contact</th>
            <th>Vues moy.</th>
            <th>Étude & Langue</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($viewData["items"] as $item)
          <tr>
            <td>{{$item->id}}</td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar-sm bg-light-primary rounded-circle text-center me-2">
                  <span class="font-size-18">{{substr($item->firstname, 0, 1)}}</span>
                </div>
                <div>
                  <h6 class="mb-0">{{$item->firstname}} {{$item->lastname}}</h6>
                </div>
              </div>
            </td>
            <td>
              @if(!empty($item->profession))
                <span>{{ $item->profession }}</span>
              @else
                <span class="badge bg-danger">Non spécifiée</span>
              @endif
              @if(!empty($item->occupation))
                <p class="text-muted mb-0">{{ $item->occupation }}</p>
              @endif
            </td>
            <td>
              <div class="d-flex flex-column">
                <span><i class="fa fa-globe-africa text-muted me-1"></i> {{ $item->country }}</span>
                @if(!empty($item->locality))
                <span class="text-muted"><i class="fa fa-map-marker-alt me-1"></i> {{ $item->locality }}</span>
                @endif
              </div>
            </td>
            <td>
              <div class="d-flex flex-column">
                <span><i class="fa fa-envelope text-muted me-1"></i> {{ $item->email }}</span>
                <span><i class="fa fa-phone text-muted me-1"></i> {{ $item->phone }}</span>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <span>{{ number_format($item->vuesmoyen) }}</span>
              </div>
            </td>
            <td>
              <div class="d-flex flex-column">
                <span><i class="fa fa-graduation-cap text-muted me-1"></i> {{ $item->study }}</span>
                <span><i class="fa fa-language text-muted me-1"></i> {{ $item->lang }}</span>
              </div>
            </td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Actions
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#"><i class="fa fa-eye me-2"></i>Voir</a></li>
                  <li><a class="dropdown-item" href="#"><i class="fa fa-edit me-2"></i>Modifier</a></li>
                  <li><a class="dropdown-item" href="#"><i class="fa fa-chart-line me-2"></i>Statistiques</a></li>
                  <li><a class="dropdown-item" href="#"><i class="fa fa-comment me-2"></i>Contacter</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $item->id }}" data-name="{{ $item->firstname }} {{ $item->lastname }}"><i class="fa fa-trash me-2"></i>Supprimer</a></li>
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th>ID</th>
            <th>Identité</th>
            <th>Profession</th>
            <th>Adresse</th>
            <th>Contact</th>
            <th>Vues moy.</th>
            <th>Étude & Langue</th>
            <th>Actions</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmer la suppression</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Êtes-vous sûr de vouloir supprimer le diffuseur <strong id="delete-user-name"></strong> ?</p>
        <p class="text-danger"><i class="fa fa-exclamation-triangle me-2"></i>Cette action est irréversible et supprimera toutes les données associées à ce diffuseur.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger" id="confirm-delete">Supprimer</button>
      </div>
    </div>
  </div>
</div>

<!-- Hidden inputs for JavaScript -->
<input type="hidden" name="bjId" id="bjId" value="{{ $viewData["bjId"] }}">
<input type="hidden" name="localitiesJson" id="localitiesJson" value="{{ $viewData["localitiesJson"] }}">
<input type="hidden" name="countriesJson" id="countriesJson" value="{{ $viewData["countriesJson"] }}">
<input type="hidden" name="contenttypesJson" id="contenttypesJson" value="{{ $viewData["contenttypesJson"] }}">
<input type="hidden" name="studiesJson" id="studiesJson" value="{{ $viewData["studiesJson"] }}">
<input type="hidden" name="categoriesJson" id="categoriesJson" value="{{ $viewData["categoriesJson"] }}">

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize DataTable
  const dataTable = $('#items_datatable').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: [
      'copyHtml5',
      'excelHtml5',
      'csvHtml5',
      'pdfHtml5'
    ],
    lengthMenu: [
      [10, 20, 50, -1],
      [10, 20, 50, 'Tous']
    ],
    pageLength: 20, // Affiche 20 données par page
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
    }
  });

  // Export buttons
  document.getElementById('export-excel').addEventListener('click', function() {
    dataTable.button('.buttons-excel').trigger();
  });
  
  document.getElementById('export-pdf').addEventListener('click', function() {
    dataTable.button('.buttons-pdf').trigger();
  });
  
  document.getElementById('export-csv').addEventListener('click', function() {
    dataTable.button('.buttons-csv').trigger();
  });

  // Country and locality filter interaction
  const countriesJson = JSON.parse(document.getElementById('countriesJson').value);
  const localitiesJson = JSON.parse(document.getElementById('localitiesJson').value);
  const countrySelect = document.getElementById('filtre_country');
  const localitySelect = document.getElementById('filtre_locality');
  
  countrySelect.addEventListener('change', function() {
    const selectedCountryId = this.value;
    
    // Clear the locality dropdown
    localitySelect.innerHTML = '<option value="all">Toutes les localités</option>';
    
    if (selectedCountryId !== 'all') {
      // Filter localities by country and add them to the dropdown
      const filteredLocalities = localitiesJson.filter(locality => locality.country_id == selectedCountryId);
      
      filteredLocalities.forEach(locality => {
        const option = document.createElement('option');
        option.value = locality.id;
        option.textContent = locality.name;
        localitySelect.appendChild(option);
      });
    }
  });
  
  // Delete modal
  const deleteModal = document.getElementById('deleteModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('delete-user-name').textContent = name;
    });
  }
  
  // Initialize Select2 for dropdowns if available
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2').select2({
      theme: 'bootstrap-5'
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le jeton CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    // 1. Gestion des boutons du dropdown
    document.querySelectorAll('.dropdown-menu a.dropdown-item').forEach(item => {
        // Pour les boutons Statistiques et Campagnes, rediriger vers la bonne URL
        if (item.innerHTML.includes('Statistiques') || item.innerHTML.includes('Campagnes')) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Récupérer l'ID de l'utilisateur
                const dropdown = this.closest('.dropdown');
                const button = dropdown.querySelector('button');
                const userId = button.id.split('-')[1] || button.getAttribute('data-user-id');
                
                // Déterminer l'action
                const action = this.innerHTML.includes('Statistiques') ? 'stats' : 'campaigns';
                
                // Rediriger vers l'URL avec l'action et l'ID
                const currentUrl = new URL(window.location.href);
                const baseUrl = currentUrl.pathname.split('?')[0];
                window.location.href = `${baseUrl}?action=${action}&id=${userId}`;
            });
        }
    });
    
    // 2. Gestion du changement de statut (toggle)
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const enabled = this.checked ? 1 : 0;
            const statusLabel = this.closest('.form-check').querySelector('.status-label') || 
                               this.nextElementSibling;
            
            // Mise à jour visuelle immédiate
            if (statusLabel) {
                if (this.checked) {
                    statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                }
            }
            
            // Envoyer la requête AJAX
            fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'toggle_status',
                    user_id: userId,
                    enabled: enabled
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Réinitialiser le toggle en cas d'erreur
                    this.checked = !this.checked;
                    if (statusLabel) {
                        if (this.checked) {
                            statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                        } else {
                            statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                        }
                    }
                    
                    // Afficher l'erreur
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Réinitialiser le toggle en cas d'erreur
                this.checked = !this.checked;
                if (statusLabel) {
                    if (this.checked) {
                        statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                    } else {
                        statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                    }
                }
                
                alert('Une erreur est survenue lors de la modification du statut');
            });
        });
    });
    
    // 3. Configuration du modal de suppression
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const userNameSpan = this.querySelector('#delete-user-name');
            const userIdInput = this.querySelector('#delete-user-id');
            
            if (userNameSpan) userNameSpan.textContent = userName;
            if (userIdInput) userIdInput.value = userId;
            
            // Configurer l'action du formulaire
            const form = this.querySelector('form#delete-user-form');
            if (form) form.action = window.location.pathname;
        });
    }
    
    // 4. Interaction entre les filtres pays et localités
    const countrySelect = document.getElementById('filtre_country');
    const localitySelect = document.getElementById('filtre_locality');
    
    if (countrySelect && localitySelect) {
        countrySelect.addEventListener('change', function() {
            const selectedCountryId = this.value;
            
            // Vider la liste des localités
            localitySelect.innerHTML = '<option value="all">Toutes les localités</option>';
            
            if (selectedCountryId !== 'all' && selectedCountryId !== '') {
                try {
                    // Récupérer les données de localités depuis l'élément caché
                    const localitiesJson = document.getElementById('localitiesJson');
                    if (localitiesJson) {
                        const localities = JSON.parse(localitiesJson.value);
                        
                        // Filtrer et ajouter les localités correspondant au pays sélectionné
                        localities
                            .filter(locality => locality.country_id == selectedCountryId)
                            .forEach(locality => {
                                const option = document.createElement('option');
                                option.value = locality.id;
                                option.textContent = locality.name;
                                localitySelect.appendChild(option);
                            });
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement des localités:', error);
                }
            }
        });
    }
});
</script>
@endpush
@endsection